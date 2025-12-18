<?php 
require 'auth.php';
require 'config.php';

// Récupérer toutes les cartes de l'utilisateur
$sqlCards = $pdo->prepare("SELECT * FROM cards WHERE user_id = ? ORDER BY created_at DESC");
$sqlCards->execute([$user_id]);
$cards = $sqlCards->fetchAll(PDO::FETCH_ASSOC);

// Calculer le solde pour chaque carte
foreach($cards as $i => $card) {
    // Revenus de cette carte
    $sqlIncomes = $pdo->prepare("SELECT SUM(montant) as total FROM incomes WHERE card_id = ?");
    $sqlIncomes->execute([$card['id']]);
    $totalIncomes = $sqlIncomes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Dépenses de cette carte
    $sqlExpenses = $pdo->prepare("SELECT SUM(montant) as total FROM expenses WHERE card_id = ?");
    $sqlExpenses->execute([$card['id']]);
    $totalExpenses = $sqlExpenses->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Calcul du solde actuel
    $cards[$i]['current_balance'] = $card['initial_balance'] + $totalIncomes - $totalExpenses;
    // echo $card['current_balance']. '<br>';
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
    <title>Mes Cartes Bancaires</title>

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

        .card-item {
            background: rgba(30, 32, 40, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .card-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border-color: rgba(59, 130, 246, 0.5);
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

        .card-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-debit {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .badge-credit {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .badge-cash {
            background: rgba(168, 85, 247, 0.2);
            color: #a855f7;
        }
    </style>
</head>

<body class="p-6">
    
    <!-- HEADER -->
    <div class="max-w-6xl mx-auto mb-10 flex flex-wrap justify-between items-center gap-4 p-4 bg-gray-800/40 backdrop-blur-md rounded-xl border border-gray-700 shadow-lg">
        <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-gray-100 tracking-wide">
                💳 Mes Cartes Bancaires
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

            <button id="btnAddCard" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter une carte
            </button>

            <a href="logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </div>

    <!-- CARTES GRID -->
    <div class="max-w-6xl mx-auto">
        
        <?php if(empty($cards)): ?>
            <!-- Message si aucune carte -->
            <div class="card text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucune carte enregistrée</h3>
                <p class="text-gray-400 mb-6">Commencez par ajouter votre première carte bancaire</p>
                <button onclick="document.getElementById('btnAddCard').click()" class="btn-primary">
                    Ajouter ma première carte
                </button>
            </div>
        <?php else: ?>
            <!-- Grid des cartes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($cards as $card): ?>
                    <div class="card-item">
                        <!-- En-tête carte -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-100"><?php echo htmlspecialchars($card['card_name']); ?></h3>
                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($card['bank_name']); ?></p>
                            </div>
                            <span class="card-badge badge-<?php echo strtolower($card['card_type']); ?>">
                                <?php echo $card['card_type']; ?>
                            </span>
                        </div>

                        <!-- Numéro de carte -->
                        <?php if($card['card_number']): ?>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Numéro de carte</p>
                                <p class="text-sm text-gray-300 font-mono">•••• •••• •••• <?php echo htmlspecialchars($card['card_number']); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Solde -->
                        <div class="mb-4 p-3 bg-gray-900/50 rounded-lg">
                            <p class="text-xs text-gray-400 mb-1">Solde actuel</p>
                            <p class="text-2xl font-bold <?php echo $card['current_balance'] >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo number_format($card['current_balance'], 2); ?> DH
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Solde initial : <?php echo number_format($card['initial_balance'], 2); ?> DH
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button class="btn-warning flex-1 editCardBtn" 
                                    data-id="<?php echo $card['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($card['card_name']); ?>"
                                    data-bank="<?php echo htmlspecialchars($card['bank_name']); ?>"
                                    data-number="<?php echo htmlspecialchars($card['card_number']); ?>"
                                    data-type="<?php echo $card['card_type']; ?>"
                                    data-balance="<?php echo $card['initial_balance']; ?>">
                                Modifier
                            </button>
                            <a href="delete_cards.php?id=<?php echo $card['id']; ?>" 
                               class="btn-danger flex-1 text-center"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                Supprimer
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
                    <label class="block mb-1 font-semibold text-gray-200">Type de carte</label>
                    <select name="card_type" id="cardType" required
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="Débit">Débit</option>
                        <option value="Crédit">Crédit</option>
                        <option value="Cash">Cash</option>
                    </select>
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
                document.getElementById("cardType").value = this.dataset.type;
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