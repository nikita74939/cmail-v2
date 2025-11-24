<?php
namespace App\Services\Crypto;
class TripleDESService
{
    private static $key = 'thisIsASecretKey123456789'; // panjang harus 24 byte (192 bit)
    private static $iv = '12345678'; // IV 8 byte

    /**
     * Enkripsi pesan menggunakan 3DES
     */
    public static function encrypt($plainText)
    {
        $cipher = openssl_encrypt(
            $plainText,
            'des-ede3-cbc',
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv
        );

        return base64_encode($cipher);
    }

    /**
     * Dekripsi pesan menggunakan 3DES
     */
    
    public static function decrypt($encryptedText)
    {
        $cipher = base64_decode($encryptedText);

        return openssl_decrypt(
            $cipher,
            'des-ede3-cbc',
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv
        );
    }
}
