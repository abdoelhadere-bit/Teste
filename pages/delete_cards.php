<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_GET['id'])){
    $id = $_GET['id'];
    try{
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = $pdo->prepare("DELETE FROM cards WHERE id = ?");
        $sql->execute([$id]);
        header('Location: cars.php');
        exit;
    }catch(PDOexception $e){
        die("Erreur lors du seppression: " .$e->getMessage());
    }
}
?>