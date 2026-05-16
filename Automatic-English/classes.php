<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$error = '';
$success = '';

// Handle Class Deletion
if (isset($_GET['delete'])) {
    if (!is_admin()) {
        $error = "You don't have permission to delete classes.";
    } else {
        $delete_id = (int) $_GET['delete'];
        try {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = :id");
            $stmt->execute(['id' => $delete_id]);
            $success = "Class deleted successfully!";
        } catch (PDOException $e) {
            $error = "Cannot delete class. It may have students assigned to it.";
        }
    }
}

// Handle Add Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    if (!is_admin()) {
        $error = "You don't have permission to create classes.";
    } else {
        $class_name = sanitize($_POST['class_name']);
        $section = sanitize($_POST['section']);

        if (empty($class_name)) {
            $error = "Class name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO classes (class_name, section) VALUES (:name, :section)");
                $stmt->execute(['name' => $class_name, 'section' => $section]);
                $success = "Class created successfully!";
            } catch (PDOException $e) {
                $error = "Error creating class: " . $e->getMessage();
            }
        }
    }
}

// Fetch all classes with student counts
$query = "SELECT c.*, (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count 
          FROM classes c 
          ORDER BY c.class_name ASC, c.section ASC";
$classes = $pdo->query($query)->fetchAll();

$page_title = 'Classes';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center justify-between sticky top-0 z-20 transition-all duration-300">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Class Architecture</h2>
        <?php if (is_admin()): ?>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center shadow-xl shadow-blue-200 dark:shadow-none transform active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i> New Designation
            </button>
        <?php endif; ?>
    </header>

    <div class="p-8">
        <?php if ($success): ?>
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl flex items-center mb-8 shadow-sm">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <span class="font-semibold">
                    <?php echo $success; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div
                class="bg-rose-50 border border-rose-200 text-rose-600 px-6 py-4 rounded-2xl flex items-center mb-8 shadow-sm">
                <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                <span class="font-semibold">
                    <?php echo $error; ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 content-start">
            <?php if (count($classes) > 0): ?>
                <?php foreach ($classes as $class): ?>
                    <div
                        class="bg-white dark:bg-dark-card p-8 rounded-[2.5rem] border border-gray-100 dark:border-dark-border shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group relative overflow-hidden">
                        <div
                            class="absolute top-0 left-0 w-2 h-full bg-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div class="flex justify-between items-start mb-6">
                            <div
                                class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-[1.5rem] flex items-center justify-center text-2xl font-black shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <?php if (is_admin()): ?>
                                <div class="flex items-center space-x-1">
                                    <a href="class-edit.php?id=<?php echo $class->id; ?>"
                                        class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $class->id; ?>)"
                                        class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-rose-600 transition-colors">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight mb-2">
                            <?php echo $class->class_name; ?>
                        </h3>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-8">
                            Section:
                            <span class="text-slate-700 dark:text-slate-200"><?php echo $class->section ?: 'Global'; ?></span>
                        </p>

                        <div class="flex items-center justify-between pt-6 border-t border-gray-50 dark:border-dark-border">
                            <div
                                class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                <i class="fas fa-users-viewfinder mr-2 text-blue-500 text-sm"></i>
                                <?php echo $class->student_count; ?> Enrolled
                            </div>
                            <a href="students.php?class_id=<?php echo $class->id; ?>"
                                class="bg-gray-50 dark:bg-slate-900 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-blue-600 hover:bg-blue-600 hover:text-white transition-all">
                                Manifest
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                    <div
                        class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                    <p class="text-slate-500 italic">No classes have been defined yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Add Class Modal -->
<div id="addModal"
    class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0">
            <h3 class="text-lg font-bold text-slate-800">Create New Class</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')"
                class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="classes.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="add_class" value="1">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Class Name *</label>
                <input type="text" name="class_name" required placeholder="Ex: Level 1 Basic"
                    class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Section / Room</label>
                <input type="text" name="section" placeholder="Ex: Room 102 or Section A"
                    class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 px-4 py-3 rounded-xl font-bold text-slate-500 hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this class? This will only work if no students are assigned.')) {
            window.location.href = 'classes.php?delete=' + id;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>