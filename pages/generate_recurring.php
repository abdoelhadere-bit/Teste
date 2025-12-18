<?php 
require 'auth.php';
require 'config.php';

function generateTransactions(PDO $pdo, int $user_id){
    $today = date('Y-m-d');
    $day = (int) date('d');

    $sql = $pdo->prepare("SELECT * FROM transactions
                          WHERE user_id = ?
                          AND is_active = 1
                          AND day_of_month = ?
                          AND (last_generated IS NULL OR last_generated <> ?");
    $sql->execute([$user_id, $day, ]);
}
?>