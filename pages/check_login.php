<?php 
require 'config.php';
require 'mail_config.php';

session_start();
// unset($_SESSION['success']);
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //Verifier les champs vides
    if(empty($email) || empty($password)){

        $_SESSION['errors'] = "Les changs sont requis";
        header('Location: login.php');
        exit;
    }

    $sqlCheck = $pdo->prepare("SELECT * FROM regiser WHERE email = ?");
    $sqlCheck->execute([$email]);

    $user = $sqlCheck->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['pass'])){

        //Génération OTP
        $otp = random_int(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minute'));
        
        //Sauvgarde OTP
        $update = $pdo->prepare("UPDATE regiser SET otp_code = ?, otp_expire = ?, otp_verified = 0 WHERE id = ?");
        $update->execute([$otp, $expires, $user['id']]);

        //Envoyer l'email

        sendOTP($user['email'], $user['nom'], $otp);

        //Sauvegarder l'id d'ultilisateur
        $_SESSION['otp_user_id'] = $user['id'];


        header('Location: verify_otp.php');
        exit;
    }else{
        
        $_SESSION['errors'] = "Email ou mot de passe incorrect";
        header('Location: login.php');
        exit;
    }
}
