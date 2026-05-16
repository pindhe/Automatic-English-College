<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin();

$exam_id = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 0;
if ($exam_id <= 0) {
    header('Location: exams.php');
    exit();
}

// Fetch exam details
$exam = $pdo->prepare("SELECT * FROM exams WHERE id = :id");
$exam->execute([':id' => $exam_id]);
$exam = $exam->fetch();

if (!$exam) {
    header('Location: exams.php');
    exit();
}

$success = '';
$error = '';

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marks'])) {
    try {
        $pdo->beginTransaction();

        foreach ($_POST['marks'] as $student_id => $total_marks) {
            $student_id = (int) $student_id;
            $total_marks = (int) $total_marks;
            if ($total_marks < 0)
                $total_marks = 0;
            if ($total_marks > 100)
                $total_marks = 100;

            // Use subject_id = 1 as default (General/Total), or upsert per student
            // Check if a mark record already exists
            $existing = $pdo->prepare("SELECT id FROM marks WHERE exam_id = :eid AND student_id = :sid AND subject_id = 1");
            $existing->execute([':eid' => $exam_id, ':sid' => $student_id]);
            $row = $existing->fetch();

            if ($row) {
                $pdo->prepare("UPDATE marks SET marks_obtained = :m, total_marks = 100 WHERE id = :id")
                    ->execute([':m' => $total_marks, ':id' => $row->id]);
            } else {
                $pdo->prepare("INSERT INTO marks (exam_id, student_id, subject_id, marks_obtained, total_marks) VALUES (:eid, :sid, 1, :m, 100)")
                    ->execute([':eid' => $exam_id, ':sid' => $student_id, ':m' => $total_marks]);
            }
        }

        $pdo->commit();
        $success = 'Marks saved successfully for all students!';
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'An error occurred while saving marks. Please try again.';
    }
}

// Ensure at least one subject (id=1) exists for the upsert logic above
$sub_check = $pdo->query("SELECT id FROM subjects WHERE id = 1")->fetch();
if (!$sub_check) {
    $pdo->exec("INSERT INTO subjects (id, subject_name, subject_code) VALUES (1, 'General', 'GEN01') ON DUPLICATE KEY UPDATE id=id");
}

// Fetch class filter
$class_filter = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name")->fetchAll();

// Fetch students with their existing marks for this exam
$query = "
    SELECT s.id, s.full_name, s.student_id_code, c.class_name, c.id AS class_id,
           COALESCE(m.marks_obtained, '') AS marks_obtained
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN marks m ON m.student_id = s.id AND m.exam_id = :eid AND m.subject_id = 1
    WHERE s.status = 'active'
";
$params = [':eid' => $exam_id];

if ($class_filter > 0) {
    $query .= " AND s.class_id = :class_id";
    $params[':class_id'] = $class_filter;
}
$query .= " ORDER BY c.class_name, s.full_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

