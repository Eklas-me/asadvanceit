<?php
// $display_user = $obj->display_user();
$display_admin = $obj->display_admin();
?>

<div class="page-header">
  <h3 class="fw-bold mb-3">List of Admins</h3>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="basic-datatables" class="display table table-striped table-hover">
            <thead>
              <tr>
                
                <th>Name</th>
                <th>Phone</th>
                <th>Profile Image</th>
                <th>Role</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>

              <!-- Loop through admins -->
              <?php while ($show_admin = mysqli_fetch_assoc($display_admin)) { ?>
              <tr>
                
                <td><?php echo $show_admin['name']; ?></td>
                <td><?php echo $show_admin['phone']; ?></td>
                <td>
                  <div class="avatar">
                    <img src="uploads/<?php echo $show_admin['profile_photo']; ?>" alt="Profile Image"
                      class="avatar-img rounded-circle"">
                  </div>

                </td>
                <td>
                  <span
                    style=" background-color: green; color: white; padding: 3px 6px; border-radius: 5px;">Admin</span>
                </td> <!-- Display 'Admin' with a green background -->
                <td>
                  <?php 
                  $createdAt = new DateTime($show_admin['created_at']);
                  echo $createdAt->format('M d, Y'); 
                  ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>