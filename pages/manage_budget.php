<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'];
    $category = trim($_POST['category']);
    $monthly_limit = trim($_POST['monthly_limit']);
    if($action === 'add'){
        try{
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlFetch = $pdo->prepare("SELECT id FROM budget_limits WHERE user_id = ? AND category = ?");
            $sqlFetch->execute([$user_id, $category]);

            if($sqlFetch->fetch()){
                $sqlUpdate = $pdo->prepare("UPDATE budget_limits SET monthly_limit = ? WHERE user_id = ? AND category = ?");
                $sqlUpdate->execute([$monthly_limit, $user_id, $category]);
            }else{
                $sql = $pdo->prepare("INSERT INTO budget_limits (user_id, category, monthly_limit) VALUES (?, ?, ?)");
                $sql->execute([$user_id, $category, $monthly_limit]);
            }


            header('Location: budget_limits.php');
            exit;
        }catch(PDOException $e){
            die("Erreur lors de l'insertion: ".$e->getMessage());
        }
    }elseif($action === 'edit') {
        try {
            $sql = $pdo->prepare("UPDATE budget_limits SET monthly_limit = ? WHERE user_id = ? AND category = ?");
            $sql->execute([$monthly_limit, $user_id, $category]);
            
            header('Location: budget_limits.php');
            exit;
            
        } catch(PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
}

// require 'auth.php';
// require 'config.php';

// if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
//     $action = $_POST['action'];
//     $category = $_POST['category'];
//     $monthlyLimit = $_POST['monthly_limit'];
    
//     // ✅ DÉBOGAGE - À SUPPRIMER APRÈS
//     echo "<h3>Débogage :</h3>";
//     echo "Action: " . $action . "<br>";
//     echo "User ID: " . $user_id . "<br>";
//     echo "Category: " . $category . "<br>";
//     echo "Monthly Limit: " . $monthlyLimit . "<br>";
    
//     if($action === 'add') {
//         try {
//             // Vérifier si une limite existe déjà
//             $checkSql = $pdo->prepare("SELECT id FROM budget_limits WHERE user_id = ? AND category = ?");
//             $checkSql->execute([$user_id, $category]);
//             $existing = $checkSql->fetch();
            
//             echo "Limite existante trouvée: " . ($existing ? "OUI (ID: {$existing['id']})" : "NON") . "<br>";
            
//             if($existing) {
//                 // Limite existe, UPDATE
//                 echo "Action: UPDATE<br>";
//                 $sql = $pdo->prepare("UPDATE budget_limits SET monthly_limit = ? WHERE user_id = ? AND category = ?");
//                 $sql->execute([$monthlyLimit, $user_id, $category]);
//                 echo "UPDATE effectué avec succès<br>";
//             } else {
//                 // Pas de limite, INSERT
//                 echo "Action: INSERT<br>";
//                 $sql = $pdo->prepare("INSERT INTO budget_limits (user_id, category, monthly_limit) VALUES (?, ?, ?)");
//                 $sql->execute([$user_id, $category, $monthlyLimit]);
//                 echo "INSERT effectué avec succès<br>";
//                 echo "Dernier ID inséré: " . $pdo->lastInsertId() . "<br>";
//             }
            
//             echo "<br><a href='budget_limits.php'>Retour</a>";
//             // Commentez temporairement la redirection pour voir les messages
//             // header('Location: budget_limits.php');
//             // exit;
            
//         } catch(PDOException $e) {
//             echo "ERREUR PDO: " . $e->getMessage() . "<br>";
//             die();
//         }
//     }
    
//     elseif($action === 'edit') {
//         try {
//             echo "Action: EDIT (UPDATE)<br>";
//             $sql = $pdo->prepare("UPDATE budget_limits SET monthly_limit = ? WHERE user_id = ? AND category = ?");
//             $sql->execute([$monthlyLimit, $user_id, $category]);
//             echo "UPDATE effectué avec succès<br>";
            
//             echo "<br><a href='budget_limits.php'>Retour</a>";
//             // header('Location: budget_limits.php');
//             // exit;
            
//         } catch(PDOException $e) {
//             echo "ERREUR PDO: " . $e->getMessage() . "<br>";
//             die();
//         }
//     }
    
// } else {
//     echo "Méthode non POST";
// }
?>
