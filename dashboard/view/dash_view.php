<?php
// Default datetime-local values
$defaultStart = date('Y-m-d') . 'T07:00';
$defaultEnd = date('Y-m-d', strtotime('+1 day')) . 'T07:00';

// User input override, না থাকলে default
$startDateInput = $_GET['start_date'] ?? $defaultStart;
$endDateInput   = $_GET['end_date'] ?? $defaultEnd;

// DB query জন্য convert
$startDate = date('Y-m-d H:i:s', strtotime($startDateInput));
$endDate   = date('Y-m-d H:i:s', strtotime($endDateInput));
// Fetch active workers in last 30 minutes
$active_last_30min = $obj->count_active_last30min();
// Fetch data
$workers_report = $obj->get_workers_report($startDate, $endDate);
// Fetch the count of tokens added today
$total_tokens_today = $obj->count_tokens_today();
// Fetch the count of workers who have added tokens today
$total_workers_today = $obj->count_workers_today();
// Fetch account added today
$total_account_today = $obj->count_account_today();
// Fetch the data from the backend
?>

<div class="page-inner">
  <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
      <h3 class="fw-bold mb-3">Admin Dashboard</h3>
      <h6 class="op-7 mb-2">Advance IT Daily Report</h6>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6 col-md-3">
      <div class="card card-stats card-round">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-icon">
              <div class="icon-big text-center icon-primary bubble-shadow-small">
                <i class="fas fa-key"></i>
              </div>
            </div>
            <div class="col col-stats ms-3 ms-sm-0">
              <div class="numbers">
                <p class="card-category">Tokens</p>
                <h4 class="card-title"><?php echo htmlspecialchars($total_tokens_today ?: '0'); ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-3">
      <div class="card card-stats card-round">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-icon">
              <div class="icon-big text-center icon-info bubble-shadow-small">
                <i class="fas fa-users"></i>
              </div>
            </div>
            <div class="col col-stats ms-3 ms-sm-0">
              <div class="numbers">
                <p class="card-category">Workers</p>
                <h4 class="card-title"><?php echo htmlspecialchars($total_workers_today); ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-md-3">
      <div class="card card-stats card-round">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-icon">
              <div class="icon-big text-center icon-success bubble-shadow-small">
                <i class="fas fa-luggage-cart"></i>
              </div>
            </div>
            <div class="col col-stats ms-3 ms-sm-0">
              <div class="numbers">
                <p class="card-category">Account</p>
                <h4 class="card-title"><?php echo htmlspecialchars($total_account_today); ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<div class="col-sm-6 col-md-3">
  <div class="card card-stats card-round">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-icon">
          <div class="icon-big text-center icon-secondary bubble-shadow-small">
            <i class="far fa-check-circle"></i>
          </div>
        </div>
        <div class="col col-stats ms-3 ms-sm-0">
          <div class="numbers">
            <p class="card-category">Active in 30 min</p>
            <h4 class="card-title">
              <?= htmlspecialchars($active_last_30min ?: 0) ?>
            </h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  </div>

  <!-- <div class="row">
    <div class="col-md-8">
      <div class="card card-round">
        <div class="card-header">
          <div class="card-head-row">
            <div class="card-title">Daily Statistics</div>
          </div>
        </div>
        <div class="card-body">
          <div class="chart-container" style="min-height: 375px">
            <canvas id="statisticsChart"></canvas>
          </div>
          <div id="myChartLegend"></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <div class="card-title">Monthly Statistics</div>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="multipleBarChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div> -->

  <!-- Daily Report -->
  <div class="row">
    <div class="col-md-12">
      <div class="card card-round">
        <div class="card-header">
          <div class="card-head-row">
            <div class="card-title">All Workers Report</div>
            <div class="ms-auto">
              <form method="GET" action="">
                <div class="d-flex">
                  <input type="datetime-local" name="start_date" class="form-control form-control-sm me-2"
                    value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : $defaultStart; ?>">

                  <input type="datetime-local" name="end_date" class="form-control form-control-sm me-2"
                    value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : $defaultEnd; ?>">

                  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead class="thead-light">
                <tr>
                  <th scope="col">Workers Name</th>
                  <th scope="col" class="text-end">Tokens</th>
                  <th scope="col" class="text-end">Last Update</th>
                </tr>
              </thead>

              <tbody>
                <?php if (!empty($workers_report)) : ?>
                <?php $serial = 1; ?>
                <?php foreach ($workers_report as $report) : ?>
                <?php
                    // Top 3 background color
                    $rowClass = '';
                    if ($serial == 1) {
                      $rowClass = 'top1';
                    } elseif ($serial == 2) {
                      $rowClass = 'top2';
                    } elseif ($serial == 3) {
                      $rowClass = 'top3';
                    }
                    ?>
                <tr class="<?php echo $rowClass; ?>">
                  <th scope="row">
                    <div class="d-flex align-items-center">
                      <span class="me-2">
                        <?php
                            if ($serial == 1) {
                              echo '<img src="./uploads/icons/1st-place.png">';
                            } elseif ($serial == 2) {
                              echo '<img src="./uploads/icons/2nd-place.png">';
                            } elseif ($serial == 3) {
                              echo '<img src="./uploads/icons/3rd-place.png">';
                            } else {
                              echo $serial . '.';
                            }
                            ?>
                      </span>

                      <div class="avatar me-2">
                        <img src="<?php
                                      echo !empty($report['profile_photo'])
                                        ? './uploads/' . htmlspecialchars($report['profile_photo'])
                                        : './uploads/user.png';
                                      ?>" alt="..." class="avatar-img rounded-circle" style="cursor:pointer;"
                          data-bs-toggle="modal" data-bs-target="#photoModal<?php echo $report['user_id']; ?>" />
                      </div>
                      <?php echo htmlspecialchars($report['worker_name']); ?>
                    </div>
                  </th>

                  <td class="text-end">
                    <span class="badge badge-success" style="font-size: 1.2rem; background-color: #1572e8;">
                      <?php echo htmlspecialchars($report['total_tokens']); ?>
                    </span>
                  </td>

