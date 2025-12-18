<?php 
require 'config.php';
require 'auth.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $montant = trim($_POST['montant']);
    $category = $_POST['Categorie'];
    $description = trim($_POST['description']);
    $date = trim($_POST['date']);
    $source = $_POST['source'];
    
    // if(empty($id) || empty($montant) || empty($description) || empty($date)){
    //     die("Erreur: Tous les champs sont requis!");
    // }
    
    try{
        if($source === 'incomes'){
            $id = $_POST['incomeId'];
            $sql = $pdo->prepare("UPDATE incomes SET montant = ?, dates = ?, decription = ? WHERE id = ?");
            $sql->execute([$montant, $date, $description, $id]);
            
            header('Location: incomes.php');
            exit;
        }elseif($source === 'expenses'){
            $sqlBudget = $pdo->prepare("SELECT monthly_limit 
                                            from budget_limits 
                                            where user_id = ? 
                                            AND category = ?");
            $sqlBudget->execute([$user_id, $category]);
            $budgetLimit = $sqlBudget->fetch(PDO::FETCH_ASSOC)['monthly_limit'] ?? null;

            $sqlBudgetCurrent = $pdo->prepare("SELECT SUM(montant) as total 
                                               from expenses 
                                               where user_id = ? 
                                               and category = ? 
                                               and MONTH(dates) = MONTH(CURRENT_DATE()) 
                                               and YEAR(dates) = YEAR(CURRENT_DATE())");
            $sqlBudgetCurrent->execute([$user_id, $category]);
            $currentBudget = $sqlBudgetCurrent->fetch(PDO::FETCH_ASSOC)['total'] ?? null;

            if($currentBudget != null && ($montant + $currentBudget) >$budgetLimit ){
                $_SESSION['error'] = "Vous pouvez pas dépasser la limite";
                header('Location: expenses.php');
                exit;
            }

            $id = $_POST['expensesId'];
            $sql = $pdo->prepare("UPDATE expenses SET montant = ?, dates = ?, decription = ? WHERE id = ?");
            $sql->execute([$montant, $date, $description, $id]);
            
            header('Location: expenses.php');
            exit;
        }

    }catch(PDOException $e){
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
} else {
    if($source === 'expenses') header('Location: expenses.php');
    if($source === 'incomes') header('Location: index.php');
    exit;
}
?>