<?php 
require 'config.php';
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    //Verification côté serveur
    $errors = [];

    if(empty($name)){
        $errors[] = "Le nom est equis";
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Email non valide ";
    }
    if(STRLEN($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)){
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères, des lettres et des chiffres.";
    }
    if($password != $confirm_password){
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if(empty($errors)){
        $sqlCheck = $pdo->prepare("SELECT COUNT(email) FROM regiser WHERE email = ?");
        $sqlCheck->execute([$email]);
        $count = $sqlCheck->fetchColumn();
    
        if($count > 0){
            $errors[] =  "cet email déjà existe";
        }else{
            //Hash the password
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    
            $sql = $pdo->prepare("INSERT INTO regiser(nom, email, pass) VALUES (?, ?, ?)");
            $_SESSION['success'] = "Inscription réussie. Vous pouvez vous connecter.";
            $sql->execute([$name, $email, $hashPassword]);
            header('Location: login.php');
            exit;
        }
    }else{
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: register.php');
        exit;
    }
}
?>