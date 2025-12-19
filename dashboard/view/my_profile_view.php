<?php 
// Fetch worker/admin details
$user_id = $_SESSION['userId'];
$user_role = $_SESSION['userRole']; // role: 'user' বা 'admin'
$edit_workers = $obj->display_workers_id($user_id, $user_role); // role অনুযায়ী fetch

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_btn'])) {
    
    // Get form data safely
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $worker_id = $user_id;

    // Default to existing profile photo
    $profile_photo_name = $edit_workers['profile_photo'];

    // Handle file upload if exists
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "./uploads/";
        $file_ext = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $safe_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($_FILES["profile_photo"]["name"], PATHINFO_FILENAME));
        $profile_photo_name = time() . '_' . $safe_name . '.' . $file_ext;
        $profile_photo_path = $target_dir . $profile_photo_name;

        if (!move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo_path)) {
            $profile_photo_name = $edit_workers['profile_photo']; // fallback
            echo "<div class='alert alert-danger'>Failed to upload profile photo!</div>";
        }
    }

    // Update in DB based on role
    $update_result = $obj->update_person_by_id($worker_id, $user_role, $name, $email, $phone, $profile_photo_name);

    if ($update_result) {
        echo "<div class='alert alert-success'>Profile updated successfully!</div>";
        // Refetch updated data
        $edit_workers = $obj->display_workers_id($user_id, $user_role);
    } else {
        echo "<div class='alert alert-danger'>Failed to update profile.</div>";
    }
}
?>

<div class="card">
  <div class="card-header">
    <h4 class="fw-bold">Update Details</h4>
  </div>
  <div class="card-body">
    <form action="" method="POST" enctype="multipart/form-data">
      <!-- Name -->
      <div class="form-group mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control"
          value="<?php echo htmlspecialchars($edit_workers['name']); ?>" required>
      </div>

      <!-- Email -->
      <div class="form-group mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
          value="<?php echo htmlspecialchars($edit_workers['email']); ?>" required>
      </div>

      <!-- Phone -->
      <div class="form-group mb-3">
        <label>Phone (Optional)</label>
        <input type="text" name="phone" class="form-control"
          value="<?php echo htmlspecialchars($edit_workers['phone']); ?>">
      </div>

      <!-- Profile Photo -->
      <div class="form-group mb-3">
        <label>Profile Photo (Optional)</label>
        <input type="file" name="profile_photo" class="form-control">
        <div class="mt-2">
          <?php if(!empty($edit_workers['profile_photo']) && file_exists('./uploads/'.$edit_workers['profile_photo'])): ?>
          <img src="./uploads/<?php echo htmlspecialchars($edit_workers['profile_photo']); ?>" width="100"
            class="rounded" alt="Profile Photo">
          <?php else: ?>
          <p>No profile photo available.</p>
          <?php endif; ?>
        </div>
      </div>

      <button type="submit" name="update_profile_btn" class="btn btn-primary w-100">Update Details</button>
    </form>
  </div>
</div>