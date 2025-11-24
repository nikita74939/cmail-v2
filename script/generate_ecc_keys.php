<?php

require __DIR__ . '/../vendor/autoload.php';

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;

$adapter = EccFactory::getAdapter();
$generator = EccFactory::getNistCurves()->generator256();

// generate key
$privateKey = $generator->createPrivateKey();
$publicKey  = $privateKey->getPublicKey();

// serializers
$derPub  = new DerPublicKeySerializer($adapter);
$derPriv = new DerPrivateKeySerializer($adapter);

$pemPub  = new PemPublicKeySerializer($derPub);
$pemPriv = new PemPrivateKeySerializer($derPriv);

// output PEM
$pubPem  = $pemPub->serialize($publicKey);
$privPem = $pemPriv->serialize($privateKey);

// save
file_put_contents(__DIR__ . '/../storage/app/public.pem', $pubPem);
file_put_contents(__DIR__ . '/../storage/app/private.pem', $privPem);

echo "ECC Keys Generated\n";
