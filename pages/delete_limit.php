
<?php
require 'auth.php';
require 'config.php';
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