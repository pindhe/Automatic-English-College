<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$error = '';
$success = '';
$class = null;

if (!isset($_GET['id'])) {
    redirect('classes.php');
}

$id = (int) $_GET['id'];

// Initial Fetch
try {
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $class = $stmt->fetch();

    if (!$class) {
        redirect('classes.php');
    }
} catch (PDOException $e) {
    redirect('classes.php');
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = sanitize($_POST['class_name']);
    $section = sanitize($_POST['section']);

    if (empty($class_name)) {
        $error = "Class name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE classes SET class_name = :name, section = :section WHERE id = :id");
            $stmt->execute([
                'name' => $class_name,
                'section' => $section,
                'id' => $id
            ]);
            $success = "Class updated successfully!";
            // Re-fetch updated data
            $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $class = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Error updating class: " . $e->getMessage();
        }
    }
}

$page_title = 'Edit Class';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <div class="min-h-full flex items-center justify-center p-8">
        <div
            class="bg-white dark:bg-dark-card rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden border border-gray-100 dark:border-dark-border transition-colors">
            <!-- Header -->
            <div
                class="px-8 py-6 border-b border-gray-50 dark:border-dark-border flex items-center justify-between sticky top-0 bg-white dark:bg-dark-card z-10 transition-colors">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">Edit Class Info</h3>
                <a href="classes.php" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </a>
            </div>

            <!-- Messages -->
            <div class="px-8 pt-6">
                <?php if ($success): ?>
                    <div
                        class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl flex items-center mb-4 shadow-sm animate-fade-in text-sm">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span class="font-semibold"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl flex items-center mb-4 shadow-sm text-sm">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-semibold"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form -->
            <form action="class-edit.php?id=<?php echo $id; ?>" method="POST" class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Class Name *</label>
                    <input type="text" name="class_name" required
                        value="<?php echo htmlspecialchars($class->class_name); ?>"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Section /
                        Room</label>
                    <input type="text" name="section" value="<?php echo htmlspecialchars($class->section); ?>"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-4">
                    <a href="classes.php"
                        class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-all">Cancel</a>
                    <button type="submit"
                        class="flex-[2] bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-[0.98]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
</style>

<?php include 'includes/footer.php'; ?>