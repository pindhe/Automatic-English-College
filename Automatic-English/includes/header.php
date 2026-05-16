<?php
$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $page_title ?? 'Dashboard'; ?> -
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
                            'orange-light': '#f4a261',
                            'orange-dark': '#d4650f',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Apply theme immediately to prevent flicker
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        main {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .submenu-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .submenu-container.open {
            max-height: 500px;
            /* Large enough for any submenu */
        }

        /* ── Brand Button & Icon System ─────────────────────── */
        :root {
            --brand-teal:   #0d7377;
            --brand-orange: #e07c24;
        }

        /* Reusable primary button */
        .btn-primary {
            background-color: var(--brand-teal) !important;
            color: #ffffff !important;
            transition: background-color 0.2s, box-shadow 0.15s;
        }
        .btn-primary:hover {
            background-color: #0d5c63 !important;
        }
        .btn-primary:active,
        .btn-primary:focus-visible {
            box-shadow: 0 0 0 4px rgba(224,124,36,0.45),
                        0 4px 20px rgba(224,124,36,0.25) !important;
        }

        /* Orange glow on ANY button click */
        button:active {
            box-shadow: 0 0 0 3px rgba(224,124,36,0.35),
                        0 2px 12px rgba(224,124,36,0.2) !important;
        }

        /* Brand icon helper */
        .icon-brand { color: var(--brand-teal); }

        /* ── ANIMATION DISABLE OVERRIDE ─────────────────────── */
        * {
            animation-duration: 0s !important;
            animation-delay: 0s !important;
            transition-duration: 0s !important;
            transition-delay: 0s !important;
            transform: none !important;
        }

        /* Disable specific animations */
        @keyframes fadeInUp {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes * {
            animation-duration: 0s !important;
        }

        /* Disable transitions */
        * {
            transition: none !important;
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
        }

        /* Disable hover effects */
        *:hover {
            transform: none !important;
            transition: none !important;
        }

        /* Disable focus transitions */
        *:focus {
            transform: none !important;
            transition: none !important;
        }

        /* Disable all Tailwind transition utilities */
        .transition-all,
        .transition-colors,
        .transition-opacity,
        .transition-shadow,
        .transition-transform,
        .transition,
        .duration-75,
        .duration-100,
        .duration-150,
        .duration-200,
        .duration-300,
        .duration-500,
        .duration-700,
        .duration-1000,
        .ease-in,
        .ease-out,
        .ease-in-out,
        .ease-linear {
            transition: none !important;
            animation: none !important;
        }

        /* Disable hover state transitions */
        .hover\:scale-95:hover,
        .hover\:scale-100:hover,
        .hover\:scale-105:hover,
        .hover\:scale-110:hover,
        .hover\:scale-125:hover,
        .hover\:scale-150:hover {
            transform: none !important;
        }

        /* Disable color transitions on hover */
        .hover\:bg-indigo-600:hover,
        .hover\:bg-indigo-700:hover,
        .hover\:bg-slate-100:hover,
        .hover\:bg-slate-200:hover,
        .hover\:bg-slate-800:hover,
        .hover\:bg-slate-700:hover,
        .hover\:bg-green-600:hover,
        .hover\:bg-green-700:hover,
        .hover\:bg-blue-600:hover,
        .hover\:bg-blue-700:hover,
        .hover\:bg-red-600:hover,
        .hover\:bg-red-700:hover,
        .hover\:bg-yellow-600:hover,
        .hover\:bg-yellow-700:hover,
        .hover\:text-slate-700:hover,
        .hover\:text-slate-300:hover,
        .hover\:text-slate-200:hover,
        .hover\:text-slate-400:hover {
            transition: none !important;
        }

        /* Disable submenu transitions */
        .submenu-container {
            transition: none !important;
            max-height: none !important;
            overflow: visible !important;
        }

        /* Disable main content animation */
        main {
            animation: none !important;
        }

        /* Restore sidebar transitions only */
        #sidebar {
            transition: transform 0.3s !important;
        }
        
        #sidebarOverlay {
            transition: opacity 0.3s !important;
        }
        
        .sidebar-content {
            transition: padding 0.3s !important;
        }
        
        .sidebar-text {
            transition: opacity 0.3s, visibility 0.3s, width 0.3s !important;
        }
        
        .sidebar-indicator {
            transition: opacity 0.3s !important;
        }

        /* Disable all animations globally except sidebar */
        *,
        *::before,
        *::after {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }

        /* Override for sidebar elements */
        #sidebar,
        #sidebarOverlay,
        .sidebar-content,
        .sidebar-text,
        .sidebar-indicator {
            transition: transform 0.3s, opacity 0.3s, padding 0.3s, width 0.3s !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggleBtn && sidebar && overlay) {
                const toggleSidebar = () => {
                    const isHidden = sidebar.classList.contains('-translate-x-full');
                    if (isHidden) {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.remove('hidden');
                        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                    } else {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('opacity-0');
                        setTimeout(() => overlay.classList.add('hidden'), 300);
                    }
                };

                toggleBtn.addEventListener('click', toggleSidebar);
                overlay.addEventListener('click', toggleSidebar);
            }

            // Sidebar Collapse Logic
            const collapseBtn = document.getElementById('collapseSidebar');
            const collapseIcon = document.getElementById('collapseIcon');
            const sidebarContent = document.querySelector('.sidebar-content');

            const updateSidebarState = (isCollapsed) => {
                if (isCollapsed) {
                    sidebar.classList.replace('w-72', 'w-20');
                    if (collapseIcon) collapseIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                    sidebar.querySelectorAll('.sidebar-text').forEach(el => {
                        el.classList.add('opacity-0', 'invisible', 'w-0', 'pointer-events-none');
                        el.classList.remove('opacity-100');
                    });
                    sidebar.querySelectorAll('.sidebar-indicator').forEach(el => el.classList.add('opacity-0'));
                    if (sidebarContent) sidebarContent.classList.replace('p-8', 'p-4');
                    // Close all submenus when collapsing
                    document.querySelectorAll('.submenu-container').forEach(sm => sm.classList.remove('open'));
                } else {
                    sidebar.classList.replace('w-20', 'w-72');
                    if (collapseIcon) collapseIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                    sidebar.querySelectorAll('.sidebar-text').forEach(el => {
                        el.classList.remove('opacity-0', 'invisible', 'w-0', 'pointer-events-none');
                        el.classList.add('opacity-100');
                    });
                    sidebar.querySelectorAll('.sidebar-indicator').forEach(el => el.classList.remove('opacity-0'));
                    if (sidebarContent) sidebarContent.classList.replace('p-4', 'p-8');
                    // Restore open submenus if possible
                }
            };

            // Initialize Sidebar State
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed && window.innerWidth >= 1024) {
                updateSidebarState(true);
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', () => {
                    const currentlyCollapsed = sidebar.classList.contains('w-20');
                    const newState = !currentlyCollapsed;
                    updateSidebarState(newState);
                    localStorage.setItem('sidebarCollapsed', newState);
                });
            }

            // Sub-menu Toggle Logic
            const submenuTriggers = document.querySelectorAll('.submenu-trigger');
            submenuTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    if (sidebar.classList.contains('w-20')) return; // Don't expand in collapsed mode

                    e.preventDefault();
                    const container = trigger.nextElementSibling;
                    const icon = trigger.querySelector('.fa-chevron-down');

                    const isOpen = container.classList.toggle('open');
                    if (icon) {
                        icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                });
            });

            // Theme Toggle Logic
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');

            const updateThemeUI = (theme) => {
                if (theme === 'dark') {
                    themeIcon?.classList.replace('fa-sun', 'fa-moon');
                    if (themeText) themeText.textContent = 'Dark Mode';
                } else {
                    themeIcon?.classList.replace('fa-moon', 'fa-sun');
                    if (themeText) themeText.textContent = 'Light Mode';
                }
            };

            // Initialize UI
            updateThemeUI(document.documentElement.classList.contains('dark') ? 'dark' : 'light');

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const isDark = document.documentElement.classList.toggle('dark');
                    const theme = isDark ? 'dark' : 'light';
                    localStorage.setItem('theme', theme);
                    updateThemeUI(theme);
                });
            }
        });
    </script>
</head>

<body class="bg-gray-50 text-slate-800 dark:bg-dark-bg dark:text-slate-100 transition-colors duration-300">
    <div class="flex h-screen overflow-hidden">