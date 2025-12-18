<?php 
require 'config.php';

session_start();

if(!isset($_SESSION['otp_user_id'])){
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $otp = trim($_POST['otp']);
    $user_id = $_SESSION['otp_user_id'];

    $sql = $pdo->prepare("SELECT * FROM regiser WHERE id = ?");
    $sql->execute([$user_id]);
    $user = $sql->fetch(PDO::FETCH_ASSOC);

    if($user && $user['otp_code'] === $otp && strtotime($user['otp_expire']) >= time()){
        //connexion
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];

        //Netoyage OTP
        $update = $pdo->prepare("UPDATE regiser SET otp_code = NULL, otp_expire = NULL, otp_verified = 1 where id = ?");
        $update->execute([$user['id']]);

        unset($_SESSION['otp_uder_id']);
        header('Location: dark_dashboard.php');
        exit;
    }else{
        $errors = "Code OTP invalide ou expiré";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <main>

        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md w-full max-w-md">
              <h2 class="text-2xl font-bold mb-6 text-center text-gray-900 dark:text-white">Login</h2>

              <form method="POST" class="max-w-sm mx-auto mt-10 p-6 bg-white dark:bg-gray-800 rounded shadow">
                <h2 class="text-xl font-bold text-center mb-4 text-gray-900 dark:text-white">
                    Vérification OTP
                </h2>

                <input type="text" name="otp" maxlength="6"
                    class="w-full text-center tracking-widest text-xl p-3 rounded border"
                    placeholder="------" required>

                <?php if (!empty($error)) : ?>
                    <p class="text-red-500 text-sm mt-2"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                
                <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Vérifier
                </button>
            </form>
            </div>
        </div>
    </main>
</body>
</html>