<?php 

if(isset($_GET['status']) && $_GET['status'] == 'workerEdit') {
  $worker_id = ($_GET['id']);
  $edit_workers = $obj->display_worker_by_id($worker_id);
}

// Handle form submission and update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_workers_btn'])) {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $worker_id = $edit_workers['id'];

    // Handle file upload for profile photo
    if (!empty($_FILES['profile_photo']['name'])) {
        // Only store the file name, not the full path
        $target_dir = "uploads/";
        $profile_photo_name = basename($_FILES["profile_photo"]["name"]);
        $profile_photo_path = $target_dir . $profile_photo_name;

        // Move uploaded file to the uploads folder
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo_path);
    } else {
        // If no file is uploaded, use the existing photo name from the database
        $profile_photo_name = $edit_workers['profile_photo'];
    }

    // Update worker information
    $update_result = $obj->update_worker_by_id($worker_id, $name, $email, $phone, $profile_photo_name);

    // Show success message and redirect
    if ($update_result) {
        $update_users = "Worker updated successfully!";
        // Set a session variable to indicate success message
        $_SESSION['success_message'] = $update_users;
        // Redirect to another page after displaying success message
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'manage_workers.php'; // Change this to the page you want to redirect to
                }, 500); // Wait for 2 seconds before redirecting
              </script>";
    } else {
        $update_users = "Error updating worker.";
    }
}
?>

<!-- Display success or error message -->
<?php if (isset($update_users)): ?>
<div class="alert alert-success" id="alertMessage">
  <?php echo $update_users; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h4 class="fw-bold">Update Workers</h4>
  </div>
  <div class="card-body">
    <form action="" method="POST" enctype="multipart/form-data">
      <!-- Name Field -->
      <div class="form-group mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo $edit_workers['name']; ?>"
          required>
      </div>

      <!-- Email Field -->
      <div class="form-group mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" value="<?php echo $edit_workers['email']; ?>"
          required>
      </div>

      <!-- Phone Field (Optional) -->
      <div class="form-group mb-3">
        <label for="phone" class="form-label">Phone (Optional)</label>
        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $edit_workers['phone']; ?>">
      </div>

      <!-- Profile Photo Field (Optional) -->
      <div class="form-group mb-3">
        <label for="profile_photo" class="form-label">Profile Photo (Optional)</label>
        <input type="file" name="profile_photo" id="profile_photo" class="form-control">
        <?php if (!empty($edit_workers['profile_photo'])): ?>
        <div class="mt-2">
          <img src="uploads/<?php echo $edit_workers['profile_photo']; ?>" alt="Current Profile Photo" width="100">
        </div>
        <?php endif; ?>
      </div>

      <!-- Submit Button -->
      <div class="form-group">
        <button type="submit" name="update_workers_btn" class="btn btn-primary w-100">Update Workers</button>
      </div>
    </form>
  </div>
</div>