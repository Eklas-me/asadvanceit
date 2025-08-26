<?php

if (isset($_POST['notification_submit'])) {
  $notification_success =$obj->add_notification($_POST);
}

?>

<!-- Display the message -->
<?php if (isset($notification_success)): ?>
<div class="alert alert-success" id="alertMessage">
  <?php print_r($notification_success); // Check what $notification contains ?>
</div>
<?php endif; ?>


<div class="row">
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">Add Notification</h4>
    </div>
    <div class="card-body">
      <form action="" method="post">
        <div class="row">

          <div class="col-md-12">
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" name="notification_title" id="title" required
                placeholder="Enter title">
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label for="message">Message</label>
              <textarea class="form-control" id="message" name="message" rows="4" required
                placeholder="Enter message"></textarea>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <button type="submit" name="notification_submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>