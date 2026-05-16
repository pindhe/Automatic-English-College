<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$page_title = 'Fee Management';
include 'includes/header.php';
include 'includes/sidebar.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$query = "SELECT f.*, s.full_name, s.student_id_code 
          FROM fees f 
          JOIN students s ON f.student_id = s.id 
          WHERE s.full_name LIKE :search OR s.student_id_code LIKE :search 
          ORDER BY f.payment_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['search' => "%$search%"]);
$fees = $stmt->fetchAll();
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-10 transition-colors duration-300">
        <h2 class="text-xl font-bold dark:text-white">Fee Management</h2>
        <a href="fee-add.php"
            class="bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold text-sm hover:bg-emerald-700 transition-all flex items-center shadow-lg shadow-emerald-200">
            <i class="fas fa-plus mr-2"></i> Collect New Payment
        </a>
    </header>

    </header>

    <div class="p-8">
        <?php if (isset($_GET['success'])): ?>
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl flex items-center mb-6 shadow-sm animate-fade-in text-sm font-bold">
                <i class="fas fa-check-circle mr-3"></i>
                Action completed successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div
                class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl flex items-center mb-6 shadow-sm text-sm font-bold">
                <i class="fas fa-exclamation-circle mr-3"></i>
                An error occurred while processing your request.
            </div>
        <?php endif; ?>
        <div
            class="bg-white dark:bg-dark-card p-6 rounded-[2rem] border border-gray-100 dark:border-dark-border shadow-sm mb-8 transition-all duration-300">
            <form action="fees.php" method="GET" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                <div class="relative flex-grow">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo $search; ?>"
                        placeholder="Search by student name or ID..."
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-slate-500 transition-all">
                </div>
                <button type="submit"
                    class="bg-slate-800 dark:bg-blue-600 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-slate-900 dark:hover:bg-blue-700 transition-all shadow-lg shadow-slate-200 dark:shadow-none">
                    Search Records
                </button>
            </form>
        </div>

        <div
            class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border shadow-sm overflow-hidden transition-colors duration-300">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                    <tr>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Student</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Month</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Amount</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Date</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Method</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                            Status</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                    <?php if (count($fees) > 0): ?>
                        <?php foreach ($fees as $fee): ?>
                            <tr
                                class="transition-all duration-300 <?php echo $fee->amount < 21 ? 'bg-amber-50 dark:bg-amber-900/10 hover:bg-amber-100/60 dark:hover:bg-amber-900/20 border-l-4 border-amber-400' : 'hover:bg-blue-50/20 dark:hover:bg-blue-900/10'; ?>">
                                <td class="px-8 py-5">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">
                                        <?php echo $fee->full_name; ?>
                                    </p>
                                    <p
                                        class="text-[10px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest mt-0.5">
                                        <?php echo $fee->student_id_code; ?>
                                    </p>
                                </td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                        <?php echo $fee->month; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="font-black <?php echo $fee->amount < 21 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white'; ?>">
                                            <?php echo format_currency($fee->amount); ?>
                                        </span>
                                        <?php if ($fee->amount < 21): ?>
                                            <span
                                                class="flex items-center space-x-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-lg text-[9px] font-black uppercase tracking-widest border border-amber-200 dark:border-amber-700">
                                                <i class="fas fa-exclamation-triangle text-[8px]"></i>
                                                <span>Partial</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-slate-600 dark:text-slate-400">
                                    <?php echo date('M d, Y', strtotime($fee->payment_date)); ?>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="flex items-center text-xs font-bold text-slate-600 dark:text-slate-400">
                                        <i
                                            class="fas <?php echo $fee->payment_method == 'Cash' ? 'fa-wallet' : 'fa-credit-card'; ?> mr-2 text-slate-400 dark:text-slate-500"></i>
                                        <?php echo $fee->payment_method; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all <?php echo $fee->status == 'paid' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/50'; ?>">
                                        <?php echo $fee->status; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="fee-receipt.php?id=<?php echo $fee->id; ?>" target="_blank"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg"
                                            title="Print Receipt">
                                            <i class="fas fa-print text-xs"></i>
                                        </a>
                                        <a href="fee-edit.php?id=<?php echo $fee->id; ?>"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg"
                                            title="Edit Payment">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $fee->id; ?>)"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg"
                                            title="Delete record">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No payment records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this payment record? If an employee performs this action, the administrator will be notified.')) {
            window.location.href = 'fee-actions.php?action=delete&id=' + id;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>