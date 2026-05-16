<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin(); // Only admins can create exams

$page_title = 'Create Exam';
$error = '';

// Fetch all classes and subjects for dropdowns
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = sanitize($_POST['exam_name'] ?? '');
    $exam_date = sanitize($_POST['exam_date'] ?? '');
    $academic_year = sanitize($_POST['academic_year'] ?? '');

    if (empty($exam_name) || empty($exam_date) || empty($academic_year)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO exams (exam_name, exam_date, academic_year) VALUES (:name, :date, :year)");
        $stmt->execute([
            ':name' => $exam_name,
            ':date' => $exam_date,
            ':year' => $academic_year,
        ]);
        header('Location: exams.php?success=1');
        exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <!-- Top Bar -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center space-x-4">
            <a href="exams.php"
                class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition-all hover:shadow-md">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Create New Exam</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Exam & Assessment
                    Setup</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <div
                class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-blue-500 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 dark:shadow-none">
                <i class="fas fa-file-invoice text-white text-sm"></i>
            </div>
        </div>
    </header>

    <div class="p-8">
        <?php if ($error): ?>
            <div
                class="flex items-center space-x-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 px-6 py-4 rounded-2xl mb-6">
                <i class="fas fa-exclamation-circle text-xl flex-shrink-0"></i>
                <p class="font-bold text-sm">
                    <?php echo $error; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto">
            <div
                class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-8">
                    <h3 class="text-xl font-black text-white tracking-tight">Exam Details</h3>
                    <p class="text-indigo-200 text-xs mt-1 font-medium">Fill in the information below to schedule a new
                        exam</p>
                </div>

                <form method="POST" action="exam-add.php" class="p-8 space-y-6">

                    <!-- Exam Name -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Exam Name *
                        </label>
                        <div class="relative">
                            <i
                                class="fas fa-file-signature absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="exam_name" required placeholder="e.g., Mid-Year English Assessment"
                                value="<?php echo isset($_POST['exam_name']) ? htmlspecialchars($_POST['exam_name']) : ''; ?>"
                                class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white text-sm font-medium transition-all">
                        </div>
                    </div>

                    <!-- Exam Date -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Exam Date *
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="date" name="exam_date" required
                                value="<?php echo isset($_POST['exam_date']) ? htmlspecialchars($_POST['exam_date']) : date('Y-m-d'); ?>"
                                class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-bold transition-all">
                        </div>
                    </div>

                    <!-- Academic Year -->
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Academic Year *
                        </label>
                        <div class="relative">
                            <i class="fas fa-history absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select name="academic_year" required
                                class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-bold transition-all appearance-none">
                                <?php
                                $current_year = (int) date('Y');
                                for ($y = $current_year + 1; $y >= $current_year - 3; $y--):
                                    $yr = $y . '-' . ($y + 1);
                                    $selected = ($yr === ($current_year . '-' . ($current_year + 1))) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $yr; ?>" <?php echo $selected; ?>>
                                        <?php echo $yr; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Info Banner -->
                    <div
                        class="flex items-start space-x-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl p-4">
                        <i class="fas fa-info-circle text-indigo-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-xs text-indigo-700 dark:text-indigo-300 font-medium leading-relaxed">
                            After creating the exam, you can go to <strong>Enter Marks</strong> from the Exams list to
                            assign scores per student and subject.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-4 pt-2">
                        <a href="exams.php"
                            class="flex-1 py-4 text-center bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700 font-black text-[11px] uppercase tracking-widest rounded-2xl transition-all">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex-1 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-black text-[11px] uppercase tracking-widest rounded-2xl transition-all shadow-xl shadow-indigo-200 dark:shadow-none transform active:scale-95 flex items-center justify-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Create Exam</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Tips Card -->
            <div
                class="mt-6 bg-white dark:bg-dark-card rounded-[2rem] border border-gray-100 dark:border-dark-border p-6 shadow-sm">
                <h4 class="font-black text-slate-700 dark:text-slate-200 text-sm mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-amber-400 mr-2"></i>
                    Quick Tips
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <li class="flex items-start space-x-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span>Use descriptive exam names like <em>"Term 1 — Grammar & Writing"</em></span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span>Set the exam date to the actual test date for accurate records</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span>After saving, you can enter student marks from the exam list</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>