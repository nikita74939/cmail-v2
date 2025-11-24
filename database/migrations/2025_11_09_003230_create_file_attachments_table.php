<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->id();

            // relasi pengguna
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // file info
            $table->string('original_name');   // nama file asli
            $table->string('encrypted_path');  // path file terenkripsi
            $table->string('decrypted_path')->nullable(); // path file hasil dekripsi
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();

            // status (opsional)
            $table->enum('status', ['encrypted', 'decrypted'])->default('encrypted');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
