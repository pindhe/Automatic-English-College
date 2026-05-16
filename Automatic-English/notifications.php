<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin(); // Only admins can see this

// Mark all as read if requested
if (isset($_GET['read_all'])) {
    $pdo->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    redirect('notifications.php?success=marked');
}

$page_title = 'System Notifications';
include 'includes/header.php';
include 'includes/sidebar.php';

$notifications = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetchAll();
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20 transition-all duration-300">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Policy Center</h2>
        <a href="notifications.php?read_all=1"
            class="text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest transition-all">
            <i class="fas fa-check-double mr-2"></i> Mark All Synced
        </a>
    </header>

    <div class="p-8 max-w-4xl mx-auto">
        <?php if (count($notifications) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $n): ?>
                    <div
                        class="bg-white dark:bg-dark-card p-10 rounded-[2.5rem] border <?php echo $n->is_read ? 'border-gray-50 dark:border-dark-border opacity-75' : 'border-blue-100 dark:border-blue-900/50 ring-2 ring-blue-50 dark:ring-blue-900/40 bg-blue-50/20 dark:bg-blue-900/10 shadow-xl shadow-blue-100'; ?> flex items-start space-x-6 hover:-translate-y-1 transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl <?php echo $n->is_read ? 'bg-slate-50 dark:bg-slate-900 text-slate-300' : 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400'; ?> flex items-center justify-center flex-shrink-0 transition-all font-black shadow-inner">
                            <i
                                class="fas <?php echo $n->type == 'fee_deletion' ? 'fa-shield-virus' : 'fa-bell'; ?> text-xl"></i>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="text-[9px] font-black uppercase tracking-[0.3em] <?php echo $n->is_read ? 'text-slate-400' : 'text-blue-600'; ?> transition-colors">
                                    <?php echo str_replace('_', ' ', $n->type); ?>
                                </span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <?php echo time_elapsed_string($n->created_at); ?>
                                </span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed font-medium transition-colors">
                                <?php echo $n->message; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div
                class="py-20 text-center bg-white dark:bg-dark-card rounded-3xl border border-dashed border-gray-200 dark:border-dark-border p-12 transition-colors">
                <div
                    class="w-20 h-20 bg-gray-50 dark:bg-slate-900 text-gray-300 dark:text-slate-700 rounded-full flex items-center justify-center mx-auto mb-6 transition-colors">
                    <i class="fas fa-bell-slash text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white transition-colors">No Notifications</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-xs mx-auto transition-colors">You're all caught up!
                    Important system alerts will appear
                    here when they happen.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>