<?php
/**
 * Utility Script: Password Hash Generator
 * --------------------------------------
 * Use this script to generate secure hashes for your database.
 * 
 * IMPORTANT: Delete this file after obtaining your hash.
 */

$password = 'admin123'; // Change this to the password you want to hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "--- VILLA DE SANT PASSWORD GENERATOR ---\n\n";
echo "Password: " . $password . "\n";
echo "Secure Hash: " . $hash . "\n\n";
echo "Copy the hash above and paste it into your SQL 'usuarios' table.\n";
echo "----------------------------------------\n";
?>
