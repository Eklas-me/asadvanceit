<?php
include("./class/function.php");
$obj = new AdvancedAdmin();

session_start();

// Check if the user is logged in
if (!isset($_SESSION['userId'])) {
  header("location: index.php");
  exit();
}

// If the user clicks on the logout link
if (isset($_GET['adminLogout']) && $_GET['adminLogout'] === 'logout') {
  $obj->user_logout();  // Updated to 'user_logout' to handle both roles
}

// Fetch user role and username from session
$userRole = $_SESSION['userRole'];  // Fetch the user role from session
$userId = $_SESSION['userId'];
$userName = isset($_SESSION['userName']) ? $_SESSION['userName'] : 'Guest';  // Fetch the user/admin name
$userEmail = $_SESSION['userEmail'];



?>

<?php include_once('./includes/head.php'); ?>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <?php include_once('./includes/sidebar.php'); ?>
    <!-- End Sidebar -->

    <div class="main-panel">
      <div class="main-header">
        <div class="main-header-logo">
          <!-- Logo Header -->
          <?php include_once('./includes/header.php'); ?>
          <!-- End Logo Header -->
        </div>
        <!-- Navbar Header -->
        <?php include_once('./includes/nav.php'); ?>
        <!-- End Navbar -->
      </div>

      <div class="container">
        <div class="page-inner">

          <?php 
          // Check the user role and render views accordingly
          if (isset($view)) {
            // Admin-specific views
            if ($userRole === 'admin') {
              if ($view == "dashboard") {
                include("./view/dash_view.php");  // Admin dashboard
              }else if ($view == "add_workers") {
                include("./view/add_workers_view.php");  // Admin-only feature
              }else if ($view == "manage_workers") {
                include("./view/manage_workers_view.php");  // Admin-only feature
              } else if ($view == "all_token") {
                include("./view/all_token_view.php");  // Admin-only feature
              } else if ($view == "add_notifications") {
                include("./view/add_notification_view.php");  // Admin-only feature
              } else if ($view == "edit_workers") {
                include("./view/edit_workers_view.php");  // Admin-only feature
              }
            }

            // Common views accessible by both admin and regular users
            if ($view == "user_dashboard") {
              include("./view/user_dash_view.php");  // User dashboard
            } else if ($view == "list_workers") {
              include("./view/list_workers_view.php");  // Admin-only feature
            } elseif ($view == "add_live_token") {
              include("./view/add_live_token_view.php");  // Admin-only feature
            } elseif ($view == "my_tokens") {
              include("./view/my_token_view.php");  // Admin-only feature
            } elseif ($view == "my_profile") {
              include("./view/my_profile_view.php");  // Admin-only feature
            }
            
            // You can add more views here based on your requirements
          } 
          ?>

        </div>
      </div>

      <?php include_once('./includes/footer.php'); ?>
    </div>
  </div>

  <?php include_once('./includes/scripts.php'); ?>
</body>