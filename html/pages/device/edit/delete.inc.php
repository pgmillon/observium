<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<form id="delete_host" name="delete_host" method="post" action="delhost/"  class="form-horizontal">
  <input type="hidden" name="id" value="<?php echo($device['device_id']); ?>">

  <script type="text/javascript">
    function showWarning(checked) {
      //$('#warning').toggle();
      if (checked) {
        $('#deleteBtn').removeAttr('disabled');
      } else {
        $('#deleteBtn').attr('disabled', 'disabled');
      }
    }
    function showWarningRRD(checked) {
      if (checked) {
        $('.alert').hide();
      } else {
        $('.alert').show();
      }
    }
  </script>

  <fieldset>
    <legend>Delete device</legend>
<?php
  print_warning("<h4>Warning!</h4>
      This will delete this device from Observium including all logging entries, but will not delete the RRDs.");
?>

    <div class="control-group">
      <label class="control-label">Delete RRDs</label>
      <div class="controls">
        <input type="checkbox" name="deleterrd" value="confirm" onchange="javascript: showWarningRRD(this.checked);">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="sysContact">Confirm Deletion</label>
      <div class="controls">
        <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning(this.checked);">
      </div>
    </div>

    <div class="form-actions">
      <button id="deleteBtn" type="submit" class="btn btn-danger" name="delete" disabled="disabled"><i class="icon-remove icon-white"></i> Delete device</button>
    </div>
  </fieldset>
</form>

<?php

// EOF
