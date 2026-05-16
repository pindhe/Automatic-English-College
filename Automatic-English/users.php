<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin(); // Only admins can manage staff

$page_title = "Staff Management";

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY role ASC, full_name ASC");
$users = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-all duration-500">
    <!-- Header -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-20 transition-all">
        <div class="flex items-center space-x-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-2xl shadow-inner">
                <i class="fas fa-users-cog text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Staff Management</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Control access &
                    permissions</p>
            </div>
        </div>
        <a href="user-add.php"
            class="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-lg hover:shadow-blue-500/20 active:scale-95 transition-all flex items-center">
            <i class="fas fa-plus mr-2 text-[10px]"></i> Add Employee
        </a>
    </header>

    <div class="p-8">
        <!-- Users Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($users as $user): ?>
                <div
                    class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border p-8 shadow-xl shadow-slate-200/50 dark:shadow-none hover:-translate-y-1 transition-all group relative overflow-hidden">

                    <!-- Role Badge -->
                    <div
                        class="absolute top-6 right-6 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest 
                        <?php echo $user->role === 'admin' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-blue-50 text-blue-600 border border-blue-100'; ?>">
                        <?php echo $user->role; ?>
                    </div>

                    <div class="flex items-center space-x-6 mb-8">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center text-xl font-black text-slate-400 shadow-inner group-hover:scale-105 transition-transform duration-500">
                            <?php echo strtoupper(substr($user->full_name, 0, 2)); ?>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-slate-800 dark:text-white tracking-tight leading-tight">
                                <?php echo $user->full_name; ?>
                            </h4>
                            <p class="text-xs font-bold text-slate-400 mt-1">@
                                <?php echo $user->username; ?>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div
                            class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-white/5 transition-all">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span>
                            <span class="flex items-center">
                                <span
                                    class="w-2 h-2 rounded-full mr-2 <?php echo $user->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'; ?>"></span>
                                <span
                                    class="text-xs font-black uppercase tracking-widest <?php echo $user->status === 'active' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                    <?php echo $user->status; ?>
                                </span>
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-white/5 transition-all">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Last Login</span>
                            <span class="text-[11px] font-black text-slate-600 dark:text-slate-300">
                                <?php echo $user->last_login ? date('M d, H:i', strtotime($user->last_login)) : 'Never'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <a href="user-edit.php?id=<?php echo $user->id; ?>"
                            class="flex-grow bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-center hover:bg-blue-600 hover:text-white transition-all">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <?php if ($user->username !== $_SESSION['username']): ?>
                            <button onclick="confirmDelete(<?php echo $user->id; ?>, '<?php echo $user->full_name; ?>')"
                                class="px-4 py-3 bg-rose-50 dark:bg-rose-900/20 text-rose-500 dark:text-rose-400 rounded-xl hover:bg-rose-500 hover:text-white transition-all">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
    function confirmDelete(id, name) {
        if (confirm(`Are you sure you want to remove ${name} from the system staff?`)) {
            window.location.href = `user-actions.php?action=delete&id=${id}`;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>