$page_title = 'Marks Entry — ' . $exam->exam_name;
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <!-- Top Bar -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-5 px-10 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center space-x-4">
            <a href="exams.php"
                class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo htmlspecialchars($exam->exam_name); ?>
                </h2>
                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">
                    <i class="far fa-calendar-alt mr-1 text-indigo-500"></i>
                    <?php echo date('d M Y', strtotime($exam->exam_date)); ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-history mr-1 text-indigo-500"></i>
                    <?php echo $exam->academic_year; ?>
                </p>
            </div>
        </div>
        <button form="marksForm" type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-7 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-indigo-200 dark:shadow-none transform active:scale-95 flex items-center space-x-2">
            <i class="fas fa-save"></i>
            <span>Save All Marks</span>
        </button>
    </header>

    <div class="p-8 space-y-6">
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

        <!-- Stats Row -->
        <div class="grid grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 flex items-center space-x-4 shadow-sm">
                <div
                    class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800 dark:text-white">
                        <?php echo count($students); ?>
                    </p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Students</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 flex items-center space-x-4 shadow-sm">
                <div
                    class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <?php $filled = count(array_filter($students, fn($s) => $s->marks_obtained !== '')); ?>
                    <p class="text-2xl font-black text-slate-800 dark:text-white">
                        <?php echo $filled; ?>
                    </p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Marks Entered</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 flex items-center space-x-4 shadow-sm">
                <div
                    class="w-12 h-12 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800 dark:text-white">
                        <?php echo count($students) - $filled; ?>
                    </p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                </div>
            </div>
        </div>

        <!-- Class Filter -->
        <div class="flex items-center space-x-3 flex-wrap gap-y-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter:</span>
            <a href="marks-entry.php?exam_id=<?php echo $exam_id; ?>"
                class="px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all <?php echo $class_filter == 0 ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' : 'bg-white dark:bg-dark-card text-slate-500 border border-gray-200 dark:border-dark-border hover:border-indigo-400'; ?>">
                All Classes
            </a>
            <?php foreach ($classes as $cls): ?>
                <a href="marks-entry.php?exam_id=<?php echo $exam_id; ?>&class_id=<?php echo $cls->id; ?>"
                    class="px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all <?php echo $class_filter == $cls->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' : 'bg-white dark:bg-dark-card text-slate-500 border border-gray-200 dark:border-dark-border hover:border-indigo-400'; ?>">
                    <?php echo htmlspecialchars($cls->class_name); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Marks Table -->
        <form id="marksForm" method="POST"
            action="marks-entry.php?exam_id=<?php echo $exam_id; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?>">
            <div
                class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">#
                            </th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                Student Name</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Class
                            </th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center">
                                Total Marks <span class="text-slate-300 normal-case font-normal">(out of 100)</span>
                            </th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center">
                                Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-dark-border" id="marksBody">
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $i => $s): ?>
                                <tr class="hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-all" data-marks-row>
                                    <td class="px-8 py-5 text-sm font-black text-slate-400">
                                        <?php echo $i + 1; ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex-shrink-0 flex items-center justify-center font-black text-base">
                                                <?php echo strtoupper(substr($s->full_name, 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800 dark:text-white text-sm">
                                                    <?php echo htmlspecialchars($s->full_name); ?>
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">
                                                    <?php echo $s->student_id_code; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span
                                            class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-900/50">
                                            <?php echo htmlspecialchars($s->class_name ?? 'Unassigned'); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-center">
                                            <input type="number" name="marks[<?php echo $s->id; ?>]"
                                                class="marks-input w-24 text-center py-3 px-3 bg-gray-50 dark:bg-slate-900 border-2 border-gray-200 dark:border-slate-700 rounded-2xl font-black text-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                min="0" max="100" value="<?php echo htmlspecialchars($s->marks_obtained); ?>"
                                                placeholder="—">
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center grade-cell" data-marks="<?php echo $s->marks_obtained; ?>">
                                        <?php
                                        $m = $s->marks_obtained;
                                        if ($m === '') {
                                            echo '<span class="text-slate-300 dark:text-slate-600 font-black text-sm">—</span>';
                                        } else {
                                            $m = (int) $m;
                                            if ($m >= 90) {
                                                $g = 'A+';
                                                $c = 'emerald';
                                            } elseif ($m >= 80) {
                                                $g = 'A';
                                                $c = 'emerald';
                                            } elseif ($m >= 70) {
                                                $g = 'B';
                                                $c = 'blue';
                                            } elseif ($m >= 60) {
                                                $g = 'C';
                                                $c = 'indigo';
                                            } elseif ($m >= 50) {
                                                $g = 'D';
                                                $c = 'amber';
                                            } else {
                                                $g = 'F';
                                                $c = 'rose';
                                            }
                                            echo "<span class='grade-badge px-3 py-1.5 bg-{$c}-50 dark:bg-{$c}-900/30 text-{$c}-600 dark:text-{$c}-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-{$c}-100 dark:border-{$c}-900/50'>{$g}</span>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <div
                                            class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-3xl flex items-center justify-center mb-4">
                                            <i class="fas fa-users text-3xl text-indigo-400"></i>
                                        </div>
                                        <p class="font-black text-slate-500 text-lg">No active students found</p>
                                        <p class="text-sm mt-1">Add students or change the class filter above.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (count($students) > 0): ?>
                    <div
                        class="px-8 py-5 border-t border-gray-100 dark:border-dark-border bg-slate-50 dark:bg-slate-900/30 flex items-center justify-between">
                        <p class="text-xs text-slate-500 font-medium">Enter scores between <strong>0–100</strong> for each
                            student. Grades update as you type.</p>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-indigo-200 dark:shadow-none flex items-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Save All Marks</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>

<script>
    // Live grade update as user types
    const gradeMap = (m) => {
        if (m === '' || isNaN(m)) return { label: '—', color: 'slate' };
        m = parseInt(m);
        if (m >= 90) return { label: 'A+', color: 'emerald' };
        if (m >= 80) return { label: 'A', color: 'emerald' };
        if (m >= 70) return { label: 'B', color: 'blue' };
        if (m >= 60) return { label: 'C', color: 'indigo' };
        if (m >= 50) return { label: 'D', color: 'amber' };
        return { label: 'F', color: 'rose' };
    };

    document.querySelectorAll('.marks-input').forEach(input => {
        const row = input.closest('tr');
        const gradeCell = row.querySelector('.grade-cell');

        const updateGrade = () => {
            const val = input.value;
            const { label, color } = gradeMap(val);
            if (label === '—') {
                gradeCell.innerHTML = `<span class="text-slate-300 dark:text-slate-600 font-black text-sm">—</span>`;
            } else {
                gradeCell.innerHTML = `<span class="px-3 py-1.5 bg-${color}-50 dark:bg-${color}-900/30 text-${color}-600 dark:text-${color}-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-${color}-100 dark:border-${color}-900/50">${label}</span>`;
            }
            // Highlight row if marks entered
            if (val !== '') {
                row.classList.add('bg-indigo-50/40', 'dark:bg-indigo-900/5');
            } else {
                row.classList.remove('bg-indigo-50/40', 'dark:bg-indigo-900/5');
            }
        };

        input.addEventListener('input', updateGrade);
        updateGrade(); // Init on load
    });
</script>

<?php include 'includes/footer.php'; ?>