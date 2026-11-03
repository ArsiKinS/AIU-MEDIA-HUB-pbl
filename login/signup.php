<?php
include "connect.php"; 
session_start();

if (isset($_POST["register_btn"])) {
    // 1. Collect and Clean Data
    $email = mysqli_real_escape_string($dbconnection, $_POST["email"]);
    $username = mysqli_real_escape_string($dbconnection, $_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $user_type = $_POST["user_type"]; 
    $location = mysqli_real_escape_string($dbconnection, $_POST["location"]);

    // 2. Validate Passwords
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // 3. Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. STEP ONE: Insert into media_hub_user (This is what allows LOGIN)
    $stmt = $dbconnection->prepare("INSERT INTO media_hub_user (email, username, password, user_type, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $username, $hashed_password, $user_type, $location);

    if ($stmt->execute()) {
        // GET THE NEW ID
        $user_id = $dbconnection->insert_id; 

        // 5. STEP TWO: Role-Specific Insertion
        if ($user_type === 'Student') {
            $stmt_sub = $dbconnection->prepare("INSERT INTO student (user_id, student_card_id, batch, major) VALUES (?, ?, ?, ?)");
            $stmt_sub->bind_param("isss", $user_id, $_POST['student_id'], $_POST['batch'], $_POST['major']);
            $stmt_sub->execute();
        } 
        elseif ($user_type === 'Staff') {
            $stmt_sub = $dbconnection->prepare("INSERT INTO aiu_staff (user_id, staff_card_id, position) VALUES (?, ?, ?)");
            $stmt_sub->bind_param("iss", $user_id, $_POST['staff_id'], $_POST['position']);
            $stmt_sub->execute();
        } 
        elseif ($user_type === 'Visitor') {
            $stmt_sub = $dbconnection->prepare("INSERT INTO visitor (user_id, phone, occupation) VALUES (?, ?, ?)");
            $stmt_sub->bind_param("iss", $user_id, $_POST['phone'], $_POST['occupation']);
            $stmt_sub->execute();
        }
        elseif ($user_type === 'Admin') {
            // INSERT INTO ADMIN PROFILE TABLE
            $stmt_sub = $dbconnection->prepare("INSERT INTO staff (staff_id, name, email, department, position, country) VALUES (?, ?, ?, ?, ?, ?)");
            
            $adm_staff_id = $_POST['admin_staff_id'];
            $adm_dept = $_POST['admin_dept'];
            $adm_pos = $_POST['admin_pos'];
            
            $stmt_sub->bind_param("isssss", $adm_staff_id, $username, $email, $adm_dept, $adm_pos, $location);
            
            if(!$stmt_sub->execute()) {
                 // If the staff table fails, we tell the user, but the login is already created
                 echo "Warning: Login created, but profile failed: " . $stmt_sub->error;
            }
        }

        echo "<script>alert('Account Created Successfully !'); window.location.href='login2.php';</script>";
    } else {
        // IF THIS RUNS, it means the media_hub_user insert failed.
        // It will print the exact database error (e.g. "Duplicate entry for email")
        die("Error creating login account: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join AIU Media Hub | Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #0c9e95; border-radius: 10px; }
        .glass-card { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); }
        .input-focus { transition: all 0.3s ease; }
        .input-focus:focus { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12, 158, 149, 0.15); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center font-sans antialiased bg-slate-900 overflow-x-hidden">

    <div class="fixed inset-0 -z-10">
        <img src="bg.jpg" alt="background" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 to-slate-900/80"></div>
    </div>

    <div class="w-full max-w-lg my-10 px-4 animate-in fade-in zoom-in duration-700">
        <div class="glass-card rounded-[2.5rem] shadow-2xl border border-white/40 overflow-hidden">
            
            <div class="bg-gradient-to-r from-[#0c9e95] to-[#087a74] p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                    <svg width="100%" height="100%"><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="white" stroke-width="0.5"/></pattern><rect width="100%" height="100%" fill="url(#grid)" /></svg>
                </div>
                <div class="flex justify-center mb-4">
                    <div class="bg-white p-2 rounded-2xl shadow-xl ring-4 ring-white/20">
                        <img src="../img/Transparent_logo.png" alt="AIU Logo" class="h-16 w-auto">
                    </div>
                </div>
                <h2 class="text-3xl font-black text-white tracking-tight drop-shadow-sm">AIU Media Hub</h2>
                <p class="text-teal-50 text-xs mt-2 uppercase tracking-[0.2em] font-medium opacity-80">Registration Portal</p>
            </div>

            <div class="p-8 md:p-10">
                <form action="" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-[10px] font-black text-[#0c9e95] uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                            <input name="email" type="email" placeholder="name@aiu.edu.my"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#0c9e95] outline-none input-focus shadow-sm text-slate-700" required>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-[10px] font-black text-[#0c9e95] uppercase tracking-widest mb-1.5 ml-1">Full Name (Username)</label>
                            <input name="username" type="text" placeholder="Enter full name"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#0c9e95] outline-none input-focus shadow-sm text-slate-700" required>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-[#0c9e95] uppercase tracking-widest mb-1.5 ml-1">Password</label>
                            <input name="password" type="password" placeholder="••••••••"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#0c9e95] outline-none input-focus shadow-sm text-slate-700" required>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-[#0c9e95] uppercase tracking-widest mb-1.5 ml-1">Confirm</label>
                            <input name="confirm_password" type="password" placeholder="••••••••"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#0c9e95] outline-none input-focus shadow-sm text-slate-700" required>
                        </div>
                    </div>
                    <p class="text-[11px] text-red-500 text-center font-bold" id="password-error"></p>

                    <hr class="border-slate-100">

                    <div class="bg-slate-50/80 p-6 rounded-[2rem] border border-slate-200/60 shadow-inner">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Select Your Profile</label>
                        
                        <div class="mb-4">
                            <select name="user_type" id="userType" 
                                class="w-full px-5 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-bold focus:ring-2 focus:ring-[#0c9e95] outline-none shadow-sm cursor-pointer appearance-none">
                                <option value="Student">Student</option>
                                <option value="Staff">Staff</option>
                                <option value="Visitor">Visitor</option>
                                <option value="Admin">System Administrator</option>
                            </select>
                        </div>

                        <div id="dynamicFields" class="space-y-4 animate-in slide-in-from-top-4 duration-500"></div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-[#0c9e95] uppercase tracking-widest mb-1.5 ml-1">Country (Location)</label>
                        <input name="location" type="text" placeholder="Malaysia"
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#0c9e95] outline-none input-focus shadow-sm text-sm text-slate-600">
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="register_btn"
                            class="w-full bg-gradient-to-r from-[#0c9e95] to-[#087a74] text-white font-black py-4 rounded-2xl transition-all transform hover:-translate-y-1 active:scale-[0.98] uppercase tracking-widest text-sm">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function updateDynamicFields() {
    const userType = document.getElementById('userType').value;
    const dynamicFields = document.getElementById('dynamicFields');
    let html = '';
    
    const inputClass = "w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#0c9e95] outline-none shadow-sm text-sm font-medium transition-all text-slate-600";
    const labelClass = "block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1";

    if (userType === 'Student') {
        html = `<div class="grid grid-cols-2 gap-4 p-2">
                    <div class="col-span-2"><label class="${labelClass}">Student ID</label><input name="student_id" type="text" placeholder="AIU..." class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Batch</label><input name="batch" type="text" class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Major</label><input name="major" type="text" class="${inputClass}" required></div>
                </div>`;
    } 
    else if (userType === 'Staff') {
        html = `<div class="space-y-4 p-2">
                    <div><label class="${labelClass}">Staff ID</label><input name="staff_id" type="text" class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Position</label><input name="position" type="text" class="${inputClass}" required></div>
                </div>`;
    } 
    else if (userType === 'Visitor') {
        html = `<div class="space-y-4 p-2">
                    <div><label class="${labelClass}">Phone</label><input name="phone" type="tel" class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Occupation</label><input name="occupation" type="text" class="${inputClass}" required></div>
                </div>`;
    }
    else if (userType === 'Admin') {
        html = `<div class="space-y-4 p-2">
                    <div><label class="${labelClass}">Admin Staff ID</label><input name="admin_staff_id" type="text" placeholder="e.g. 201" class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Department</label><input name="admin_dept" type="text" placeholder="e.g. IT" class="${inputClass}" required></div>
                    <div><label class="${labelClass}">Position</label><input name="admin_pos" type="text" placeholder="e.g. Manager" class="${inputClass}" required></div>
                </div>`;
    }
    dynamicFields.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    updateDynamicFields();
    document.getElementById('userType').addEventListener('change', updateDynamicFields);
});
</script>
</body>
</html>