<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$error = '';
$success = '';

// Get classes for selection
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name ASC")->fetchAll();

// Get active students for the selection (now with class_id)
$students = $pdo->query("SELECT id, full_name, student_id_code, class_id FROM students WHERE status = 'active' ORDER BY full_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int) $_POST['student_id'];
    $amount = (float) $_POST['amount'];
    $month = sanitize($_POST['month']);
    $payment_date = $_POST['payment_date'];
    $payment_method = sanitize($_POST['payment_method'] ?? 'Cash');
    $transaction_id = sanitize($_POST['transaction_id'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');
    $status = sanitize($_POST['status'] ?? 'paid');

    if (empty($student_id) || empty($amount) || empty($month) || empty($payment_date)) {
        $error = "Please provide all required fields (Student, Amount, Month, and Date).";
    } elseif ($amount > 21) {
        $error = "The maximum fee amount allowed is 21. You entered " . $amount . ".";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO fees (student_id, amount, month, payment_date, payment_method, transaction_id, remarks, status) 
                                   VALUES (:student_id, :amount, :month, :payment_date, :payment_method, :transaction_id, :remarks, :status)");
            $stmt->execute([
                'student_id' => $student_id,
                'amount' => $amount,
                'month' => $month,
                'payment_date' => $payment_date,
                'payment_method' => $payment_method,
                'transaction_id' => $transaction_id,
                'remarks' => $remarks,
                'status' => $status
            ]);
            $success = "Payment of " . format_currency($amount) . " for " . $month . " recorded successfully!";
            redirect('fees.php?success=paid');
        } catch (PDOException $e) {
            $error = "Failed to record payment: " . $e->getMessage();
        }
    }
}

$page_title = 'Collect Fee';
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
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">Collect Student Fee</h3>
                <a href="fees.php" class="text-slate-400 hover:text-slate-600 transition-colors">
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
            <form action="fee-add.php" method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">First Select
                            Class *</label>
                        <select id="class_selector" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="">-- Choose Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class->id; ?>">
                                    <?php echo $class->class_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Then Select
                            Student *</label>
                        <select id="student_selector" name="student_id" required disabled
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer opacity-50 cursor-not-allowed dark:text-white dark:placeholder-slate-500">
                            <option value="">-- Select Class First --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student->id; ?>" data-class="<?php echo $student->class_id; ?>"
                                    class="hidden">
                                    <?php echo $student->full_name; ?> (<?php echo $student->student_id_code; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Amount (USD)
                            *</label>
                        <input type="number" step="0.01" max="21" name="amount" required placeholder="0.00"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white dark:placeholder-slate-500">
                        <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-tight">Standard fee is
                            21.00</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Fee Month
                            *</label>
                        <select name="month" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <?php
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            $current_month = date('F');
                            foreach ($months as $m) {
                                $selected = ($m == $current_month) ? 'selected' : '';
                                echo "<option value='$m' $selected>$m</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Payment Date
                            *</label>
                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Payment
                            Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid / Pending</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Transaction ID /
                            Reference</label>
                        <input type="text" name="transaction_id" placeholder="T-..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Remarks</label>
                        <textarea name="remarks" rows="2" placeholder="Notes..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500"></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-4">
                    <a href="fees.php"
                        class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-all">Cancel</a>
                    <button type="submit"
                        class="flex-[2] bg-emerald-600 text-white py-4 rounded-2xl font-bold shadow-xl shadow-emerald-200 hover:bg-emerald-700 transition-all transform active:scale-[0.98]">
                        Process Payment
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

<script>
    document.getElementById('class_selector').addEventListener('change', function () {
        const classId = this.value;
        const studentSelector = document.getElementById('student_selector');
        const options = studentSelector.querySelectorAll('option');

        if (!classId) {
            studentSelector.disabled = true;
            studentSelector.classList.add('opacity-50', 'cursor-not-allowed');
            studentSelector.value = "";
            return;
        }

        studentSelector.disabled = false;
        studentSelector.classList.remove('opacity-50', 'cursor-not-allowed');
        studentSelector.value = "";

        // Reset text
        options[0].textContent = "-- Choose Student --";

        let found = false;
        options.forEach((opt, index) => {
            if (index === 0) return;
            if (opt.getAttribute('data-class') == classId) {
                opt.style.display = 'block';
                found = true;
            } else {
                opt.style.display = 'none';
            }
        });

        if (!found) {
            options[0].textContent = "-- No students in this class --";
        }
    });
</script>

<?php include 'includes/footer.php'; ?>