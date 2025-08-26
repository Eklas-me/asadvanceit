<?php
if (isset($_POST['submit_all_token'])) {
    // Include session data in the $_POST array
    $_POST['user_id'] = $_SESSION['userId'] ?? null;
    $_POST['user_name'] = $_SESSION['userName'] ?? null;
    $_POST['user_role'] = $_SESSION['userRole'] ?? null;

    // Call the add_live_token method with all tokens in one go
    $add_live_token = $obj->add_live_token($_POST);
}

if (isset($add_live_token)): ?>
<div
  class="alert <?php echo strpos($add_live_token, 'Duplicate token') !== false ? 'alert-danger' : 'alert-success'; ?>"
  id="alertMessage">
  <?php echo htmlspecialchars($add_live_token); ?>
</div>
<script>
// Auto hide alert after 3 seconds
setTimeout(() => document.getElementById('alertMessage').style.display = 'none', 3000);
</script>
<?php endif; ?>

<h4>Add Today Tokens</h4>
<div class="row">
  <div class="col-md-12 col-lg-12">
    <form method="POST" action="" onsubmit="return validateTokens()">
      <div class="form-group">
        <textarea name="tinder_token" class="form-control" rows="10" cols="50" id="tinder_token" required
          placeholder="Enter one token at a time / প্রতিবার একটি করে টোকেন জমা দিবেন। "></textarea>
      </div>

      <div class="col-md-12 text-center">
        <button id="submitBtn" type="submit" name="submit_all_token" class="btn btn-primary">Submit</button>
        <div id="loading" style="display:none;">Processing...</div>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
  document.getElementById('submitBtn').disabled = true;
  document.getElementById('loading').style.display = 'block';
});

function validateTokens() {
  const tokenInput = document.getElementById('tinder_token').value;
  const tokens = tokenInput.split('\n').map(token => token.trim()).filter(token => token !== '');

  const uniqueTokens = new Set(tokens); // Use Set to filter out duplicates

  // Check if there are any empty tokens or duplicates
  if (tokens.length !== uniqueTokens.size) {
    alert('Duplicate tokens found! Please enter unique tokens only.');
    return false; // Prevent form submission
  }

  if (tokens.length === 0) {
    alert('Please enter at least one valid token.');
    return false; // Prevent form submission
  }

  // Optionally, add a maximum token count check
  if (tokens.length > 100) { // Example: limit to 100 tokens
    alert('Please enter a maximum of 100 tokens.');
    return false; // Prevent form submission
  }

  return true; // Allow form submission
}
</script>