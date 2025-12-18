<?php 
require 'auth.php';
require 'config.php';

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