<td class="text-end">
  <?php
  if (!empty($report['last_update'])) {
      $lastUpdate = strtotime($report['last_update']);
      $now = time();
      $diffMinutes = round(($now - $lastUpdate) / 60);

      if ($diffMinutes <= 30) {
          // Last 30 min highlight
          echo '<span class="badge badge-success recent-update" style="font-size: 1rem;">'
             . $obj->timeAgo($report['last_update'])
             . '</span>';
      } else {
          // Normal
          echo '<span class="badge badge-info" style="font-size: 1rem;">'
             . $obj->timeAgo($report['last_update'])
             . '</span>';
      }
  } else {
      echo '<span class="badge badge-secondary" style="font-size: 1rem;">No activity</span>';
  }
  ?>
</td>

                </tr>

                <!-- Modal for this worker -->
                <div class="modal fade" id="photoModal<?php echo $report['user_id']; ?>" tabindex="-1"
                  aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-body p-0">
                        <img src="<?php
                                      echo !empty($report['profile_photo'])
                                        ? './uploads/' . htmlspecialchars($report['profile_photo'])
                                        : './uploads/user.png';
                                      ?>" class="img-fluid" alt="Profile Photo">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <?php $serial++;
                  endforeach; ?>
                <?php else : ?>
                <tr>
                  <td colspan="3" class="text-center">No data available for this range.</td>
                </tr>
                <?php endif; ?>
              </tbody>


            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* ===========================
   Marquee & small icons
=========================== */
marquee {
  background-color: #e0f7fa;
  color: #00796b;
  padding: 10px;
  border-radius: 5px;
  font-size: 1.1em;
}

.history-icon {
  top: 5px;
  right: 5px;
}

/* ===========================
   Top 3 Row Background Colors
=========================== */
.top1 {
  background-color: #fff3e0;
  /* Gold-ish */
  font-weight: bold;
}

.top2 {
  background-color: #e0f7fa;
  /* Silver-ish */
  font-weight: bold;
}

.top3 {
  background-color: #f3e5f5;
  /* Bronze-ish */
  font-weight: bold;
}

/* ===========================
   Table Hover Effect
=========================== */
table tbody tr:hover {
  background-color: #f1f1f1;
  transition: background-color 0.3s;
}

/* ===========================
   Avatar Styling
=========================== */
.avatar-img {
  width: 35px !important;
  height: 35px !important;
  object-fit: cover !important;
  border-radius: 50% !important;
  border: 1px solid #ddd !important;
}

/* ===========================
   Top 3 Medal Icons
=========================== */
th .me-2 img {
  width: 30px;
  /* Icon width */
  height: 30px;
  /* Icon height */
  object-fit: contain;
  vertical-align: middle;
}

/* ===========================
   Badge Styling
=========================== */
.badge-success {
  font-size: 1.2rem;
  padding: 0.4rem 0.6rem;
}

.badge-info {
  font-size: 1rem;
  padding: 0.35rem 0.55rem;
}

/* ===========================
   Table Styling
=========================== */
.table th,
.table td {
  vertical-align: middle;
}

.table thead.thead-light th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.recent-update {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.6); }
  70% { box-shadow: 0 0 0 10px rgba(40,167,69,0); }
  100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
}


/* ===========================
   Responsive Adjustments
=========================== */
@media (max-width: 768px) {
  .avatar-img {
    width: 30px;
    height: 30px;
  }

  th .me-2 img {
    width: 25px;
    height: 25px;
  }

  .badge-success {
    font-size: 1rem;
  }

  .badge-info {
    font-size: 0.9rem;
  }
}
</style>


<script>
// Convert PHP JSON to JavaScript array
const dailyData = <?php echo $daily_counts; ?>;
const MonthlyData = <?php echo $monthly_data; ?>;
</script>