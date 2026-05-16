<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$exam_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($exam_id <= 0) {
    header('Location: exams.php');
    exit();
}

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = :id");
$stmt->execute([':id' => $exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    header('Location: exams.php');
    exit();
}

// Get search and filter parameters
$search = sanitize($_GET['search'] ?? '');
$class_filter = sanitize($_GET['class'] ?? '');
$grade_filter = sanitize($_GET['grade'] ?? '');

// Fetch all students with their marks for this exam
$query = "
    SELECT s.id AS student_id, s.full_name, s.student_id_code, s.photo,
           c.class_name,
           COALESCE(m.marks_obtained, NULL) AS marks_obtained,
           m.total_marks
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN marks m ON m.student_id = s.id AND m.exam_id = :eid AND m.subject_id = 1
    WHERE (s.status = 'active' OR m.student_id IS NOT NULL)";

$params = [':eid' => $exam_id];

// Add search conditions
if (!empty($search)) {
    $query .= " AND (s.full_name LIKE :search OR s.student_id_code LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($class_filter)) {
    $query .= " AND c.class_name = :class";
    $params[':class'] = $class_filter;
}

// Add grade filter (based on marks)
if (!empty($grade_filter)) {
    switch ($grade_filter) {
        case 'A+':
            $query .= " AND (m.marks_obtained >= 90 OR m.marks_obtained IS NULL)";
            break;
        case 'A':
            $query .= " AND (m.marks_obtained >= 80 AND m.marks_obtained < 90 OR m.marks_obtained IS NULL)";
            break;
        case 'B':
            $query .= " AND (m.marks_obtained >= 70 AND m.marks_obtained < 80 OR m.marks_obtained IS NULL)";
            break;
        case 'C':
            $query .= " AND (m.marks_obtained >= 60 AND m.marks_obtained < 70 OR m.marks_obtained IS NULL)";
            break;
        case 'D':
            $query .= " AND (m.marks_obtained >= 50 AND m.marks_obtained < 60 OR m.marks_obtained IS NULL)";
            break;
        case 'F':
            $query .= " AND (m.marks_obtained < 50 OR m.marks_obtained IS NULL)";
            break;
    }
}

$query .= " ORDER BY m.marks_obtained DESC, s.full_name ASC";

$results = $pdo->prepare($query);
$results->execute($params);
$students = $results->fetchAll();

// Compute stats
$total_students = count($students);
$entered = array_filter($students, fn($s) => $s->marks_obtained !== null);
$entered_count = count($entered);
$avg = $entered_count > 0 ? round(array_sum(array_map(fn($s) => $s->marks_obtained, $entered)) / $entered_count, 1) : 0;
$highest = $entered_count > 0 ? max(array_map(fn($s) => (int) $s->marks_obtained, $entered)) : 0;

function get_grade($m)
{
    if ($m === null)
        return ['—', 'slate'];
    $m = (int) $m;
    if ($m >= 90)
        return ['A+', 'emerald'];
    if ($m >= 80)
        return ['A', 'emerald'];
    if ($m >= 70)
        return ['B', 'blue'];
    if ($m >= 60)
        return ['C', 'indigo'];
    if ($m >= 50)
        return ['D', 'amber'];
    return ['F', 'rose'];
}

$page_title = 'Exam Results — ' . $exam->exam_name;
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    
    /* Print Styles for Professional Results */
    @media print {
        /* Hide unnecessary elements */
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        
        /* Page setup */
        @page {
            size: A4;
            margin: 1cm;
            @top-center {
                content: "";
                font-size: 10pt;
                color: #64748b;
            }
        }
        
        body { 
            font-size: 12pt; 
            line-height: 1.6;
            color: #1f2937;
            background: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Hide table when printing - show list instead */
        table { display: none !important; }
        
        /* Student list styling for print */
        .student-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .student-list li {
            padding: 12pt 0;
            margin-bottom: 8pt;
            border-bottom: 1px solid #e5e7eb;
            page-break-inside: avoid;
        }
        
        .student-list li:last-child {
            border-bottom: none;
        }
        
        /* Student name styling */
        .student-name {
            font-size: 14pt;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4pt;
        }
        
        /* Score styling */
        .student-score {
            font-size: 16pt;
            font-weight: 700;
            color: #1f2937;
            float: right;
            background: #f8fafc;
            padding: 4pt 8pt;
            border-radius: 4pt;
            border: 1px solid #e5e7eb;
        }
        
        /* Rank styling */
        .student-rank {
            font-size: 12pt;
            font-weight: 600;
            color: #6b7280;
            background: #fef3c7;
            padding: 2pt 6pt;
            border-radius: 50%;
            margin-right: 8pt;
            min-width: 30pt;
            text-align: center;
        }
        
        /* Class styling */
        .student-class {
            font-size: 10pt;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 4pt;
        }
        
        /* Exam header for print */
        .exam-header {
            text-align: center;
            margin-bottom: 30pt;
            page-break-after: avoid;
        }
        
        .exam-title {
            font-size: 18pt;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8pt;
        }
        
        .exam-subtitle {
            font-size: 12pt;
            color: #6b7280;
            margin-bottom: 20pt;
        }
        
        /* Footer for print */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8pt;
        }
        
        /* Summary section */
        .print-summary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 12pt;
            margin: 20pt 0;
            border-radius: 4pt;
        }
        
        .summary-item {
            display: inline-block;
            margin: 0 15pt;
        }
        
        /* Remove shadows and backgrounds */
        * {
            box-shadow: none !important;
            background: transparent !important;
            text-shadow: none !important;
        }
    }
    
    /* Screen styles */
    .print-only { display: none; }
