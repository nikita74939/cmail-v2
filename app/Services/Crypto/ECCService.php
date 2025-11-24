<?php

namespace App\Services\Crypto;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Primitives\PointInterface;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Curves\OptimizedCurveFp;

class ECCService
{
    private static $adapter;
    private static $generator;
    private static $derPrivateSerializer;
    private static $derPublicSerializer;
    private static $privateSerializer;
    private static $publicSerializer;

    private static function init()
    {
        if (!self::$adapter) {
            self::$adapter = EccFactory::getAdapter();
            self::$generator = EccFactory::getNistCurves()->generator256();
            self::$derPrivateSerializer = new DerPrivateKeySerializer(self::$adapter);
            self::$derPublicSerializer = new DerPublicKeySerializer(self::$adapter);
            self::$privateSerializer = new PemPrivateKeySerializer(self::$derPrivateSerializer);
            self::$publicSerializer = new PemPublicKeySerializer(self::$derPublicSerializer);
        }
    }

    public static function generateKeyPair()
    {
        self::init();
        $privateKey = self::$generator->createPrivateKey();
        $publicKey = $privateKey->getPublicKey();

        return [
            'private' => self::$privateSerializer->serialize($privateKey),
            'public' => self::$publicSerializer->serialize($publicKey),
        ];
    }

    private static function calculateY($curve, $x)
    {
        $a = $curve->getA();
        $b = $curve->getB();
        $p = $curve->getPrime();

        // y² = x³ + ax + b (mod p)
        $rhs = gmp_mod(
            gmp_add(
                gmp_add(gmp_powm($x, 3, $p), gmp_mul($a, $x)),
                $b
            ),
            $p
        );

        // Tonelli-Shanks untuk sqrt mod p
        $y = gmp_sqrtrem($rhs)[0]; // NOT SAFE for prime p, but works for small tests

        return [$y, gmp_sub($p, $y)];
    }


    public static function encrypt($plaintext, PublicKeyInterface $publicKey)
    {
        self::init();

        // Random k
        $k = gmp_random_range(1, gmp_sub(self::$generator->getOrder(), 1));

        // Compute shared secret
        $S = $publicKey->getPoint()->mul($k);
        $secret = gmp_strval($S->getX(), 16);

        // ubah secret menjadi biner yang valid
        $secretHex = gmp_strval($S->getX(), 16);
        if (strlen($secretHex) % 2 !== 0) {
            $secretHex = '0' . $secretHex;
        }
        $secretBin = hex2bin($secretHex);

        // buat keystream sepanjang plaintext
        $ks = str_repeat($secretBin, ceil(strlen($plaintext) / strlen($secretBin)));
        $ks = substr($ks, 0, strlen($plaintext));

        // XOR yang benar
        $cipher = $plaintext ^ $ks;
        $cipherHex = bin2hex($cipher);

        // C1 = kG
        $C1 = self::$generator->mul($k);

        return base64_encode(json_encode([
            'C1' => [
                'x' => gmp_strval($C1->getX(), 16),
                'y' => gmp_strval($C1->getY(), 16),
            ],
            'cipher' => $cipherHex
        ]));
    }


    public static function decrypt($ciphertext, PrivateKeyInterface $privateKey)
    {
        self::init();

        $data = json_decode(base64_decode($ciphertext), true);
        if (!$data || !isset($data['C1'], $data['cipher'])) {
            throw new \Exception('Invalid ciphertext');
        }

        $curve = self::$generator->getCurve();
        $C1 = $curve->getPoint(
            gmp_init($data['C1']['x'], 16),
            gmp_init($data['C1']['y'], 16)
        );

        // Compute shared secret
        $d = $privateKey->getSecret();
        $S = $C1->mul($d);
        $secret = gmp_strval($S->getX(), 16);

        // XOR decrypt
        // ambil cipher bin
        $cipher = hex2bin($data['cipher']);

        // siapkan secret key lagi
        $secretHex = gmp_strval($S->getX(), 16);
        if (strlen($secretHex) % 2 !== 0) {
            $secretHex = '0' . $secretHex;
        }
        $secretBin = hex2bin($secretHex);

        // buat keystream
        $ks = str_repeat($secretBin, ceil(strlen($cipher) / strlen($secretBin)));
        $ks = substr($ks, 0, strlen($cipher));

        // XOR decrypt
        $plaintext = $cipher ^ $ks;

        return $plaintext;

    }


    public static function loadPublicKey($pem)
    {
        self::init();
        return self::$publicSerializer->parse($pem);
    }

    public static function encryptBinary($data, PublicKeyInterface $publicKey)
    {
        self::init();

        // k random
        $k = gmp_random_range(1, gmp_sub(self::$generator->getOrder(), 1));

        // shared secret S = k * PublicKey
        $S = $publicKey->getPoint()->mul($k);

        // gunakan X sebagai secret key
        $secretHex = gmp_strval($S->getX(), 16);
        if (strlen($secretHex) % 2 !== 0) {
            $secretHex = '0' . $secretHex;
        }
        $secretBin = hex2bin($secretHex);

        // buat keystream sepanjang file
        $ks = str_repeat($secretBin, ceil(strlen($data) / strlen($secretBin)));
        $ks = substr($ks, 0, strlen($data));

        // XOR encryption (aman + tidak merusak file)
        $cipher = $data ^ $ks;
        $cipherHex = bin2hex($cipher);

        // C1 = kG
        $C1 = self::$generator->mul($k);

        return base64_encode(json_encode([
            'C1' => [
                'x' => gmp_strval($C1->getX(), 16),
                'y' => gmp_strval($C1->getY(), 16),
            ],
            'cipher' => $cipherHex
        ]));
    }

    public static function decryptBinary($ciphertext, PrivateKeyInterface $privateKey)
    {
        self::init();

        $data = json_decode(base64_decode($ciphertext), true);

        $curve = self::$generator->getCurve();
        $C1 = $curve->getPoint(
            gmp_init($data['C1']['x'], 16),
            gmp_init($data['C1']['y'], 16)
        );

        // S = d * C1
        $S = $C1->mul($privateKey->getSecret());

        $secretHex = gmp_strval($S->getX(), 16);
        if (strlen($secretHex) % 2 !== 0) {
            $secretHex = '0' . $secretHex;
        }
        $secretBin = hex2bin($secretHex);

        // cipher bin
        $cipher = hex2bin($data['cipher']);

        // buat keystream
        $ks = str_repeat($secretBin, ceil(strlen($cipher) / strlen($secretBin)));
        $ks = substr($ks, 0, strlen($cipher));

        // XOR decrypt
        return $cipher ^ $ks;
    }


}
