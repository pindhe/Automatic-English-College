<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white/80 dark:bg-dark-card/80 backdrop-blur-xl border-r border-gray-100 dark:border-dark-border transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-300 ease-in-out">
    <div class="h-full flex flex-col p-8 sidebar-content transition-all duration-300">
        <!-- Brand Section & Collapse Toggle -->
        <div class="flex items-center justify-between mb-12 px-2">
            <div class="flex items-center space-x-4 overflow-hidden">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-gradient-to-tr from-brand-teal to-brand-teal-light rounded-2xl flex items-center justify-center shadow-xl shadow-brand-teal/30 dark:shadow-none rotate-3">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
                <div class="sidebar-text transition-all duration-300 opacity-100">
                    <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">AEC</h1>
                    <p
                        class="text-[10px] font-black text-brand-teal dark:text-brand-teal-light uppercase tracking-widest mt-1">
                        Management</p>
                </div>
            </div>
            <!-- Desktop Collapse Toggle -->
            <button id="collapseSidebar"
                class="hidden lg:flex w-8 h-8 items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-brand-teal transition-all duration-300 transform group">
                <i class="fas fa-chevron-left text-xs group-hover:-translate-x-0.5 transition-transform"
                    id="collapseIcon"></i>
            </button>
        </div>

        <!-- Navigation Section -->
        <div class="flex-grow overflow-y-auto -mx-2 px-2 custom-scrollbar overflow-x-hidden">
            <p
                class="sidebar-text text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-6 px-4 transition-all duration-300 opacity-100 italic">
                Executive Menu</p>

            <nav class="space-y-1.5">
                <?php
                $nav_items = [
                    ['name' => 'Dashboard', 'icon' => 'fas fa-th-large', 'url' => 'index.php'],
                ];

                foreach ($nav_items as $item):
                    $is_active = ($current_page == $item['url']);
                    $active_classes = $is_active
                        ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-teal-light shadow-sm'
                        : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-brand-teal dark:hover:text-brand-teal-light transition-all duration-300';
                    ?>
                    <a href="<?php echo $item['url']; ?>"
                        class="group flex items-center justify-between px-4 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest <?php echo $active_classes; ?>"
                        title="<?php echo $item['name']; ?>">
                        <div class="flex items-center space-x-4">
                            <i
                                class="<?php echo $item['icon']; ?> text-lg flex-shrink-0 transition-transform group-hover:scale-110"></i>
                            <span
                                class="sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap"><?php echo $item['name']; ?></span>
                        </div>
                        <?php if ($is_active): ?>
                            <div
                                class="sidebar-indicator w-1.5 h-1.5 rounded-full bg-brand-teal dark:bg-brand-teal-light transition-all duration-300 opacity-100">
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>

                <!-- Students Nested Menu -->
                <?php
                $student_pages = ['students.php', 'student-add.php', 'student-view.php', 'student-edit.php'];
                $is_student_active = in_array($current_page, $student_pages);
                ?>
                <div class="space-y-1">
                    <button
                        class="submenu-trigger w-full group flex items-center justify-between px-4 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-brand-teal dark:hover:text-brand-teal-light transition-all duration-300">
                        <div class="flex items-center space-x-4">
                            <i
                                class="fas fa-user-graduate text-lg flex-shrink-0 transition-transform group-hover:scale-110"></i>
                            <span
                                class="sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap">Students</span>
                        </div>
                        <i
                            class="sidebar-text fas fa-chevron-down text-[10px] transition-transform duration-300 <?php echo $is_student_active ? 'rotate-180' : ''; ?>"></i>
                    </button>
                    <div class="submenu-container px-4 space-y-1 <?php echo $is_student_active ? 'open' : ''; ?>">
                        <a href="students.php"
                            class="flex items-center space-x-3 px-8 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest <?php echo $current_page == 'students.php' ? 'text-brand-teal bg-brand-teal/10' : 'text-slate-400 hover:text-brand-teal'; ?> transition-all">
                            <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                            <span>Current Body</span>
                        </a>
                        <a href="graduation.php"
                            class="flex items-center space-x-3 px-8 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest <?php echo $current_page == 'graduation.php' ? 'text-amber-600 bg-amber-50/50' : 'text-slate-400 hover:text-amber-500'; ?> transition-all">
                            <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                            <span>Graduation</span>
                        </a>
                    </div>
                </div>

                <?php
                $other_nav_items = [
                    ['name' => 'Classes', 'icon' => 'fas fa-door-open', 'url' => 'classes.php'],
                    ['name' => 'Attendance', 'icon' => 'fas fa-clipboard-check', 'url' => 'attendance.php'],
                    ['name' => 'Exams', 'icon' => 'fas fa-file-invoice', 'url' => 'exams.php'],
                    ['name' => 'Financials', 'icon' => 'fas fa-hand-holding-usd', 'url' => 'fees.php'],
                ];

                // Admin-only nav items
                if (is_admin()) {
                    $other_nav_items[] = ['name' => 'Staff Policy', 'icon' => 'fas fa-users-cog', 'url' => 'users.php'];
                    $other_nav_items[] = ['name' => 'Architecture', 'icon' => 'fas fa-cog', 'url' => 'settings.php'];
                }

                foreach ($other_nav_items as $item):
                    $is_active = ($current_page == $item['url']);
                    $active_classes = $is_active
                        ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-teal-light shadow-sm'
                        : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-brand-teal dark:hover:text-brand-teal-light transition-all duration-300';
                    ?>
                    <a href="<?php echo $item['url']; ?>"
                        class="group flex items-center justify-between px-4 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest <?php echo $active_classes; ?>"
                        title="<?php echo $item['name']; ?>">
                        <div class="flex items-center space-x-4">
                            <i
                                class="<?php echo $item['icon']; ?> text-lg flex-shrink-0 transition-transform group-hover:scale-110"></i>
                            <span
                                class="sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap"><?php echo $item['name']; ?></span>
                        </div>
                        <?php if ($is_active): ?>
                            <div
                                class="sidebar-indicator w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 transition-all duration-300 opacity-100">
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Theme Toggle Section -->
        <div class="mt-auto pt-6 border-t border-gray-100 dark:border-dark-border">
            <button id="themeToggle"
                class="group flex items-center justify-between w-full px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-300"
                title="Toggle Theme">
                <div class="flex items-center space-x-4">
                    <i id="themeIcon"
                        class="fas fa-moon text-xl flex-shrink-0 group-hover:rotate-12 transition-transform"></i>
                    <span id="themeText"
                        class="sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap">Dark Mode</span>
                </div>
                <div
                    class="sidebar-text w-8 h-4 bg-slate-200 dark:bg-slate-700 rounded-full relative transition-colors opacity-100 duration-300">
                    <div id="themeIndicator"
                        class="absolute top-1 left-1 w-2 h-2 bg-white rounded-full transition-all duration-300 transform dark:translate-x-4">
                    </div>
                </div>
            </button>
        </div>

        <!-- Sign Out Section -->
        <div class="pt-4">
            <a href="logout.php"
                class="group flex items-center space-x-4 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all duration-300"
                title="Sign Out">
                <i
                    class="fas fa-sign-out-alt text-xl flex-shrink-0 group-hover:-translate-x-1 transition-transform"></i>
                <span class="sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap">Sign Out
                    Account</span>
            </a>
        </div>
    </div>
</aside>

<!-- Overlay for mobile -->
<div id="sidebarOverlay"
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 opacity-0">
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.1);
        border-radius: 10px;
    }

    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.3);
    }
</style>