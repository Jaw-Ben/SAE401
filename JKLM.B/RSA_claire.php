<?php

function generateRSAKeys($bitLength) {
    $p = generatePrime($bitLength);
    $q = generatePrime($bitLength);

    $n = gmp_mul($p, $q);
    $phi = gmp_mul(gmp_sub($p, 1), gmp_sub($q, 1));

    $e = findCoprime($phi, $bitLength);
    $d = modInverse($e, $phi);
    
    return [
        'publicKey' => ['e' => gmp_strval($e), 'n' => gmp_strval($n)],
        'privateKey' => ['d' => gmp_strval($d), 'n' => gmp_strval($n)]
    ];
}

function generatePrime($bitLength) {
    do {
        $randomNumber = gmp_random_bits($bitLength);
    } while (!gmp_prob_prime($randomNumber, 50));

    return $randomNumber;
}

function findCoprime($phi, $bitLength) {
    $e = generatePrime($bitLength);
    $phi = gmp_init($phi);

    while (gmp_cmp(gmp_gcd($e, $phi), 1) != 0) {
        $e = gmp_add($e, 1);
    }

    return $e;
}

function modInverse($a, $m) {
    $a = gmp_init($a);
    $m = gmp_init($m);

    $inv = gmp_invert($a, $m);

    return $inv;
}


function modPow($base, $exposant, $modulo) {
    $base = gmp_init($base);
    $exposant = gmp_init($exposant);
    $modulo = gmp_init($modulo);

    $resultatFinal = gmp_init(1);

    while (gmp_cmp($exposant, 0) > 0) {

        if (gmp_cmp(gmp_mod($exposant, 2), 1) == 0) {
            $resultatFinal = gmp_mod(gmp_mul($resultatFinal, $base), $modulo);
        }

        $base = gmp_mod(gmp_mul($base, $base), $modulo);

        $exposant = gmp_div($exposant, 2);
    }

    return gmp_strval($resultatFinal);
}

function stringToNumber($string) {
    $result = gmp_init('0');
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
        $result = gmp_mul($result, '256');
        $result = gmp_add($result, ord($string[$i]));
    }

    return $result;
}

function numberToString($number) {
    $result = '';

    while (gmp_cmp($number, 0) > 0) {
        $byte = gmp_mod($number, '256');
        $result = chr((int)gmp_strval($byte)) . $result;
        $number = gmp_div($number, '256', GMP_ROUND_ZERO);
    }

    return $result;
}

function encryptRSA($message, $publicKey) {
    $numericMessage = stringToNumber($message);
    $encryptedMessage = modPow($numericMessage, $publicKey['e'], $publicKey['n']);
    return $encryptedMessage;
}

function decryptRSA($encryptedMessage, $privateKey) {
    $decryptedNumericMessage = modPow($encryptedMessage, $privateKey['d'], $privateKey['n']);
    $decryptedMessage = numberToString($decryptedNumericMessage);
    return $decryptedMessage;
}

$keys = generateRSAKeys(1024);
$publicKey = $keys['publicKey'];
$privateKey = $keys['privateKey'];

echo "Clé publique (n, e) : (" . $publicKey['n'] . ", " . $publicKey['e'] . ")" . PHP_EOL;
echo "Clé privée (n, d) : (" . $privateKey['n'] . ", " . $privateKey['d'] . ")" . PHP_EOL;

$message = "Salut, ceci est un nouveau message à chiffrer et déchiffrer.";

$encryptedMessage = encryptRSA($message, $publicKey);
echo "Message chiffré : " . $encryptedMessage . PHP_EOL;

$decryptedMessage = decryptRSA($encryptedMessage, $privateKey);
echo "Message déchiffré : " . $decryptedMessage . PHP_EOL;

?>