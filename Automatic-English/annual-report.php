<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

start_secure_session();
require_login();
require_admin();

// Get form data
$class_id = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 0;
$academic_year = sanitize($_POST['academic_year'] ?? date('Y'));

// Fetch classes for dropdown
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

// Fetch academic years
$years = $pdo->query("SELECT DISTINCT academic_year FROM exams WHERE academic_year IS NOT NULL ORDER BY academic_year DESC")->fetchAll();

// Generate report if form submitted
$report_data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $class_id > 0) {
    // Get class details
    $class_stmt = $pdo->prepare("SELECT class_name FROM classes WHERE id = :id");
    $class_stmt->execute([':id' => $class_id]);
    $class = $class_stmt->fetch();
    
    // Get all exams for the selected academic year
    $exams_stmt = $pdo->prepare("SELECT id, exam_name FROM exams WHERE academic_year = :year ORDER BY exam_date");
    $exams_stmt->execute([':year' => $academic_year]);
    $exams = $exams_stmt->fetchAll();
    
    if ($exams) {
        // Get students in the class with their marks for all exams
        $student_query = "
            SELECT s.id, s.full_name, s.student_id_code,
                   e.exam_name,
                   m.marks_obtained,
                   m.total_marks
            FROM students s
            LEFT JOIN marks m ON m.student_id = s.id
            LEFT JOIN exams e ON m.exam_id = e.id
            WHERE s.class_id = :class_id 
            AND e.academic_year = :year
            ORDER BY s.full_name, e.exam_date
        ";
        
        $stmt = $pdo->prepare($student_query);
        $stmt->execute([':class_id' => $class_id, ':year' => $academic_year]);
        $students_data = $stmt->fetchAll();
        
        // Organize data by student
        $report_data = [];
        foreach ($students_data as $row) {
            $student_key = $row->id;
            if (!isset($report_data[$student_key])) {
                $report_data[$student_key] = [
                    'full_name' => $row->full_name,
                    'student_id_code' => $row->student_id_code,
                    'exams' => []
                ];
            }
            $report_data[$student_key]['exams'][] = [
                'exam_name' => $row->exam_name,
                'marks_obtained' => $row->marks_obtained,
                'total_marks' => $row->total_marks
            ];
        }
    }
}

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Report - <?php echo $settings->college_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155'
                        },
                        brand: {
                            teal: '#0d7377',
                            'teal-light': '#14b8a6',
                            'teal-dark': '#0d5c63',
                            orange: '#e07c24',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        /* Print Styles for Annual Report */
        @media print {
            /* Hide unnecessary elements */
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            
            /* Page setup */
            @page {
                size: A4;
                margin: 1cm;
            }
            
            body { 
                font-size: 12pt; 
                line-height: 1.6;
                color: #1f2937;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Show table when printing */
            table { display: table !important; }
            
            /* Table styling for print */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 0 auto 20pt auto;
                page-break-inside: avoid;
                max-width: 80%;
            }
            
            th, td {
                border: 1px solid #e5e7eb;
                padding: 8pt;
                text-align: left;
                vertical-align: middle;
            }
            
            th {
                background-color: #f8fafc;
                font-weight: 600;
                font-size: 10pt;
                text-transform: uppercase;
                color: #374151;
                border-bottom: 2px solid #e5e7eb;
            }
            
            /* Hide ID column when printing */
            td:nth-child(2) { display: none !important; }
            th:nth-child(2) { display: none !important; }
            
            /* Student name styling */
            td:first-child {
                font-size: 12pt;
                font-weight: 600;
                color: #1f2937;
            }
            
            /* Score styling */
            td:not(:first-child):not(:nth-child(2)) {
                text-align: center;
                font-weight: 600;
                color: #1f2937;
            }
            
            /* Exam header for print */
            .exam-header {
                text-align: center;
                margin-bottom: 20pt;
                margin-top: 0;
                page-break-after: avoid;
                position: relative;
                top: 0;
                border-top: 3px solid #1f2937;
                padding-top: 15pt;
            }
            
            .exam-title {
                font-size: 20pt;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 8pt;
                margin-top: 0;
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
            
            /* Remove shadows and backgrounds */
            * {
                box-shadow: none !important;
                background: transparent !important;
                text-shadow: none !important;
            }
            
            .print-break { page-break-inside: avoid; }
        }
        
        .print-only { display: none; }
    </style>
</head>

<body class="bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow overflow-y-auto">
        <div class="p-8">
            <!-- Page Header -->
            <div class="mb-8 no-print">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-2">
                            Annual Report Generator
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">
                            Generate printable annual reports for students and their marks
                        </p>
                    </div>
                    <div class="flex space-x-3 no-print">
                        <a href="index.php" 
                           class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                            <i class="fas fa-arrow-left mr-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Form -->
            <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl mb-8 no-print">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Class Selection -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                <i class="fas fa-school mr-1"></i>Select Class
                            </label>
                            <select name="class_id" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-border bg-white dark:bg-dark-card text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all">
                                <option value="">Choose a class...</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class->id; ?>" 
                                            <?php echo $class_id === $class->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class->class_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Academic Year -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i>Academic Year
                            </label>
                            <select name="academic_year" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-border bg-white dark:bg-dark-card text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all">
                                <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year->academic_year; ?>" 
                                            <?php echo $academic_year === $year->academic_year ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($year->academic_year); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Generate Button -->
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                                <i class="fas fa-file-alt mr-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Display -->
            <?php if ($report_data && count($report_data) > 0): ?>
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <!-- Report Header -->
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-dark-border flex items-center justify-between no-print">
                        <div>
                            <h3 class="font-black text-slate-800 dark:text-white text-lg tracking-tight">
                                Annual Report - <?php echo htmlspecialchars($class->class_name); ?>
                            </h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                                Academic Year: <?php echo htmlspecialchars($academic_year); ?>
                            </p>
                        </div>
                        <div class="flex space-x-3 no-print">
                            <button onclick="window.print()" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                                <i class="fas fa-print mr-2"></i>Print Report
                            </button>
                            <button onclick="exportToExcel()" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                                <i class="fas fa-file-excel mr-2"></i>Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left print-break">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Student Name</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ID Code</th>
                                    <?php foreach ($exams as $exam): ?>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center">
                                            <?php echo htmlspecialchars($exam->exam_name); ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-center no-print">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                                <?php foreach ($report_data as $student): ?>
                                    <tr class="hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-all print-break">
                                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-white">
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            <?php echo htmlspecialchars($student['student_id_code']); ?>
                                        </td>
                                        <?php foreach ($exams as $exam): ?>
                                            <td class="px-6 py-4 text-center">
                                                <?php 
                                                $marks = null;
                                                foreach ($student['exams'] as $student_exam) {
                                                    if ($student_exam['exam_name'] === $exam->exam_name) {
                                                        $marks = $student_exam['marks_obtained'];
                                                        break;
                                                    }
                                                }
                                                if ($marks !== null && $marks !== '') {
                                                    echo '<span class="font-black text-slate-800 dark:text-white">' . $marks . '</span>';
                                                } else {
                                                    echo '<span class="text-slate-300 dark:text-slate-600">—</span>';
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-6 py-4 text-center no-print">
                                            <button onclick="showStudentDetails('<?php echo $student['full_name']; ?>', '<?php echo $student['student_id_code']; ?>')" 
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all">
                                                <i class="fas fa-eye mr-1"></i>Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Print Header (hidden on screen, visible on print) -->
                    <div class="print-only exam-header">
                        <div class="exam-title">Annual Academic Report</div>
                        <div class="exam-subtitle">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <?php echo htmlspecialchars($class->class_name); ?>
                            &nbsp;·&nbsp;
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Academic Year: <?php echo htmlspecialchars($academic_year); ?>
                        </div>
                    </div>

                    <!-- Report Summary -->
                    <div class="px-8 py-6 border-t border-gray-100 dark:border-dark-border print-only">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <p class="font-black text-slate-600 dark:text-slate-400">
                                    Examination Signature: <span class="font-bold text-slate-800 dark:text-slate-200"><?php echo count($exams); ?></span>
                                </p>
                            </div>
                            <div>
                                <!-- Empty div for spacing -->
                            </div>
                            <div>
                                <p class="font-black text-slate-600 dark:text-slate-400 text-right">
                                    TS: <span class="font-bold text-slate-800 dark:text-slate-200"><?php echo count($report_data); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $class_id > 0): ?>
                <div class="bg-white dark:bg-dark-card p-8 rounded-2xl border border-gray-100 dark:border-dark-border shadow-xl">
                    <div class="text-center py-12">
                        <i class="fas fa-users text-slate-300 dark:text-slate-600 text-5xl mb-4"></i>
                        <p class="text-lg font-black text-slate-600 dark:text-slate-400">No students found in this class</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mt-2">The selected class may not have any students assigned.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function exportToExcel() {
            // Simple CSV export for Excel
            let table = document.querySelector('table');
            if (!table) return;
            
            let rows = [];
            let headers = [];
            
            // Get headers
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            rows.push(headers);
            
            // Get data rows
            table.querySelectorAll('tbody tr').forEach(tr => {
                let rowData = [];
                tr.querySelectorAll('td').forEach(td => {
                    rowData.push(td.textContent.trim());
                });
                rows.push(rowData);
            });
            
            // Create CSV
            let csv = rows.map(row => row.map(cell => '"' + cell + '"').join(',')).join('\n');
            
            // Download
            let blob = new Blob([csv], { type: 'text/csv' });
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'annual_report_<?php echo $class_id ?? 'export'; ?>_<?php echo $academic_year; ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        function showStudentDetails(studentName, studentId) {
            // Create modal if it doesn't exist
            let modal = document.getElementById('studentDetailsModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'studentDetailsModal';
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white dark:bg-dark-card rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-black text-slate-800 dark:text-white">Student Details</h3>
                            <button onclick="closeStudentDetails()" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div id="studentDetailsContent">
                            <!-- Content will be populated here -->
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            
            // Find student data
            const studentData = <?php echo json_encode($report_data); ?>;
            const student = Object.values(studentData).find(s => s.full_name === studentName && s.student_id_code === studentId);
            
            // Populate modal content
            const content = document.getElementById('studentDetailsContent');
            let gradesHtml = '';
            
            if (student && student.exams) {
                gradesHtml = `
                    <div class="mt-6">
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4">Exam Grades</h4>
                        <div class="space-y-3">
                            ${student.exams.map(exam => `
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">${exam.exam_name}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Score</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-black text-slate-800 dark:text-white">
                                            ${exam.marks_obtained !== null && exam.marks_obtained !== '' ? exam.marks_obtained + '%' : '—'}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            ${exam.total_marks ? 'out of ' + exam.total_marks : ''}
                                        </p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Student Name</label>
                            <p class="font-bold text-slate-800 dark:text-white">${studentName}</p>
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Student ID</label>
                            <p class="font-bold text-slate-800 dark:text-white">${studentId}</p>
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Class</label>
                            <p class="font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($class->class_name); ?></p>
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Academic Year</label>
                            <p class="font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($academic_year); ?></p>
                        </div>
                    </div>
                    
                    ${gradesHtml}
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-dark-border flex space-x-3">
                        <button onclick="printStudentReport('${studentName}', '${studentId}')" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-black text-xs uppercase tracking-widest transition-all">
                            <i class="fas fa-print mr-2"></i>Print Report
                        </button>
                        <button onclick="closeStudentDetails()" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-black text-xs uppercase tracking-widest transition-all">
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            // Show modal
            modal.style.display = 'flex';
        }
        
        function printStudentReport(studentName, studentId) {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Find student data
            const studentData = <?php echo json_encode($report_data); ?>;
            const student = Object.values(studentData).find(s => s.full_name === studentName && s.student_id_code === studentId);
            
            // Generate HTML for print
            let gradesHtml = '';
            if (student && student.exams) {
                gradesHtml = student.exams.map(exam => `
                    <tr>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">${exam.exam_name}</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: center;">
                            ${exam.marks_obtained !== null && exam.marks_obtained !== '' ? exam.marks_obtained + '%' : '—'}
                        </td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: center;">
                            ${exam.total_marks || '—'}
                        </td>
                    </tr>
                `).join('');
            }
            
            const printHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Student Report - ${studentName}</title>
                    <style>
                        body { font-family: 'Inter', sans-serif; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-top: 3px solid #1f2937; padding-top: 15px; }
                        .title { font-size: 20pt; font-weight: 700; margin-bottom: 8px; }
                        .subtitle { font-size: 12pt; color: #6b7280; margin-bottom: 20px; }
                        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                        .info-item { margin-bottom: 10px; }
                        .label { font-size: 10pt; font-weight: 600; text-transform: uppercase; color: #6b7280; }
                        .value { font-size: 12pt; font-weight: 600; color: #1f2937; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                        th { background-color: #f8fafc; font-weight: 600; font-size: 10pt; text-transform: uppercase; }
                        td { font-size: 12pt; }
                        .footer { margin-top: 30px; text-align: center; font-size: 10pt; color: #6b7280; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="title">Student Academic Report</div>
                        <div class="subtitle">
                            🎓 <?php echo htmlspecialchars($class->class_name); ?> · 📅 Academic Year: <?php echo htmlspecialchars($academic_year); ?>
                        </div>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Student Name</div>
                            <div class="value">${studentName}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Student ID</div>
                            <div class="value">${studentId}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Class</div>
                            <div class="value"><?php echo htmlspecialchars($class->class_name); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Academic Year</div>
                            <div class="value"><?php echo htmlspecialchars($academic_year); ?></div>
                        </div>
                    </div>
                    
                    <h3 style="font-size: 14pt; font-weight: 600; margin-bottom: 15px;">Exam Results</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th style="text-align: center;">Score</th>
                                <th style="text-align: center;">Total Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${gradesHtml}
                        </tbody>
                    </table>
                </body>
                </html>
            `;
            
            printWindow.document.write(printHtml);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
        
        function closeStudentDetails() {
            const modal = document.getElementById('studentDetailsModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
