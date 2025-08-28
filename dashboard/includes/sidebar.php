<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="<?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] == 'admin') { echo 'admin_dashboard.php'; } else { echo 'user_dashboard.php'; } ?>"
        class="logo">
        <h4 class="logo-text text-white ml-3 mt-2 mb-0 ">Advance IT</h4>
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
  </div>
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-item">
          <a href="<?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] == 'admin') { echo 'admin_dashboard.php'; } else { echo 'user_dashboard.php'; }?>"
            class="collapsed">
            <i class="fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section">Components</h4>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#tokens">
            <i class="fas fa-key"></i>
            <p>Tokens</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="tokens">
            <ul class="nav nav-collapse">
              <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] == 'admin') {?>
              <li>
                <a href="all_token.php">
                  <span class="sub-item">All Tokens</span>
                </a>
              </li>
              <?php }?>
              <li>
                <a href="add_live_token.php">
                  <span class="sub-item">Add Tokens</span>
                </a>
              </li>
              <li>
                <a href="my_tokens.php">
                  <span class="sub-item">My Tokens</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <!-- Check if the session 'role' is set admin before trying to access it -->
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] == 'admin') {?>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#tables">
            <i class="fas fa-users"></i>
            <p>Office Staff</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="tables">
            <ul class="nav nav-collapse">
              <li>
                <a href="list_workers.php">
                  <span class="sub-item">List Admin</span>
                </a>
              </li>
              <li>
                <a href="add_workers.php">
                  <span class="sub-item">Add Workers</span>
                </a>
              </li>
              <li>
                <a href="manage_workers.php">
                  <span class="sub-item">Manage Workers</span>
                </a>
              </li>
          </div>
        </li>
        <li class="nav-item">
          <a href="add_notifications.php">
            <i class="fas fa-bell"></i>
            <p>Notifications</p>
          </a>
        </li>
        <?php }?>
        <!-- <li class="nav-item">
          <a data-bs-toggle="collapse" href="#dashboard" class="collapsed" aria-expanded="false">
            <i class="fas fa-cog"></i>
            <p>Settings</p>
          </a>
        </li> -->
      </ul>
    </div>
  </div>
</div>