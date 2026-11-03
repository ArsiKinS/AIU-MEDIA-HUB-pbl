<?php
require('../login/connect.php');

// Fetch users from your XAMPP table
$sql = "SELECT * FROM media_hub_user";
$result = $dbconnection->query($sql);
$total_users = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Media Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex flex-col md:flex-row min-h-screen">
        <aside class="w-full md:w-64 bg-[#0c9e95] to-blue-900 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-indigo-500/50 text-center">
                AIU Media<span class="text-white"> Hub</span>
            </div>

<nav class="flex-grow p-4 space-y-2 mt-4 hidden md:block" x-data="{ generalOpen: false, activityOpen: false }">
    
    <div class="space-y-1">
        <button @click="generalOpen = !generalOpen" 
                class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-white hover:text-[#64b12b] transition focus:outline-none"
                :class="generalOpen ? 'bg-[#0c9e95] shadow-md text-white' : 'text-indigo-100'">
            <div class="flex items-center">
                <span class="mr-3">📊</span> General
            </div>
            <span class="text-[10px] transition-transform duration-200" :class="generalOpen ? 'rotate-180' : ''">▼</span>
        </button>

        <div x-show="generalOpen" x-transition class="pl-10 space-y-1">
            <a href="user_info_view.php" class="block p-2 text-sm text-indigo-200  hover:text-[#64b12b] transition italic">
                 • User Activity
            </a>
            <a href="add_user.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                • Add New User
            </a>
            <a href="user_profile.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                • User Activity Tracking
            </a>
        </div>
    </div>



    <a href="create_content.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">🕒</span> Create Content
    </a>

    <a href="edit_content.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Content History
    </a>



        <a href="staff_activity.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Staff Activity
    </a>
            <a href="interaction_search.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Interaction Tracking
    </a>
</nav>
        </aside>

        <main class="flex-1 p-4 md:p-8">
            <header class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <h2 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h2>
                <div class="flex items-center space-x-4 w-full md:w-auto">
                    
                        
                    <div
                        class="w-10 h-10 bg-[#0c9e95] rounded-full flex items-center justify-center text-white font-bold shadow-lg shrink-0">
                        AD
                    </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
   

                <div class="bg-[#0d1849] p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-blue-100 text-sm font-semibold uppercase">Database Users</p>
                    <h3 class="text-3xl font-bold"><?php echo $total_users; ?></h3>
                    <p class="text-xs mt-2">Live from XAMPP</p>
                </div>

                <div class="bg-[#0d1849] p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-orange-100 text-sm font-semibold uppercase">System Status</p>
                    <h3 class="text-3xl font-bold">Online</h3>
                </div>
            </div>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 uppercase tracking-wider">AIU MEDIA HUB USERS</h3>
                    <button
                        class="bg-[#0c9e95] text-white px-4 py-2 rounded-lg text-sm font-semibold  transition">
                        <a href="add_user.php"
                            class="bg-[#0c9e95] text-white px-4 py-2 rounded-lg text-sm font-semibold  transition inline-block">
                            + Add User
                        </a>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                                <th class="p-4 font-medium">Full Name</th>
                                <th class="p-4 font-medium">Email</th>
                                <th class="p-4 font-medium">User Type</th>
                                <th class="p-4 font-medium">Join Date</th>
                                <th class="p-4 font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4 font-medium text-gray-900 font-semibold">
                                            <?php echo htmlspecialchars($row['full_name'] ?? $row['username']); ?>
                                        </td>
                                        <td class="p-4">
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <?php
                                            $type = strtolower($row['subtype'] ?? $row['user_type'] ?? 'unknown');
                                            if ($type == 'staff') {
                                                echo '<span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold uppercase">Staff</span>';
                                            } else {
                                                echo '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase">Student</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="view.php?id=<?php echo $row['id']; ?>"
                                                    class="px-3 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition">View</a>
                                                <a href="edit.php?id=<?php echo $row['id']; ?>"
                                                    class="px-3 py-1.5 bg-amber-100 text-amber-600 rounded-lg text-xs font-bold hover:bg-amber-600 hover:text-white transition">Edit</a>

                                                <button
                                                    onclick="showDeleteModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')"
                                                    class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-400">No users found in database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-gray-100">
            <div class="text-center text-gray-900">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6 text-4xl">⚠️
                </div>
                <h3 class="text-2xl font-bold">Are you sure?</h3>
                <p class="text-sm text-gray-500 mt-3">Deleting <span id="modalUserName"
                        class="font-bold text-gray-800"></span> can affect whole system data.</p>
            </div>
            <div class="mt-8 flex flex-col space-y-3">
                <a id="confirmDeleteLink" href="#"
                    class="w-full py-3 bg-red-600 text-white text-center rounded-xl font-bold hover:bg-red-700 transition">Yes,
                    Delete User</a>
                <button onclick="closeDeleteModal()"
                    class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-bold">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal(id, name) {
            document.getElementById('modalUserName').innerText = name;
            document.getElementById('confirmDeleteLink').href = 'delete_user.php?id=' + id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        window.onclick = function (event) {
            if (event.target == document.getElementById('deleteModal')) closeDeleteModal();
        }


        
    </script>
</body>

</html>