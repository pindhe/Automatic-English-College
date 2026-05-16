<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['full_name'] = $user->full_name;
            $_SESSION['role'] = $user->role;

            redirect('index.php');
        } else {
            $error = 'Invalid username or password.';
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
    <title>Login -
        <?php echo $settings->college_name; ?>
    </title>
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

        body {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --brand-teal: #0d7377;
            --brand-orange: #e07c24;
        }

        .btn-login {
            background-color: var(--brand-teal);
            color: #fff;
            transition: background-color 0.2s, box-shadow 0.15s, transform 0.1s;
        }

        .btn-login:hover {
            background-color: #0d5c63;
        }

        .btn-login:active {
            transform: scale(0.97);
            box-shadow: 0 0 0 4px rgba(224, 124, 36, 0.45), 0 4px 24px rgba(224, 124, 36, 0.3);
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-brand-teal/5 to-brand-teal/10 dark:from-dark-bg dark:to-slate-900 flex items-center justify-center min-h-screen transition-all duration-500">

    <div
        class="max-w-md w-full p-1 border-t-4 border-brand-teal rounded-[2.8rem] shadow-2xl shadow-brand-teal/20 dark:shadow-none transition-all">
        <div class="bg-white dark:bg-dark-card rounded-[2.5rem] p-10 border border-white dark:border-dark-border">
            <div class="text-center mb-8">
                <?php if ($settings->college_logo): ?>
                    <img src="public/uploads/<?php echo $settings->college_logo; ?>" alt="Logo"
                        class="mx-auto h-24 w-auto mb-6 drop-shadow-2xl transform hover:scale-105 transition-transform duration-500">
                <?php else: ?>
                    <div
                        class="mx-auto h-20 w-20 bg-gradient-to-br from-brand-teal to-brand-teal-light rounded-[2rem] flex items-center justify-center mb-6 shadow-xl shadow-brand-teal/30 dark:shadow-none">
                        <i class="fas fa-graduation-cap text-white text-4xl"></i>
                    </div>
                <?php endif; ?>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight uppercase">
                    Automatic <span style="color:var(--brand-teal)">English</span>
                </h1>
                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em] mt-3">
                    Executive Access Terminal</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-lg flex items-center mb-6">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span>
                        <?php echo $error; ?>
                    </span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label for="username"
                        class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-2">Secure
                        Identifier</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i
                                class="fas fa-user-shield text-slate-300 group-focus-within:text-brand-teal transition-colors"></i>
                        </div>
                        <input type="text" id="username" name="username" required
                            class="block w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border-2 border-transparent focus:border-brand-teal/30 rounded-2xl focus:ring-0 focus:outline-none transition-all dark:text-white font-black text-sm tracking-widest placeholder-slate-300 dark:placeholder-slate-700"
                            placeholder="USERNAME">
                    </div>
                </div>

                <div>
                    <label for="password"
                        class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-2">Access
                        Code</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i
                                class="fas fa-key text-slate-300 group-focus-within:text-brand-teal transition-colors"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="block w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-slate-900 border-2 border-transparent focus:border-brand-teal/30 rounded-2xl focus:ring-0 focus:outline-none transition-all dark:text-white font-black text-sm tracking-widest placeholder-slate-300 dark:placeholder-slate-700"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between py-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-teal">
                        </div>
                        <span class="ml-3 text-[11px] font-black text-slate-500 uppercase tracking-widest">Keep
                            Unlocked</span>
                    </label>
                </div>

                <button type="submit"
                    class="btn-login w-full font-black uppercase tracking-[0.3em] py-5 px-4 rounded-2xl shadow-2xl shadow-brand-teal/20 dark:shadow-none mb-6 text-[11px]">
                    Initialize Session
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-slate-500">
                &copy;
                <?php echo date('Y'); ?> Automatic English College. All rights reserved.
            </div>
        </div>

</body>

</html>