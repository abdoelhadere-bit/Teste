<?php 
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);    $index = $_GET['id'];
    $source = $_GET['source'];

    if ($source === "incomes"){
        $sql = $pdo->prepare("DELETE FROM incomes WHERE id = ?");
        $sql->execute([$index]);
        header("Location: incomes.php");
        exit;
    }elseif($source === "expenses"){
        $sql = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $sql->execute([$index]);
        header("Location: expenses.php");
        exit;
    }

    
?>