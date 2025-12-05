<?php
$haslo = '123456789';
$hash = password_hash($haslo, PASSWORD_DEFAULT);
echo "Zahashowane hasło: " . $hash;
?>
