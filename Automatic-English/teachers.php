<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();
require_admin();

$page_title = 'Teachers';
include 'includes/header.php';
include 'includes/sidebar.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE full_name LIKE :search OR teacher_id_code LIKE :search ORDER BY created_at DESC");
$stmt->execute(['search' => "%$search%"]);
$teachers = $stmt->fetchAll();
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-10 transition-colors duration-300">
        <h2 class="text-xl font-bold dark:text-white">Teacher Management</h2>
        <a href="teacher-add.php"
            class="bg-blue-600 text-white px-4 py-2 rounded-xl font-bold text-sm hover:bg-blue-700 transition-all flex items-center shadow-lg shadow-blue-200">
            <i class="fas fa-plus mr-2"></i> Add New Teacher
        </a>
    </header>

    <div class="p-8">
        <div
            class="bg-white dark:bg-dark-card p-6 rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm mb-8 transition-colors">
            <form action="teachers.php" method="GET" class="flex space-x-4">
                <div class="relative flex-grow">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo $search; ?>"
                        placeholder="Search by name or ID..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-slate-500 transition-colors">
                </div>
                <button type="submit"
                    class="bg-slate-800 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-slate-900 transition-all">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (count($teachers) > 0): ?>
                <?php foreach ($teachers as $teacher): ?>
                    <div
                        class="bg-white dark:bg-dark-card p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:-translate-y-1 border border-gray-100 dark:border-dark-border transition-all relative overflow-hidden group">
                        <!-- Status Badge -->
                        <div class="absolute top-6 right-6">
                            <span
                                class="px-2.5 py-1 <?php echo $teacher->status == 'active' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400'; ?> rounded-lg text-[10px] font-black uppercase tracking-widest border border-current opacity-80">
                                <?php echo $teacher->status; ?>
                            </span>
                        </div>

                        <!-- Card Header: Avatar & Name -->
                        <div class="flex items-center space-x-4 mb-6">
                            <div
                                class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-2xl font-black shadow-inner transition-colors">
                                <?php echo strtoupper(substr($teacher->full_name, 0, 1)); ?>
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="font-black text-slate-800 dark:text-white text-lg truncate pr-16"
                                    title="<?php echo $teacher->full_name; ?>">
                                    <?php echo $teacher->full_name; ?>
                                </h4>
                                <p
                                    class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">
                                    ID: <?php echo $teacher->teacher_id_code; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Card Body: Details -->
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center text-sm group/item">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-900 flex items-center justify-center mr-3 transition-colors group-hover/item:bg-blue-50 dark:group-hover/item:bg-blue-900/30">
                                    <i
                                        class="fas fa-book-open text-xs text-slate-400 group-hover/item:text-blue-600 transition-colors"></i>
                                </div>
                                <span
                                    class="font-bold text-slate-600 dark:text-slate-300"><?php echo $teacher->subject ?: 'General Studies'; ?></span>
                            </div>
                            <div class="flex items-center text-sm group/item">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-900 flex items-center justify-center mr-3 transition-colors group-hover/item:bg-indigo-50 dark:group-hover/item:bg-indigo-900/30">
                                    <i
                                        class="fas fa-phone text-xs text-slate-400 group-hover/item:text-indigo-600 transition-colors"></i>
                                </div>
                                <span
                                    class="text-slate-500 dark:text-slate-400 font-medium"><?php echo $teacher->phone ?: 'No contact info'; ?></span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div
                            class="pt-6 border-t border-gray-50 dark:border-dark-border flex items-center justify-between transition-colors">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Monthly Salary
                                </p>
                                <p class="text-lg font-black text-blue-600">
                                    <?php echo format_currency($teacher->salary); ?>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="teacher-edit.php?id=<?php echo $teacher->id; ?>"
                                    class="w-10 h-10 bg-slate-50 dark:bg-slate-900 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl flex items-center justify-center transition-all hover:shadow-lg">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <?php if (is_admin()): ?>
                                    <button onclick="confirmDelete(<?php echo $teacher->id; ?>)"
                                        class="w-10 h-10 bg-slate-50 dark:bg-slate-900 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-xl flex items-center justify-center transition-all hover:shadow-lg">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div
                    class="col-span-full py-12 text-center text-slate-500 italic bg-white rounded-2xl border border-dashed border-gray-200">
                    No teachers found. Start by adding one!
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to remove this teacher?')) {
            window.location.href = 'teacher-actions.php?action=delete&id=' + id;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>