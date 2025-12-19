<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=smart_wallet;charset=utf8mb4',
        'smart_user',
        'Abderrah;ane'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur lors de la connexion : " . $e->getMessage());
}