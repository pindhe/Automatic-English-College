<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();
require_admin();

$error = '';
$success = '';
$teacher = null;

if (!isset($_GET['id'])) {
    redirect('teachers.php');
}

$id = (int) $_GET['id'];

// Fetch Teacher Data
try {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        redirect('teachers.php');
    }
} catch (PDOException $e) {
    redirect('teachers.php');
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $salary = (float) $_POST['salary'];
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE teachers SET full_name = :name, email = :email, phone = :phone, 
                               subject = :subject, salary = :salary, hire_date = :hire_date, status = :status 
                               WHERE id = :id");
        $stmt->execute([
            'name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'salary' => $salary,
            'hire_date' => $hire_date,
            'status' => $status,
            'id' => $id
        ]);
        $success = "Teacher records updated successfully!";

        // Re-fetch updated data
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error updating teacher: " . $e->getMessage();
    }
}

$page_title = 'Edit Teacher';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <div class="min-h-full flex items-center justify-center p-8">
        <div
            class="bg-white dark:bg-dark-card rounded-[2rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-dark-border transition-colors transition-colors">
            <!-- Header -->
            <div
                class="px-8 py-6 border-b border-gray-50 dark:border-dark-border flex items-center justify-between sticky top-0 bg-white dark:bg-dark-card z-10 transition-colors">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">Edit Teacher Details</h3>
                <a href="teachers.php" class="text-slate-400 hover:text-slate-600 transition-colors">
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
            <form action="teacher-edit.php?id=<?php echo $id; ?>" method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Full Name
                            *</label>
                        <input type="text" name="full_name" required
                            value="<?php echo htmlspecialchars($teacher->full_name); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subject Specialty
                            *</label>
                        <input type="text" name="subject" required
                            value="<?php echo htmlspecialchars($teacher->subject); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Monthly Salary
                            (USD) *</label>
                        <input type="number" step="0.01" name="salary" required
                            value="<?php echo htmlspecialchars($teacher->salary); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                            Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($teacher->email); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone
                            Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($teacher->phone); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Hire Date</label>
                        <input type="date" name="hire_date" value="<?php echo htmlspecialchars($teacher->hire_date); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="active" <?php echo $teacher->status == 'active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="inactive" <?php echo $teacher->status == 'inactive' ? 'selected' : ''; ?>>
                                Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-6">
                    <a href="teachers.php"
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