<?php

function generateKeyPair($keyBits = 2048) {
    // Configuration pour la génération de clés
    $config = array(
        "private_key_bits" => $keyBits,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Génère une nouvelle paire de clés
    $res = openssl_pkey_new($config);

    // Exporte la clé privée
    openssl_pkey_export($res, $privateKey);

    // Récupère les détails de la clé publique
    $publicKeyDetails = openssl_pkey_get_details($res);
    $publicKey = $publicKeyDetails['key'];

    // Sauvegarde les clés dans des fichiers
    file_put_contents("key.private", $privateKey);
    file_put_contents("key.public", $publicKey);
}

function encryptWithPublicKey($str) {
    // Récupère la clé publique depuis le fichier
    $publicKey = openssl_pkey_get_public(file_get_contents("key.public"));

    // Chiffre le message avec la clé publique
    openssl_public_encrypt($str, $crypted, $publicKey);

    // Retourne le message chiffré en base64
    return base64_encode($crypted);
}

function decryptWithPrivateKey($str) {
    // Récupère la clé privée depuis le fichier
    $privateKey = openssl_pkey_get_private(file_get_contents("key.private"));

    // Déchiffre le message avec la clé privée
    openssl_private_decrypt(base64_decode($str), $decrypted, $privateKey);

    return $decrypted;
}

// Chiffrement avec la clé publique
$encryptedMessage = encryptWithPublicKey("Ceci est un message de test pour le chiffrement RSA.");
echo "Message chiffré : " . $encryptedMessage . PHP_EOL;

// Déchiffrement avec la clé privée
$decryptedMessage = decryptWithPrivateKey($encryptedMessage);
echo "Message déchiffré : " . $decryptedMessage . PHP_EOL;

?>
