<?php
session_start();
require('../login/connect.php');

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">

    <!-- Navigation Sidebar -->
    <div class="flex min-h-screen">
        <aside class="w-64 bg-indigo-900 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Settings</p>
            </div>
            
            <nav class="flex-grow p-4 space-y-2 mt-4 hidden md:block" x-data="{ generalOpen: false, activityOpen: false }">
                
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

                <a href="create_content.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100">
                    <span class="mr-3">📝</span> Create Content
                </a>

                <a href="view_content.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-600 transition text-indigo-100">
                    <span class="mr-3">🕒</span> Content History
                </a>

                <a href="settings.php" class="flex items-center p-3 rounded-lg bg-indigo-800 shadow-md hover:bg-indigo-700 transition text-white">
                    <span class="mr-3">⚙️</span> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6">
            <div class="max-w-4xl mx-auto">
                
                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">⚙️ Settings</h2>
                    <p class="text-gray-600">Account settings and preferences</p>
                </div>

                <!-- Simple Settings Card with Logout -->
                <div class="bg-white rounded-lg shadow p-8 max-w-md mx-auto">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🚪</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Logout</h3>
                        <p class="text-gray-600">Sign out from your account</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <span class="text-yellow-600 mr-2">⚠️</span>
                                <div>
                                    <h4 class="font-medium text-yellow-800">Important Notice</h4>
                                    <p class="text-sm text-yellow-600 mt-1">
                                        You will be redirected to the login page after logging out. 
                                        Make sure to save any unsaved work before proceeding.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form method="POST">
                            <button type="submit" name="logout" 
                                    class="w-full px-6 py-3 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition text-lg flex items-center justify-center gap-2">
                                <span>🚪</span>
                                Logout from Account
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="view_content.php" 
                               class="inline-block px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>