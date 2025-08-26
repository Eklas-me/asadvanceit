<?php 
include __DIR__ . "/security.php";
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log'); // saves error in your project folder

include("./class/function.php");
$obj = new AdvancedAdmin();
session_start();

// Check if the login form is submitted
if (isset($_POST['login_btn'])) {
    // Call the updated user login function
    $msg = $obj->user_login($_POST);  // Updated to 'user_login'
}

// If the user is already logged in, redirect based on role
if (isset($_SESSION['userId'])) {
    if ($_SESSION['userRole'] === 'admin') {
        header("location:admin_dashboard.php");
    } else {
        header("location:user_dashboard.php");
    }
    exit();
}

include_once('./includes/head.php');



?>

<style>
body {
  background: url('./uploads//bg-01.jpg') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  /* Full height of the viewport */
  margin: 0;
}

.card {
  background: rgba(255, 255, 255, 0.9);
  /* Slightly transparent background */
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
  /* Limit the width */
}

.card-header {
  color: #000;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
}

.login-btn {
  background: rgb(242, 4, 221);
  background: linear-gradient(90deg, rgba(242, 4, 221, 1) 0%, rgba(2, 138, 232, 1) 53%, rgba(0, 212, 255, 1) 100%);
  border: none;
  color: #fff;
}

.btn-primary:hover {
  background-color: #0056b3;
}
</style>

<body>
  <div class="card">
    <div class="card-header text-center">
      <h3>Advanced It Login</h3>
    </div>
    <div class="card-body">
      <!-- Login Form -->
      <form action="" method="POST">
        <div class="form-group mb-3">
          <label for="email">Email address</label>
          <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" required>
        </div>
        <div class="form-group mb-3">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        </div>
        <?php if (isset($msg)): ?>
        <div class="alert alert-success" id="alertMessage">
          <?php echo $msg; ?>
        </div>
        <?php endif; ?>
        <button name="login_btn" type="submit" class="btn login-btn w-100">Login</button>
      </form>
    </div>
  </div>

  <?php include_once('./includes/scripts.php'); ?>
</body>