<?php 
    require 'config.php';
    $index = $_GET['id'];
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