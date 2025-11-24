<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FileAttachment;
use App\Services\Crypto\ECCService;
use Illuminate\Support\Facades\Storage;

class FileEncryptController extends Controller
{
    public function encrypt(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'file' => 'required|file'
        ]);

        $receiver = User::find($request->receiver_id);
        $uploaded = $request->file('file');
        $path = $uploaded->store('files/original', 'public');

        $outputPath = storage_path('app/public/files/encrypted/' . time() . '_enc.txt');
        $pubPath = storage_path('app/public/receiver_public.pem');
        file_put_contents($pubPath, $receiver->ecc_public_key);

        ECCService::encryptFile(storage_path('app/public/' . $path), $pubPath, $outputPath);

        FileAttachment::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'original_name' => $uploaded->getClientOriginalName(),
            'encrypted_path' => 'files/encrypted/' . basename($outputPath),
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
            'status' => 'encrypted',
        ]);

        return response()->json([
            'status' => 'success',
            'encrypted_file' => asset('storage/files/encrypted/' . basename($outputPath))
        ]);
    }

    public function decrypt(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'file' => 'required|file'
        ]);

        $user = User::find($request->user_id);
        $uploaded = $request->file('file');
        $path = $uploaded->store('files/encrypted', 'public');

        $outputPath = storage_path('app/public/files/decrypted/' . time() . '_dec_' . $uploaded->getClientOriginalName());
        $privPath = storage_path('app/public/private.pem');
        file_put_contents($privPath, $user->ecc_private_key);

        ECCService::decryptFile(storage_path('app/public/' . $path), $privPath, $outputPath);

        return response()->download($outputPath);
    }
}
