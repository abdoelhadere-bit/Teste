<?php

require __DIR__ . '/../config.php';

// ---------- CONFIG ----------
$today        = date('Y-m-d');
$currentDay   = (int) date('d');
$logFile      = __DIR__ . '/logs/recurring.log';

// Ensure log directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// ---------- LOG FUNCTION ----------
function logMessage(string $message, string $file): void {
    file_put_contents(
        $file,
        '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
        FILE_APPEND
    );
}

logMessage('CRON START', $logFile);

try {
    // 1️⃣ Fetch all active recurring transactions scheduled for today
    $stmt = $pdo->prepare("
        SELECT rt.*
        FROM recurring_transactions rt
        WHERE rt.is_active = 1
          AND rt.day_of_month = ?
          AND (
                rt.last_generated IS NULL
                OR rt.last_generated <> ?
              )
    ");
    $stmt->execute([$currentDay, $today]);
    $recurrings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$recurrings) {
        logMessage('No recurring transactions to generate.', $logFile);
        exit;
    }

    foreach ($recurrings as $rec) {

        // 2️⃣ Insert into expenses or incomes
        if ($rec['type'] === 'expense') {
            $insert = $pdo->prepare("
                INSERT INTO expenses
                    (user_id, card_id, category, montant, description, dates, created_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, NOW())
            ");
        } else { // income
            $insert = $pdo->prepare("
                INSERT INTO incomes
                    (user_id, card_id, category, montant, description, dates, created_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, NOW())
            ");
        }

        $insert->execute([
            $rec['user_id'],
            $rec['card_id'],
            $rec['category'],
            $rec['montant'],
            $rec['description'],
            $today
        ]);

        // 3️⃣ Update last_generated
        $update = $pdo->prepare("
            UPDATE recurring_transactions
            SET last_generated = ?
            WHERE id = ?
        ");
        $update->execute([$today, $rec['id']]);

        logMessage(
            "Generated {$rec['type']} | User {$rec['user_id']} | {$rec['category']} | {$rec['montant']} DH",
            $logFile
        );
    }

    logMessage('CRON END SUCCESS', $logFile);

} catch (PDOException $e) {
    logMessage('ERROR: ' . $e->getMessage(), $logFile);
}
