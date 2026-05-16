<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$page_title = 'Students';
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Search & Filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$class_filter = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

$query = "SELECT s.*, c.class_name 
          FROM students s 
          LEFT JOIN classes c ON s.class_id = c.id 
          WHERE (s.full_name LIKE :search OR s.student_id_code LIKE :search)";

$params = ['search' => "%$search%"];

if ($class_filter > 0) {
    $query .= " AND s.class_id = :class_id";
    $params['class_id'] = $class_filter;
}

$query .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get Classes for filter
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
?>

<!-- Main Content -->
<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <!-- Top Bar -->
    <header class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20 transition-all duration-300">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Student Management</h2>
        <a href="student-add.php" class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center shadow-xl shadow-blue-200 dark:shadow-none transform active:scale-95">
            <i class="fas fa-plus mr-2 text-xs"></i> Add New Student
        </a>
    </header>

    <div class="p-8">
        <!-- Search & Filter Bar -->
        <div class="bg-white dark:bg-dark-card p-6 rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-sm mb-8 transition-all">
            <form action="students.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search by name or ID..." 
                           class="w-full pl-10 pr-4 py-3.5 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white dark:placeholder-slate-500 transition-all text-sm font-medium">
                </div>
                
                <select name="class_id" class="w-full px-5 py-3.5 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all text-sm font-black">
                    <option value="0">All Classes</option>
                    <?php foreach($classes as $class): ?>
                        <option value="<?php echo $class->id; ?>" <?php echo $class_filter == $class->id ? 'selected' : ''; ?>>
                            <?php echo $class->class_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
 
                <button type="submit" class="bg-slate-800 dark:bg-indigo-600 text-white px-6 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 dark:hover:bg-indigo-700 transition-all shadow-lg shadow-slate-200 dark:shadow-none">
                    Filter Results
                </button>
            </form>
        </div>

        <!-- Student Table -->
        <div class="bg-white dark:bg-dark-card rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-xl overflow-hidden transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-dark-border">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">Student</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">Identification</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">Class Assigned</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">Contact</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                        <?php if (count($students) > 0): ?>
                            <?php foreach($students as $student): ?>
                            <tr class="hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 flex-shrink-0 flex items-center justify-center overflow-hidden font-black text-lg transition-colors shadow-inner">
                                            <?php if($student->photo): ?>
                                                <img src="public/uploads/students/<?php echo $student->photo; ?>" alt="Photo" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($student->full_name, 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 dark:text-white text-lg tracking-tight"><?php echo $student->full_name; ?></p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest mt-0.5"><?php echo $student->email ?: 'No email assigned'; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 font-black text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest"><?php echo $student->student_id_code; ?></td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-900/50">
                                        <?php echo $student->class_name ?: 'Unassigned'; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-medium text-slate-600 dark:text-slate-400"><?php echo $student->phone ?: 'N/A'; ?></td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1.5 <?php echo $student->status == 'active' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/50'; ?> rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">
                                        <?php echo $student->status; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="student-view.php?id=<?php echo $student->id; ?>" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg" title="View Profile">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="student-edit.php?id=<?php echo $student->id; ?>" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg" title="Edit Student">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <?php if(is_admin()): ?>
                                        <button onclick="confirmDelete(<?php echo $student->id; ?>)" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 bg-gray-50 dark:bg-slate-900 transition-all hover:shadow-lg" title="Delete record">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">No students found matching your criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this student? Information will be lost forever.')) {
        window.location.href = 'student-actions.php?action=delete&id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
