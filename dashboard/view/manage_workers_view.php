<?php 
$display_users = $obj->display_all_users();

if(isset($_GET['status']) && $_GET['status'] == 'workerDelete') {
  $worker_id = intval($_GET['id']);
  $delete_worker = $obj->delete_worker($worker_id);
}

if (isset($delete_worker)): ?>
<div class="alert alert-success" id="alertMessage">
  <?php echo $delete_worker; ?>
</div>
<?php endif;

?>
<div class="card">
  <div class="card-header">
    <div class="card-title">Manage Workers</div>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          
          <th scope="col">Name</th>
          <th scope="col">Phone</th>
          <th scope="col">Date</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($worker = mysqli_fetch_assoc($display_users)) { ?>
        <!-- Assuming you have a query to fetch workers -->
        <tr>
          
          <td><?php echo $worker['name']; ?></td>
          <td>
            <div class="avatar">
              <img src="uploads/<?php echo $worker['profile_photo']; ?>" alt="Profile Image" class="avatar-img rounded">
            </div>
          </td>
          <td><?php echo $worker['phone']; ?></td>
          <td>
            <?php 
                  $createdAt = new DateTime($worker['created_at']);
                  echo $createdAt->format('M d, Y'); 
                  ?>
          </td>
          <td>
            <a href="edit_workers.php?status=workerEdit&id=<?php echo $worker['id']; ?>" class="btn btn-info btn-sm">
              <i class="fas fa-edit"></i> Edit
            </a>
            <a href="?status=workerDelete&id=<?php echo $worker['id']; ?>" class="btn btn-danger btn-sm"
              onclick="return confirm('Are you sure you want to delete this worker?');">
              <i class="fas fa-trash-alt"></i> Delete
            </a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>