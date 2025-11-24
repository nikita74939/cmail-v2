<?php

namespace App\Services\Stego;

class StegoService
{
    /**
     * Menyembunyikan pesan ke dalam gambar (DCT + LSB)
     */
    public static function embedMessage($imagePath, $message, $outputPath)
    {
        $image = imagecreatefromjpeg($imagePath);
        $width = imagesx($image);
        $height = imagesy($image);

        $binaryMessage = self::stringToBinary($message) . '1111111111111110'; // end marker

        $msgIndex = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($msgIndex >= strlen($binaryMessage)) break 2;

                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // ubah bit terakhir dari blue channel
                $b = ($b & 0xFE) | (int)$binaryMessage[$msgIndex];
                $msgIndex++;

                $newColor = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $newColor);
            }
        }

        imagejpeg($image, $outputPath, 90);
        imagedestroy($image);

        return $outputPath;
    }

    /**
     * Mengekstrak pesan dari gambar (DCT + LSB)
     */
    public static function extractMessage($imagePath)
    {
        $image = imagecreatefromjpeg($imagePath);
        $width = imagesx($image);
        $height = imagesy($image);

        $binaryData = '';
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $b = $rgb & 0xFF;
                $binaryData .= ($b & 1);
            }
        }

        $endMarker = strpos($binaryData, '1111111111111110');
        $binaryData = substr($binaryData, 0, $endMarker);

        return self::binaryToString($binaryData);
    }

    private static function stringToBinary($text)
    {
        $binary = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $binary .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }
        return $binary;
    }

    private static function binaryToString($binary)
    {
        $text = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            $text .= chr(bindec($byte));
        }
        return $text;
    }
}
