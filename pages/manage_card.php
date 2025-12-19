<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $action = trim($_POST['action']) ?? null;
    $card_name = trim($_POST['card_name']) ?? null;
    $bank_name = trim($_POST['bank_name']) ?? null;
    $card_number = trim($_POST['card_number']) ?? null;
    $card_type = trim($_POST['card_type']) ?? null;
    $initial_balance = trim($_POST['initial_balance']) ?? null;

    if($action === 'add'){
    
        try{
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $sqlCards = $pdo->prepare("INSERT INTO cards(user_id, card_name, bank_name, card_number, initial_balance) VALUES (?, ?, ?, ?, ?)");
            $sqlCards->execute([$user_id, $card_name, $bank_name, $card_number, $initial_balance]);
            header('Location: cars.php');
            exit;
        }catch(PDOException $e){
        die("Erreur lors de l insertion : " .$e->getMessage());
    }
    }
    if($action === 'edit'){
        $cardId = $_POST['cardId'];
        echo $cardId;
        try{
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $sqlCards = $pdo->prepare("UPDATE cards SET card_name = ?, bank_name = ?, card_number = ?, initial_balance = ? where id = ?");
            $sqlCards->execute([$card_name, $bank_name, $card_number, $initial_balance, $cardId]);
            header('Location: cars.php');
            exit;
        }catch(PDOException $e){
        die("Erreur lors de la modification : " .$e->getMessage());
    }
    }

}else{
    header('Location: cars.php');
    exit;
}
?>