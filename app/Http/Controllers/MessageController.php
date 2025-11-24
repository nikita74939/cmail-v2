<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Services\Crypto\SuperEncryptionService;  // Ganti dari ECCService ke SuperEncryptionService
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    # Tampilkan halaman create message
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('dashboard.create', compact('users'));  // Ubah ke dashboard.create
    }

    # Kirim pesan terenkripsi (text) - Sudah ada, tidak diubah
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',  // Text opsional jika ada file
            'image' => 'nullable|image|mimes:jpg,png,gif|max:2048',  // Gambar: max 2MB
            'file' => 'nullable|file|max:5120',  // File: max 5MB
        ]);

        $sender = Auth::user();
        $receiver = User::find($request->receiver_id);

        $imagePath = null;
        $filePath = null;

        // Handle upload gambar
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');  // Simpan di storage/app/public/images
        }

        // Handle upload file
        if ($request->hasFile('file')) {

            // 1. Simpan file asli
            $filePath = $request->file('file')->store('files', 'public');

            // 2. Ambil isi file
            $plaintext = file_get_contents(storage_path("app/public/" . $filePath));

            // 3. Load public key (pem)
            $publicKeyPem = file_get_contents(storage_path('app/public.pem'));
            $publicKey = \App\Services\Crypto\ECCService::loadPublicKey($publicKeyPem);

            // 4. Encrypt
            $ciphertext = \App\Services\Crypto\ECCService::encryptBinary($plaintext, $publicKey);

            // 5. Simpan file terenkripsi
            $encPath = 'encrypted/' . basename($filePath) . '.ecc';
            file_put_contents(storage_path("app/public/files" . $encPath), $ciphertext);

            return response()->json([
                'status' => 'ok',
                'encrypted_file' => $encPath
            ]);
        }


        $encryptedMessage = null;
        if ($request->message) {
            $encryptedMessage = SuperEncryptionService::encrypt($request->message, $receiver->ecc_public_key);
        }

        // Simpan pesan ke database (update model Message jika perlu kolom baru)
        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message_encrypted' => $encryptedMessage,
            'image_path' => $imagePath,  // Tambahkan kolom ini di tabel messages
            'file_path' => $filePath,    // Tambahkan kolom ini di tabel messages
        ]);

        return redirect()->route('dashboard.inbox')->with('success', 'Pesan berhasil dikirim!');
    }

    # Tampilkan pesan masuk (ubah dari JSON ke view)
    public function inbox()
    {
        $messages = Message::where('receiver_id', Auth::id())->get(); 

        $decryptedMessages = $messages->map(function ($msg) {
            $decryptedText = null;
            if ($msg->message_encrypted) {
                $currentUser = Auth::user();
                if (!$currentUser->ecc_private_key) {
                    $decryptedText = 'Private key tidak ditemukan - tidak bisa dekripsi.';
                } else {
                    try {
                        $decryptedText = SuperEncryptionService::decrypt($msg->message_encrypted, $currentUser->ecc_private_key);
                    } catch (\Exception $e) {
                        $decryptedText = 'Error dekripsi: ' . $e->getMessage();
                    }
                }
            }
            return [
                'id' => $msg->id,
                'from' => $msg->sender->name ?? 'Unknown', 
                'message' => $decryptedText,
                'time' => $msg->created_at->toDateTimeString(),
            ];
        });

        return view('dashboard.inbox', compact('decryptedMessages'));
    }

    public function showSent()  
    {
        $messages = Message::where('sender_id', Auth::id())->get();  //
        $decryptedMessages = $messages->map(function ($msg) {
            $messageText = SuperEncryptionService::decrypt(
                $msg->message_encrypted,
                $msg->receiver->ecc_private_key  
            );
            return [
                'id' => $msg->id,
                'to' => $msg->receiver->name ?? 'Unknown',  
                'message' => $messageText,
                'time' => $msg->created_at->toDateTimeString(),
            ];
        });

        return view('dashboard.sent', compact('decryptedMessages'));
    }
}
