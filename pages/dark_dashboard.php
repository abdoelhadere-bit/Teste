<?php
require __DIR__ . '/../auth.php';
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Calcul du total des revenus
$totalIncomes = $pdo->prepare("SELECT SUM(montant) as total FROM incomes WHERE user_id = ?");
$totalIncomes->execute([$user_id]);
$totalIncomes = $totalIncomes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Calcul du total des dépenses
$totalExpenses = $pdo->prepare("SELECT SUM(montant) as total FROM expenses WHERE user_id = ?");
$totalExpenses->execute([$user_id]);
$totalExpenses = $totalExpenses->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Calcul du solde
$balance = $totalIncomes - $totalExpenses;

// Revenus du mois en cours
$currentMonthIncomes = $pdo->prepare("SELECT SUM(montant) as total FROM incomes WHERE MONTH(dates) = MONTH(CURRENT_DATE()) AND YEAR(dates) = YEAR(CURRENT_DATE()) AND user_id = ?");
$currentMonthIncomes->execute([$user_id]);
$currentMonthIncomes = $currentMonthIncomes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Dépenses du mois en cours
$currentMonthExpenses = $pdo->prepare("SELECT SUM(montant) as total FROM expenses WHERE MONTH(dates) = MONTH(CURRENT_DATE()) AND YEAR(dates) = YEAR(CURRENT_DATE()) AND user_id = ?");
$currentMonthExpenses->execute([$user_id]);
$currentMonthExpenses = $currentMonthExpenses->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Données pour le graphique mensuel
$monthlyIncomes = $pdo->prepare("SELECT DATE_FORMAT(dates, '%Y-%m') as month, SUM(montant) as total FROM incomes WHERE user_id = ? GROUP BY DATE_FORMAT(dates, '%Y-%m') ORDER BY month DESC LIMIT 6");
$monthlyIncomes->execute([$user_id]);
$monthlyIncomes = $monthlyIncomes->fetchAll(PDO::FETCH_ASSOC);

$monthlyExpenses = $pdo->prepare("SELECT DATE_FORMAT(dates, '%Y-%m') as month, SUM(montant) as total FROM expenses WHERE user_id = ? GROUP BY DATE_FORMAT(dates, '%Y-%m') ORDER BY month DESC LIMIT 6");
$monthlyExpenses->execute([$user_id]);
$monthlyExpenses = $monthlyExpenses->fetchAll(PDO::FETCH_ASSOC);

$monthlyIncomes = array_reverse($monthlyIncomes);
$monthlyExpenses = array_reverse($monthlyExpenses);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Wallet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        }

        .sidebar-hidden {
            transform: translateX(-100%);
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
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .card {
            background: rgba(30, 32, 40, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.2);
        }

        .stat-card {
            background: rgba(30, 32, 40, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 40px rgba(0,0,0,0.3);
        }
        .btn-primary {
            background: linear-gradient(to right,#3b82f6,#2563eb);
            padding: 10px 16px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
        }
        .mobile-menu-btn {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }

        /* Badge for notifications */
        .badge {
            position: absolute;
            top: 10px;
            right: 15px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <!-- Logo & User -->
        <div class="p-6 border-b border-gray-700/50">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-200"><?= htmlspecialchars($user_name) ?></p>
                    <p class="text-xs text-gray-500">Utilisateur</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="py-4">
            <!-- Dashboard -->
            <a href="dark_dashboard.php" class="sidebar-item active">
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
    <div class="main-content" id="mainContent">
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn fixed top-4 left-4 z-50 bg-gray-800 p-3 rounded-lg" onclick="toggleSidebar()">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-100 mb-2">Dashboard Financier</h1>
            <p class="text-gray-400"><?php echo date('l, d F Y'); ?></p>
        </div>

        <!-- Cards de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Revenus -->
            <div class="stat-card border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Total Revenus</p>
                        <p class="text-3xl font-bold text-green-400"><?php echo number_format($totalIncomes, 2); ?> DH</p>
                    </div>
                    <div class="bg-green-500/20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Dépenses -->
            <div class="stat-card border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Total Dépenses</p>
                        <p class="text-3xl font-bold text-red-400"><?php echo number_format($totalExpenses, 2); ?> DH</p>
                    </div>
                    <div class="bg-red-500/20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Solde -->
            <div class="stat-card border-l-4 <?php echo $balance >= 0 ? 'border-blue-500' : 'border-orange-500'; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Solde Actuel</p>
                        <p class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-blue-400' : 'text-orange-400'; ?>">
                            <?php echo number_format($balance, 2); ?> DH
                        </p>
                    </div>
                    <div class="<?php echo $balance >= 0 ? 'bg-blue-500/20' : 'bg-orange-500/20'; ?> rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 <?php echo $balance >= 0 ? 'text-blue-400' : 'text-orange-400'; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Mois en cours -->
            <div class="stat-card border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-400 mb-2">Ce Mois</p>
                        <p class="text-lg font-semibold text-green-400">
                            +<?php echo number_format($currentMonthIncomes, 2); ?> DH
                        </p>
                        <p class="text-lg font-semibold text-red-400">
                            -<?php echo number_format($currentMonthExpenses, 2); ?> DH
                        </p>
                    </div>
                    <div class="bg-purple-500/20 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- Graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            
            <!-- Graphique Principal -->
            <div class="card">
                <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Évolution Mensuelle
                </h2>
                <canvas id="mainChart"></canvas>
            </div>

            <!-- Graphique en Ligne -->
            <div class="card">
                <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    Tendance des 6 Derniers Mois
                </h2>
                <canvas id="lineChart"></canvas>
            </div>

        </div>

        <!-- Graphique Balance -->
        <div class="card mb-8">
            <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Balance Mensuelle (Revenus - Dépenses)
            </h2>
            <canvas id="balanceChart"></canvas>
        </div>

    </div>

    <script>
        // Toggle sidebar mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }

        // Données depuis PHP
        const monthlyIncomes = <?php echo json_encode($monthlyIncomes); ?>;
        const monthlyExpenses = <?php echo json_encode($monthlyExpenses); ?>;

        // Préparer les données
        const months = monthlyIncomes.map(item => item.month);
        const incomesData = monthlyIncomes.map(item => parseFloat(item.total || 0));
        const expensesData = monthlyExpenses.map(item => parseFloat(item.total || 0));
        const balanceData = incomesData.map((income, index) => income - expensesData[index]);

        // Configuration commune pour thème sombre
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#e5e7eb',
                        font: { size: 13, weight: '600' }
                    }
                }
            }
        };
    
        // Graphique 1
        new Chart(document.getElementById('mainChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Revenus',
                        data: incomesData,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    },
                    {
                        label: 'Dépenses',
                        data: expensesData,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#9ca3af',
                            callback: value => value.toLocaleString() + ' DH'
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });

        // Graphique 2
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Revenus',
                        data: incomesData,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    },
                    {
                        label: 'Dépenses',
                        data: expensesData,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#9ca3af',
                            callback: value => value.toLocaleString() + ' DH'
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });

        // Graphique 3
        new Chart(document.getElementById('balanceChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Balance (Revenus - Dépenses)',
                    data: balanceData,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        ticks: {
                            color: '#9ca3af',
                            callback: value => value.toLocaleString() + ' DH'
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });
    </script>

</body>
</html>