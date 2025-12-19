<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("
        DELETE FROM recurring_transactions
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
}

header('Location: recurring_transactions.php');
exit;
