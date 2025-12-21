<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';session_start();

$card_id = $_GET['id'];

$pdo->prepare("UPDATE cards SET is_primary = 0 WHERE user_id = ?")
    ->execute([$user_id]);

$pdo->prepare("UPDATE cards SET is_primary = 1 WHERE id = ? AND user_id = ?")
    ->execute([$card_id, $user_id]);

header("Location: ../pages/cards.php");
