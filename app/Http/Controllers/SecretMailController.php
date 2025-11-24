<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecretMessage;
use App\Models\User;
use App\Services\Stego\StegoService;
use App\Services\Crypto\SuperEncryptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SecretMailController extends Controller
{
    /**
     * Tampilkan halaman secret messages inbox
     */
    public function index()
    {
        // Ambil semua secret messages yang diterima user
        $messages = SecretMessage::where('receiver_id', Auth::id())
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->get();

        $decryptedMessages = $messages->map(function ($msg) {
            $decryptedText = null;
            
            // Ekstrak pesan dari gambar menggunakan steganografi
            if ($msg->stego_image_path) {
                try {
                    $imagePath = storage_path('app/public/' . $msg->stego_image_path);
                    
                    if (file_exists($imagePath)) {
                        // Ekstrak encrypted message dari gambar
                        $encryptedMessage = StegoService::extractMessage($imagePath);
                        
                        // Decrypt pesan menggunakan private key receiver
                        $currentUser = Auth::user();
                        if (!$currentUser->ecc_private_key) {
                            $decryptedText = 'Private key tidak ditemukan - tidak bisa dekripsi.';
                        } else {
                            $decryptedText = SuperEncryptionService::decrypt(
                                $encryptedMessage, 
                                $currentUser->ecc_private_key
                            );
                        }
                    } else {
                        $decryptedText = 'Gambar tidak ditemukan.';
                    }
                } catch (\Exception $e) {
                    $decryptedText = 'Error ekstraksi/dekripsi: ' . $e->getMessage();
                }
            }

            return [
                'id' => $msg->id,
                'from' => $msg->sender->name ?? 'Unknown',
                'message' => $decryptedText,
                'image_path' => $msg->stego_image_path,
                'time' => $msg->created_at->toDateTimeString(),
                'is_secret' => true,
                'has_file' => true, // Secret mail selalu punya gambar
            ];
        });

        return view('dashboard.secret', compact('decryptedMessages'));
    }

    /**
     * Kirim secret message (embed ke gambar)
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000', // Batasi panjang pesan untuk stego
            'image' => 'required|image|mimes:jpg,jpeg|max:2048', // Hanya JPEG untuk stego
        ], [
            'message.required' => 'Message is required for secret mail.',
            'message.max' => 'Message is too long. Maximum 1000 characters for secret mail.',
            'image.required' => 'Image is required for secret mail.',
            'image.mimes' => 'Only JPEG images are supported for steganography.',
        ]);

        $sender = Auth::user();
        $receiver = User::find($request->receiver_id);

        if (!$receiver->ecc_public_key) {
            return back()->withErrors(['error' => 'Receiver does not have a public key configured.']);
        }

        try {
            // 1. Encrypt pesan menggunakan public key receiver
            $encryptedMessage = SuperEncryptionService::encrypt(
                $request->message, 
                $receiver->ecc_public_key
            );

            // 2. Upload gambar original
            $originalImage = $request->file('image');
            $originalImageName = time() . '_' . $originalImage->getClientOriginalName();
            $originalImagePath = $originalImage->storeAs('secret_images/original', $originalImageName, 'public');
            $fullOriginalPath = storage_path('app/public/' . $originalImagePath);

            // 3. Embed encrypted message ke dalam gambar
            $stegoImageName = time() . '_stego_' . $originalImage->getClientOriginalName();
            $stegoImagePath = 'secret_images/stego/' . $stegoImageName;
            $fullStegoPath = storage_path('app/public/' . $stegoImagePath);

            // Pastikan directory ada
            if (!file_exists(dirname($fullStegoPath))) {
                mkdir(dirname($fullStegoPath), 0755, true);
            }

            // Embed pesan
            StegoService::embedMessage($fullOriginalPath, $encryptedMessage, $fullStegoPath);

            // 4. Simpan ke database
            SecretMessage::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'stego_image_path' => $stegoImagePath,
                'original_image_path' => $originalImagePath, // Simpan juga original untuk backup
            ]);

            // 5. Hapus original image (opsional, untuk security)
            // Storage::disk('public')->delete($originalImagePath);

            return redirect()->route('secret.index')->with('success', 'Secret message sent successfully! The message is hidden in the image.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send secret message: ' . $e->getMessage()]);
        }
    }

    /**
     * Tampilkan secret messages yang dikirim (sent)
     */
    public function sent()
    {
        $messages = SecretMessage::where('sender_id', Auth::id())
            ->with('receiver')
            ->orderBy('created_at', 'desc')
            ->get();

        $decryptedMessages = $messages->map(function ($msg) {
            $decryptedText = null;

            // Ekstrak pesan dari gambar
            if ($msg->stego_image_path) {
                try {
                    $imagePath = storage_path('app/public/' . $msg->stego_image_path);
                    
                    if (file_exists($imagePath)) {
                        // Ekstrak encrypted message
                        $encryptedMessage = StegoService::extractMessage($imagePath);
                        
                        // Decrypt menggunakan private key receiver untuk verifikasi
                        // Note: Sender tidak bisa decrypt karena butuh private key receiver
                        // Jadi kita hanya tampilkan bahwa pesan sudah dikirim
                        $decryptedText = '[Secret message hidden in image - only receiver can read]';
                        
                        // Alternatif: Simpan plain text untuk sender di database jika diperlukan
                        // atau decrypt dengan private key receiver (tapi sender tidak punya)
                    } else {
                        $decryptedText = 'Gambar tidak ditemukan.';
                    }
                } catch (\Exception $e) {
                    $decryptedText = 'Error: ' . $e->getMessage();
                }
            }

            return [
                'id' => $msg->id,
                'to' => $msg->receiver->name ?? 'Unknown',
                'message' => $decryptedText,
                'image_path' => $msg->stego_image_path,
                'time' => $msg->created_at->toDateTimeString(),
                'is_secret' => true,
                'has_file' => true,
            ];
        });

        return view('dashboard.secret-sent', compact('decryptedMessages'));
    }

    /**
     * Hapus secret message
     */
    public function destroy($id)
    {
        $message = SecretMessage::findOrFail($id);

        // Pastikan hanya sender atau receiver yang bisa hapus
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            return back()->withErrors(['error' => 'Unauthorized action.']);
        }

        // Hapus file gambar
        if ($message->stego_image_path) {
            Storage::disk('public')->delete($message->stego_image_path);
        }
        if ($message->original_image_path) {
            Storage::disk('public')->delete($message->original_image_path);
        }

        $message->delete();

        return back()->with('success', 'Secret message deleted successfully.');
    }

    /**
     * Download stego image
     */
    public function downloadImage($id)
    {
        $message = SecretMessage::findOrFail($id);

        // Pastikan user adalah sender atau receiver
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $imagePath = storage_path('app/public/' . $message->stego_image_path);

        if (!file_exists($imagePath)) {
            abort(404, 'Image not found.');
        }

        return response()->download($imagePath);
    }

    /**
     * Preview decrypted message (AJAX)
     */
    public function preview($id)
    {
        $message = SecretMessage::findOrFail($id);

        // Pastikan user adalah receiver
        if ($message->receiver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $imagePath = storage_path('app/public/' . $message->stego_image_path);
            $encryptedMessage = StegoService::extractMessage($imagePath);
            $decryptedText = SuperEncryptionService::decrypt(
                $encryptedMessage,
                Auth::user()->ecc_private_key
            );

            return response()->json([
                'success' => true,
                'message' => $decryptedText,
                'sender' => $message->sender->name,
                'time' => $message->created_at->diffForHumans(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to decrypt message: ' . $e->getMessage()
            ], 500);
        }
    }
}