</style>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <!-- Top Bar -->
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-5 px-10 flex items-center justify-between sticky top-0 z-20 no-print">
        <div class="flex items-center space-x-4">
            <a href="exams.php"
                class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-indigo-600 transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                    <?php echo htmlspecialchars($exam->exam_name); ?>
                </h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">
                    <i class="far fa-calendar-alt mr-1 text-indigo-500"></i>
                    <?php echo date('d M Y', strtotime($exam->exam_date)); ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-history mr-1 text-indigo-500"></i>
                    <?php echo htmlspecialchars($exam->academic_year); ?>
                </p>
            </div>
        </div>
        <?php if (is_admin()): ?>
            <a href="marks-entry.php?exam_id=<?php echo $exam_id; ?>"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-indigo-200 dark:shadow-none flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Edit Marks</span>
            </a>
            <button onclick="window.print()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-green-200 dark:shadow-none flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print List</span>
            </button>
        <?php else: ?>
            <button onclick="window.print()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-green-200 dark:shadow-none flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print List</span>
            </button>
        <?php endif; ?>
    </header>

    <div class="p-8 space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 no-print">
            <!-- Total Students -->
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div
                        class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 dark:text-white">
                    <?php echo $total_students; ?>
                </p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Students</p>
            </div>
            <!-- Marks Entered -->
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div
                        class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 dark:text-white">
                    <?php echo $entered_count; ?>
                </p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Graded</p>
            </div>
            <!-- Average -->
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 dark:text-white">
                    <?php echo $avg; ?>
                </p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Class Average</p>
            </div>
            <!-- Highest -->
            <div
                class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trophy text-amber-600 dark:text-amber-400 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 dark:text-white">
                    <?php echo $highest; ?>
                </p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Highest Score</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl p-6 mb-8 no-print">
            <form method="GET" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $exam_id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            <i class="fas fa-search mr-1"></i>Search Students
                        </label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-border bg-white dark:bg-dark-card text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all"
                               placeholder="Search by name or ID...">
                    </div>
                    
                    <!-- Class Filter -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            <i class="fas fa-filter mr-1"></i>Filter by Class
                        </label>
                        <select name="class" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-border bg-white dark:bg-dark-card text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all">
                            <option value="">All Classes</option>
                            <?php
                            $classes = $pdo->query("SELECT DISTINCT class_name FROM classes ORDER BY class_name")->fetchAll();
                            foreach ($classes as $class):
                            ?>
                                <option value="<?php echo htmlspecialchars($class->class_name); ?>" 
                                        <?php echo $class_filter === $class->class_name ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class->class_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Grade Filter -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            <i class="fas fa-graduation-cap mr-1"></i>Filter by Grade
                        </label>
                        <select name="grade" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-border bg-white dark:bg-dark-card text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all">
                            <option value="">All Grades</option>
                            <option value="A+" <?php echo $grade_filter === 'A+' ? 'selected' : ''; ?>>A+ (90-100)</option>
                            <option value="A" <?php echo $grade_filter === 'A' ? 'selected' : ''; ?>>A (80-89)</option>
                            <option value="B" <?php echo $grade_filter === 'B' ? 'selected' : ''; ?>>B (70-79)</option>
                            <option value="C" <?php echo $grade_filter === 'C' ? 'selected' : ''; ?>>C (60-69)</option>
                            <option value="D" <?php echo $grade_filter === 'D' ? 'selected' : ''; ?>>D (50-59)</option>
                            <option value="F" <?php echo $grade_filter === 'F' ? 'selected' : ''; ?>>F (0-49)</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="exam-view.php?id=<?php echo $exam_id; ?>" 
                       class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 text-sm font-medium transition-all">
                        <i class="fas fa-times mr-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Print Header (hidden on screen, visible on print) -->
        <div class="print-only exam-header">
            <div class="exam-title"><?php echo htmlspecialchars($exam->exam_name); ?></div>
            <div class="exam-subtitle">
                <i class="fas fa-calendar-alt mr-2"></i>
                <?php echo date('F j, Y', strtotime($exam->exam_date)); ?>
                &nbsp;·&nbsp;
                <i class="fas fa-clock mr-2"></i>
                Academic Year: <?php echo htmlspecialchars($exam->academic_year); ?>
            </div>
        </div>

        <!-- Print Student List (hidden on screen, visible on print) -->
        <div class="print-only">
            <ul class="student-list">
                <?php 
                $print_rank = 1;
                foreach ($students as $s):
                    [$grade_label, $grade_color] = get_grade($s->marks_obtained);
                    $pct = $s->marks_obtained !== null ? (int) $s->marks_obtained : 0;
                ?>
                <li>
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="student-name">
                                <?php echo htmlspecialchars($s->full_name); ?>
                                <?php if (!empty($s->class_name)): ?>
                                    <div class="student-class"><?php echo htmlspecialchars($s->class_name); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <?php if ($s->marks_obtained !== null): ?>
                                <div class="student-rank"><?php echo $print_rank; ?></div>
                                <div class="student-score"><?php echo $s->marks_obtained; ?>/100</div>
                            <?php else: ?>
                                <div class="student-rank">—</div>
                                <div class="student-score">No Score</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php 
                if ($s->marks_obtained !== null)
                    $print_rank++; 
                endforeach; 
                ?>
            </ul>
            
            <!-- Summary Section -->
            <div class="print-summary">
                <div class="text-center mb-4">
                    <strong>Summary Statistics</strong>
                </div>
                <div class="summary-item">
                    <strong>Total Students:</strong> <?php echo count($students); ?>
                </div>
                <div class="summary-item">
                    <strong>Students Graded:</strong> <?php echo $entered_count; ?>
                </div>
                <div class="summary-item">
                    <strong>Class Average:</strong> <?php echo $avg; ?>%
                </div>
                <div class="summary-item">
                    <strong>Highest Score:</strong> <?php echo $highest; ?>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div
            class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-dark-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 dark:text-white text-xl tracking-tight">Student Results</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                                <i class="fas fa-trophy mr-1 text-indigo-500"></i>
                                Ranked by performance
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            <i class="fas fa-users mr-1"></i>
                            <?php echo count($students); ?> Students
                        </span>
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            <i class="fas fa-check-circle mr-1"></i>
                            <?php echo $entered_count; ?> Graded
                        </span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] print-hide">Rank
                            </th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] print-show">Student
                            </th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] print-hide">Class
                            </th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center print-show">Score
                            </th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center print-hide">Grade
                            </th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center print-hide">Progress
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
<?php if (count($students) > 0): ?>
    <?php
    $rank = 1;
    foreach ($students as $s):
        [$grade_label, $grade_color] = get_grade($s->marks_obtained);
        $pct = $s->marks_obtained !== null ? (int) $s->marks_obtained : 0;
    ?>
        <tr class="hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-all border-b border-gray-100 dark:border-dark-border/50">
            
            <!-- Rank -->
            <td class="px-8 py-5">
                <?php if ($s->marks_obtained !== null): ?>
                    <div class="flex items-center justify-center">
                        <span class="w-8 h-8 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-lg">
                            <?php echo $rank; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="text-slate-400 dark:text-slate-600 text-lg font-medium">—</span>
                <?php endif; ?>
            </td>

            <!-- Student -->
            <td class="px-8 py-5">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <?php if ($s->photo): ?>
                            <img src="public/uploads/students/<?php echo $s->photo; ?>" 
                                 class="w-12 h-12 rounded-xl object-cover shadow-md border-2 border-white" alt="">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white font-bold text-lg flex items-center justify-center shadow-lg">
                                <?php echo strtoupper(substr($s->full_name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-slate-800 dark:text-white text-base">
                            <?php echo htmlspecialchars($s->full_name); ?>
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-widest">
                            <?php echo $s->student_id_code; ?>
                        </p>
                    </div>
                </div>
            </td>

            <!-- Class -->
            <td class="px-8 py-5">
                <span class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-900/50 
                             text-indigo-700 dark:text-indigo-300 
                             rounded-xl text-[11px] font-bold uppercase tracking-widest border border-indigo-200 dark:border-indigo-700 shadow-sm">
                    <i class="fas fa-graduation-cap mr-2 text-xs"></i>
                    <?php echo htmlspecialchars($s->class_name ?? 'Unassigned'); ?>
                </span>
            </td>

            <!-- Score -->
            <td class="px-8 py-5 text-center">
                <?php if ($s->marks_obtained !== null): ?>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-black text-slate-800 dark:text-white font-bold">
                            <?php echo $s->marks_obtained; ?>
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">out of 100</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-center">
                        <span class="text-2xl text-slate-400 dark:text-slate-600 font-medium">—</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 ml-2">No Score</span>
                    </div>
                <?php endif; ?>
            </td>

            <!-- Grade -->
            <td class="px-8 py-5 text-center">
                <?php if ($grade_label !== '—'): ?>
                    <div class="inline-flex items-center justify-center">
                        <span class="relative">
                            <span class="px-4 py-2 bg-gradient-to-r from-<?php echo $grade_color; ?>-50 to-<?php echo $grade_color; ?>-100 
                                         dark:from-<?php echo $grade_color; ?>-900/30 dark:to-<?php echo $grade_color; ?>-900/50 
                                         text-<?php echo $grade_color; ?>-700 dark:text-<?php echo $grade_color; ?>-300 
                                         rounded-xl text-[12px] font-black uppercase tracking-widest border border-<?php echo $grade_color; ?>-200 dark:border-<?php echo $grade_color; ?>-700 shadow-sm font-bold">
                                <?php echo $grade_label; ?>
                            </span>
                            <?php if ($grade_label === 'A+' || $grade_label === 'A'): ?>
                                <span class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="text-slate-400 dark:text-slate-600 text-lg font-medium">—</span>
                <?php endif; ?>
            </td>

            <!-- Progress -->
            <td class="px-8 py-5">
                <div class="w-36">
                    <?php if ($s->marks_obtained !== null): ?>
                        <div class="space-y-2">
                            <div class="h-3 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden shadow-inner">
                                <div class="h-full rounded-full bg-gradient-to-r transition-all duration-500
                                <?php echo $pct >= 70 ? 'from-emerald-400 to-emerald-500 shadow-emerald-200' : 
                                    ($pct >= 50 ? 'from-amber-400 to-amber-500 shadow-amber-200' : 
                                    'from-rose-400 to-rose-500 shadow-rose-200'); ?>"
                                    style="width: <?php echo $pct; ?>%">
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">
                                    <?php 
                                    if ($pct >= 90) echo 'Excellent';
                                    elseif ($pct >= 80) echo 'Very Good';
                                    elseif ($pct >= 70) echo 'Good';
                                    elseif ($pct >= 50) echo 'Average';
                                    else echo 'Needs Improvement';
                                    ?>
                                </span>
                                <span class="text-xs font-black text-slate-800 dark:text-white">
                                    <?php echo $pct; ?>%
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="flex items-center justify-center text-slate-400 dark:text-slate-600">
                        <i class="fas fa-minus-circle mr-2"></i>
                        <span class="text-sm">No Progress</span>
                    </div>
                <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="px-8 py-5 text-center">
            <p class="text-slate-400 dark:text-slate-500 font-medium text-sm">
                No results found.
            </p>
        </td>
    </tr>
<?php endif; ?>
</tbody>
                </table>
            </div>
        </div>

        <!-- Print Footer (hidden on screen, visible on print) -->
        <div class="print-only print-footer">
            <div class="flex justify-between items-center">
                <div>
                    <i class="fas fa-chart-bar mr-2"></i>
                    Total Students: <strong><?php echo count($students); ?></strong>
                </div>
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    Graded: <strong><?php echo $entered_count; ?></strong>
                </div>
                <div>
                    <i class="fas fa-percentage mr-2"></i>
                    Average: <strong><?php echo $avg; ?>%</strong>
                </div>
            </div>
            <div class="mt-4 text-center">
                <small>
                    Generated on <?php echo date('F j, Y, g:i A'); ?> 
                    via Automatic Management System
                </small>
            </div>
        </div>
    </div>
</main>
