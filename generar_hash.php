<?php
// Generar hash para password "Alonso1997"
$password = "Alonso1997";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nVerificación: " . (password_verify($password, $hash) ? "✅ OK" : "❌ FAIL") . "\n";
?>
