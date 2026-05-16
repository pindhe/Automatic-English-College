<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$error = '';
$success = '';
$student = null;

if (!isset($_GET['id'])) {
    redirect('students.php');
}

$id = (int) $_GET['id'];

// Fetch Student Data
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    if (!$student) {
        redirect('students.php');
    }
} catch (PDOException $e) {
    redirect('students.php');
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll();

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

    // Handle Photo Upload (Optional update)
    $photo = $student->photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = 'public/uploads/students/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_photo = $student->student_id_code . '_' . time() . '.' . $ext;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_photo)) {
            // Delete old photo if exists
            if ($photo && file_exists($upload_dir . $photo)) {
                unlink($upload_dir . $photo);
            }
            $photo = $new_photo;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET 
                               full_name = :name, gender = :gender, dob = :dob, phone = :phone, 
                               email = :email, address = :address, class_id = :class_id, 
                               enrollment_date = :enrollment, photo = :photo, status = :status 
                               WHERE id = :id");
        $stmt->execute([
            'name' => $full_name,
            'gender' => $gender,
            'dob' => $dob,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'class_id' => $class_id,
            'enrollment' => $enrollment_date,
            'photo' => $photo,
            'status' => $status,
            'id' => $id
        ]);
        $success = "Student records updated successfully!";

        // Re-fetch updated data
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error updating student: " . $e->getMessage();
    }
}

$page_title = 'Edit Student';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-slate-50/50 dark:bg-dark-bg transition-colors duration-300">
    <div class="min-h-full flex items-center justify-center p-8">
        <div
            class="bg-white dark:bg-dark-card rounded-[2rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-dark-border transition-colors">
            <!-- Header -->
            <div
                class="px-8 py-6 border-b border-gray-50 dark:border-dark-border flex items-center justify-between sticky top-0 bg-white dark:bg-dark-card z-10 transition-colors">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">Edit Student Info</h3>
                <a href="students.php" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
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
            <form action="student-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data"
                class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Full Name
                            *</label>
                        <input type="text" name="full_name" required
                            value="<?php echo htmlspecialchars($student->full_name); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gender *</label>
                        <select name="gender" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="Male" <?php echo $student->gender == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $student->gender == 'Female' ? 'selected' : ''; ?>>Female
                            </option>
                            <option value="Other" <?php echo $student->gender == 'Other' ? 'selected' : ''; ?>>Other
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Date of Birth
                            *</label>
                        <input type="date" name="dob" required value="<?php echo htmlspecialchars($student->dob); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone
                            Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($student->phone); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                            Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($student->email); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400 dark:text-white dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Class</label>
                        <select name="class_id"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c->id; ?>" <?php echo $student->class_id == $c->id ? 'selected' : ''; ?>><?php echo $c->class_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Update
                            Photo</label>
                        <div class="flex items-center space-x-4">
                            <?php if ($student->photo): ?>
                                <img src="public/uploads/students/<?php echo $student->photo; ?>"
                                    class="w-12 h-12 rounded-xl object-cover border dark:border-dark-border transition-colors">
                            <?php endif; ?>
                            <input type="file" name="photo" accept="image/*"
                                class="flex-grow text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 dark:file:bg-blue-900/40 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/60 transition-all cursor-pointer">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Home
                            Address</label>
                        <textarea name="address" rows="2"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white"><?php echo htmlspecialchars($student->address); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all appearance-none cursor-pointer dark:text-white">
                            <option value="active" <?php echo $student->status == 'active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="inactive" <?php echo $student->status == 'inactive' ? 'selected' : ''; ?>>
                                Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Enrollment
                            Date</label>
                        <input type="date" name="enrollment_date"
                            value="<?php echo htmlspecialchars($student->enrollment_date); ?>"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4 pt-6">
                    <a href="students.php"
                        class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-all">Cancel</a>
                    <button type="submit"
                        class="flex-[2] bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-[0.98]">
                        Save Changes
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