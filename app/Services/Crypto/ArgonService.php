<?php
namespace App\Services\Crypto;
use Illuminate\Support\Facades\Hash;

class ArgonService
{
    public static function hashPassword($password)
    {
        return Hash::make($password);
    }

    public static function verifyPassword($password, $hashedPassword)
    {
        return Hash::check($password, $hashedPassword);
    }
}

