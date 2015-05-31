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

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

$pagetitle[] = "Delete devices";

if (is_numeric($vars['id']))
{
  $device = device_by_id_cache($vars['id']);
  if ($device && $vars['confirm'])
  {
    $delete_rrd = ($vars['deleterrd'] == 'confirm') ? TRUE : FALSE;
    print_warning(delete_device($vars['id'], $delete_rrd));
    //echo('<div class="btn-group ">
    //        <button type="button" class="btn btn-default"><a href="/"><i class="oicon-globe-model"></i> Overview</a></button>
    //        <button type="button" class="btn btn-default"><a href="/devices/"><i class="oicon-servers"></i> Devices List</a></button>
    //      </div>');
  } else {
    print_warning("Are you sure you want to delete device <strong>" . $device['hostname'] . "</strong>?");
?>
<br />
<form name="form1" method="post" action="" class="form-horizontal" >
  <input type="hidden" name="id" value="<?php echo $vars['id'] ?>" />
  <input type="hidden" name="confirm" value="1" />
  <!--<input type="submit" class="submit" name="Submit" value="Confirm host deletion" />-->
  <button type="submit" class="btn btn-danger"><i class="icon-remove icon-white"></i> Delete Device</button>
</form>

<?php
  }
} else {
?>

<form name="form1" method="post" action="" class="form-horizontal" >

  <script type="text/javascript">
    function showWarning(checked) {
      $('#warning').toggle();
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
      <label class="control-label" for="id">Device</label>
      <div class="controls">
        <select class="selectpicker" name="id">
<?php
foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `disabled` DESC, `hostname`") as $data)
{
  $status = '';
  if ($data['disabled'])
  {
    $status = ' [disabled]';
  }
  else if (!$data['status'])
  {
    $status = ' [down]';
  }
  echo("<option value='".$data['device_id']."'>".$data['hostname'].$status."</option>");
}
?>
        </select>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label">Delete RRDs</label>
      <div class="controls">
        <input type="checkbox" name="deleterrd" value="confirm" onchange="javascript: showWarningRRD(this.checked);">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="id">Confirm Deletion</label>
      <div class="controls">
        <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning(this.checked);">
      </div>
    </div>
  </fieldset>

  <div class="form-actions">
    <button id="deleteBtn" type="submit" class="btn btn-danger" disabled="disabled"><i class="icon-remove icon-white"></i> Delete Device</button>
  </div>

</form>
<?php
}

// EOF
