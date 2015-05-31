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

$svcs = dbFetchRows("SELECT * FROM `netscaler_services` WHERE `device_id` = ? ORDER BY `svc_name`", array($device['device_id']));

#print_vars($svcs);

if ($_POST['submit'] == "update-svcs" && $_SESSION['userlevel'] == '10')
{
  foreach ($svcs AS $svc)
  {
    if ($_POST['svcs'][$svc['svc_id']]['svc_ignore'] == "on") { $_POST['svcs'][$svc['svc_id']]['svc_ignore'] = "1"; } else { $_POST['svcs'][$svc['svc_id']]['svc_ignore'] = "0"; }

    foreach (array('svc_ignore','svc_limit_low','svc_limit') as $field)
    {
      if ($_POST['svcs'][$svc['svc_id']][$field]    != $svc[$field])    { $sup[$field] = $_POST['svcs'][$svc['svc_id']][$field]; }
    }

    if (is_array($sup))
    {
      dbUpdate($sup, 'netscaler_services', '`svc_id` = ?', array($svc['svc_id']));
      $did_update = TRUE;
    }
    unset($sup);
  }

  $svcs = dbFetchRows("SELECT * FROM `netscaler_services` WHERE `device_id` = ? ORDER BY `svc_label`", array($device['device_id']));
}

#print_vars($_POST);

?>

<form id='update-svcs' name='update-svcs' method='post' action=''>
<fieldset>
  <legend>Netscaler Service Properties</legend>

<table class="table table-bordered table-striped table-condensed">
  <thead>
    <tr>
      <th width="120">Type</th>
      <th>Name</th>
      <th width="120">Status</th>
      <th width="80">Alerts</th>
    </tr>
  </thead>
  <tbody>

<?php
$row=1;
foreach ($svcs as $svc)
{

  echo('<tr>');
  echo('<td>'.htmlentities($svc['svc_type']).'</td>');
  echo('<td>'.htmlentities($svc['svc_label']).'</td>');
  echo('<td>'.htmlentities($svc['svc_state']).'</td>');
  echo('<td>
          <input type=checkbox data-toggle="switch-revert" id="svcs['.$svc['svc_id'].'][svc_ignore]" name="svcs['.$svc['svc_id'].'][svc_ignore]"'.($svc['svc_ignore'] ? "checked" : "").'>
        </td>');
  echo('</tr>');
}
?>

</tbody>
</table>
</fieldset>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" name="submit" value="update-svcs"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>
</form>
<?php

// EOF
