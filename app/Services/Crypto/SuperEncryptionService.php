<?php

namespace App\Services\Crypto;

use App\Services\Crypto\ECCService;
use RuntimeException;
use InvalidArgumentException;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Curves\OptimizedCurveFp;

class SuperEncryptionService
{
    public static function routeEncrypt(string $plaintext, int $cols = 5): string
    {
        $text = str_replace(["", ""], ' ', $plaintext);
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $rows = (int) ceil(count($chars) / $cols);
        $grid = array_fill(0, $rows, array_fill(0, $cols, ""));

        $k = 0;
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $grid[$r][$c] = $chars[$k] ?? "";
                $k++;
            }
        }

        $out = '';
        for ($c = 0; $c < $cols; $c++) {
            for ($r = 0; $r < $rows; $r++) {
                if ($grid[$r][$c] !== "") {
                    $out .= $grid[$r][$c];
                }
            }
        }
        return $out;
    }

    public static function routeDecrypt(string $ciphertext, int $cols = 5): string
    {
        $chars = preg_split('//u', $ciphertext, -1, PREG_SPLIT_NO_EMPTY);

        $rows = (int) ceil(count($chars) / $cols);
        $grid = array_fill(0, $rows, array_fill(0, $cols, null));

        $index = 0;
        for ($c = 0; $c < $cols; $c++) {
            for ($r = 0; $r < $rows; $r++) {
                if ($index >= count($chars)) {
                    break 2;
                }
                $grid[$r][$c] = $chars[$index++];
            }
        }

        $out = '';
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if ($grid[$r][$c] !== null) {
                    $out .= $grid[$r][$c];
                }
            }
        }
        return $out;
    }

    public static function encrypt(string $plaintext, string $publicKeyPem, int $cols = 5): string
    {
        $route = self::routeEncrypt($plaintext, $cols);

        // Parse public key PEM string ke objek PublicKeyInterface
        $adapter = EccFactory::getAdapter();
        $derPublicSerializer = new DerPublicKeySerializer($adapter);
        $publicSerializer = new PemPublicKeySerializer($derPublicSerializer);
        try {
            $publicKey = $publicSerializer->parse($publicKeyPem);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid public key PEM: ' . $e->getMessage());
        }

        // Panggil ECCService::encrypt langsung (diasumsikan method ada)
        try {
            $enc = ECCService::encrypt($route, $publicKey);
        } catch (\Exception $e) {
            throw new RuntimeException('Error during ECC encryption: ' . $e->getMessage());
        }

        // Output jadi string: cipher||cols
        return $enc . '||' . $cols;
    }

    public static function decrypt(string $bundleJson, string $privateKeyPem): string
    {
        // Parse string: cipher||cols
        $parts = explode('||', $bundleJson);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Format bundle salah');
        }
        $enc = $parts[0];
        $cols = (int) $parts[1];

        // Parse private key PEM string ke objek PrivateKeyInterface
        $adapter = EccFactory::getAdapter();
        $derPrivateSerializer = new DerPrivateKeySerializer($adapter);
        $privateSerializer = new PemPrivateKeySerializer($derPrivateSerializer);
        try {
            $privateKey = $privateSerializer->parse($privateKeyPem);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid private key PEM: ' . $e->getMessage());
        }

        // Panggil ECCService::decrypt langsung (diasumsikan method ada)
        try {
            $route = ECCService::decrypt($enc, $privateKey);
        } catch (\Exception $e) {
            throw new RuntimeException('Error during ECC decryption: ' . $e->getMessage());
        }

        return self::routeDecrypt($route, $cols);
    }
}
