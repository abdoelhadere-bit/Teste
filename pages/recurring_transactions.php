<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupérer toutes les transactions récurrentes de l'utilisateur
$sqlRecurring = $pdo->prepare("
    SELECT rt.*, c.card_name 
    FROM recurring_transactions rt
    LEFT JOIN cards c ON rt.card_id = c.id
    WHERE rt.user_id = ? 
    ORDER BY rt.day_of_month ASC
");
$sqlRecurring->execute([$user_id]);
$recurringTransactions = $sqlRecurring->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les cartes de l'utilisateur pour le formulaire
$sqlCards = $pdo->prepare("SELECT * FROM cards WHERE user_id = ?");
$sqlCards->execute([$user_id]);
$cards = $sqlCards->fetchAll(PDO::FETCH_ASSOC);

// Catégories
$categoriesExpense = ['Nourriture', 'Transport', 'Loyer', 'Facture Eau', 'Internet', 'Shopping', 'Santé'];
$categoriesIncome = ['Salaire', 'Prime', 'Heures supplémentaires', 'Aide', 'Autre'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <title>Transactions Récurrentes</title>

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

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-income {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .badge-expense {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .badge-active {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .badge-inactive {
            background: rgba(107, 114, 128, 0.2);
            color: #9ca3af;
        }
    </style>
</head>

<body class="p-6">
    
    <!-- HEADER -->
    <div class="max-w-6xl mx-auto mb-10 flex flex-wrap justify-between items-center gap-4 p-4 bg-gray-800/40 backdrop-blur-md rounded-xl border border-gray-700 shadow-lg">
        <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-gray-100 tracking-wide">
                🔄 Transactions Récurrentes
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

            <button id="btnGenerate" class="px-5 py-2 rounded-lg bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Générer maintenant
            </button>

            <button id="btnAdd" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter récurrence
            </button>

            <a href="logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </div>

    <!-- TABLEAU -->
    <div class="max-w-6xl mx-auto card mt-10">
        
        <?php if(empty($recurringTransactions)): ?>
            <!-- Message si aucune transaction récurrente -->
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucune transaction récurrente</h3>
                <p class="text-gray-400 mb-6">Automatisez vos revenus et dépenses mensuels</p>
                <button onclick="document.getElementById('btnAdd').click()" class="btn-primary">
                    Créer ma première récurrence
                </button>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Catégorie</th>
                        <th>Carte</th>
                        <th>Jour</th>
                        <th>Dernière génération</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach($recurringTransactions as $rt): ?>
                    <tr>
                        <!-- Type -->
                        <td>
                            <span class="badge <?= $rt['type'] === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                <?= $rt['type'] === 'income' ? '💰 Revenu' : '💸 Dépense' ?>
                            </span>
                        </td>
                        
                        <!-- Montant -->
                        <td class="font-semibold <?= $rt['type'] === 'income' ? 'text-green-400' : 'text-red-400' ?>">
                            <?= number_format($rt['montant'], 2) ?> DH
                        </td>
                        
                        <!-- Catégorie -->
                        <td><?= htmlspecialchars($rt['category']) ?></td>
                        
                        <!-- Carte -->
                        <td class="text-gray-300"><?= htmlspecialchars($rt['card_name']) ?></td>
                        
                        <!-- Jour du mois -->
                        <td>
                            <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg font-semibold">
                                <?= $rt['day_of_month'] ?><?= $rt['day_of_month'] == 1 ? 'er' : '' ?>
                            </span>
                        </td>
                        
                        <!-- Dernière génération -->
                        <td class="text-sm text-gray-400">
                            <?= $rt['last_generated'] ? date('d/m/Y', strtotime($rt['last_generated'])) : 'Jamais' ?>
                        </td>
                        
                        <!-- Statut -->
                        <td>
                            <form action="toggle_recurring.php" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $rt['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $rt['is_active'] ?>">
                                <button type="submit" class="badge <?= $rt['is_active'] ? 'badge-active' : 'badge-inactive' ?> cursor-pointer">
                                    <?= $rt['is_active'] ? '✓ Actif' : '✗ Inactif' ?>
                                </button>
                            </form>
                        </td>
                        
                        <!-- Actions -->
                        <td class="text-center">
                            <button class="btn-warning editBtn"
                                    data-id="<?= $rt['id'] ?>"
                                    data-type="<?= $rt['type'] ?>"
                                    data-montant="<?= $rt['montant'] ?>"
                                    data-category="<?= htmlspecialchars($rt['category']) ?>"
                                    data-description="<?= htmlspecialchars($rt['description']) ?>"
                                    data-card="<?= $rt['card_id'] ?>"
                                    data-day="<?= $rt['day_of_month'] ?>">
                                Modifier
                            </button>
                            <a href="delete_recurring.php?id=<?= $rt['id'] ?>" 
                               class="btn-danger"
                               onclick="return confirm('Supprimer cette récurrence ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

    <!-- MODAL -->
    <div id="recurringModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50">
        <div class="bg-gray-800 w-full max-w-lg p-6 rounded-2xl shadow-2xl border border-gray-700 m-4">
            
            <h2 id="modalTitle" class="text-xl font-bold mb-4 text-gray-100">Ajouter une transaction récurrente</h2>

            <form id="recurringForm" action="manage_recurring.php" method="POST" data-parsley-validate>
                
                <input type="hidden" id="recurringId" name="recurringId">
                <input type="hidden" name="action" id="formAction" value="add">

                <!-- Type -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Type</label>
                    <select name="type" id="typeSelect" required
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner un type</option>
                        <option value="income">💰 Revenu</option>
                        <option value="expense">💸 Dépense</option>
                    </select>
                </div>

                <!-- Montant -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Montant (DH)</label>
                    <input type="number" name="montant" id="montantInput" required
                        placeholder="Ex: 3000"
                        step="0.01"
                        min="0"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Catégorie -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Catégorie</label>
                    <select name="category" id="categorySelect" required
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une catégorie</option>
                    </select>
                </div>

                <!-- Carte -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Carte</label>
                    <select name="card_id" id="cardSelect" required
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une carte</option>
                        <?php foreach($cards as $card): ?>
                            <option value="<?= $card['id'] ?>"><?= htmlspecialchars($card['card_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jour du mois -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Jour du mois</label>
                    <input type="number" name="day_of_month" id="dayInput" required
                        placeholder="1 à 31"
                        min="1"
                        max="31"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Le jour où la transaction sera créée automatiquement</p>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-200">Description</label>
                    <textarea name="description" id="descriptionInput" required
                        rows="2"
                        placeholder="Ex: Loyer mensuel"
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" id="cancelBtn" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("recurringModal");
        const btnAdd = document.getElementById("btnAdd");
        const cancelBtn = document.getElementById("cancelBtn");
        const recurringForm = document.getElementById("recurringForm");
        const modalTitle = document.getElementById("modalTitle");
        const typeSelect = document.getElementById("typeSelect");
        const categorySelect = document.getElementById("categorySelect");

        const categoriesExpense = <?= json_encode($categoriesExpense) ?>;
        const categoriesIncome = <?= json_encode($categoriesIncome) ?>;

        // Changer les catégories selon le type
        typeSelect.addEventListener('change', function() {
            categorySelect.innerHTML = '<option value="">Sélectionner une catégorie</option>';
            
            const categories = this.value === 'expense' ? categoriesExpense : categoriesIncome;
            
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat;
                categorySelect.appendChild(option);
            });
        });

        // Ouvrir modal pour ajouter
        btnAdd.onclick = () => {
            modalTitle.textContent = "Ajouter une transaction récurrente";
            document.getElementById("formAction").value = "add";
            recurringForm.reset();
            document.getElementById("recurringId").value = "";
            modal.classList.remove("hidden");
        }

        // Fermer modal
        cancelBtn.onclick = () => {
            modal.classList.add("hidden");
        }

        // Modifier
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                modalTitle.textContent = "Modifier la transaction récurrente";
                document.getElementById("formAction").value = "edit";
                
                document.getElementById("recurringId").value = this.dataset.id;
                document.getElementById("typeSelect").value = this.dataset.type;
                
                // Déclencher le changement pour charger les bonnes catégories
                typeSelect.dispatchEvent(new Event('change'));
                
                // Attendre que les catégories soient chargées
                setTimeout(() => {
                    document.getElementById("categorySelect").value = this.dataset.category;
                }, 50);
                
                document.getElementById("montantInput").value = this.dataset.montant;
                document.getElementById("cardSelect").value = this.dataset.card;
                document.getElementById("dayInput").value = this.dataset.day;
                document.getElementById("descriptionInput").value = this.dataset.description;
                
                modal.classList.remove("hidden");
            });
        });

        // Générer maintenant
        document.getElementById("btnGenerate").onclick = () => {
            if(confirm('Générer toutes les transactions récurrentes du jour ?')) {
                window.location.href = 'generate_recurring.php';
            }
        }

        // Fermer modal en cliquant dehors
        modal.onclick = (e) => {
            if(e.target === modal) {
                modal.classList.add("hidden");
            }
        }

        // Initialiser Parsley
        $('#recurringForm').parsley();
    </script>

</body>
</html>