<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$page_title = 'Exams';
include 'includes/header.php';
include 'includes/sidebar.php';

$exams = $pdo->query("SELECT * FROM exams ORDER BY exam_date DESC")->fetchAll();
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-10 transition-colors duration-300">
        <h2 class="text-xl font-bold dark:text-white">Exams & Management</h2>
        <?php if (is_admin()): ?>
            <a href="exam-add.php"
                class="bg-blue-600 text-white px-4 py-2 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all flex items-center shadow-lg shadow-blue-200">
                <i class="fas fa-plus mr-2"></i> Create New Exam
            </a>
        <?php endif; ?>
    </header>

    <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Exam List -->
            <div class="lg:col-span-2 space-y-6">
                <?php if (count($exams) > 0): ?>
                    <?php foreach ($exams as $exam): ?>
                        <div
                            class="bg-white dark:bg-dark-card p-6 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm flex items-center justify-between group hover:border-blue-200 dark:hover:border-blue-500/50 transition-all hover:shadow-xl hover:-translate-y-1">
                            <div class="flex items-center space-x-6">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-2xl group-hover:scale-110 transition-all shadow-inner">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="font-black text-slate-800 dark:text-white text-lg truncate mb-1">
                                        <?php echo $exam->exam_name; ?>
                                    </h4>
                                    <div
                                        class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 space-x-4">
                                        <span class="flex items-center"><i class="far fa-calendar-alt mr-2 text-blue-500"></i>
                                            <?php echo date('M d, Y', strtotime($exam->exam_date)); ?>
                                        </span>
                                        <span class="flex items-center"><i class="fas fa-history mr-2 text-indigo-500"></i>
                                            <?php echo $exam->academic_year; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <?php if (is_admin()): ?>
                                    <a href="marks-entry.php?exam_id=<?php echo $exam->id; ?>"
                                        class="px-6 py-2.5 bg-slate-800 dark:bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 dark:hover:bg-blue-700 transition-all shadow-lg shadow-slate-200 dark:shadow-none">
                                        Enter Marks
                                    </a>
                                <?php endif; ?>
                                <a href="exam-view.php?id=<?php echo $exam->id; ?>"
                                    class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 bg-gray-50 dark:bg-slate-900 rounded-xl transition-all hover:shadow-lg">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                        <p class="text-slate-500 italic">No exams created yet. Click the button above to start.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <div
                    class="bg-gradient-to-br from-indigo-600 to-blue-700 p-8 rounded-[2.5rem] text-white shadow-xl shadow-blue-200 dark:shadow-none relative overflow-hidden group">
                    <div
                        class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700">
                    </div>
                    <h5 class="font-black text-xs uppercase tracking-[0.2em] mb-6 text-blue-100 opacity-80">Quick Stats
                    </h5>
                    <div class="space-y-6 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-blue-50/80 text-sm font-medium">Active Exams</span>
                            <span class="font-black text-xl">
                                <?php echo count($exams); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/10 pt-4">
                            <span class="text-blue-50/80 text-sm font-medium">Reports Finalized</span>
                            <span class="font-black text-xl">124</span>
                        </div>
                    </div>
                    <button
                        class="w-full mt-8 bg-white text-blue-600 hover:bg-blue-50 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-900/20 transform active:scale-95 transition-all">
                        Generate Annual Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>