<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin();

$page_title = "Add New Employee";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $username, $hashed_password, $role])) {
                $success = "User added successfully!";
                header("Refresh: 2; url=users.php");
            } else {
                $error = "Failed to add user.";
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-all duration-500">
    <!-- Header -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-20 transition-all">
        <div class="flex items-center space-x-4">
            <a href="users.php" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Create Account</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Provision a new staff
                    member</p>
            </div>
        </div>
    </header>

    <div class="p-8 max-w-2xl mx-auto">
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-600 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div
                class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl flex items-center shadow-sm animate-bounce">
                <i class="fas fa-check-circle mr-3"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div
            class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border p-10 shadow-2xl shadow-slate-200/50 dark:shadow-none">
            <form action="user-add.php" method="POST" class="space-y-8">
                <!-- Name -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Full
                        Name</label>
                    <div class="relative group">
                        <i
                            class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" name="full_name" required placeholder="e.g. John Doe"
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Username</label>
                    <div class="relative group">
                        <i
                            class="fas fa-at absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" name="username" required placeholder="unique_username"
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Account
                        Role</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="role" value="employee" checked class="peer sr-only">
                            <div
                                class="p-6 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all text-center">
                                <i
                                    class="fas fa-user-tie text-xl mb-2 block text-slate-400 peer-checked:text-blue-500"></i>
                                <span
                                    class="text-xs font-black uppercase tracking-widest text-slate-500 peer-checked:text-blue-600 dark:peer-checked:text-blue-400">Employee</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="role" value="admin" class="peer sr-only">
                            <div
                                class="p-6 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 transition-all text-center">
                                <i
                                    class="fas fa-user-shield text-xl mb-2 block text-slate-400 peer-checked:text-amber-500"></i>
                                <span
                                    class="text-xs font-black uppercase tracking-widest text-slate-500 peer-checked:text-amber-600 dark:peer-checked:text-amber-400">Administrator</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Password -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Password</label>
                        <div class="relative group">
                            <i
                                class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                        </div>
                    </div>
                    <!-- Confirm Password -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Confirm
                            Password</label>
                        <div class="relative group">
                            <i
                                class="fas fa-check-shield absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="password" name="confirm_password" required placeholder="••••••••"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-[0_20px_50px_rgba(37,99,235,0.3)] active:scale-95 transition-all shadow-xl shadow-blue-500/10">
                    Finalize Account Provisioning
                </button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>