<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin();

$settings = get_settings($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_name = sanitize($_POST['college_name']);
    $academic_year = sanitize($_POST['academic_year']);
    $email = sanitize($_POST['contact_email']);
    $phone = sanitize($_POST['contact_phone']);
    $address = sanitize($_POST['address']);

    // Logo Upload logic here... (omitted for brevity but pattern is same as student photo)

    try {
        $stmt = $pdo->prepare("UPDATE settings SET college_name = ?, academic_year = ?, contact_email = ?, contact_phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$college_name, $academic_year, $email, $phone, $address, $settings->id]);
        $success = "Settings updated successfully!";
        $settings = get_settings($pdo); // Refresh
    } catch (PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

$page_title = 'System Settings';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-grow overflow-y-auto bg-gray-50 dark:bg-dark-bg transition-all duration-300">
    <header
        class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-gray-100 dark:border-dark-border py-6 px-10 flex items-center sticky top-0 z-20 transition-all duration-300">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">System Configuration</h2>
    </header>

    <div class="p-8 max-w-4xl">
        <?php if ($success): ?>
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl flex items-center mb-8 shadow-sm">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <span class="font-semibold">
                    <?php echo $success; ?>
                </span>
            </div>
        <?php endif; ?>

        <form action="settings.php" method="POST" class="space-y-8">
            <div
                class="bg-white dark:bg-dark-card p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none border border-gray-100 dark:border-dark-border transition-all duration-300">
                <h3 class="text-xl font-black mb-10 text-slate-800 dark:text-white tracking-tight">Branding & Identity
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">College
                            Name</label>
                        <input type="text" name="college_name" value="<?php echo $settings->college_name; ?>"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Current
                            Academic Year</label>
                        <input type="text" name="academic_year" value="<?php echo $settings->academic_year; ?>"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">College
                            Logo</label>
                        <input type="file"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 transition-all">
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-dark-card p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none border border-gray-100 dark:border-dark-border transition-all duration-300">
                <h3 class="text-xl font-black mb-10 text-slate-800 dark:text-white tracking-tight">Global Contact
                    Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Official
                            Email</label>
                        <input type="email" name="contact_email" value="<?php echo $settings->contact_email; ?>"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Official
                            Phone</label>
                        <input type="text" name="contact_phone" value="<?php echo $settings->contact_phone; ?>"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Office
                            Address</label>
                        <textarea name="address" rows="3"
                            class="w-full px-5 py-4 bg-gray-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 dark:text-white transition-all"><?php echo $settings->address; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="bg-blue-600 text-white px-12 py-4 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] shadow-xl shadow-blue-200 dark:shadow-none hover:bg-blue-700 transform active:scale-95 transition-all">
                    Apply Global Settings
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>