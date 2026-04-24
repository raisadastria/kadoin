<?php
$passwordToHash = "admin123"; // Ganti dengan password yang kamu inginkan
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);
echo "Password: " . htmlspecialchars($passwordToHash) . "<br>";
echo "Hashed: " . htmlspecialchars($hashedPassword);
?>