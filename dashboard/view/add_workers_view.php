<?php
if (isset($_POST['add_workers_btn'])) {
  $add_workers = $obj->add_workers($_POST);
}
?>

<!-- Display the message -->
<?php if (isset($add_workers)): ?>
<div class="alert alert-success" id="alertMessage">
  <?php echo $add_workers; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h4 class="fw-bold">Add Workers</h4>
  </div>
  <div class="card-body">
    <form action="" method="POST" enctype="multipart/form-data">
      <!-- Name Field -->
      <div class="form-group mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control" required placeholder="Enter full name">
      </div>

      <!-- Email Field -->
      <div class="form-group mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required placeholder="Enter email address">
      </div>

      <!-- Phone Field (Optional) -->
      <div class="form-group mb-3">
        <label for="phone" class="form-label">Phone (Optional)</label>
        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter phone number">
      </div>

      <!-- Password Field -->
      <!-- Password Field with Eye Icon -->
      <div class="form-group mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" required
            placeholder="Enter password">
          <span class="input-group-text">
            <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
          </span>
        </div>
      </div>


      <!-- Profile Photo Field (Optional) -->
      <div class="form-group mb-3">
        <label for="profile_photo" class="form-label">Profile Photo (Optional)</label>
        <input type="file" name="profile_photo" id="profile_photo" class="form-control">
      </div>

      <!-- Role (Default User) -->
      <div class="form-group mb-3">
        <label for="role" class="form-label">Role</label>
        <select name="role" id="role" class="form-control">
          <option value="user" selected>User</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <!-- Submit Button -->
      <div class="form-group">
        <button type="submit" name="add_workers_btn" class="btn btn-primary w-100">Add User</button>
      </div>
    </form>
  </div>
</div>

<script>
const alertMessage = document.getElementById('alertMessage');
if (alertMessage) {
  setTimeout(() => {
    alertMessage.style.display = 'none';
  }, 3000);
}
</script>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

togglePassword.addEventListener('click', function(e) {
  // Toggle the type attribute
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);

  // Toggle the eye icon
  this.classList.toggle('fa-eye-slash');
});
</script>