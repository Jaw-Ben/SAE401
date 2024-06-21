<?php

// Fonction modPow originale
function modPowOriginal($base, $exposant, $modulo) {
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

// Fonction modPow optimisée
function modPowOptimisee($base, $exposant, $modulo) {
    $base = gmp_init($base);
    $exposant = gmp_init($exposant);
    $modulo = gmp_init($modulo);

    $result = gmp_init(1);

    while (gmp_cmp($exposant, 0) > 0) {
        if (gmp_intval(gmp_and($exposant, 1)) == 1) {
            $result = gmp_mod(gmp_mul($result, $base), $modulo);
        }
        $base = gmp_mod(gmp_mul($base, $base), $modulo);
        $exposant = gmp_div($exposant, 2);
    }

    return gmp_strval($result);
}


$base = "123456789";  
$exposant = "987654321";  
$modulo = "999999999";  

$debutOriginal = microtime(true);
$resultatOriginal = modPowOriginal($base, $exposant, $modulo);
$finOriginal = microtime(true);
$tempsExecutionOriginal = $finOriginal - $debutOriginal;

echo "Résultat de modPow original : {$resultatOriginal}<br>";
echo "Temps d'exécution pour la fonction modPow originale : {$tempsExecutionOriginal} secondes<br><br>";

$debutOptimisee = microtime(true);
$resultatOptimisee = modPowOptimisee($base, $exposant, $modulo);
$finOptimisee = microtime(true);
$tempsExecutionOptimisee = $finOptimisee - $debutOptimisee;

echo "Résultat de modPow optimisée : {$resultatOptimisee}<br>";
echo "Temps d'exécution pour la fonction modPow optimisée : {$tempsExecutionOptimisee} secondes<br>";

?>