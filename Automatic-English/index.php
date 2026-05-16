<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

// Mock stats for initial dashboard
$stats = [
    'total_students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'total_teachers' => $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
    'total_classes' => $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
    'total_exams' => $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn(),
    'total_reports' => $pdo->query("SELECT COUNT(DISTINCT academic_year) FROM exams WHERE academic_year IS NOT NULL")->fetchColumn(),
    'total_fees' => $pdo->query("SELECT SUM(amount) FROM fees WHERE status = 'paid'")->fetchColumn() ?: 0,
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-grow overflow-y-auto">
    <!-- Top Bar -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20 transition-all duration-300">
        <div class="flex items-center space-x-4">
            <button id="toggleSidebar"
                class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-800 lg:hidden text-slate-500">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Dashboard</h2>
        </div>

        <div class="flex items-center space-x-6">
            <?php if (is_admin()): ?>
                <?php $unread_count = get_unread_notifications_count($pdo); ?>
                <div id="notifTrigger"
                    class="relative group cursor-pointer p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                    <i class="fas fa-bell text-gray-400 text-xl hover:text-blue-600 transition-all"></i>
                    <?php if ($unread_count > 0): ?>
                        <span
                            class="absolute top-1 right-1 bg-rose-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center border-2 border-white animate-pulse">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>

                    <!-- Alerts Center Dropdown -->
                    <div id="notifDropdown"
                        class="absolute right-0 mt-4 w-80 bg-white dark:bg-dark-card rounded-[2rem] shadow-2xl border border-gray-100 dark:border-dark-border opacity-0 invisible translate-y-2 transition-all z-50 overflow-hidden">
                        <div
                            class="px-6 py-5 border-b border-gray-50 dark:border-dark-border flex items-center justify-between">
                            <h4 class="text-[10px] font-black text-slate-800 dark:text-white uppercase tracking-[0.2em]">
                                Alert Stream</h4>
                            <span
                                class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 text-[10px] font-black rounded-lg"><?php echo $unread_count; ?>
                                NEW</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <?php
                            $notifs = get_latest_notifications($pdo);
                            if ($notifs):
                                foreach ($notifs as $n):
                                    ?>
                                    <a href="notifications.php"
                                        class="block px-6 py-5 border-b border-gray-50 dark:border-dark-border hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                                            <?php echo $n->message; ?>
                                        </p>
                                        <p
                                            class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-2 flex items-center">
                                            <i class="far fa-clock mr-1 text-blue-500"></i>
                                            <?php echo time_elapsed_string($n->created_at); ?>
                                        </p>
                                    </a>
                                    <?php
                                endforeach;
                            else:
                                ?>
                                <div class="p-10 text-center">
                                    <div
                                        class="w-12 h-12 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-check text-slate-200 text-xl"></i>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">System
                                        Synchronized</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="notifications.php"
                            class="block w-full text-center py-4 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-black text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 uppercase tracking-[0.3em] transition-all border-t border-gray-100 dark:border-dark-border">
                            Access Policy Center
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex items-center space-x-4 border-l pl-8 border-gray-100 dark:border-dark-border">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 dark:text-white">
                        <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                    </p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">
                        <?php echo $_SESSION['role'] ?? 'Guest'; ?>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-black text-lg shadow-inner">
                    <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'User', 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>

    <div class="p-8">
        <!-- Welcome Section -->
        <div
            class="mb-10 bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-blue-200 dark:shadow-none transition-all duration-300 group">
            <div class="relative z-10">
                <h1 class="text-4xl font-black mb-3 tracking-tight">Welcome Back,
                    <?php echo explode(' ', $_SESSION['full_name'] ?? 'User')[0]; ?>! 👋
                </h1>
                <p class="text-blue-50/80 max-w-lg text-lg leading-relaxed font-medium">
                    Here's what's happening at Automatic English College today. All systems are operational and up to
                    date.
                </p>
                <div class="mt-8 flex space-x-4">
                    <a href="student-add.php"
                        class="bg-white text-blue-700 px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-50 transition-all shadow-lg shadow-blue-900/20 transform active:scale-95">
                        Enroll Student
                    </a>
                    <a href="fees.php"
                        class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all backdrop-blur-md">
                        Financial Center
                    </a>
                </div>
            </div>
            <!-- Decorative Elements -->
            <div
                class="absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700">
            </div>
            <div
                class="absolute -bottom-24 -left-24 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700 delay-100">
            </div>
            <i
                class="fas fa-graduation-cap absolute right-12 bottom-0 text-white/5 text-[20rem] translate-y-24 rotate-12"></i>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-10 text-slate-500">
            <a href="attendance.php"
                class="bg-white dark:bg-dark-card p-8 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group duration-300 block">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
                    Attendance</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo number_format($stats['total_students']); ?>
                </h3>
            </a>

            <a href="exam-view.php"
                class="bg-white dark:bg-dark-card p-8 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group duration-300 block">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
                    Active Exams</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo number_format($stats['total_exams']); ?>
                </h3>
            </a>

            <a href="fee-analytics.php"
                class="bg-white dark:bg-dark-card p-8 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group duration-300 block">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Paid
                    Fees</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo format_currency($stats['total_fees']); ?>
                </h3>
            </a>

            <a href="class-analytics.php"
                class="bg-white dark:bg-dark-card p-8 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group duration-300 block">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-door-open"></i>
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
                    Total Classes</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo number_format($stats['total_classes']); ?>
                </h3>
            </a>

            <a href="annual-report.php"
                class="bg-white dark:bg-dark-card p-8 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group duration-300 block">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
                    Reports Finalized</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo number_format($stats['total_reports']); ?>
                </h3>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <!-- Recent Activities -->
            <div
                class="bg-white dark:bg-dark-card p-10 rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl transition-all duration-300">
                <h4 class="font-black text-lg mb-8 dark:text-white tracking-tight">Pulse Activity</h4>
                <div
                    class="space-y-8 relative before:absolute before:left-[7px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100 dark:before:bg-slate-800">
                    <div class="flex space-x-6 relative">
                        <div class="mt-1 w-4 h-4 rounded-full bg-white dark:bg-dark-card border-4 border-blue-600 z-10">
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white tracking-tight">New Student
                                Enrolled</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">John Doe was added
                                to Introductory Class</p>
                            <p
                                class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-2 flex items-center">
                                <i class="far fa-clock mr-1"></i> 2 HOURS AGO
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-6 relative">
                        <div
                            class="mt-1 w-4 h-4 rounded-full bg-white dark:bg-dark-card border-4 border-emerald-500 z-10">
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white tracking-tight">Fee Payment
                                Received</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Sarah Smith
                                finalized payment for April Session</p>
                            <p
                                class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-2 flex items-center">
                                <i class="far fa-clock mr-1"></i> 5 HOURS AGO
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-6 relative">
                        <div
                            class="mt-1 w-4 h-4 rounded-full bg-white dark:bg-dark-card border-4 border-indigo-600 z-10">
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800 dark:text-white tracking-tight">Teacher Hire
                                Complete</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Professor Adam
                                joined the Languages Department</p>
                            <p
                                class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-2 flex items-center">
                                <i class="far fa-clock mr-1"></i> YESTERDAY
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Notification Dropdown Toggle
    const notifTrigger = document.getElementById('notifTrigger');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifTrigger && notifDropdown) {
        notifTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = !notifDropdown.classList.contains('invisible');

            if (isVisible) {
                notifDropdown.classList.add('opacity-0', 'invisible', 'translate-y-2');
                notifDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
            } else {
                notifDropdown.classList.remove('opacity-0', 'invisible', 'translate-y-2');
                notifDropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target) && !notifTrigger.contains(e.target)) {
                notifDropdown.classList.add('opacity-0', 'invisible', 'translate-y-2');
                notifDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>