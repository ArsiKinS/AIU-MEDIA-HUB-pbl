<?php
require('../login/connect.php');

// Fetch all announcements
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $dbconnection->query($sql);
$total_announcements = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Announcements | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-gradient-to-b from-indigo-700 to-blue-900 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-indigo-500/50 text-center">
                Admin<span class="text-indigo-300">Pro</span>
            </div>

            <nav class="flex-grow p-4 space-y-2 mt-4 hidden md:block" x-data="{ generalOpen: false, contentOpen: true }">
                <!-- General Section -->
                <div class="space-y-1">
                    <button @click="generalOpen = !generalOpen" 
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-indigo-600 transition focus:outline-none"
                            :class="generalOpen ? 'bg-indigo-800 shadow-md text-white' : 'text-indigo-100'">
                        <div class="flex items-center">
                            <span class="mr-3">📊</span> General
                        </div>
                        <span class="text-[10px] transition-transform duration-200" :class="generalOpen ? 'rotate-180' : ''">▼</span>
                    </button>

                    <div x-show="generalOpen" x-transition class="pl-10 space-y-1">
                        <a href="user_info_view.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                             • User Activity
                        </a>
                        <a href="add_user.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                            • Add New User
                        </a>
                    </div>
                </div>

                <!-- Content Section (ACTIVE) -->
                <div class="space-y-1">
                    <button class="w-full flex items-center justify-between p-3 rounded-lg bg-indigo-800 shadow-md text-white transition focus:outline-none">
                        <div class="flex items-center">
                            <span class="mr-3">📰</span> Content
                        </div>
                        <span class="text-[10px] rotate-180">▼</span>
                    </button>

                    <div class="pl-10 space-y-1">
                        <a href="create_announcement.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                            • Create Announcement
                        </a>
                        <a href="view_announcements.php" class="block p-2 text-sm text-white bg-indigo-900 rounded transition font-bold">
                            • View All Announcements
                        </a>
                        <a href="create_news.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                            • Create News
                        </a>
                        <a href="view_news.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                            • View All News
                        </a>
                    </div>
                </div>

                <a href="content_history.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100">
                    <span class="mr-3">🕒</span> Content History
                </a>

                <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100">
                    <span class="mr-3">⚙️</span> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8">
            <header class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">All Announcements</h2>
                    <p class="text-gray-500 text-sm mt-1">Manage and monitor all published announcements</p>
                </div>
                <div class="flex items-center space-x-4">
                    <input type="text" id="searchBox" placeholder="Search announcements..." onkeyup="searchTable()"
                        class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <a href="create_announcement.php" 
                       class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg whitespace-nowrap">
                        + New Announcement
                    </a>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-blue-100 text-sm font-semibold uppercase">Total Announcements</p>
                    <h3 class="text-3xl font-bold"><?php echo $total_announcements; ?></h3>
                </div>

                <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-emerald-100 text-sm font-semibold uppercase">Published Today</p>
                    <h3 class="text-3xl font-bold">
                        <?php 
                        $today_sql = "SELECT COUNT(*) as count FROM announcements WHERE DATE(created_at) = CURDATE()";
                        $today_result = $dbconnection->query($today_sql);
                        echo $today_result->fetch_assoc()['count'];
                        ?>
                    </h3>
                </div>

                <div class="bg-gradient-to-br from-amber-400 to-orange-500 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-orange-100 text-sm font-semibold uppercase">Urgent</p>
                    <h3 class="text-3xl font-bold">
                        <?php 
                        $urgent_sql = "SELECT COUNT(*) as count FROM announcements WHERE priority = 'Urgent'";
                        $urgent_result = $dbconnection->query($urgent_sql);
                        echo $urgent_result->fetch_assoc()['count'];
                        ?>
                    </h3>
                </div>

                <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-purple-100 text-sm font-semibold uppercase">Categories</p>
                    <h3 class="text-3xl font-bold">5</h3>
                </div>
            </div>

            <!-- Announcements Table -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 uppercase tracking-wider">ANNOUNCEMENTS LIST</h3>
                    <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        <option value="Academic">Academic</option>
                        <option value="Administrative">Administrative</option>
                        <option value="Events">Events</option>
                        <option value="Emergency">Emergency</option>
                        <option value="General">General</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="announcementTable">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                                <th class="p-4 font-medium">Title</th>
                                <th class="p-4 font-medium">Category</th>
                                <th class="p-4 font-medium">Priority</th>
                                <th class="p-4 font-medium">Author</th>
                                <th class="p-4 font-medium">Posted Date</th>
                                <th class="p-4 font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4">
                                            <div class="font-semibold text-gray-900">
                                                <?php echo htmlspecialchars(substr($row['title'], 0, 50)); ?>
                                                <?php echo strlen($row['title']) > 50 ? '...' : ''; ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php echo htmlspecialchars(substr($row['content'], 0, 60)) . '...'; ?>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <?php
                                            $category = $row['category'];
                                            $colors = [
                                                'Academic' => 'bg-blue-100 text-blue-700',
                                                'Administrative' => 'bg-purple-100 text-purple-700',
                                                'Events' => 'bg-pink-100 text-pink-700',
                                                'Emergency' => 'bg-red-100 text-red-700',
                                                'General' => 'bg-gray-100 text-gray-700'
                                            ];
                                            $color_class = $colors[$category] ?? 'bg-gray-100 text-gray-700';
                                            ?>
                                            <span class="px-3 py-1 <?php echo $color_class; ?> rounded-full text-xs font-bold uppercase">
                                                <?php echo htmlspecialchars($category); ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <?php
                                            $priority = $row['priority'];
                                            if ($priority == 'Urgent') {
                                                echo '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">🔴 URGENT</span>';
                                            } elseif ($priority == 'High') {
                                                echo '<span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">⚠️ HIGH</span>';
                                            } else {
                                                echo '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Normal</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <?php echo htmlspecialchars($row['author']); ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                            <div class="text-xs text-gray-400">
                                                <?php echo date('h:i A', strtotime($row['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <button onclick="viewAnnouncement(<?php echo $row['id']; ?>)"
                                                    class="px-3 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition">
                                                    View
                                                </button>
                                                <a href="edit_announcement.php?id=<?php echo $row['id']; ?>"
                                                    class="px-3 py-1.5 bg-amber-100 text-amber-600 rounded-lg text-xs font-bold hover:bg-amber-600 hover:text-white transition">
                                                    Edit
                                                </a>
                                                <button onclick="showDeleteModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')"
                                                    class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-gray-400">
                                        <div class="text-6xl mb-4">📢</div>
                                        <p class="text-lg font-semibold">No announcements yet.</p>
                                        <a href="create_announcement.php" class="text-indigo-600 hover:underline mt-2 inline-block">
                                            Create your first announcement →
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-gray-100">
            <div class="text-center text-gray-900">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6 text-4xl">⚠️</div>
                <h3 class="text-2xl font-bold">Delete Announcement?</h3>
                <p class="text-sm text-gray-500 mt-3">
                    Are you sure you want to delete "<span id="modalAnnouncementTitle" class="font-bold text-gray-800"></span>"? 
                    This action cannot be undone.
                </p>
            </div>
            <div class="mt-8 flex flex-col space-y-3">
                <a id="confirmDeleteLink" href="#"
                    class="w-full py-3 bg-red-600 text-white text-center rounded-xl font-bold hover:bg-red-700 transition">
                    Yes, Delete
                </a>
                <button onclick="closeDeleteModal()"
                    class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-bold">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal(id, title) {
            document.getElementById('modalAnnouncementTitle').innerText = title;
            document.getElementById('confirmDeleteLink').href = 'delete_announcement.php?id=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function viewAnnouncement(id) {
            window.location.href = 'view_announcement_detail.php?id=' + id;
        }

        function searchTable() {
            let input = document.getElementById('searchBox');
            let filter = input.value.toUpperCase();
            let table = document.getElementById('announcementTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        let txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? "" : "none";
            }
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('deleteModal')) closeDeleteModal();
        }
    </script>

</body>
</html>