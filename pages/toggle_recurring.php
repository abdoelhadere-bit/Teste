<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("
        UPDATE recurring_transactions
        SET is_active = IF(is_active = 1, 0, 1)
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
}

header('Location: recurring_transactions.php');
exit;
