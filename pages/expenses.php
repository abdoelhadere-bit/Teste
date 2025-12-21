<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$sql = $pdo->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY dates DESC");
$sql->execute([$user_id]);
$amouts = $sql->fetchAll(PDO::FETCH_ASSOC);

$sqlCards = $pdo->prepare("SELECT * FROM cards WHERE user_id = ?");
$sqlCards->execute([$user_id]);
$cards = $sqlCards->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépenses - Smart Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <style>
        body {
            background: #0f1117;
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }

        /* SIDEBAR */
        .sidebar {
    width: 280px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg,#1a1d29,#0f1117);
    border-right: 1px solid rgba(255,255,255,.08);

    /* IMPORTANT */
    display: flex;
    flex-direction: column;
}

.sidebar-item svg {
    width: 22px;
    height: 22px;
    margin-right: 12px;
}

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            margin: 6px 12px;
            border-radius: 12px;
            color: #9ca3af;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .sidebar-item:hover {
            background: rgba(59,130,246,0.1);
            color: #3b82f6;
        }

        .sidebar-item.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 15px rgba(59,130,246,0.3);
        }

        .sidebar-item svg {
            width: 22px;
            height: 22px;
            margin-right: 12px;
        }

        /* MAIN */
        .main-content {
            margin-left: 280px;
            padding: 24px;
        }

        .card {
            background: rgba(30,32,40,0.7);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            padding: 14px;
            text-transform: uppercase;
            font-size: 13px;
            color: #9ca3af;
            text-align: left;
        }

        table td {
            padding: 14px;
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: rgba(59,130,246,0.08);
        }

        .btn-primary {
            background: linear-gradient(to right, #3b82f6, #2563eb);
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 600;
            color: white;
        }

        .btn-warning {
            background: rgba(59,130,246,0.15);
            color: #3b82f6;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-danger {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-secondary {
            background: #374151;
            color: #e5e7eb;
            padding: 10px 16px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="p-6 border-b border-gray-700/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                <?= strtoupper(substr($user_name, 0, 1)) ?>
            </div>
            <div>
                <p class="font-semibold"><?= htmlspecialchars($user_name) ?></p>
                <p class="text-xs text-gray-400">Utilisateur</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
        <nav class="py-4">
            <!-- Dashboard -->
            <a href="dark_dashboard.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <!-- Revenus -->
            <a href="incomes.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Revenus
            </a>

            <!-- Dépenses -->
            <a href="expenses.php" class="sidebar-item active">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Dépenses
            </a>

            <!-- Mes Cartes -->
            <a href="cards.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Mes Cartes
            </a>

            <!-- Limites Budgétaires -->
            <a href="budget_limits.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Limites
            </a>

            <!-- Transactions Récurrentes -->
            <a href="recurring_transactions.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Récurrentes
            </a>

            <!-- Transferts -->
            <a href="transfers.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Transferts
                <!-- <span class="badge">NEW</span> -->
            </a>
        </nav>

        <!-- Logout -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700/50">
            <a href="logout.php" class="sidebar-item bg-red-500/10 hover:bg-red-500/20 text-red-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Déconnexion
            </a>
        </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- HEADER -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-2">
                Dépenses
            </h1>
            <p class="text-gray-400">Historique et gestion de vos dépenses</p>
        </div>
        <button id="btnAdd" class="btn-primary">+ Ajouter une dépense</button>
    </div>

    <?php if($errors): ?>
        <div class="mb-4 p-3 bg-red-500/10 text-red-400 rounded-lg text-center">
            <?= htmlspecialchars($errors) ?>
        </div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Montant</th>
                    <th>Catégorie</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($amouts as $amout): ?>
                <tr>
                    <td><?= $amout['id'] ?></td>
                    <td><?= $amout['montant'] ?> DH</td>
                    <td><?= $amout['category'] ?></td>
                    <td><?= date('Y-m-d', strtotime($amout['dates'])) ?></td>
                    <td><?= $amout['decription'] ?></td>
                    <td class="text-center space-x-2">
                        <button class="btn-warning editBtn">Modifier</button>
                        <a href="delete.php?id=<?= $amout['id'] ?>&source=expenses" class="btn-danger">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- MODAL -->
    <div id="formModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center">
        <div class="bg-gray-800 w-96 p-6 rounded-2xl shadow-2xl border border-gray-700">

            <h2 id="formTitle" class="text-xl font-bold mb-4 text-gray-100">Ajouter un dépense</h2>

            <form id="expensesForm" class="space-y-4" action="addIncome.php" method="POST" data-parsley-validate>

                <input type="hidden" id="expensesId" name="expensesId">
                <input type="hidden" name="source" value="expenses">

                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Montant</label>
                    <input type="number" name="montant" id="amount" required
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-pattern = "^[0-9]+$"
                         data-parsley-trigger = "change"
                         data-parslay-message="hqdhqs">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Categorie</label>
                    <select name="Categorie" id="Categorie" required 
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une categorie</option>
                        <option value="Nourriture">Nourriture</option>
                        <option value="Transport">Transport</option>
                        <option value="Loyer">Loyer</option>
                        <option value="Facture Eau">Facture Électricité</option>
                        <option value="Internet">Internet</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Santé">Santé</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Date</label>
                    <input type="date" name="date" id="incomeDate" required
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-max="<?= date('Y-m-d'); ?>"
                        data-parsley-max-message="La date ne peut pas dépasser aujourd'hui."
                        data-parsley-trigger = "change">
                </div>

                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Cartes</label>
                    <select name="cards" id="cards" required 
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une carte</option>
                        <?php foreach($cards as $card) :?>
                            <option value="<?= $card['id'] ?>"><?= $card['card_name'] ?></option>
                        <?php endforeach ; ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Description</label>
                    <textarea id="description" name="description" required
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="cancelBtn" class="btn-secondary">Annuler</button>
                    <button type="submit" id="submitBtn" class="btn-primary opacity-50 cursor-not-allowed" disabled=true>Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <!-- JS -->
    <script>
        const modal = document.getElementById("formModal");
        const btnAdd = document.getElementById("btnAdd");
        const cancelBtn = document.getElementById("cancelBtn");
        const form = document.getElementById("expensesForm");
        const formTitle = document.getElementById("formTitle");

        btnAdd.onclick = () => {
            formTitle.textContent = "Ajouter un dépenses";
            form.action = "addIncome.php";
            form.reset();
            document.getElementById("expensesId").value = "";
            modal.classList.remove("hidden");
        }

        cancelBtn.onclick = () => {
            modal.classList.add("hidden");
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    formTitle.textContent = "Modifier un dépense";
                    form.action = "update.php";
                    e.preventDefault();

                    const row = this.closest('tr');
                    const id = row.cells[0].textContent.trim();
                    const montant = row.cells[1].textContent.trim();
                    const category = row.cells[2].textContent.trim();
                    const date = row.cells[3].textContent.trim();
                    const desc = row.cells[4].textContent.trim();

                    document.getElementById("expensesId").value = id;
                    document.getElementById("amount").value = montant;
                    document.getElementById("Categorie").value = category;
                    document.getElementById("incomeDate").value = date;
                    document.getElementById("description").value = desc;

                    modal.classList.remove("hidden");
                    
                });
            });
        });
        
        form.onsubmit = () => {
            modal.classList.add("hidden");
        }
        const parsleyCheck = $(form).parsley()

        form.addEventListener('input', () => {
            if(parsleyCheck.isValid()){
                submitBtn.disabled = false
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed')
            }else{
                submitBtn.disabled = true
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed')
            }
        })
        </script>

</body>
</html>
