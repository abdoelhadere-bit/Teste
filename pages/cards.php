<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupérer toutes les cartes
$sqlCards = $pdo->prepare("SELECT * FROM cards WHERE user_id = ? ORDER BY created_at DESC");
$sqlCards->execute([$user_id]);
$cards = $sqlCards->fetchAll(PDO::FETCH_ASSOC);

// Calcul des soldes
foreach($cards as $i => $card) {
    $sqlIncomes = $pdo->prepare("SELECT SUM(montant) total FROM incomes WHERE card_id = ?");
    $sqlIncomes->execute([$card['id']]);
    $incomes = $sqlIncomes->fetch()['total'] ?? 0;

    $sqlExpenses = $pdo->prepare("SELECT SUM(montant) total FROM expenses WHERE card_id = ?");
    $sqlExpenses->execute([$card['id']]);
    $expenses = $sqlExpenses->fetch()['total'] ?? 0;

    $cards[$i]['current_balance'] = $card['initial_balance'] + $incomes - $expenses;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Cartes - Smart Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <style>
        body {
            background: #0f1117;
            color: #e5e7eb;
            font-family: Inter, sans-serif;
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
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar-item:hover {
            background: rgba(59,130,246,0.1);
            color: #3b82f6;
        }

        .sidebar-item.active {
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            color: white;
        }

        /* MAIN */
        .main-content {
            margin-left: 280px;
            padding: 24px;
        }

        .card {
            background: rgba(30,32,40,.7);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,.05);
            border-radius: 16px;
            padding: 24px;
        }

        .card-item {
            background: rgba(30,32,40,.9);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            padding: 20px;
            transition: .2s;
        }

        .card-item:hover {
            transform: translateY(-4px);
            border-color: rgba(59,130,246,.5);
        }

        .btn-primary {
            background: linear-gradient(to right,#3b82f6,#2563eb);
            padding: 10px 16px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }

        .btn-warning {
            background: rgba(59,130,246,.15);
            color: #3b82f6;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .btn-danger {
            background: rgba(239,68,68,.15);
            color: #ef4444;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .btn-secondary {
            background: #374151;
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
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold">
                <?= strtoupper($user_name[0]) ?>
            </div>
            <div>
                <p class="font-semibold"><?= htmlspecialchars($user_name) ?></p>
                <p class="text-xs text-gray-400">Utilisateur</p>
            </div>
        </div>
    </div>

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
            <a href="expenses.php" class="sidebar-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Dépenses
            </a>

            <!-- Mes Cartes -->
            <a href="cards.php" class="sidebar-item active">
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

<!-- MAIN -->
<div class="main-content">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold">💳 Mes Cartes</h1>
            <p class="text-gray-400">Gestion de vos cartes bancaires</p>
        </div>
        <button id="btnAddCard" class="btn-primary">+ Ajouter une carte</button>
    </div>

    <!-- CONTENT -->
    <?php if(empty($cards)): ?>
        <div class="card text-center py-12">
            <p class="text-gray-400 mb-4">Aucune carte enregistrée</p>
            <button class="btn-primary" onclick="btnAddCard.click()">Ajouter une carte</button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($cards as $card): ?>
                <div class="card-item">
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($card['card_name']) ?></h3>
                    <p class="text-gray-400 text-sm mb-3"><?= htmlspecialchars($card['bank_name']) ?></p>

                    <div class="bg-gray-900/60 p-3 rounded-lg mb-4">
                        <p class="text-xs text-gray-400">Solde actuel</p>
                        <p class="text-2xl font-bold <?= $card['current_balance']>=0?'text-green-400':'text-red-400' ?>">
                            <?= number_format($card['current_balance'],2) ?> DH
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button class="btn-warning editCardBtn flex-1"
                            data-id="<?= $card['id'] ?>"
                            data-name="<?= htmlspecialchars($card['card_name']) ?>"
                            data-bank="<?= htmlspecialchars($card['bank_name']) ?>"
                            data-number="<?= htmlspecialchars($card['card_number']) ?>"
                            data-balance="<?= $card['initial_balance'] ?>">
                            Modifier
                        </button>
                        <a href="delete_cards.php?id=<?= $card['id'] ?>" 
                           class="btn-danger flex-1 text-center"
                           onclick="return confirm('Supprimer cette carte ?')">
                            Supprimer
                        </a>
                        <a href="set_primary_card.php?id=<?= $card['id'] ?>" class="btn-primary">
                            Définir principale
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

    <!-- MODAL AJOUTER/MODIFIER CARTE -->
    <div id="cardModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50">
        <div class="bg-gray-800 w-full max-w-md p-6 rounded-2xl shadow-2xl border border-gray-700 m-4">
            
            <h2 id="cardModalTitle" class="text-xl font-bold mb-4 text-gray-100">Ajouter une carte</h2>

            <form id="cardForm" action="manage_card.php" method="POST" data-parsley-validate>
                
                <input type="hidden" id="cardId" name="cardId">
                <input type="hidden" name="action" id="formAction" value="add">

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Nom de la carte</label>
                    <input type="text" name="card_name" id="cardName" required
                        placeholder="Ex: Carte Salaire"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-required-message="Le nom est requis">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Banque</label>
                    <select name="bank_name" id="bankName" required
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une banque</option>
                        <option value="Banque Populaire">Banque Populaire</option>
                        <option value="CIH Bank">CIH Bank</option>
                        <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                        <option value="BMCE Bank">BMCE Bank</option>
                        <option value="Crédit du Maroc">Crédit du Maroc</option>
                        <option value="Société Générale">Société Générale</option>
                        <option value="Cash">Cash (Espèces)</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">4 derniers chiffres (optionnel)</label>
                    <input type="text" name="card_number" id="cardNumber"
                        placeholder="Ex: 4567"
                        maxlength="4"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-type="digits"
                        data-parsley-length="[4, 4]"
                        data-parsley-length-message="Exactement 4 chiffres">
                </div>


                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Solde initial</label>
                    <input type="number" name="initial_balance" id="initialBalance" required
                        placeholder="Ex: 5000"
                        step="0.01"
                        min="0"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-required-message="Le solde initial est requis"
                        data-parsley-type="number">
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" id="cancelCardBtn" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        const modal = document.getElementById("cardModal");
        const btnAddCard = document.getElementById("btnAddCard");
        const cancelCardBtn = document.getElementById("cancelCardBtn");
        const cardForm = document.getElementById("cardForm");
        const modalTitle = document.getElementById("cardModalTitle");

        // Ouvrir modal pour ajouter
        btnAddCard.onclick = () => {
            modalTitle.textContent = "Ajouter une carte";
            document.getElementById("formAction").value = "add";
            cardForm.reset();
            document.getElementById("cardId").value = "";
            modal.classList.remove("hidden");
        }

        // Fermer modal
        cancelCardBtn.onclick = () => {
            modal.classList.add("hidden");
        }

        // Modifier une carte
        document.querySelectorAll('.editCardBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                modalTitle.textContent = "Modifier la carte";
                document.getElementById("formAction").value = "edit";
                
                document.getElementById("cardId").value = this.dataset.id;
                document.getElementById("cardName").value = this.dataset.name;
                document.getElementById("bankName").value = this.dataset.bank;
                document.getElementById("cardNumber").value = this.dataset.number;
                document.getElementById("initialBalance").value = this.dataset.balance;
                
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
        $('#cardForm').parsley();
    </script>

</body>
</html>