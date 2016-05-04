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

$vsvrs = dbFetchRows("SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? ORDER BY `vsvr_label`", array($device['device_id']));

#print_r($vsvrs);

if ($vars['submit'] == "update-vsvrs")
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    foreach ($vsvrs as $vsvr)
    {
      if ($vars['vsvrs'][$vsvr['vsvr_id']]['vsvr_ignore'] == 'on' || $vars['vsvrs'][$vsvr['vsvr_id']]['vsvr_ignore'] == '1') { $vars['vsvrs'][$vsvr['vsvr_id']]['vsvr_ignore'] = "1"; } else { $vars['vsvrs'][$vsvr['vsvr_id']]['vsvr_ignore'] = "0"; }

      foreach (array('vsvr_ignore','vsvr_limit_low','vsvr_limit') as $field)
      {
        if ($vars['vsvrs'][$vsvr['vsvr_id']][$field]    != $vsvr[$field])    { $sup[$field] = $vars['vsvrs'][$vsvr['vsvr_id']][$field]; }
      }

      if (is_array($sup))
      {
        dbUpdate($sup, 'netscaler_vservers', '`vsvr_id` = ?', array($vsvr['vsvr_id']));
        $did_update = TRUE;
      }
      unset($sup);
    }

    $vsvrs = dbFetchRows("SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? ORDER BY `vsvr_label`", array($device['device_id']));
  }
}

?>

<form id='update-vsvrs' name='update-vsvrs' method='post' action=''>
<fieldset>
  <legend>Netscaler vServer Properties</legend>

<table class="table  table-striped table-condensed">
  <thead>
    <tr>
      <th style="width: 120px;">MIB Type</th>
      <th>Name</th>
      <th style="width: 60px;">Status</th>
      <th style="width: 50px;">Alerts</th>
    </tr>
  </thead>
  <tbody>

<?php
$row=1;
foreach ($vsvrs as $vsvr)
{

  echo('<tr>');
  echo('<td>'.escape_html($vsvr['vsvr_type']).'</td>');
  echo('<td>'.escape_html($vsvr['vsvr_label']).'</td>');
  echo('<td>'.escape_html($vsvr['vsvr_state']).'</td>');
  echo('<td>
          <input type=checkbox data-toggle="switch-revert" id="vsvrs['.$vsvr['vsvr_id'].'][vsvr_ignore]" name="vsvrs['.$vsvr['vsvr_id'].'][vsvr_ignore]"'.($vsvr['vsvr_ignore'] ? "checked" : "").'>
        </td>');
  echo('</tr>');
}
?>

</tbody>
</table>
</fieldset>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" name="submit" value="update-vsvrs"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>
</form>
<?php

// EOF
