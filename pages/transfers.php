<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

/* =========================
   Get primary card
========================= */
$stmt = $pdo->prepare("
    SELECT * FROM cards
    WHERE user_id = ? AND is_primary = 1
    LIMIT 1
");
$stmt->execute([$user_id]);
$primaryCard = $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   Transfer history
========================= */
$stmt = $pdo->prepare("
    SELECT 
        t.id,
        t.amount,
        t.created_at,
        s.nom AS sender_name,
        r.nom AS receiver_name,
        t.sender_id,
        t.receiver_id
    FROM transfers t
    JOIN register s ON t.sender_id = s.id
    JOIN register r ON t.receiver_id = r.id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Transferts - Smart Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: #0f1117;
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1d29 0%, #0f1117 100%);
            border-right: 1px solid rgba(255,255,255,0.08);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            transition: transform 0.3s ease;
            z-index: 1000;
            padding-top: 20px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            margin: 6px 12px;
            border-radius: 12px;
            color: #9ca3af;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }

        .sidebar-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .sidebar-item.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .sidebar-item svg {
            width: 22px;
            height: 22px;
            margin-right: 12px;
        }

        .main-content {
            margin-left: 280px;
            padding: 24px;
        }

        .card {
            background: rgba(30, 32, 40, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background: linear-gradient(to right,#3b82f6,#2563eb);
            padding: 10px 16px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <a href="dark_dashboard.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        Dashboard
    </a>

    <a href="incomes.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Revenus
    </a>

    <a href="expenses.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Dépenses
    </a>

    <a href="cards.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Mes Cartes
    </a>

    <a href="budget_limits.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Limites
    </a>

    <a href="recurring_transactions.php" class="sidebar-item">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Récurrentes
    </a>

    <a href="transfers.php" class="sidebar-item active">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        Transferts
    </a>

    <!-- Logout en bas -->
    <div style="position:absolute; bottom:0; left:0; right:0; padding:16px; border-top:1px solid rgba(255,255,255,0.08);">
        <a href="logout.php" class="sidebar-item" style="background: rgba(239,68,68,0.10); color:#f87171;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Déconnexion
        </a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="max-w-4xl">

        <h1 class="text-3xl font-bold mb-6">🔁 Transferts</h1>
        <?php if (!empty($_SESSION['errors'])): ?>
    <div class="mb-4 p-4 rounded bg-red-500/20 text-red-300 border border-red-500/30">
        <?php foreach ($_SESSION['errors'] as $err): ?>
            <div>❌ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="mb-4 p-4 rounded bg-green-500/20 text-green-300 border border-green-500/30">
        ✅ <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

        <!-- SEND TRANSFER -->
        <div class="card mb-8">
            <h2 class="text-xl font-semibold mb-4">Envoyer de l'argent</h2>

            <form action="transfers_send.php" method="POST" class="space-y-4">

                <input
                    type="text"
                    name="receiver_id"
                    required
                    placeholder="ID du destinataire"
                    class="w-full p-3 rounded bg-gray-900 border border-gray-700"
                >

                <input
                    type="number"
                    step="0.01"
                    min="1"
                    name="amount"
                    required
                    placeholder="Montant (DH)"
                    class="w-full p-3 rounded bg-gray-900 border border-gray-700"
                >

                <?php if (!$primaryCard): ?>
                    <p class="text-red-400 text-sm">
                        ❌ Définissez une carte principale pour envoyer de l’argent
                    </p>
                <?php else: ?>
                    <p class="text-green-400 text-sm">
                        ✔ Carte principale : <?= htmlspecialchars($primaryCard['card_name']) ?>
                    </p>
                <?php endif; ?>

                <button class="btn-primary w-full" <?= !$primaryCard ? 'disabled' : '' ?>>
                    Envoyer
                </button>
            </form>
        </div>

        <!-- HISTORY -->
        <div class="card">
            <h2 class="text-xl font-semibold mb-4">Historique</h2>

            <?php if (empty($history)): ?>
                <p class="text-gray-400">Aucun transfert trouvé</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="py-2">Date</th>
                                <th>De</th>
                                <th>À</th>
                                <th class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $t): ?>
                                <tr class="border-b border-gray-800">
                                    <td class="py-2">
                                        <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['sender_name']) ?></td>
                                    <td><?= htmlspecialchars($t['receiver_name']) ?></td>
                                    <td class="text-right font-semibold
                                        <?= $t['sender_id'] == $user_id ? 'text-red-400' : 'text-green-400' ?>">
                                        <?= number_format($t['amount'], 2) ?> DH
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>
