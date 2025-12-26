<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get visitor data for last 7 days
$stmt = $pdo->query("
    SELECT 
        visit_date, 
        page_views,
        unique_visitors
    FROM visitor_logs
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY visit_date ASC
");
$visitor_data = $stmt->fetchAll();

// Prepare data for chart
$dates = [];
$page_views = [];
$unique_visitors = [];

foreach ($visitor_data as $data) {
    $dates[] = date('M d', strtotime($data['visit_date']));
    $page_views[] = $data['page_views'];
    $unique_visitors[] = $data['unique_visitors'];
}

// Get totals
$stmt = $pdo->query("
    SELECT 
        SUM(page_views) as total_page_views,
        SUM(unique_visitors) as total_unique_visitors
    FROM visitor_logs
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$totals = $stmt->fetch();

$total_page_views = $totals['total_page_views'] ?? 0;
$total_unique_visitors = $totals['total_unique_visitors'] ?? 0;
$avg_daily = count($visitor_data) > 0 ? round($total_page_views / 7, 1) : 0;
$peak = count($page_views) > 0 ? max($page_views) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Analytics - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
        }
        .stat-box h3 {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
        }
        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .chart-wrapper {
            position: relative;
            height: 400px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">
                    <span class="logo-icon">⚙️</span>
                    <span class="logo-text">Admin Panel</span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="analytics.php" class="active">Analytics</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main>
        <section class="dashboard-section">
            <div class="container">
                <h1 class="section-title">Website Analytics</h1>
                <p class="section-subtitle">Last 7 Days Visitor Statistics</p>
                
                <!-- Analytics Stats -->
                <div class="analytics-stats">
                    <div class="stat-box">
                        <h3><?= number_format($total_page_views) ?></h3>
                        <p>Total Page Views</p>
                    </div>
                    <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h3><?= number_format($total_unique_visitors) ?></h3>
                        <p>Total Visitors</p>
                    </div>
                    <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h3><?= $avg_daily ?></h3>
                        <p>Avg Daily Views</p>
                    </div>
                    <div class="stat-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <h3><?= $peak ?></h3>
                        <p>Peak Daily Views</p>
                    </div>
                </div>
                
                <!-- Page Views Chart -->
                <div class="chart-container">
                    <h2 style="margin-top: 0;">📈 Page Views Trend</h2>
                    <div class="chart-wrapper">
                        <canvas id="pageViewsChart"></canvas>
                    </div>
                </div>
                
                <!-- Visitors Chart -->
                <div class="chart-container">
                    <h2 style="margin-top: 0;">👥 Unique Visitors Trend</h2>
                    <div class="chart-wrapper">
                        <canvas id="visitorsChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <script>
        // Page Views Chart
        const pageViewsCtx = document.getElementById('pageViewsChart').getContext('2d');
        const pageViewsChart = new Chart(pageViewsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Page Views',
                    data: <?= json_encode($page_views) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Unique Visitors Chart
        const visitorsCtx = document.getElementById('visitorsChart').getContext('2d');
        const visitorsChart = new Chart(visitorsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Unique Visitors',
                    data: <?= json_encode($unique_visitors) ?>,
                    backgroundColor: 'rgba(249, 147, 251, 0.6)',
                    borderColor: '#f093fb',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
