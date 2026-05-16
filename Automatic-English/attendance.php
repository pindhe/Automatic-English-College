<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$class_id = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;

$students = [];
if ($class_id > 0) {
    $stmt = $pdo->prepare("SELECT s.*, a.status as attendance_status 
                          FROM students s 
                          LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = :date 
                          WHERE s.class_id = :class_id AND s.status = 'active'");
    $stmt->execute(['date' => $date, 'class_id' => $class_id]);
    $students = $stmt->fetchAll();
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();

$page_title = 'Attendance';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20 transition-all duration-300">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Daily Attendance</h2>
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-3">
                <span
                    class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Select
                    Date:</span>
                <input type="date" value="<?php echo $date; ?>"
                    class="border-none bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-black rounded-xl px-5 py-2.5 focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all text-sm"
                    onchange="window.location.href='attendance.php?date='+this.value+'&class_id=<?php echo $class_id; ?>'">
            </div>
        </div>
    </header>

    <div class="p-8">
        <div
            class="bg-white dark:bg-dark-card p-6 rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-sm mb-8 flex flex-col md:flex-row items-center justify-between transition-all duration-300">
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <div
                    class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                    <i class="fas fa-layer-group"></i>
                </div>
                <select id="classSelect"
                    class="bg-gray-50 dark:bg-slate-900 border-none rounded-2xl px-6 py-3 font-black text-sm focus:ring-2 focus:ring-blue-500 dark:text-white transition-all min-w-[240px]"
                    onchange="window.location.href='attendance.php?date=<?php echo $date; ?>&class_id='+this.value">
                    <option value="0">Select Class to Manage</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c->id; ?>" <?php echo $class_id == $c->id ? 'selected' : ''; ?>>
                            <?php echo $c->class_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($class_id > 0): ?>
                <div class="flex space-x-4">
                    <div
                        class="flex items-center space-x-2 px-5 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl font-black text-[10px] uppercase tracking-widest border border-emerald-100 dark:border-emerald-900/50">
                        <i class="fas fa-check-circle"></i> <span>Present</span>
                    </div>
                    <div
                        class="flex items-center space-x-2 px-5 py-2.5 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl font-black text-[10px] uppercase tracking-widest border border-rose-100 dark:border-rose-900/50">
                        <i class="fas fa-times-circle"></i> <span>Absent</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($class_id > 0): ?>
            <form action="attendance-save.php" method="POST"
                class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden transition-all duration-300">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                        <tr>
                            <th
                                class="px-10 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">
                                Student Information
                            </th>
                            <th
                                class="px-10 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-center">
                                Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                        <?php foreach ($students as $student): ?>
                            <tr class="hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-all duration-300">
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-5">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-lg font-black shadow-inner">
                                            <?php echo strtoupper(substr($student->full_name, 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 dark:text-white text-lg tracking-tight">
                                                <?php echo $student->full_name; ?>
                                            </p>
                                            <p
                                                class="text-[10px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest mt-0.5">
                                                ID: <?php echo $student->student_id_code; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-12">
                                        <label class="relative flex items-center cursor-pointer group">
                                            <input type="radio" name="attendance[<?php echo $student->id; ?>]" value="present"
                                                <?php echo $student->attendance_status == 'present' ? 'checked' : ''; ?>
                                                class="sr-only peer" required>
                                            <div
                                                class="w-14 h-14 flex items-center justify-center rounded-2xl border-2 border-gray-100 dark:border-dark-border text-gray-300 dark:text-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 transition-all group-hover:border-emerald-200 dark:group-hover:border-emerald-800/50 shadow-sm peer-checked:shadow-emerald-100 dark:peer-checked:shadow-none">
                                                <i class="fas fa-check text-xl"></i>
                                            </div>
                                        </label>
                                        <label class="relative flex items-center cursor-pointer group">
                                            <input type="radio" name="attendance[<?php echo $student->id; ?>]" value="absent"
                                                <?php echo $student->attendance_status == 'absent' ? 'checked' : ''; ?>
                                                class="sr-only peer">
                                            <div
                                                class="w-14 h-14 flex items-center justify-center rounded-2xl border-2 border-gray-100 dark:border-dark-border text-gray-300 dark:text-slate-700 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400 transition-all group-hover:border-rose-200 dark:group-hover:border-rose-800/50 shadow-sm peer-checked:shadow-rose-100 dark:peer-checked:shadow-none">
                                                <i class="fas fa-times text-xl"></i>
                                            </div>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div
                    class="px-10 py-8 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-end border-t border-gray-100 dark:border-dark-border">
                    <button type="submit"
                        class="bg-blue-600 text-white px-12 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl shadow-blue-200 dark:shadow-none hover:bg-blue-700 transform active:scale-95 transition-all">
                        Save Attendance Records
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                <div class="w-20 h-20 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-users-viewfinder text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">No Class Selected</h3>
                <p class="text-slate-500 mt-2">Please select a class from the dropdown above to mark daily attendance.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>