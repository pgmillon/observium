<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$svcs = dbFetchRows("SELECT * FROM `netscaler_services` WHERE `device_id` = ? ORDER BY `svc_name`", array($device['device_id']));

#print_vars($svcs);

if ($vars['submit'] == "update-svcs")
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    foreach ($svcs as $svc)
    {
      if ($vars['svcs'][$svc['svc_id']]['svc_ignore'] == 'on' || $vars['svcs'][$svc['svc_id']]['svc_ignore'] == '1') { $vars['svcs'][$svc['svc_id']]['svc_ignore'] = "1"; } else { $vars['svcs'][$svc['svc_id']]['svc_ignore'] = "0"; }

      foreach (array('svc_ignore','svc_limit_low','svc_limit') as $field)
      {
        if ($vars['svcs'][$svc['svc_id']][$field]    != $svc[$field])    { $sup[$field] = $vars['svcs'][$svc['svc_id']][$field]; }
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
}

?>

<form id='update-svcs' name='update-svcs' method='post' action=''>
<fieldset>
  <legend>Netscaler Service Properties</legend>

<table class="table  table-striped table-condensed">
  <thead>
    <tr>
      <th style="width: 120px;">Type</th>
      <th>Name</th>
      <th style="width: 120px;">Status</th>
      <th style="width: 80px;">Alerts</th>
    </tr>
  </thead>
  <tbody>

<?php
$row=1;
foreach ($svcs as $svc)
{

  echo('<tr>');
  echo('<td>'.escape_html($svc['svc_type']).'</td>');
  echo('<td>'.escape_html($svc['svc_label']).'</td>');
  echo('<td>'.escape_html($svc['svc_state']).'</td>');
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
