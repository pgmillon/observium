<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($vars['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $param = array('icon' => $vars['icon']);

    $rows_updated = dbUpdate($param, 'devices', '`device_id` = ?', array($device['device_id']));

    if ($rows_updated > 0 || $updated)
    {
      $update_message = "Device icon updated.";
      $updated = 1;
      $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
    }
    else if ($rows_updated = '-1')
    {
      $update_message = "Device icon unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device icon update error.";
    }
  } else {
    include("includes/error-no-perm.inc.php");
  }
}

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

?>

<h3>Device icon</h3>

<table cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <form id="edit" name="edit" method="post" action="">
        <input type="hidden" name="editing" value="yes">
        <table border="0">
<?php

$numicons = 1;
echo("          <tr>\n");

// Default icon
$icon = get_device_icon($device, TRUE);

echo('            <td width="64" align="center"><img src="images/os/' . $icon . '.png"><br /><i>' . nicecase($icon) . '</i><p />');
echo('<input name="icon" type="radio" value="' . $icon . '"' . ($device['icon'] == '' || $device['icon'] == $icon ? ' checked="1"' : '') . ' /></td>' . "\n");

foreach ($config['os'][$device['os']]['icons'] as $icon_new)
{
  if ($icon_new != $icon)
  {
    echo('            <td align="center"><img src="images/os/' . $icon_new . '.png"><br /><i>' . ucwords(strtr($icon_new, '_', ' ')) . '</i><p />');
    echo('<input name="icon" type="radio" value="' . $icon_new . '"' . ($device['icon'] == $icon ? ' checked="1"' : '') . ' /></td>' . "\n");
    $numicons++;
  }
}

if ($numicons %10 == 0)
{
  echo("          </tr>\n");
  echo("          <tr>\n");
}
?>
          </tr>
          <tr>
            <td colspan="10">
              <br />
              <input type="submit" name="Submit" value="Save" />
            </td>
          </tr>
        </table>
        <br />
      </form>
    </td>
    <td width="50"></td>
    <td></td>
  </tr>
</table>
<?php

// EOF
