<?php
require('../login/connect.php');

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch Login History
$login_sql = "SELECT l.*, u.username, u.email, u.location 
              FROM login l 
              JOIN media_hub_user u ON l.user_id = u.id 
              ORDER BY l.login_time DESC LIMIT 10";
$login_result = $dbconnection->query($login_sql);

// Fetch Reading History with Content Title
$history_sql = "SELECT h.*, u.username, u.email, c.title as content_title 
                FROM reading_history h 
                JOIN media_hub_user u ON h.user_id = u.id 
                LEFT JOIN content c ON h.content_id = c.content_id
                ORDER BY h.action_time DESC LIMIT 10";
$history_result = $dbconnection->query($history_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Insights | AdminPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex flex-col md:flex-row min-h-screen ">
        <aside class="w-full   md:w-64 bg-[#0c9e95]  text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-indigo-500/50 text-center">
                AIU MEDIA <span class="text-indigo-300"> HUB</span>
            </div>
            
            <nav class="flex-grow p-4 space-y-2 mt-4 hidden md:block" 
                 x-data="{ generalOpen: <?= ($current_page == 'user_info_view.php' || $current_page == 'admin.php') ? 'true' : 'false' ?> }">
                
                <div class="space-y-1">
                    <a href="admin.php">
                    <button @click="generalOpen = !generalOpen" 
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-white hover:text-[#64b12b] transition focus:outline-none"
                            :class="generalOpen ?  shadow-md text-white' : 'text-indigo-100'">
                        <div class="flex items-center">
                            <span class="mr-3">📊</span> General
                        </div>
                        <span class="text-[10px] transition-transform duration-200" :class="generalOpen ? 'rotate-180' : ''">▼</span>
                    </button>
                    </a>

                    <div x-show="generalOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="pl-10 space-y-1">
                        
                        <a href="user_info_view.php " 
                           class="block p-2 text-sm transition <?= ($current_page == 'user_info_view.php') ? 'text-white font-bold' : 'text-indigo-200 hover:text-white' ?>">
                            • User Activity
                        </a>

                        <a href="add_user.php" 
                           class="block p-2 text-sm transition <?= ($current_page == 'add_user.php') ? 'text-white font-bold' : 'text-indigo-200 hover:text-white' ?>">
                            • Add New User
                        </a>
                        
                    </div>
                </div>
    <a href="create_content.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">🕒</span> Create Content
    </a>

    <a href="edit_content.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Content History
    </a>



        <a href="staff_activity.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Staff Activity
    </a>
            <a href="interaction_search.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Interaction Tracking
    </a>
            </nav>
        </aside>

        <main class="flex-1 p-4 md:p-8">
            <header class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 ">
                <h2 class="text-2xl font-bold text-slate-900">System Activity</h2>
                <div class="flex items-center space-x-4 w-full md:w-auto">
                    <input type="text" placeholder="Search logs..." class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-900">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg shrink-0">AD</div>
                </div>
            </header>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider">Recent Login Sessions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-indigo-200  text-slate-900 text-sm uppercase">
                                <th class="p-4 font-bold">User & Email</th>
                                <th class="p-4 font-bold">Location</th>
                                <th class="p-4 font-bold">IP Address</th>
                                <th class="p-4 font-bold">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($row = $login_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <div class="text-slate-900 font-bold"><?= htmlspecialchars($row['username']) ?></div>
                                    <div class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($row['email']) ?></div>
                                </td>
                                <td class="p-4 text-sm text-slate-900 font-medium"><?= htmlspecialchars($row['location'] ?? 'Unknown') ?></td>
                                <td class="p-4 text-xs font-mono text-indigo-700 font-bold"><?= $row['ip_address'] ?></td>
                                <td class="p-4 text-sm text-slate-900">
                                    <span class="font-bold"><?= date('H:i', strtotime($row['login_time'])) ?></span>
                                    <span class="block text-[10px] text-slate-400 font-normal"><?= date('M d, Y', strtotime($row['login_time'])) ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider">Reading Activity Stream</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-slate-900 text-sm uppercase">
                                <th class="p-4 font-bold">Reader</th>
                                <th class="p-4 font-bold">Content Title</th> 
                                <th class="p-4 font-bold">Action</th>
                                <th class="p-4 font-bold">Activity Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($h = $history_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($h['username']) ?></td>
                                <td class="p-4">
                                    <span class="text-sm font-bold text-indigo-800">
                                        <?= htmlspecialchars($h['content_title'] ?? 'Unknown Content') ?>
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-normal">ID: #<?= $h['content_id'] ?></span>
                                </td>
                                <td class="p-4">
                                    <?php if($h['action'] == 'COMPLETED'): ?>
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-black uppercase border border-emerald-200">Completed</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-[10px] font-black uppercase border border-blue-200">Viewed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm text-slate-900 font-medium">
                                    <?= date('M d, H:i', strtotime($h['action_time'])) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>