<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/cron_php_error.log');
error_reporting(E_ALL);

date_default_timezone_set('Africa/Casablanca');

file_put_contents(
    '/tmp/cron_debug.txt',
    date('Y-m-d H:i:s') . " | day=" . date('d') . PHP_EOL,
    FILE_APPEND
);

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

logMessage('========== CRON START ==========', $logFile);
logMessage('Date: ' . $today . ' | Jour: ' . $currentDay, $logFile);

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
        logMessage('✓ No recurring transactions to generate.', $logFile);
        logMessage('========== CRON END ==========', $logFile);
        exit;
    }

    logMessage('Found ' . count($recurrings) . ' transactions to generate', $logFile);

    $successCount = 0;
    $errorCount = 0;

    foreach ($recurrings as $rec) {
        try {
            // 2️⃣ Insert into expenses or incomes
            if ($rec['type'] === 'expense') {
                $insert = $pdo->prepare("
                    INSERT INTO expenses
                        (user_id, card_id, category, montant, decription, dates)
                    VALUES
                        (?, ?, ?, ?, ?, ?)
                ");
            } else { // income
                $insert = $pdo->prepare("
                    INSERT INTO incomes
                        (user_id, card_id, category, montant, decription, dates)
                    VALUES
                        (?, ?, ?, ?, ?, ?)
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
                "✓ Generated {$rec['type']} | User {$rec['user_id']} | {$rec['category']} | {$rec['montant']} DH",
                $logFile
            );
            $successCount++;

        } catch (PDOException $e) {
            logMessage("✗ Error for recurring ID {$rec['id']}: " . $e->getMessage(), $logFile);
            $errorCount++;
        }
    }

    logMessage("========== CRON END ==========", $logFile);
    logMessage("SUCCESS: $successCount | ERRORS: $errorCount", $logFile);

} catch (PDOException $e) {
    logMessage('✗ CRITICAL ERROR: ' . $e->getMessage(), $logFile);
}