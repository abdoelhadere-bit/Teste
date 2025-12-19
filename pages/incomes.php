<?php 
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = $pdo->prepare("SELECT i.*, c.card_name FROM incomes i 
                      LEFT JOIN cards c ON i.card_id = c.id
                      WHERE i.user_id = ? 
                      order by i.dates desc");
$sql->execute([$user_id]);
$amouts = $sql->fetchAll(PDO::FETCH_ASSOC);

$sqlCards = $pdo->prepare("SELECT * FROM cards WHERE user_id = ?");
$sqlCards->execute([$user_id]);
$cards = $sqlCards->fetchAll(PDO::FETCH_ASSOC);

// $cardsById = [];
// foreach ($cards as $card) {
//     $cardsById[$card['id']] = $card['card_name'];
// }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <title>Gestion des Revenus</title>

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

    .header-title {
        font-size: 32px;
        font-weight: 700;
        color: #f3f4f6;
    }
</style>

</head>

<body class="p-6">

       <!-- HEADER -->
    <div class="max-w-6xl mx-auto mb-10 flex flex-wrap justify-between items-center gap-4 p-4 bg-gray-800/40 backdrop-blur-md rounded-xl border border-gray-700 shadow-lg">
        <div class="flex flex-col">
            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-100 tracking-wide">
                Gestion des Revenues
            </h1>
            <a href="expenses.php" class="text-blue-400 hover:text-blue-300 hover:underline text-sm">
                ← Retour aux depenses
            </a>
        </div>
    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">

        <!-- Dashboard -->
        <a href="dark_dashboard.php"
           class="px-4 py-2 rounded-lg bg-gray-700 text-gray-200 font-semibold shadow hover:bg-gray-600 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7m-7-7v18" />
            </svg>
            Dashboard
        </a>

        <!-- Add Expense -->
        <button id="btnAdd"
            class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un revenue
        </button>

        <!-- Add cart -->
        <a href="cars.php"
           class="px-4 py-2 rounded-lg bg-purple-600 text-white font-semibold shadow hover:bg-purple-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18" />
            </svg>
            Mes Cartes
        </a>

        <!-- Log out -->
        <a href="logout.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg> Logout
        </a>
        
    </div>
</div>

    <!-- TABLE CARD -->
   <div class="max-w-5xl mx-auto card mt-10">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Montant</th>
                <th>Categorie</th>
                <th>Date</th>
                <th>Carte</th>
                <th>Description</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach($amouts as $i => $amout) :?>
            <tr>
                <td><?= $amout['id'] ?></td>
                <td><?= $amout['montant'] ?></td>
                <td><?= $amout['category'] ?></td>
                <td><?= date('Y-m-d', strtotime($amout['dates'])) ?></td>
                <td><?= $amout['card_name'] ?: 'Aucune carte' ?></td>
                <td><?= $amout['decription'] ?></td>

                <td class="text-center">
                    <button class="btn-warning editBtn">Modifier</button>
                    <a href="delete.php?id=<?= $amout['id'] ?>&source=incomes" class="btn-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach ;?>
        </tbody>
    </table>

</div>


    <!-- MODAL -->
    <div id="formModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center">
        <div class="bg-gray-800 w-96 p-6 rounded-2xl shadow-2xl border border-gray-700">

            <h2 id="formTitle" class="text-xl font-bold mb-4 text-gray-100">Ajouter un revenu</h2>

            <form id="incomeForm" class="space-y-4" action="addIncome.php" method="POST" data-parsley-validate>

                <input type="hidden" id="incomeId" name="incomeId">
                <input type="hidden" name="source" value="incomes">

                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Montant</label>
                    <input type="number" name="montant" id="amount" required
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                        data-parsley-pattern = "^[0-9]+$"
                         data-parsley-trigger = "change">
                </div>

                <div>
                    <label class="block mb-1 font-semibold text-gray-200">Categorie</label>
                    <select name="Categorie" id="Categorie" required 
                            class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner une categorie</option>
                        <option value="Salaire">Salaire</option>
                        <option value="Prime">Prime</option>
                        <option value="Heures supplémentaires">Heures supplémentaires</option>
                        <option value="Aide">Aide</option>
                        <option value="Autre">Autre</option>

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
                        class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
                         data-parsley-pattern = "^[a-zA-Z0-9 ]+$"
                         data-parsley-trigger = "change">
                    </textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="cancelBtn" class="btn-secondary">Annuler</button>
                    <button type="submit" id="submitBtn" class="btn-primary opacity-50 cursor-not-allowed" disabled="true">Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <!-- JS -->
    <script>
        const modal = document.getElementById("formModal");
        const btnAdd = document.getElementById("btnAdd");
        const cancelBtn = document.getElementById("cancelBtn");
        const form = document.getElementById("incomeForm");
        const formTitle = document.getElementById("formTitle");
        const submitBtn = document.getElementById("submitBtn");

        btnAdd.onclick = () => {
            formTitle.textContent = "Ajouter un revenu";
            form.action = "addIncome.php";
            form.reset();
            document.getElementById("incomeId").value = "";
            modal.classList.remove("hidden");
        }

        cancelBtn.onclick = () => {
            modal.classList.add("hidden");
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    formTitle.textContent = "Modifier un revenu";
                    form.action = "update.php";
                    e.preventDefault();

                    const row = this.closest('tr');
                    const id = row.cells[0].textContent.trim();
                    const montant = row.cells[1].textContent.trim();
                    const category = row.cells[2].textContent.trim();
                    const date = row.cells[3].textContent.trim();
                    const desc = row.cells[4].textContent.trim();

                    document.getElementById("incomeId").value = id;
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
