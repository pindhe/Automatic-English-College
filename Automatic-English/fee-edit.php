<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

if (!isset($_GET['id'])) {
    redirect('fees.php');
}

$id = (int) $_GET['id'];
$error = '';
$success = '';

// Fetch existing fee record
$stmt = $pdo->prepare("SELECT f.*, s.full_name, s.student_id_code 
                       FROM fees f 
                       JOIN students s ON f.student_id = s.id 
                       WHERE f.id = :id");
$stmt->execute(['id' => $id]);
$fee = $stmt->fetch();

if (!$fee) {
    redirect('fees.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) $_POST['amount'];
    $month = sanitize($_POST['month']);
    $payment_date = $_POST['payment_date'];
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $transaction_id = sanitize($_POST['transaction_id']);
    $remarks = sanitize($_POST['remarks']);
    $status = sanitize($_POST['status']);

    if (empty($amount) || empty($month) || empty($payment_date)) {
        $error = "Required fields are missing.";
    } elseif ($amount > 21) {
        $error = "The maximum fee amount allowed is 21. You entered " . $amount . ".";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE fees SET 
                                   amount = :amount, 
                                   month = :month, 
                                   payment_date = :payment_date, 
                                   payment_method = :payment_method, 
                                   transaction_id = :transaction_id, 
                                   remarks = :remarks,
                                   status = :status 
                                   WHERE id = :id");
            $stmt->execute([
                'amount' => $amount,
                'month' => $month,
                'payment_date' => $payment_date,
                'payment_method' => $payment_method,
                'transaction_id' => $transaction_id,
                'remarks' => $remarks,
                'status' => $status,
                'id' => $id
            ]);
            header('Location: fees.php?success=1');
            exit();

            // Refresh fee data
            $stmt = $pdo->prepare("SELECT f.*, s.full_name, s.student_id_code FROM fees f JOIN students s ON f.student_id = s.id WHERE f.id = :id");
            $stmt->execute(['id' => $id]);
            $fee = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Failed to update record: " . $e->getMessage();
        }
    }
}

$page_title = 'Edit Fee - ' . $fee->full_name;
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
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Edit Payment Record</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-1">
                        <?php echo $fee->full_name; ?> (
                        <?php echo $fee->student_id_code; ?>)
                    </p>
                </div>
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
                        <span class="font-semibold">
                            <?php echo $success; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl flex items-center mb-4 shadow-sm text-sm">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-semibold">
                            <?php echo $error; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form -->
            <form action="fee-edit.php?id=<?php echo $id; ?>" method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Amount (USD)
                            *</label>
                        <input type="number" step="0.01" max="21" name="amount" value="<?php echo $fee->amount; ?>"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
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
                            foreach ($months as $m) {
                                $selected = ($m == $fee->month) ? 'selected' : '';
                                echo "<option value='$m' $selected>$m</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Payment Date
                            *</label>
                        <input type="date" name="payment_date" value="<?php echo $fee->payment_date; ?>" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Payment
                            Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="paid" <?php echo $fee->status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="unpaid" <?php echo $fee->status == 'unpaid' ? 'selected' : ''; ?>>Unpaid /
                                Pending</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Transaction ID /
                            Reference</label>
                        <input type="text" name="transaction_id" value="<?php echo $fee->transaction_id; ?>"
                            placeholder="T-..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Remarks</label>
                        <textarea name="remarks" rows="2" placeholder="Notes..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500"><?php echo $fee->remarks; ?></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-4">
                    <a href="fees.php"
                        class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-all">Cancel</a>
                    <button type="submit"
                        class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all transform active:scale-[0.98]">
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