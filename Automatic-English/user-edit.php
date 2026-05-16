<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php");
    exit();
}

$page_title = "Edit Staff Member";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);

    // Update basic info
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $username, $role, $status, $id])) {
        // If password is provided, update it too
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $id]);
        }
        $success = "User updated successfully!";
        header("Refresh: 2; url=users.php");
    } else {
        $error = "Failed to update user.";
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
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Edit Account</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Modify staff
                    permissions & profile</p>
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
                class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-3"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div
            class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border p-10 shadow-2xl shadow-slate-200/50 dark:shadow-none">
            <form action="user-edit.php?id=<?php echo $id; ?>" method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Name -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Full
                            Name</label>
                        <input type="text" name="full_name" value="<?php echo $user->full_name; ?>" required
                            class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                    </div>
                    <!-- Username -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Username</label>
                        <input type="text" name="username" value="<?php echo $user->username; ?>" required
                            class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Role Selection -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Access
                            Role</label>
                        <select name="role"
                            class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white appearance-none">
                            <option value="employee" <?php echo $user->role === 'employee' ? 'selected' : ''; ?>>Employee</option>
                            <option value="admin" <?php echo $user->role === 'admin' ? 'selected' : ''; ?>>Administrator
                            </option>
                        </select>
                    </div>
                    <!-- Status Selection -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Account
                            Status</label>
                        <select name="status"
                            class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white appearance-none">
                            <option value="active" <?php echo $user->status === 'active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="inactive" <?php echo $user->status === 'inactive' ? 'selected' : ''; ?>>Inactive / Blocked</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-white/5">
                    <label
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Security
                        / Password</label>
                    <p class="text-[9px] font-bold text-slate-400 mb-4 ml-1 italic">Leave blank to keep the current
                        password</p>
                    <div class="relative group">
                        <i
                            class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="password" name="password" placeholder="New complexity required"
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold dark:text-white">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-[0_20px_50px_rgba(37,99,235,0.3)] active:scale-95 transition-all">
                    Commit Changes
                </button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>