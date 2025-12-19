<?php
$current_user_id = $_SESSION['userId'];

// Selected date, default today
$selected_date = $_POST['task_date'] ?? date('Y-m-d');

// Fetch tokens for selected date
$tokens = $obj->display_my_tokens($current_user_id, $selected_date);
?>
<?php
if (isset($_POST['delete_token'])) {
    $token_id = $_POST['delete_token'];
    $obj->delete_my_token($current_user_id, $token_id);
    echo "<p class='text-success'>Token deleted successfully!</p>";
}
?>

<h4 class="mb-4">My Tokens</h4>
<form method="POST" class="row">
  <div class="col-md-3 mb-3">
    <label for="task_date" class="form-label">Select Date</label>
    <input type="date" class="form-control" name="task_date" id="task_date"
      value="<?php echo htmlspecialchars($selected_date); ?>" required>
  </div>

  <div class="col-md-3 mb-3 d-flex align-items-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
</form>

<div class="col-12 mt-3">
  <div class="p-3 border rounded bg-white">
    <h5>Tokens (<?php echo date('F j, Y, g:i a', strtotime($selected_date . ' 07:00')); ?> →
      <?php echo date('F j, Y, g:i a', strtotime($selected_date . ' +1 day 07:00')); ?>)</h5>
    <div style="overflow-x: auto;">
      <?php if (mysqli_num_rows($tokens) > 0): ?>
      <table class="table table-head-bg-success">
        <thead>
          <tr>
            <th>ID</th>
            <th>Token</th>
            <th>Inserted Time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($token = mysqli_fetch_assoc($tokens)) {
      $formatted_time = date('F j, Y, g:i a', strtotime($token['insert_time']));
    ?>
          <tr>
            <td><?php echo htmlspecialchars($token['id']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($token['live_token'])); ?></td>
            <td><?php echo htmlspecialchars($formatted_time); ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this token?');">
                <button type="submit" name="delete_token" value="<?php echo $token['id']; ?>"
                  class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>

      <?php else: ?>
      <p class="text-danger">No tokens found for this date range.</p>
      <?php endif; ?>
    </div>
  </div>
</div>