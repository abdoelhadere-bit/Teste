<?php 
require 'auth.php';
require 'config.php';


$categories = [
    'Nourriture',
    'Transport',
    'Loyer',
    'Facture Eau',
    'Internet',
    'Shopping',
    'Santé'
];
try{
    $sqlLimits = $pdo->prepare("SELECT * FROM budget_limits WHERE user_id = ?");
    $sqlLimits->execute([$user_id]);
    $limits = $sqlLimits->fetchAll(PDO::FETCH_ASSOC);

}catch(PDOException $e){
    die("Erreur lors de Selection: ".$e->getMessage());
}

$expensesArray = [];

foreach($categories as $category){
    $sqlExpense = $pdo->prepare("SELECT SUM(montant) as total from expenses
                          WHERE user_id = ? AND category = ? 
                          AND MONTH(dates) = MONTH(CURRENT_DATE())
                          AND YEAR(dates) = YEAR(CURRENT_DATE())");
    $sqlExpense->execute([$user_id, $category]);
    $total = $sqlExpense->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $expensesArray[$category] = $total;
}

$limitsArray = [];
foreach($limits as $limit){
    $limitsArray[$limit['category']] = $limit['monthly_limit'];
}

$budgetData = [];
foreach($categories as $category){

    $limit = $limitsArray[$category] ?? null;
    $spent = $expensesArray[$category];

    if($limit !== null){
        $rest = $limit - $spent;
        $percentage = $limit > 0 ? ($spent/$limit)*100 : 0;
    }else{
        $rest = null;
        $percentage = 0;
    }

    $budgetData[] = [
        'category'   =>$category,
        'limit'      =>$limit,
        'spent'      =>$spent,
        'rest'       =>$rest,
        'percentage'=>$percentage
    ];
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <title>Limites Budgétaires</title>
<style>
    body {
        background: #0f1117;
        color: #e5e7eb;
        font-family: Inter, sans-serif;
    }

    .card {
        background: rgba(30, 32, 40, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.05);
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.2);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead tr {
        background: rgba(255,255,255,0.04);
    }

    table th {
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
    }

    table tbody tr {
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: 0.2s ease;
    }

    table tbody tr:hover {
        background: rgba(255,255,255,0.08);
    }

    table td {
        padding: 14px 16px;
        font-size: 15px;
    }

    .btn-primary {
        background: #3b82f6;
        padding: 10px 16px;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-warning {
        background: #f59e0b;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 14px;
        color: #1e1e1e;
        font-weight: 600;
    }

    .btn-danger {
        background: #ef4444;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 14px;
        color: white;
        font-weight: 600;
    }

    .btn-secondary {
        background: rgba(255,255,255,0.1);
        padding: 8px 14px;
        border-radius: 8px;
        color: #e5e7eb;
        font-weight: 600;
        transition: 0.2s;
    }

    .progress-bar {
        height: 8px;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 4px;
    }

    .progress-fill {
        height: 100%;
        transition: width 0.3s ease;
    }

    .progress-success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
    }

    .progress-warning {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .progress-danger {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }
</style>
</head>

<body class="p-6">
    <!-- HEADER -->
<div class="max-w-6xl mx-auto mb-10 flex flex-wrap justify-between items-center gap-4 p-4 bg-gray-800/40 backdrop-blur-md rounded-xl border border-gray-700 shadow-lg">
    <div class="flex flex-col">
        <h1 class="text-3xl font-bold text-gray-100 tracking-wide">
            💰 Limites Budgétaires
        </h1>
        <p class="text-gray-400 text-sm mt-1">
            Bienvenue, <?php echo htmlspecialchars($user_name); ?>
        </p>
    </div>

    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
    <a href="dark_dashboard.php" class="px-4 py-2 rounded-lg bg-gray-700 text-gray-200 font-semibold shadow hover:bg-gray-600 transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-7-7v18" />
        </svg>
        Dashboard
    </a>

    <button id="btnAddLimit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Définir une limite
    </button>

    <a href="logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Logout
    </a>
</div>
</div>

<div class="max-w-6xl mx-auto card mt-10">
    <table>
        <thead>
            <tr>
                <th>Catégorie</th>
                <th>Dépensé ce mois</th>
                <th>Limite mensuelle</th>
                <th>Reste</th>
                <th>Progression</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
    <tbody>
    <?php foreach($budgetData as $data): ?>
        <tr>
            <!-- Catégorie -->
            <td class="font-semibold text-center"><?= htmlspecialchars($data['category']) ?></td>
            
            <!-- Dépensé ce mois -->
            <td class="text-center"><?= number_format($data['spent'], 2) ?> DH</td>
            
            <!-- Limite mensuelle -->
            <td class="text-center">
                <?php if($data['limit'] !== null): ?>
                    <?= number_format($data['limit'], 2) ?> DH
                <?php else: ?>
                    <span class="text-gray-500">Non définie</span>
                <?php endif; ?>
            </td>
            
            <!-- Reste -->
            <td class="text-center">
                <?php if($data['rest'] !== null): ?>
                    <span class="<?= $data['rest'] >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                        <?= number_format($data['rest'], 2) ?> DH
                    </span>
                <?php else: ?>
                    <span class="text-gray-500">-</span>
                <?php endif; ?>
            </td>
            
            <!-- Barre de progression -->
            <td class="text-center" style="min-width: 200px;">
                <?php if($data['limit'] !== null): ?>
                    <div class="flex items-center gap-2">
                        <div class="progress-bar flex-1">
                            <?php 
                                $percentage = min($data['percentage'], 100); // Limiter à 100% pour la barre
                                if($data['percentage'] < 75) {
                                    $colorClass = 'progress-success';
                                } elseif($data['percentage'] < 100) {
                                    $colorClass = 'progress-warning';
                                } else {
                                    $colorClass = 'progress-danger';
                                }
                            ?>
                            <div class="progress-fill <?= $colorClass ?>" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="text-sm font-semibold <?= $data['percentage'] > 100 ? 'text-red-400' : 'text-gray-300' ?>">
                            <?= round($data['percentage'], 1) ?>%
                        </span>
                    </div>
                <?php else: ?>
                    <span class="text-gray-500 text-sm">Aucune limite</span>
                <?php endif; ?>
            </td>
            
            <!-- Actions -->
            <td class="text-center">
                <?php if($data['limit'] !== null): ?>
                    <!-- Modifier -->
                    <button class="btn-warning editLimitBtn"
                            data-category="<?= htmlspecialchars($data['category']) ?>"
                            data-limit="<?= $data['limit'] ?>">
                        Modifier
                    </button>
                    <!-- Supprimer -->
                    <a href="delete_limit.php?category=<?= urlencode($data['category']) ?>" 
                       class="btn-danger"
                       onclick="return confirm('Supprimer la limite pour <?= htmlspecialchars($data['category']) ?> ?')">
                        Supprimer
                    </a>
                <?php else: ?>
                    <!-- Définir -->
                    <button class="btn-primary defineLimitBtn"
                            data-category="<?= htmlspecialchars($data['category']) ?>">
                        Définir limite
                    </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- MODAL AJOUTER/MODIFIER LIMITE -->
<div id="limitModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-gray-800 w-full max-w-md p-6 rounded-2xl shadow-2xl border border-gray-700 m-4">
    <h2 id="limitModalTitle" class="text-xl font-bold mb-4 text-gray-100">Définir une limite</h2>

    <form id="limitForm" action="manage_budget.php" method="POST" data-parsley-validate>
        
        <input type="hidden" name="action" id="formAction" value="add">
        
        <div class="mb-4">
            <label class="block mb-1 font-semibold text-gray-200">Catégorie</label>
            <select name="category" id="categorySelect" required
                    class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                <option value="">Sélectionner une catégorie</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold text-gray-200">Limite mensuelle (DH)</label>
            <input type="number" name="monthly_limit" id="monthlyLimit" required
                placeholder="Ex: 1500"
                step="0.01"
                min="0"
                class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                data-parsley-required-message="La limite est requise"
                data-parsley-type="number">
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <button type="button" id="cancelLimitBtn" class="btn-secondary">Annuler</button>
            <button type="submit" class="btn-primary">Enregistrer</button>
        </div>

    </form>
</div>
</div>
</body>
<script>
    const modal = document.getElementById("limitModal");
    const btnAddLimit = document.getElementById("btnAddLimit");
    const cancelLimitBtn = document.getElementById("cancelLimitBtn");
    const limitForm = document.getElementById("limitForm");
    const modalTitle = document.getElementById("limitModalTitle");
    const categorySelect = document.getElementById("categorySelect");
// Ouvrir modal pour ajouter
btnAddLimit.onclick = () => {
    modalTitle.textContent = "Définir une limite";
    document.getElementById("formAction").value = "add";
    limitForm.reset();

    categorySelect.style.pointerEvents = 'auto'
    categorySelect.style.opacity = '1'
    categorySelect.setAttribute('name', 'category')

    const hiddenCategory = document.getElementById('hiddenCategory');
    if(hiddenCategory){
        hiddenCategory.remove()
    }

    categorySelect.disabled = false;
    modal.classList.remove("hidden");
}

// Fermer modal
cancelLimitBtn.onclick = () => {
    modal.classList.add("hidden");
}

// Définir limite (catégories sans limite)
document.querySelectorAll('.defineLimitBtn').forEach(btn => {
    btn.addEventListener('click', function() {

        modalTitle.textContent = "Définir une limite pour " + this.dataset.category;
        document.getElementById("formAction").value = "add";
        limitForm.reset();
        categorySelect.value = this.dataset.category;
        categorySelect.style.pointerEvents = 'none';
        categorySelect.style.opacity = '0.6';

        let hiddenCategory = document.getElementById('hiddenCategory');
        if(!hiddenCategory){
            hiddenCategory = document.createElement('input');
            hiddenCategory.type = 'hidden';
            hiddenCategory.id = 'hiddenCategory';
            hiddenCategory.name = 'category';
            limitForm.appendChild(hiddenCategory);
        }
        hiddenCategory.value = this.dataset.category;

        categorySelect.removeAttribute('name');
        categorySelect.disabled = true; // Empêcher de changer la catégorie
        modal.classList.remove("hidden");
    });
});

// Modifier une limite existante
document.querySelectorAll('.editLimitBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        modalTitle.textContent = "Modifier la limite pour " + this.dataset.category;
        document.getElementById("formAction").value = "edit";
        
        categorySelect.style.pointerEvents = 'none';
        categorySelect.style.opacity = '0.6';
        categorySelect.value = this.dataset.category;
        
        let hiddenCategory = document.getElementById('hiddenCategory')

        if(!hiddenCategory){
            hiddenCategory = document.createElement('input');
            hiddenCategory.type = 'hidden';
            hiddenCategory.id = 'hiddenCategory'
            hiddenCategory.name = 'category';
            limitForm.appendChild(hiddenCategory);
        }

        hiddenCategory.value = this.dataset.category;

        categorySelect.removeAttribute('name');
        categorySelect.disabled = true;
        document.getElementById("monthlyLimit").value = this.dataset.limit;
        
        modal.classList.remove("hidden");
    });
});

// Fermer modal en cliquant en dehors
modal.onclick = (e) => {
    if(e.target === modal) {
        modal.classList.add("hidden");
    }
}

// Initialiser Parsley
$('#limitForm').parsley();
</script>
</html>
