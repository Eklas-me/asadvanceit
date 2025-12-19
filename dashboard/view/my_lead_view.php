<?php
$current_user_id = $_SESSION['userId'];
$current_user_name = $_SESSION['userName'];
$selected_date = $_POST['task_date'] ?? date('Y-m-d'); // Default to current date
$accounts = $obj->display_my_accounts($current_user_id, $selected_date);
?>

<h4 class="mb-4">My Tokens</h4>
<form method="POST" class="row">
  <div class="col-md-6 col-lg-6 mb-3 d-flex align-items-end">
    <div class="form-group w-100">
      <label for="task_date" class="form-label">Account Date</label>
      <div class="d-flex">
        <input type="date" class="form-control me-2" name="task_date" id="task_date" required
          placeholder="Select task date" value="<?php echo htmlspecialchars($selected_date); ?>" />
        <button type="submit" class="btn btn-success">Filter</button>
      </div>
    </div>
  </div>
</form>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="basic-datatables" class="display table-head-bg-success table table-striped table-hover">
            <thead>
              <tr>
                <th>User ID</th>

                <th>Account Email</th>
                <th>Password</th>
                <th>Recovery</th>
                <th>Tinder Username</th>
                <th>Tinder Token</th>
                <th>Lat-Long</th>
                <th>Numbers</th>
                <th>Extra Info</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($accounts)): ?>
              <?php foreach ($accounts as $account): ?>
              <tr>
                <td><?php echo htmlspecialchars($account['user_id']); ?></td>
                <td><?php echo htmlspecialchars($account['account_email']); ?></td>
                <td><?php echo htmlspecialchars($account['password']); ?></td>
                <td><?php echo htmlspecialchars($account['recovery']); ?></td>
                <td><?php echo htmlspecialchars($account['tinder_username']); ?></td>
                <td><?php echo htmlspecialchars($account['token']); ?></td>
                <td><?php echo htmlspecialchars($account['lat_long']); ?></td>
                <td><?php echo htmlspecialchars($account['numbers']); ?></td>
                <td><?php echo htmlspecialchars($account['comments']); ?></td>
                <td>
                  <?php 
        // Create a DateTime object and format it to show full date and time
        $created_at = new DateTime($account['created_at']);
        echo htmlspecialchars($created_at->format('d M Y H:i:s')); // Outputs: 01 Nov 2024 12:34:56
      ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="11" class="text-center">No data found</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>