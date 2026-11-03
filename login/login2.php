<?php
session_start();
require('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. First, check if the user exists in the main login table
    $stmt = $dbconnection->prepare("SELECT * FROM media_hub_user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // 2. Verify Password
        if (password_verify($password, $user['password'])) {
            
            // 3. ADMIN SPECIAL CHECK
            if ($user['user_type'] === 'Admin') {
                // Check if this admin actually exists in the 'staff' profile table
                $stmt_admin = $dbconnection->prepare("SELECT * FROM staff WHERE email = ?");
                $stmt_admin->bind_param("s", $email);
                $stmt_admin->execute();
                $admin_result = $stmt_admin->get_result();

                if ($admin_result->num_rows === 0) {
                    // This email is an Admin in media_hub_user but MISSING in staff table
                    echo "<script>alert('Admin profile not found in staff records.'); window.history.back();</script>";
                    exit;
                }
                
                // If they exist in staff table, set specific admin session
                $admin_data = $admin_result->fetch_assoc();
                $_SESSION['admin_staff_id'] = $admin_data['staff_id'];
                $_SESSION['role'] = 'Admin';
            }

            // 4. Set General Sessions
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['logged_in'] = time();

            // 5. Record Login Activity
            $device = $_SERVER['HTTP_USER_AGENT'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt2 = $dbconnection->prepare("INSERT INTO login (device, ip_address, user_id) VALUES (?, ?, ?)");
            $stmt2->bind_param("ssi", $device, $ip, $user['id']);
            $stmt2->execute();

            // 6. REDIRECTION
            if ($user['user_type'] === 'Admin') {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: ../index.html");
            }
            exit;

        } else {
            echo "<script>alert('Incorrect password');</script>";
        }
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* FORCE box sizing */
    * {
      box-sizing: border-box;
    }

    /* DESKTOP DEFAULT */
    body,
    html {
      margin: 0;
      padding: 0;
    }

    /* MOBILE FIX */
    @media (max-width: 768px) {

      /* MAIN FLEX WRAPPER */
      body>.container {
        flex-direction: column !important;
        height: auto !important;
      }

      /* HIDE IMAGE SECTION */
      .right-container {
        display: none !important;
      }

      /* FULL WIDTH LOGIN */
      .left-container {
        width: 100% !important;
        height: 100vh !important;
        justify-content: center !important;
      }

      /* REMOVE DOUBLE CONTAINER EFFECT */
      .container .container {
        width: 100% !important;
        height: auto !important;
      }
    }
  </style>

</head>


<body>
  <div class="container w-full h-screen flex">
    <div class="container w-full h-screen flex font-sans ">
      <!-- Right Section (Image + Card) -->

      <div class="right-container w-[55%] bg-[url('bg.jpg')] bg-cover bg-center h-screen
           rounded-tr-[95px] rounded-br-[95px] 
           flex items-center justify-center ">
        <div class="inner w-[500px] h-[450px] bg-white/90 backdrop-blur-md
             rounded-tr-[95px] rounded-br-[95px]
             p-10 flex flex-col justify-center gap-4 shadow-lg">
          <img src="../img/logo.jpg" class="w-[120px] h-[60px] " alt="">
          <h1 class="text-4xl font-bold text-[#0d1849]">
            AIU Media Hub
          </h1>

          <p class="text-lg font-medium text-gray-600">
            Stay Informed, Stay Inspired
          </p>

          <p class="text-sm text-gray-500 leading-relaxed">
            We deliver the latest campus updates, research breakthroughs,
            and student success stories directly to your feed — no clutter,
            just what matters to you.
          </p>

          <p class="text-sm text-[#0c9e95] font-medium cursor-pointer hover:underline">
            <a href="../index.html">Let's explore the news ...</a>
          </p>



        </div>
      </div>

      <!-- Left Section -->
      <div class="left-container w-[45%] flex items-center justify-center">
        <div class="w-[380px] bg-white p-8 rounded-2xl ">
          <form action="login2.php" method="POST">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
              Login
            </h2>
            <p class="text-sm text-gray-500 mb-6">
              Welcome back! Please login to your account.
            </p>

            <!-- Email -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
              </label>
              <input type="email" placeholder="Enter your email" name="email" class="w-full px-4 py-3 border rounded-lg
               focus:outline-none focus:ring-2 focus:ring-[#0c9e95]" />
            </div>

            <!-- Password -->
            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Password
              </label>
              <input type="password" name="password" placeholder="Enter your password" class="w-full px-4 py-3 border rounded-lg
               focus:outline-none focus:ring-2 focus:ring-[#0c9e95]" />
            </div>

            <!-- Forgot Password -->
            <div class="text-right mb-4">
              <a href="forgot_password.php" class="text-sm text-[#0c9e95] hover:underline">
                Forgot password?
              </a>
            </div>

            <!-- Login Button -->
            <button class="w-full bg-[#0c9e95] hover:bg-[#0c615c]
             text-white font-semibold py-3 rounded-xl
             transition duration-300">
              Login
            </button>

            <!-- Sign Up Link -->
            <p class="text-sm text-center text-[#0c9e95] mt-6">
              Don’t have an account?
              <span class="text-[amber-500] font-medium cursor-pointer hover:underline">

                <a href="signup.php">Sign Up</a>
              </span>
            </p>

          </form>

        </div>


      </div>

    </div>

  </div>

</body>

</html>