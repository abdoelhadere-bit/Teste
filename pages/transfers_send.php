<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transfers.php');
    exit;
}

$sender_id   = (int)$user_id;
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$amount      = (float)($_POST['amount'] ?? 0);

if ($receiver_id <= 0) {
    $_SESSION['errors'][] = "ID du destinataire invalide.";
    header("Location: transfers.php");
    exit;
}
if ($amount <= 0) {
    $_SESSION['errors'][] = "Montant invalide.";
    header("Location: transfers.php");
    exit;
}
if ($receiver_id === $sender_id) {
    $_SESSION['errors'][] = "Vous ne pouvez pas vous envoyer de l'argent à vous-même.";
    header("Location: transfers.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM register WHERE id = ? LIMIT 1");
$stmt->execute([$receiver_id]);
if (!$stmt->fetch()) {
    $_SESSION['errors'][] = "Utilisateur introuvable.";
    header("Location: transfers.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT id, current_balance
        FROM cards
        WHERE user_id = ? AND is_primary = 1
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$sender_id]);
    $senderCard = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$senderCard) throw new Exception("Vous devez définir une carte principale.");

    $stmt = $pdo->prepare("
        SELECT id, current_balance
        FROM cards
        WHERE user_id = ? AND is_primary = 1
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$receiver_id]);
    $receiverCard = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receiverCard) throw new Exception("Le destinataire n'a pas de carte principale.");

    if ((float)$senderCard['current_balance'] < $amount) {
        throw new Exception("Solde insuffisant.");
    }

    $stmt = $pdo->prepare("UPDATE cards SET current_balance = current_balance - ? WHERE id = ?");
    $stmt->execute([$amount, $senderCard['id']]);

    $stmt = $pdo->prepare("UPDATE cards SET current_balance = current_balance + ? WHERE id = ?");
    $stmt->execute([$amount, $receiverCard['id']]);

    $stmt = $pdo->prepare("
        INSERT INTO transfers (sender_id, receiver_id, card_id, amount, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$sender_id, $receiver_id, $receiverCard['id'], $amount]);

    $pdo->commit();

    $_SESSION['success'] = "Transfert effectué avec succès.";
    header("Location: transfers.php");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['errors'][] = $e->getMessage();
    header("Location: transfers.php");
    exit;
}
