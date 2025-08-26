<?php
$user = $obj->display_user();
$admin = $obj->display_admin();
$my_token_today = $obj->my_token_count_today();
$my_token_yesterday = $obj->my_token_count_yesterday();
$my_token_month = $obj->my_token_count_this_month();
$my_token_total = $obj->my_token_count_lifetime();
?>

<div class="container">
  <div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3"><?= htmlspecialchars($userName); ?> Dashboard</h3>
        <h6 class="op-7 mb-2">Advance IT User Dashboard</h6>
      </div>
    </div>

    <div class="row">

      <!-- Today -->
      <div class="col-sm-1 col-md-3">
        <div class="card card-stats card-info card-round">
          <div class="card-body">
            <div class="row">
              <div class="col-5">
                <div class="icon-big text-center">
                  <i class="fas fa-key"></i>
                </div>
              </div>
              <div class="col-7 col-stats">
                <div class="numbers">
                  <p class="card-category">Today</p>
                  <h4 class="card-title"><?php echo htmlspecialchars($my_token_today ?: '0'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Yesterday -->
      <div class="col-sm-1 col-md-3">
        <div class="card card-stats card-secondary card-round">
          <div class="card-body">
            <div class="row">
              <div class="col-4">
                <div class="icon-big text-center">
                  <i class="fas fa-key"></i>
                </div>
              </div>
              <div class="col-md-8 col-stats">
                <div class="numbers">
                  <p class="card-category">Yesterday</p>
                  <h4 class="card-title"><?php echo htmlspecialchars($my_token_yesterday ?: '0'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- This Month -->
      <div class="col-sm-1 col-md-3">
        <div class="card card-stats card-primary card-round">
          <div class="card-body">
            <div class="row">
              <div class="col-4">
                <div class="icon-big text-center">
                  <i class="fas fa-calendar-alt"></i>
                </div>
              </div>
              <div class="col-md-8 col-stats">
                <div class="numbers">
                  <p class="card-category">This Month</p>
                  <h4 class="card-title"><?php echo htmlspecialchars($my_token_month ?: '0'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lifetime -->
      <div class="col-sm-1 col-md-3">
        <div class="card card-stats card-success card-round">
          <div class="card-body">
            <div class="row">
              <div class="col-4">
                <div class="icon-big text-center">
                  <i class="fas fa-key"></i>
                </div>
              </div>
              <div class="col-md-8 col-stats">
                <div class="numbers">
                  <p class="card-category">Lifetime</p>
                  <h4 class="card-title"><?php echo htmlspecialchars($my_token_total ?: '0'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>