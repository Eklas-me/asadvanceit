<?php
// Fetch user, admin data, and notifications
$user = $obj->display_user();
$admin = $obj->display_admin();
$profilePic = $obj->getProfilePicture($userId, $userRole);
$notifications = $obj->get_notification();
$unreadCount = $obj->getUnreadNotificationCount();

// Mark notification as read when an ID is passed in POST request
if (isset($_POST['id'])) {
  $notificationId = $_POST['id'];
  $obj->markNotificationAsRead($notificationId);
}
?>

<!-- Modal for previous notifications -->
<div class="modal z-index-1051 fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="historyModalLabel">Notification History</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Notification Title -->
        <!-- <h4 id="notificationTitle"></h4> -->
        <!-- Notification Message -->
        <p id="notificationMessage"></p>
        <!-- Notification Time -->
        <p class="time" id="notificationTime"></p>
      </div>
    </div>
  </div>
</div>


<!-- Navbar Section -->
<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
  <div class="container-fluid">
    <!-- <h3 class="fw-bold">Dashboard</h3> -->

    <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
      <!-- Search Icon for small screens -->
      <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
          <i class="fa fa-search"></i>
        </a>
        <ul class="dropdown-menu dropdown-search animated fadeIn">
          <form class="navbar-left navbar-form nav-search">
            <div class="input-group">
              <input type="text" placeholder="Search ..." class="form-control" />
            </div>
          </form>
        </ul>
      </li>

      <!-- Notification Dropdown -->
      <li class="nav-item topbar-icon dropdown hidden-caret">
        <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-bell"></i>
          <span class="notification"><?= $unreadCount; ?></span>
        </a>
        <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
          <li>
            <div class="dropdown-title">You have <?= $unreadCount; ?> new notifications</div>
          </li>
          <li>
            <div class="notif-scroll scrollbar-outer px-3">
              <div class="notif-center pl-5">
                <?php foreach ($notifications as $notification): ?>
                <?php $timeAgo = $obj->timeAgo($notification['created_at']); ?>
                <a href="javascript:void(0);" class="notification-link" data-id="<?= $notification['id']; ?>"
                  data-title="<?= $notification['title']; ?>" data-message="<?= $notification['message']; ?>"
                  data-time="<?= $timeAgo; ?>">
                  <div class="notif-content">
                    <span class="block"><?= $notification['title']; ?></span>
                    <span class="time"><?= $timeAgo; ?></span>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
            </div>
          </li>
          <!-- <li><a class="see-all" href="javascript:void(0);">See all notifications<i class="fa fa-angle-right"></i></a> -->
      </li>
    </ul>
    </li>

    <!-- Profile Dropdown -->
    <li class="nav-item topbar-user dropdown hidden-caret">
      <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
        <div class="avatar-sm">
          <img src="uploads/<?= htmlspecialchars($profilePic); ?>" alt="Profile Picture"
            class="avatar-img rounded-circle" />
        </div>
        <span class="profile-username">
          <span class="op-7">Hi,</span>
          <span class="fw-bold"><?= htmlspecialchars($userName); ?></span>
        </span>
      </a>
      <ul class="dropdown-menu dropdown-user animated fadeIn">
        <div class="dropdown-user-scroll scrollbar-outer">
          <li>
            <div class="user-box">
              <div class="avatar-lg">
                <img src="uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Image"
                  class="avatar-img rounded" />
              </div>
              <div class="u-text">
                <h4><?= htmlspecialchars($userName); ?></h4>
                <p class="text-muted"><?= htmlspecialchars($userEmail); ?></p>
                <a href="my_profile.php" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
              </div>
            </div>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li><a class="dropdown-item" href="my_profile.php">My Profile</a></li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <!-- <li><a class="dropdown-item" href="#">Account Setting</a></li> -->
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li><a class="dropdown-item" href="?adminLogout=logout">Logout</a></li>
        </div>
      </ul>
    </li>
    </ul>
  </div>
</nav>

<style>
.modal-backdrop {
  z-index: 1 !important;
  /* Ensure the backdrop is above other elements */
}
</style>

<!-- JavaScript to handle notification clicks and show modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle notification click
  document.querySelectorAll('.notification-link').forEach(function(notificationLink) {
    notificationLink.addEventListener('click', function() {
      // Get notification data
      const title = this.getAttribute('data-title');
      const message = this.getAttribute('data-message');
      const time = this.getAttribute('data-time');
      const notificationId = this.getAttribute('data-id');

      // Update modal content
      document.querySelector('#historyModalLabel').textContent = title;
      // document.querySelector('#historyModal .modal-body h4').textContent = title;
      document.querySelector('#historyModal .modal-body p').textContent = message;
      document.querySelector('#historyModal .modal-body .time').textContent = time;

      // Show the modal (Bootstrap)
      var myModal = new bootstrap.Modal(document.getElementById('historyModal'), {
        keyboard: false
      });
      myModal.show();

      // Mark the notification as read via AJAX
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'mark_notification.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (xhr.status === 200) {
          console.log('Notification marked as read.');
        } else {
          console.log('Error marking notification as read.');
        }
      };
      xhr.onerror = function() {
        console.log('AJAX request failed.');
      };
      xhr.send('id=' + notificationId);
    });
  });
});
</script>