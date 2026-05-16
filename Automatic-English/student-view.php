<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

if (!isset($_GET['id'])) {
    redirect('students.php');
}

$id = (int) $_GET['id'];

// Fetch Student Data
try {
    $stmt = $pdo->prepare("SELECT s.*, c.class_name 
                           FROM students s 
                           LEFT JOIN classes c ON s.class_id = c.id 
                           WHERE s.id = :id");
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    if (!$student) {
        redirect('students.php');
    }

    // Fetch Recent Attendance
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = :id ORDER BY attendance_date DESC LIMIT 5");
    $stmt->execute(['id' => $id]);
    $attendance = $stmt->fetchAll();

    // Fetch Recent Fees
    $stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = :id ORDER BY payment_date DESC LIMIT 5");
    $stmt->execute(['id' => $id]);
    $fees = $stmt->fetchAll();

} catch (PDOException $e) {
    redirect('students.php');
}

$page_title = 'Student Profile: ' . $student->full_name;
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <!-- Header -->
    <header
        class="bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border py-4 px-8 flex items-center justify-between sticky top-0 z-10 no-print transition-colors">
        <div class="flex items-center space-x-4">
            <a href="students.php"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Student Profile</h2>
        </div>
        <div class="flex items-center space-x-3">
            <a href="student-edit.php?id=<?php echo $id; ?>"
                class="bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all">
                <i class="fas fa-edit mr-2"></i> Edit Profile
            </a>
            <button onclick="window.print()"
                class="bg-slate-800 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-900 transition-all flex items-center shadow-lg shadow-slate-200">
                <i class="fas fa-print mr-2"></i> Print Card
            </button>
        </div>
    </header>

    <div class="p-8 max-w-6xl mx-auto space-y-8">
        <!-- Top Card: Profile Overview -->
        <div
            class="bg-white dark:bg-dark-card rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-dark-border p-8 flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-10 relative overflow-hidden transition-colors">
            <!-- Glassmorphism Backdrops -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl"></div>

            <div class="relative">
                <div
                    class="w-40 h-40 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden border-4 border-white dark:border-slate-800 shadow-2xl relative z-10 transition-colors">
                    <?php if ($student->photo): ?>
                        <img src="public/uploads/students/<?php echo $student->photo; ?>" alt="Profile"
                            class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-6xl font-black text-indigo-600 dark:text-indigo-400">
                            <?php echo strtoupper(substr($student->full_name, 0, 1)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div
                    class="absolute -bottom-3 -right-3 w-12 h-12 bg-emerald-500 border-4 border-white dark:border-slate-800 rounded-2xl flex items-center justify-center text-white shadow-lg animate-bounce z-20 transition-colors">
                    <i class="fas fa-check text-xs"></i>
                </div>
            </div>

            <div class="flex-grow text-center md:text-left relative z-10">
                <div class="flex items-center justify-center md:justify-start space-x-3 mb-2">
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                        <?php echo $student->full_name; ?>
                    </h1>
                    <span
                        class="px-3 py-1 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-blue-100/50 dark:border-blue-900/50 transition-colors">
                        <?php echo $student->student_id_code; ?>
                    </span>
                </div>
                <p
                    class="text-slate-500 dark:text-slate-400 font-medium flex items-center justify-center md:justify-start">
                    <i class="fas fa-graduation-cap mr-2 text-indigo-400"></i>
                    Enrolled in <span class="text-slate-800 dark:text-slate-100 font-bold ml-1">
                        <?php echo $student->class_name ?: 'Waitlist'; ?>
                    </span>
                </p>
                <div class="flex items-center justify-center md:justify-start mt-6 space-x-6">
                    <div class="text-center md:text-left">
                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-none mb-1">
                            Status</p>
                        <span
                            class="text-sm font-bold <?php echo $student->status == 'active' ? 'text-emerald-500' : 'text-rose-500'; ?> capitalize">
                            <?php echo $student->status; ?>
                        </span>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <div class="text-center md:text-left">
                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-none mb-1">
                            Gender</p>
                        <span class="text-sm font-bold text-slate-700 capitalize">
                            <?php echo $student->gender; ?>
                        </span>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <div class="text-center md:text-left">
                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-none mb-1">
                            Joined</p>
                        <span class="text-sm font-bold text-slate-700">
                            <?php echo date('M Y', strtotime($student->enrollment_date)); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Detailed Info -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Personal Details -->
                <div
                    class="bg-white dark:bg-dark-card rounded-[2rem] shadow-sm border border-gray-100 dark:border-dark-border p-8 transition-colors">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center">
                        <span
                            class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-500 dark:text-blue-400 flex items-center justify-center mr-3 text-xs italic transition-colors">
                            <i class="fas fa-id-card"></i>
                        </span>
                        Personal Details
                    </h3>
                    <div class="space-y-6">
                        <div class="group">
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">
                                Phone Number</p>
                            <p class="text-sm font-bold text-slate-700">
                                <?php echo $student->phone ?: 'Not provided'; ?>
                            </p>
                        </div>
                        <div class="group">
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">
                                Email Address</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                <?php echo $student->email ?: 'Not provided'; ?>
                            </p>
                        </div>
                        <div class="group">
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">
                                Date of Birth</p>
                            <p class="text-sm font-bold text-slate-700">
                                <?php echo date('F d, Y', strtotime($student->dob)); ?>
                            </p>
                        </div>
                        <div class="group">
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">
                                Home Address</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300 leading-relaxed">
                                <?php echo $student->address ?: 'No address specified'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Activity & Finance -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Attendance Summary -->
                <div
                    class="bg-white dark:bg-dark-card rounded-[2rem] shadow-sm border border-gray-100 dark:border-dark-border p-8 transition-colors">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center">
                            <span
                                class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-500 dark:text-emerald-400 flex items-center justify-center mr-3 text-xs transition-colors">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            Recent Attendance
                        </h3>
                        <a href="attendance.php?class_id=<?php echo $student->class_id; ?>"
                            class="text-[10px] font-bold text-blue-600 uppercase tracking-widest hover:underline">Full
                            History</a>
                    </div>

                    <div class="grid grid-cols-5 gap-4">
                        <?php if (count($attendance) > 0): ?>
                            <?php foreach ($attendance as $att): ?>
                                <div class="text-center">
                                    <div
                                        class="w-full aspect-square rounded-2xl flex flex-col items-center justify-center mb-2 <?php echo $att->status == 'present' ? 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50'; ?> transition-colors">
                                        <i
                                            class="fas <?php echo $att->status == 'present' ? 'fa-check' : 'fa-times'; ?> text-xs mb-1"></i>
                                        <span class="text-[8px] font-black uppercase tracking-tighter">
                                            <?php echo $att->status; ?>
                                        </span>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-500">
                                        <?php echo date('M d', strtotime($att->attendance_date)); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-5 py-4 text-center text-slate-400 italic text-sm">No attendance records
                                found.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div
                    class="bg-white dark:bg-dark-card rounded-[2rem] shadow-sm border border-gray-100 dark:border-dark-border transition-colors overflow-hidden">
                    <div class="p-8 border-b border-gray-50 dark:border-dark-border flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center">
                            <span
                                class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-500 dark:text-amber-400 flex items-center justify-center mr-3 text-xs transition-colors">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            Payment History
                        </h3>
                        <a href="fees.php?search=<?php echo $student->student_id_code; ?>"
                            class="text-[10px] font-bold text-blue-600 uppercase tracking-widest hover:underline">View
                            Ledger</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border transition-colors">
                                <tr>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Fee Month</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Date</th>
                                    <th
                                        class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-dark-border transition-colors">
                                <?php if (count($fees) > 0): ?>
                                    <?php foreach ($fees as $fee): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                                            <td class="px-8 py-5">
                                                <div class="font-bold text-slate-700 dark:text-slate-300">
                                                    <?php echo $fee->month; ?> Fee
                                                </div>
                                            </td>
                                            <td class="px-8 py-5 text-sm text-slate-500 dark:text-slate-400">
                                                <?php echo date('M d, Y', strtotime($fee->payment_date)); ?>
                                            </td>
                                            <td
                                                class="px-8 py-5 text-right font-black text-slate-800 dark:text-white transition-colors">
                                                <?php echo format_currency($fee->amount); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-8 py-10 text-center text-slate-400 italic">No payments
                                            recorded.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    @media print {
        .bg-slate-50\/50 {
            background: white !important;
        }

        .shadow-xl,
        .shadow-sm {
            box-shadow: none !important;
        }

        .blur-3xl {
            display: none !important;
        }

        aside {
            display: none !important;
        }

        header.no-print {
            display: none !important;
        }

        main {
            background: white !important;
            padding: 0 !important;
        }

        .max-w-6xl {
            max-width: 100% !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>