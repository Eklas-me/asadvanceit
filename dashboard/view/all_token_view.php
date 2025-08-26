<?php
// -------------------- Backend Logic --------------------
// Fetch selected user and datetime range from POST
$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
$from_datetime = !empty($_POST['from_datetime']) ? $_POST['from_datetime'] : date('Y-m-d 07:00');
$to_datetime   = !empty($_POST['to_datetime']) ? $_POST['to_datetime'] : date('Y-m-d 07:00', strtotime('+1 day'));

// Sanitize
$user_id = htmlspecialchars($user_id);
$from_datetime = htmlspecialchars($from_datetime);
$to_datetime = htmlspecialchars($to_datetime);

// Fetch tokens
$tokens = $obj->display_all_tokens($user_id, $from_datetime, $to_datetime);
$workers = $obj->display_workers();

// Export if requested
if (isset($_POST['export'])) {
    $filePath = $obj->export_tokens_to_text($user_id, $from_datetime, $to_datetime);
    if ($filePath) {
        echo "<p>Tokens exported successfully. <a href='$filePath' download>Download here</a></p>";
    } else {
        echo "<p class='text-danger'>Failed to export tokens.</p>";
    }
}
?>

<h4>All Tokens</h4>
<div class="row">
  <form method="POST" class="d-flex align-items-center justify-content-center">
    <!-- Worker Selection -->
    <div class="col-md-3">
      <div class="form-group">
        <label for="largeSelect">Select Worker</label>
        <select name="user_id" class="form-select form-control-lg" id="largeSelect">
          <option value="">Show All</option>
          <?php while ($worker = mysqli_fetch_assoc($workers)): ?>
          <option value="<?php echo htmlspecialchars($worker['id']); ?>"
            <?php echo ($user_id == $worker['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($worker['name']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- From Datetime -->
    <div class="col-md-3">
      <div class="form-group">
        <label for="from_datetime">From</label>
        <input type="datetime-local" class="form-control" name="from_datetime" id="from_datetime"
          value="<?php echo date('Y-m-d\TH:i', strtotime($from_datetime)); ?>" required>
      </div>
    </div>

    <!-- To Datetime -->
    <div class="col-md-3">
      <div class="form-group">
        <label for="to_datetime">To</label>
        <input type="datetime-local" class="form-control" name="to_datetime" id="to_datetime"
          value="<?php echo date('Y-m-d\TH:i', strtotime($to_datetime)); ?>" required>
      </div>
    </div>

    <!-- Submit Buttons -->
    <div class="col-md-3 mt-4">
      <div class="form-group d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Filter</button>
        <button type="submit" name="export" class="btn btn-primary mx-3">Export</button>
      </div>
    </div>
  </form>
</div>

<!-- Tokens Display -->
<div class="row mt-4">
  <div class="col-md-12">
    <?php if (mysqli_num_rows($tokens) > 0): ?>
    <?php 
        $usersTokens = [];
        $totalTokens = 0; // Count variable
        while ($row = mysqli_fetch_assoc($tokens)) {
            $userName = $row['user_name'];
            $token = $row['live_token'];
            $usersTokens[$userName][] = $token;
            $totalTokens++; // Count increment
        }
    ?>

    <!-- Show total count -->
    <div class="alert alert-info text-center mb-3">
      Total Tokens Found: <strong><?php echo $totalTokens; ?></strong>
    </div>

    <div class="tokens-wrapper <?php echo (count($usersTokens) === 1) ? 'single-column' : ''; ?>">
      <?php foreach ($usersTokens as $userName => $tokens): ?>
      <div class="user-tokens-column">
        <h5 class="bg-secondary text-white p-2">
          <?php echo htmlspecialchars($userName); ?>
          <button class="btn btn-link"
            onclick="copyTokens('<?php echo implode('\\n', array_map('htmlspecialchars', $tokens)); ?>')">
            <i class="far fa-copy"></i>
          </button>
        </h5>
        <?php foreach ($tokens as $token): ?>
        <div class="token-entry">
          <span><?php echo htmlspecialchars($token); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-danger text-center">No tokens found for this range.</p>
    <?php endif; ?>
  </div>
</div>

<script>
function copyTokens(tokens) {
  const textarea = document.createElement('textarea');
  textarea.value = tokens;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  document.body.removeChild(textarea);
  alert('Tokens copied to clipboard!');
}
</script>

<style>
.tokens-wrapper {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  overflow-x: auto;
  max-height: 100%;
}

.tokens-wrapper.single-column {
  grid-template-columns: 1fr;
}

.user-tokens-column {
  padding: 10px;
  overflow-y: auto;
  border: 1px solid #ddd;
  max-height: 100%;
  box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
  margin-bottom: 5px;
}

.token-entry {
  padding: 2px 0;
  white-space: nowrap;
}

.btn-link {
  margin-left: 10px;
  color: white;
  text-decoration: none;
}

.btn-link:hover {
  text-decoration: underline;
}

h5.bg-secondary.text-white.p-2 {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>