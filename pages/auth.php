<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];    
$user_name = $_SESSION['user_name'];   
// $user_email = $_SESSION['user_email']; 
?>