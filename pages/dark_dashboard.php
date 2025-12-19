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

// ???????????????
$monthlyIncomes = array_reverse($monthlyIncomes);
$monthlyExpenses = array_reverse($monthlyExpenses);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Financier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .header-title {
            font-size: 32px;
            font-weight: 700;
            color: #f3f4f6;
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

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            padding: 10px 16px;
            border-radius: 10px;
            color: #e5e7eb;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.15);
        }
    </style>
</head>

<body class="p-6">
    
    <!-- HEADER -->
    <div class="max-w-7xl mx-auto mb-10 p-4 bg-gray-800/40 backdrop-blur-md rounded-xl border border-gray-700 shadow-lg">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex flex-col">
                <h1 class="header-title">Dashboard Financier</h1>
                <p class="text-gray-400 text-sm mt-1"><?php echo date('d/m/Y'); ?></p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
                <a href="incomes.php" class="btn-secondary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Revenus
                </a>
                
                <a href="expenses.php" class="btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    Dépenses
                </a>
                <a href="logout.php" 
                     class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                     </svg>
                     Logout
                </a>
            </div>
            
        </div>
    </div>

    <main class="max-w-7xl mx-auto">
        
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
        <div class="card">
            <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Balance Mensuelle (Revenus - Dépenses)
            </h2>
            <canvas id="balanceChart"></canvas>
        </div>

    </main>

    <script>
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
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                }
            }
        };
    
        // Graphique 1: Barres (Revenus vs Dépenses)
        const bars = document.getElementById('mainChart').getContext('2d');
        new Chart(bars, {
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
                            callback: function(value) {
                                return value.toLocaleString() + ' DH';
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    }
                }
            }
        });

        // Graphique 2: Ligne (Tendance)
        const lines = document.getElementById('lineChart').getContext('2d');
        new Chart(lines, {
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
                            callback: function(value) {
                                return value.toLocaleString() + ' DH';
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    }
                }
            }
        });

        // Graphique 3: Balance
        const ctx3 = document.getElementById('balanceChart').getContext('2d');
        new Chart(ctx3, {
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
                            callback: function(value) {
                                return value.toLocaleString() + ' DH';
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)'
                        }
                    }
                },
                plugins: {
                    ...commonOptions.plugins,
                    tooltip: {
                        backgroundColor: 'rgba(30, 32, 40, 0.9)',
                        titleColor: '#e5e7eb',
                        bodyColor: '#e5e7eb',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toLocaleString() + ' DH';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>