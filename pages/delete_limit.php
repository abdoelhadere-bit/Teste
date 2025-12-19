
<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(isset($_GET['category'])) {
$category = $_GET['category'];

try {
    $sql = $pdo->prepare("DELETE FROM budget_limits WHERE user_id = ? AND category = ?");
    $sql->execute([$user_id, $category]);
    
    header('Location: budget_limits.php');
    exit;
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
} else {
header('Location: budget_limits.php');
exit;
}
?>