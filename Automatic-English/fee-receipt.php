<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

if (!isset($_GET['id'])) {
    die("Receipt ID not provided.");
}

$fee_id = (int) $_GET['id'];

// Get database query for receipt details
$query = "SELECT f.*, s.full_name, s.student_id_code, c.class_name 
          FROM fees f 
          JOIN students s ON f.student_id = s.id 
          LEFT JOIN classes c ON s.class_id = c.id
          WHERE f.id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $fee_id]);
$fee = $stmt->fetch();

if (!$fee) {
    die("Payment record not found.");
}

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt -
        <?php echo $fee->student_id_code; ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .receipt-card {
                border: none;
                shadow: none;
            }
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-dark-bg py-12 px-4 shadow-inner transition-colors duration-300">

    <div class="max-w-2xl mx-auto py-12 px-4 no-print flex justify-between items-center mb-6">
        <a href="fees.php" class="text-slate-500 hover:text-slate-800 font-bold flex items-center transition-all">
            <i class="fas fa-arrow-left mr-2"></i> Back to Fees
        </a>
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-95">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </button>
    </div>

    <!-- Receipt Card -->
    <div
        class="max-w-2xl mx-auto bg-white dark:bg-dark-card rounded-[2rem] shadow-2xl border border-gray-100 dark:border-dark-border overflow-hidden receipt-card transition-colors">
        <!-- Header / Logo -->
        <div class="bg-slate-900 px-10 py-10 text-white flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold tracking-tight uppercase dark:text-white">
                    <?php echo $settings->college_name; ?>
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    <?php echo $settings->address ?: 'Educational Management System'; ?>
                </p>
                <div class="flex items-center space-x-4 mt-4 text-xs font-semibold text-slate-300">
                    <span class="flex items-center"><i class="fas fa-phone mr-1.5 opacity-50"></i>
                        <?php echo $settings->contact_phone ?: '+1 234 567 890'; ?>
                    </span>
                    <span class="flex items-center"><i class="fas fa-envelope mr-1.5 opacity-50"></i>
                        <?php echo $settings->contact_email; ?>
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-blue-600 px-4 py-2 rounded-xl inline-block font-bold text-sm tracking-widest uppercase">
                    Official Receipt</div>
                <p class="text-xs text-slate-500 mt-4 leading-relaxed font-mono uppercase tracking-tighter">Receipt #:
                    <span class="text-white">RCP-
                        <?php echo str_pad($fee->id, 6, '0', STR_PAD_LEFT); ?>
                    </span>
                </p>
                <p class="text-xs text-slate-500 leading-relaxed font-mono uppercase tracking-tighter">Date: <span
                        class="text-white">
                        <?php echo date('M d, Y', strtotime($fee->payment_date)); ?>
                    </span></p>
            </div>
        </div>

        <div class="p-10 space-y-10">
            <!-- Student Info -->
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h5
                        class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">
                        Received From</h5>
                    <div class="font-bold text-slate-800 dark:text-white text-lg">
                        <?php echo $fee->full_name; ?>
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                        <?php echo $fee->student_id_code; ?>
                    </div>
                    <div
                        class="inline-block mt-2 px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                        <?php echo $fee->class_name ?: 'Global Section'; ?>
                    </div>
                </div>
                <div class="text-right">
                    <h5
                        class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">
                        Payment Detail</h5>
                    <div class="font-bold text-slate-800 dark:text-white text-lg">
                        <?php echo format_currency($fee->amount); ?>
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                        <?php echo $fee->payment_method; ?>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-1 italic">
                        <?php echo $fee->transaction_id ? 'Ref: ' . $fee->transaction_id : 'Manual Entry'; ?>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-gray-100 dark:border-dark-border overflow-hidden">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border transition-colors">
                        <tr>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                Description</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">
                                Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-dark-border transition-colors">
                        <tr>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-700 dark:text-slate-300 transition-colors">Tuition Fee
                                    -
                                    <?php echo $fee->month; ?>
                                </div>
                                <div class="mt-2 flex items-center space-x-2">
                                    <span
                                        class="px-2 py-0.5 bg-slate-100/50 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200/50">
                                        <?php echo $settings->academic_year; ?>
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest <?php echo $fee->status == 'paid' ? 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50'; ?>">
                                        <?php echo $fee->status; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right font-bold text-slate-800 dark:text-white transition-colors">
                                <?php echo format_currency($fee->amount); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-right">Total Paid</th>
                            <th class="px-6 py-5 text-lg font-bold text-right">
                                <?php echo format_currency($fee->amount); ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Remarks & Footer -->
            <div class="flex justify-between items-end pt-6">
                <div class="max-w-[60%]">
                    <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Collector Remarks
                    </h5>
                    <p class="text-xs text-slate-500 leading-relaxed italic border-l-2 border-slate-200 pl-4 py-1">
                        <?php echo $fee->remarks ?: 'No additional remarks provided.'; ?>
                    </p>
                </div>
                <div class="text-center w-40">
                    <div class="h-10 border-b border-slate-200 mb-2"></div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Authorized Signature</p>
                </div>
            </div>
        </div>

        <!-- Footer Bar -->
        <div
            class="bg-gray-50 dark:bg-slate-900 px-10 py-6 border-t border-gray-100 dark:border-dark-border flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.1em] transition-colors">
            <span>Generated on
                <?php echo date('M d, Y H:i'); ?>
            </span>
            <span>Thank you for your payment</span>
        </div>
    </div>

    <p class="text-center text-slate-400 text-[10px] mt-12 uppercase tracking-widest font-bold no-print">
        &copy;
        <?php echo date('Y'); ?>
        <?php echo $settings->college_name; ?>. Digitally Generated Document.
    </p>

</body>

</html>