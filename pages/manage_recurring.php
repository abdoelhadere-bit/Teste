<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recurring_transactions.php');
    exit;
}

$action      = $_POST['action'] ?? 'add';
$id          = $_POST['recurringId'] ?? null;
$type        = $_POST['type'];
$montant     = $_POST['montant'];
$category    = $_POST['category'];
$description = $_POST['description'] ?? null;
$card_id     = $_POST['card_id'];
$day         = $_POST['day_of_month'];

if ($action === 'add') {
    $stmt = $pdo->prepare("
        INSERT INTO recurring_transactions
        (user_id, type, montant, category, description, card_id, day_of_month)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $type,
        $montant,
        $category,
        $description,
        $card_id,
        $day
    ]);
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("
        UPDATE recurring_transactions
        SET type = ?, montant = ?, category = ?, description = ?, card_id = ?, day_of_month = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $type,
        $montant,
        $category,
        $description,
        $card_id,
        $day,
        $id,
        $user_id
    ]);
}

header('Location: recurring_transactions.php');
exit;
