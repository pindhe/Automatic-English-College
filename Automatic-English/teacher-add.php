<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $salary = (float) $_POST['salary'];
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];
    $teacher_id_code = generate_id('TEA');

    try {
        $stmt = $pdo->prepare("INSERT INTO teachers (teacher_id_code, full_name, email, phone, subject, salary, hire_date, status) 
                               VALUES (:id, :name, :email, :phone, :subject, :salary, :hire_date, :status)");
        $stmt->execute([
            'id' => $teacher_id_code,
            'name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'salary' => $salary,
            'hire_date' => $hire_date,
            'status' => $status
        ]);
        $success = "Teacher added successfully! ID: " . $teacher_id_code;
    } catch (PDOException $e) {
        $error = "Error adding teacher: " . $e->getMessage();
    }
}

$page_title = 'Add Teacher';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <div class="min-h-full flex items-center justify-center p-8">
        <div
            class="bg-white dark:bg-dark-card rounded-[2rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-dark-border transition-colors">
            <!-- Header -->
            <div
                class="px-8 py-6 border-b border-gray-50 dark:border-dark-border flex items-center justify-between sticky top-0 bg-white dark:bg-dark-card z-10 transition-colors">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">Add New Teacher</h3>
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
            <form action="teacher-add.php" method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Full Name
                            *</label>
                        <input type="text" name="full_name" required placeholder="Ex: Prof. Sarah Wilson"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subject Specialty
                            *</label>
                        <input type="text" name="subject" required placeholder="Ex: English Literature"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Monthly Salary
                            (USD) *</label>
                        <input type="number" step="0.01" name="salary" required placeholder="0.00"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                            Address</label>
                        <input type="email" name="email" placeholder="sarah@example.com"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone
                            Number</label>
                        <input type="text" name="phone" placeholder="+1..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Hire Date</label>
                        <input type="date" name="hire_date" value="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-6">
                    <a href="teachers.php"
                        class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-all">Cancel</a>
                    <button type="submit"
                        class="flex-[2] bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-[0.98]">
                        Assign Teacher
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