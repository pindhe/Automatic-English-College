<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

// Auto-create graduations table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS `graduations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` int(11) NOT NULL,
    `certificate_type` varchar(100) NOT NULL,
    `graduation_date` date NOT NULL,
    `remarks` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_grad_student` (`student_id`),
    CONSTRAINT `fk_grad_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$page_title = 'Graduation';
$success = '';
$error = '';

// Handle Graduation Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['graduate_student'])) {
    $student_id = (int) $_POST['student_id'];
    $certificate = sanitize($_POST['certificate_type']);
    $grad_date = sanitize($_POST['graduation_date']);
    $remarks = sanitize($_POST['remarks'] ?? '');

    if ($student_id > 0 && !empty($certificate) && !empty($grad_date)) {
        // Add graduation record
        $stmt = $pdo->prepare("
            INSERT INTO graduations (student_id, certificate_type, graduation_date, remarks, created_at)
            VALUES (:student_id, :certificate_type, :graduation_date, :remarks, NOW())
        ");
        $stmt->execute([
            ':student_id' => $student_id,
            ':certificate_type' => $certificate,
            ':graduation_date' => $grad_date,
            ':remarks' => $remarks,
        ]);

        // Mark student as inactive (graduated)
        $pdo->prepare("UPDATE students SET status = 'inactive' WHERE id = :id")
            ->execute([':id' => $student_id]);

        $success = 'Student has been successfully graduated and their certificate has been recorded.';
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Fetch active students for the select dropdown
$active_students = $pdo->query("
    SELECT s.id, s.full_name, s.student_id_code, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.status = 'active'
    ORDER BY s.full_name ASC
")->fetchAll();

// Fetch graduated students (all inactive who have a graduation record)
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$grad_query = "
    SELECT g.*, s.full_name, s.student_id_code, s.photo, c.class_name
    FROM graduations g
    JOIN students s ON g.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.full_name LIKE :search OR s.student_id_code LIKE :search
    ORDER BY g.graduation_date DESC
";
$stmt = $pdo->prepare($grad_query);
$stmt->execute([':search' => "%$search%"]);
$graduated = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <!-- Top Bar -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center space-x-4">
            <div
                class="w-12 h-12 bg-gradient-to-tr from-amber-500 to-orange-400 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-200 dark:shadow-none">
                <i class="fas fa-graduation-cap text-white text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Graduation Registry</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Certificate &
                    Completion Management</p>
            </div>
        </div>
        <span
            class="bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-2xl border border-amber-200 dark:border-amber-800">
            <?php echo count($graduated); ?> Graduate
            <?php echo count($graduated) != 1 ? 's' : ''; ?> Total
        </span>
    </header>

    <div class="p-8 space-y-8">
        <!-- Alerts -->
        <?php if ($success): ?>
            <div
                class="flex items-center space-x-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-6 py-4 rounded-2xl">
                <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
                <p class="font-bold text-sm">
                    <?php echo $success; ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div
                class="flex items-center space-x-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 px-6 py-4 rounded-2xl">
                <i class="fas fa-exclamation-circle text-xl flex-shrink-0"></i>
                <p class="font-bold text-sm">
                    <?php echo $error; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <!-- === GRADUATION FORM === -->
            <div class="lg:col-span-2">
                <div
                    class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden">
                    <!-- Form Header -->
                    <div class="bg-gradient-to-r from-amber-500 to-orange-400 p-8 pb-10">
                        <h3 class="text-xl font-black text-white tracking-tight">Graduate a Student</h3>
                        <p class="text-amber-100 text-xs mt-1 font-medium">Mark student as completed & issue a
                            certificate</p>
                    </div>

                    <form method="POST" action="graduation.php" class="p-8 -mt-4 space-y-6">
                        <!-- Student Select with Search -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Select
                                Student *</label>
                            <div class="relative">
                                <i
                                    class="fas fa-user-graduate absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select name="student_id" id="studentSelect" required
                                    class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:text-white text-sm font-bold transition-all appearance-none">
                                    <option value="">-- Search & Select Student --</option>
                                    <?php foreach ($active_students as $s): ?>
                                        <option value="<?php echo $s['id']; ?>">
                                            <?php echo htmlspecialchars($s['full_name']); ?>
                                            (
                                            <?php echo $s['student_id_code']; ?>)
                                            <?php echo $s['class_name'] ? ' — ' . $s['class_name'] : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if (empty($active_students)): ?>
                                <p class="text-xs text-rose-500 mt-1 font-medium">No active students found.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Certificate Type -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Certificate
                                Type *</label>
                            <div class="grid grid-cols-2 gap-3" id="certButtons">
                                <?php
                                $cert_types = [
                                    ['label' => 'General English', 'icon' => 'fa-file-alt', 'color' => 'indigo'],
                                    ['label' => 'IELTS Prep', 'icon' => 'fa-globe', 'color' => 'blue'],
                                    ['label' => 'TOEFL Prep', 'icon' => 'fa-award', 'color' => 'violet'],
                                    ['label' => 'Business English', 'icon' => 'fa-briefcase', 'color' => 'amber'],
                                    ['label' => 'Conversation', 'icon' => 'fa-comments', 'color' => 'emerald'],
                                    ['label' => 'Custom', 'icon' => 'fa-pen', 'color' => 'slate'],
                                ];
                                foreach ($cert_types as $ct): ?>
                                    <label class="cert-option cursor-pointer">
                                        <input type="radio" name="certificate_type" value="<?php echo $ct['label']; ?>"
                                            class="sr-only cert-radio" required>
                                        <div
                                            class="cert-card flex items-center space-x-2 px-3 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 hover:border-amber-400 transition-all text-xs font-black text-slate-600 dark:text-slate-300">
                                            <i class="fas <?php echo $ct['icon']; ?> text-slate-400"></i>
                                            <span>
                                                <?php echo $ct['label']; ?>
                                            </span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <!-- Hidden real input so we can use the custom UI above -->
                            <input type="hidden" name="certificate_type" id="certTypeHidden">
                        </div>

                        <!-- Graduation Date -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Graduation
                                Date *</label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="date" name="graduation_date" required value="<?php echo date('Y-m-d'); ?>"
                                    class="w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-amber-500 dark:text-white text-sm font-bold transition-all">
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Remarks
                                <span class="text-slate-300">(Optional)</span></label>
                            <textarea name="remarks" rows="3"
                                placeholder="e.g., Graduated with distinction, Passed all exams..."
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-amber-500 dark:text-white text-sm font-medium resize-none transition-all"></textarea>
                        </div>

                        <button type="submit" name="graduate_student"
                            class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-600 hover:to-orange-500 text-white font-black text-[11px] uppercase tracking-widest rounded-2xl transition-all shadow-xl shadow-amber-200 dark:shadow-none transform active:scale-95 flex items-center justify-center space-x-2">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Record Graduation</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- === GRADUATED STUDENTS TABLE === -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Search -->
                <form action="graduation.php" method="GET" class="flex gap-3">
                    <div class="relative flex-grow">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?php echo $search; ?>"
                            placeholder="Search graduates by name or ID..."
                            class="w-full pl-10 pr-4 py-3.5 bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-amber-500 dark:text-white text-sm font-medium transition-all">
                    </div>
                    <button type="submit"
                        class="bg-slate-800 dark:bg-amber-500 text-white px-6 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 dark:hover:bg-amber-600 transition-all">
                        Search
                    </button>
                </form>

                <!-- Table -->
                <div
                    class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-dark-border">
                        <h3 class="font-black text-slate-800 dark:text-white text-lg tracking-tight">Graduation Records
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                                <tr>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                        Graduate</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                        Certificate</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                        Grad. Date</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                        Remarks</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                                <?php if (count($graduated) > 0): ?>
                                    <?php foreach ($graduated as $g): ?>
                                        <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-900/10 transition-all">
                                            <td class="px-6 py-5">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 flex-shrink-0 flex items-center justify-center font-black text-base overflow-hidden">
                                                        <?php if ($g['photo']): ?>
                                                            <img src="public/uploads/students/<?php echo $g['photo']; ?>" alt=""
                                                                class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <?php echo strtoupper(substr($g['full_name'], 0, 1)); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-black text-slate-800 dark:text-white text-sm">
                                                            <?php echo htmlspecialchars($g['full_name']); ?>
                                                        </p>
                                                        <p
                                                            class="text-[10px] text-slate-400 font-black uppercase tracking-widest">
                                                            <?php echo $g['student_id_code']; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <span
                                                    class="px-3 py-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-xl text-[10px] font-black uppercase tracking-widest border border-amber-100 dark:border-amber-800">
                                                    <i class="fas fa-certificate mr-1"></i>
                                                    <?php echo htmlspecialchars($g['certificate_type']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-sm font-bold text-slate-600 dark:text-slate-300">
                                                <?php echo date('d M Y', strtotime($g['graduation_date'])); ?>
                                            </td>
                                            <td
                                                class="px-6 py-5 text-xs text-slate-500 dark:text-slate-400 max-w-[160px] truncate">
                                                <?php echo htmlspecialchars($g['remarks'] ?: '—'); ?>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex items-center justify-end">
                                                    <a href="student-view.php?id=<?php echo $g['student_id']; ?>"
                                                        class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-amber-600 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg"
                                                        title="View Profile">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center text-slate-400">
                                                <div
                                                    class="w-20 h-20 bg-amber-50 dark:bg-amber-900/20 rounded-3xl flex items-center justify-center mb-4">
                                                    <i class="fas fa-graduation-cap text-3xl text-amber-400"></i>
                                                </div>
                                                <p class="font-black text-slate-500 text-lg">No graduates yet</p>
                                                <p class="text-sm mt-1">Use the form to record your first graduation.</p>
                                            </div>
                                        </td>
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

<script>
    // Certificate type button selection UI
    const certRadios = document.querySelectorAll('.cert-radio');
    const certHidden = document.getElementById('certTypeHidden');

    certRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.cert-card').forEach(card => {
                card.classList.remove('border-amber-400', 'bg-amber-50', 'dark:bg-amber-900/20', 'text-amber-700', 'dark:text-amber-300');
                card.classList.add('border-gray-100', 'dark:border-slate-700', 'text-slate-600', 'dark:text-slate-300');
            });
            const card = radio.closest('label').querySelector('.cert-card');
            card.classList.add('border-amber-400', 'bg-amber-50', 'dark:bg-amber-900/20', 'text-amber-700', 'dark:text-amber-300');
            card.classList.remove('border-gray-100', 'dark:border-slate-700', 'text-slate-600', 'dark:text-slate-300');
            certHidden.value = radio.value;
        });
    });

    // Override the form so only the hidden field is submitted, not radio buttons
    document.querySelector('form').addEventListener('submit', function (e) {
        if (!certHidden.value) {
            // Check if any radio is checked
            const checked = document.querySelector('.cert-radio:checked');
            if (!checked) {
                e.preventDefault();
                alert('Please select a certificate type.');
                return;
            }
            certHidden.value = checked.value;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>