<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

// Get class statistics by different time periods
$daily_classes = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM classes 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
")->fetchAll();

$monthly_classes = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM classes 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
")->fetchAll();

$yearly_classes = $pdo->query("
    SELECT YEAR(created_at) as year, COUNT(*) as count 
    FROM classes 
    GROUP BY YEAR(created_at)
    ORDER BY year DESC
")->fetchAll();

// Get current period stats
$today_classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$this_month_classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn();
$this_year_classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Analytics - <?php echo $settings->college_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155'
                        },
                        brand: {
                            teal: '#0d7377',
                            'teal-light': '#14b8a6',
                            'teal-dark': '#0d5c63',
                            orange: '#e07c24',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow overflow-y-auto">
        <div class="p-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-2">
                            Class Analytics
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">
                            Track class creation patterns and trends over time
                        </p>
                    </div>
                    <a href="index.php" 
                       class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-day text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Today</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo number_format($today_classes); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Classes Created</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-50 dark:bg-green-900/40 text-green-600 dark:text-green-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">This Month</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo number_format($this_month_classes); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Classes Created</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">This Year</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo number_format($this_year_classes); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Classes Created</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-infinity text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">All Time</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo number_format($total_classes); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total Classes</p>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Classes (Last 30 Days) -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i>Daily Classes (Last 30 Days)
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($daily_classes): ?>
                            <?php foreach ($daily_classes as $daily): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo date('M j, Y', strtotime($daily->date)); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-black text-slate-800 dark:text-white"><?php echo $daily->count; ?></span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">classes</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-line text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No class data available for the last 30 days</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Monthly Classes (Last 12 Months) -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-bar text-green-500 mr-2"></i>Monthly Classes (Last 12 Months)
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($monthly_classes): ?>
                            <?php foreach ($monthly_classes as $monthly): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo date('F Y', strtotime($monthly->month . '-01')); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-black text-slate-800 dark:text-white"><?php echo $monthly->count; ?></span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">classes</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-bar text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No class data available for the last 12 months</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Yearly Classes -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-pie text-purple-500 mr-2"></i>Yearly Classes
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($yearly_classes): ?>
                            <?php foreach ($yearly_classes as $yearly): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo $yearly->year; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-black text-slate-800 dark:text-white"><?php echo $yearly->count; ?></span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">classes</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-pie text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No yearly class data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-info-circle text-indigo-500 mr-2"></i>Summary Statistics
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Average Daily (Last 30 Days)</span>
                            <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                <?php 
                                $total_daily = array_sum(array_column($daily_classes, 'count'));
                                $avg_daily = count($daily_classes) > 0 ? round($total_daily / count($daily_classes), 1) : 0;
                                echo $avg_daily;
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Average Monthly (Last 12 Months)</span>
                            <span class="text-lg font-black text-green-600 dark:text-green-400">
                                <?php 
                                $total_monthly = array_sum(array_column($monthly_classes, 'count'));
                                $avg_monthly = count($monthly_classes) > 0 ? round($total_monthly / count($monthly_classes), 1) : 0;
                                echo $avg_monthly;
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Most Active Year</span>
                            <span class="text-lg font-black text-purple-600 dark:text-purple-400">
                                <?php 
                                $most_active = null;
                                if (!empty($yearly_classes)) {
                                    $sorted = $yearly_classes;
                                    usort($sorted, function($a, $b) { return $b->count <=> $a->count; });
                                    $most_active = $sorted[0];
                                }
                                echo $most_active ? $most_active->year . ' (' . $most_active->count . ')' : 'N/A';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
