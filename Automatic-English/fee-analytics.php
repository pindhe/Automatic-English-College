<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();
require_admin();

// Get fee statistics by different time periods
$daily_fees = $pdo->query("
    SELECT DATE(payment_date) as date, SUM(amount) as total, COUNT(*) as count
    FROM fees 
    WHERE status = 'paid' AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(payment_date)
    ORDER BY date DESC
")->fetchAll();

$monthly_fees = $pdo->query("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count
    FROM fees 
    WHERE status = 'paid' AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month DESC
")->fetchAll();

$yearly_fees = $pdo->query("
    SELECT YEAR(payment_date) as year, SUM(amount) as total, COUNT(*) as count
    FROM fees 
    WHERE status = 'paid'
    GROUP BY YEAR(payment_date)
    ORDER BY year DESC
")->fetchAll();

// Get current period stats
$today_fees = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM fees WHERE status = 'paid' AND DATE(payment_date) = CURDATE()")->fetch();
$this_month_fees = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM fees WHERE status = 'paid' AND YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE())")->fetch();
$this_year_fees = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM fees WHERE status = 'paid' AND YEAR(payment_date) = YEAR(CURDATE())")->fetch();
$total_fees = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM fees WHERE status = 'paid'")->fetch();

// Get fee status breakdown
$fee_status = $pdo->query("
    SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
    FROM fees
    GROUP BY status
")->fetchAll();

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Analytics - <?php echo $settings->college_name; ?></title>
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
                            Fee Analytics
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">
                            Track college revenue and payment patterns over time
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="fee-add.php" 
                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Fee
                        </a>
                        <a href="fees.php" 
                           class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                            <i class="fas fa-list mr-2"></i>All Fees
                        </a>
                        <a href="index.php" 
                           class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                            <i class="fas fa-arrow-left mr-2"></i>Dashboard
                        </a>
                    </div>
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
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo format_currency($today_fees->total); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo $today_fees->count; ?> payments</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-50 dark:bg-green-900/40 text-green-600 dark:text-green-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">This Month</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo format_currency($this_month_fees->total); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo $this_month_fees->count; ?> payments</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">This Year</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo format_currency($this_year_fees->total); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo $this_year_fees->count; ?> payments</p>
                </div>

                <div class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-xl flex items-center justify-center">
                            <i class="fas fa-infinity text-lg"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">All Time</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white"><?php echo format_currency($total_fees->total); ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?php echo $total_fees->count; ?> payments</p>
                </div>
            </div>

            <!-- Fee Status Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                            <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>Fee Status Overview
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($fee_status as $status): ?>
                                <div class="p-6 rounded-xl border <?php echo $status->status === 'paid' ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($status->status === 'pending' ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800'); ?>">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-black uppercase tracking-widest <?php echo $status->status === 'paid' ? 'text-green-600 dark:text-green-400' : ($status->status === 'pending' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'); ?>">
                                            <?php echo ucfirst($status->status); ?>
                                        </span>
                                        <span class="text-2xl font-black text-slate-800 dark:text-white"><?php echo $status->count; ?></span>
                                    </div>
                                    <div class="text-lg font-black text-slate-700 dark:text-slate-300">
                                        <?php echo format_currency($status->total); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-percentage text-purple-500 mr-2"></i>Collection Rate
                    </h3>
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4">
                            <svg class="transform -rotate-90 w-32 h-32">
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-200 dark:text-gray-700"></circle>
                                <?php 
                                $collection_rate = $total_fees->count > 0 ? ($fee_status[0]->count / $total_fees->count) * 100 : 0;
                                $circumference = 2 * pi() * 56;
                                $offset = $circumference - ($collection_rate / 100) * $circumference;
                                ?>
                                <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" 
                                        class="text-green-500" stroke-dasharray="<?php echo $circumference; ?>" 
                                        stroke-dashoffset="<?php echo $offset; ?>"></circle>
                            </svg>
                            <div class="absolute">
                                <span class="text-2xl font-black text-slate-800 dark:text-white"><?php echo round($collection_rate, 1); ?>%</span>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Payment Success Rate</p>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Fees (Last 30 Days) -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i>Daily Revenue (Last 30 Days)
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($daily_fees): ?>
                            <?php foreach ($daily_fees as $daily): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo date('M j, Y', strtotime($daily->date)); ?>
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black text-slate-800 dark:text-white"><?php echo format_currency($daily->total); ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo $daily->count; ?> payments</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-line text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No fee data available for the last 30 days</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Monthly Fees (Last 12 Months) -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-bar text-green-500 mr-2"></i>Monthly Revenue (Last 12 Months)
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($monthly_fees): ?>
                            <?php foreach ($monthly_fees as $monthly): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo date('F Y', strtotime($monthly->month . '-01')); ?>
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black text-slate-800 dark:text-white"><?php echo format_currency($monthly->total); ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo $monthly->count; ?> payments</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-bar text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No fee data available for the last 12 months</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Yearly Fees -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-chart-pie text-purple-500 mr-2"></i>Yearly Revenue
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php if ($yearly_fees): ?>
                            <?php foreach ($yearly_fees as $yearly): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <?php echo $yearly->year; ?>
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black text-slate-800 dark:text-white"><?php echo format_currency($yearly->total); ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo $yearly->count; ?> payments</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-chart-pie text-slate-200 dark:text-slate-700 text-4xl mb-3"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No yearly fee data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 tracking-tight">
                        <i class="fas fa-info-circle text-indigo-500 mr-2"></i>Financial Summary
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Average Daily (Last 30 Days)</span>
                            <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                <?php 
                                $total_daily = array_sum(array_column($daily_fees, 'total'));
                                $avg_daily = count($daily_fees) > 0 ? $total_daily / count($daily_fees) : 0;
                                echo format_currency($avg_daily);
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Average Monthly (Last 12 Months)</span>
                            <span class="text-lg font-black text-green-600 dark:text-green-400">
                                <?php 
                                $total_monthly = array_sum(array_column($monthly_fees, 'total'));
                                $avg_monthly = count($monthly_fees) > 0 ? $total_monthly / count($monthly_fees) : 0;
                                echo format_currency($avg_monthly);
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Highest Revenue Year</span>
                            <span class="text-lg font-black text-purple-600 dark:text-purple-400">
                                <?php 
                                $highest_year = null;
                                if (!empty($yearly_fees)) {
                                    $sorted = $yearly_fees;
                                    usort($sorted, function($a, $b) { return $b->total <=> $a->total; });
                                    $highest_year = $sorted[0];
                                }
                                echo $highest_year ? $highest_year->year . ' (' . format_currency($highest_year->total) . ')' : 'N/A';
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
