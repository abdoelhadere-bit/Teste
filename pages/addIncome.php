<?php 
require 'auth.php';
require 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $montant = (float) $_POST['montant'];
        $category = $_POST['Categorie'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $source = $_POST['source'];
        $card_id = $_POST['cards'];
        try{
            if($source === 'incomes'){
                $sql = $pdo->prepare("INSERT INTO 
                                      incomes (montant, `dates`, decription, category, user_id, card_id) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $sql->execute([$montant, $date, $description, $category, $user_id, $card_id]);
                header('Location: incomes.php');
                exit;
            }
            if($source === 'expenses'){
                //Import the month limit
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

                $sql = $pdo->prepare("INSERT INTO expenses (montant, `dates`, decription, category, user_id, card_id) VALUES (?, ?, ?, ?, ?, ?)");
                $sql->execute([$montant, $date, $description, $category, $user_id, $card_id]);

                $sqlBudget = $pdo->prepare("INSERT INTO budget_limits ()");
                 // $_SESSION['error'] = "Vous pouvez pas dépasser la limite";
                header('Location: expenses.php');
                exit;

            }
        }catch(PDOexception $e){
            die("Erreur lors de l'insertion : ".$e->getMessage());
        }
    
    
}
?>