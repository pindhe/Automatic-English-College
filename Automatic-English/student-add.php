<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address']);
    $class_id = (int) $_POST['class_id'];
    $enrollment_date = $_POST['enrollment_date'];
    $status = $_POST['status'];
    $student_id_code = generate_id('STU');

    // Handle Photo Upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = 'public/uploads/students/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = $student_id_code . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO students (student_id_code, full_name, gender, dob, phone, email, address, class_id, enrollment_date, photo, status) 
                               VALUES (:id, :name, :gender, :dob, :phone, :email, :address, :class_id, :enrollment, :photo, :status)");
        $stmt->execute([
            'id' => $student_id_code,
            'name' => $full_name,
            'gender' => $gender,
            'dob' => $dob,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'class_id' => $class_id,
            'enrollment' => $enrollment_date,
            'photo' => $photo,
            'status' => $status
        ]);
        $success = "Student enrolled successfully! ID: " . $student_id_code;
        redirect('students.php?success=enrolled');
    } catch (PDOException $e) {
        $error = "Error adding student: " . $e->getMessage();
    }
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$page_title = 'Add Student';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <div class="min-h-full flex items-center justify-center p-8">
        <div
            class="bg-white dark:bg-dark-card rounded-[2rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-dark-border transition-colors">
            <!-- Header -->
            <div
                class="px-10 py-8 border-b border-gray-100 dark:border-dark-border flex items-center justify-between sticky top-0 bg-white dark:bg-dark-card z-20 transition-all">
                <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Accession Enrollment</h3>
                <a href="students.php"
                    class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 bg-gray-50 dark:bg-slate-900 rounded-xl transition-all">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <!-- Messages -->
            <div class="px-8 pt-6">
                <?php if ($success): ?>
                    <div
                        class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl flex items-center mb-4 shadow-sm animate-fade-in text-sm">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span class="font-semibold"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl flex items-center mb-4 shadow-sm text-sm">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-semibold"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form -->
            <form action="student-add.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label
                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-2">Legal
                            Full Identity *</label>
                        <input type="text" name="full_name" required placeholder="Ex: John Harrison Doe"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-slate-300 font-black text-sm dark:text-white dark:placeholder-slate-700">
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-2">Gender
                            *</label>
                        <select name="gender" required
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white font-black text-sm">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-2">Date
                            of Birth *</label>
                        <input type="date" name="dob" required
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white font-black text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone
                            Number</label>
                        <input type="text" name="phone" placeholder="+1..."
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                            Address</label>
                        <input type="email" name="email" placeholder="john@example.com"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Assign
                            Class</label>
                        <select name="class_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo $c->class_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Profile
                            Photo</label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 dark:file:bg-blue-900/40 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/60 transition-all cursor-pointer">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Home
                            Address</label>
                        <textarea name="address" rows="2" placeholder="Street, City, Country"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Enrollment
                            Date</label>
                        <input type="date" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-6 pt-10">
                    <a href="students.php"
                        class="flex-1 text-center py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900 transition-all">Abeyance</a>
                    <button type="submit"
                        class="flex-[2] bg-blue-600 text-white py-5 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl shadow-blue-200 dark:shadow-none hover:bg-blue-700 transition-all transform active:scale-95">
                        Finalize Accession
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
</style>

<?php include 'includes/footer.php'; ?>