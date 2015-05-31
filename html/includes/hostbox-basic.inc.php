<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// FIXME - This view seems almost pointless. What should we do?

unset($row_class);

if ($device['status'] == '0')
{
  $class = "entity-title red";
  $row_class = "error";
} else {
  $class = "entity-title";
}
if ($device['ignore'] == '1')
{
  $class = "entity-title gray";
  if ($device['status'] == '1')
  {
    $class = "entity-title green";
  }
}
if ($device['disabled'] == '1')
{
  $class = "entity-title";
}

$type = strtolower($device['os']);

if ($device['os'] == "ios") { formatCiscoHardware($device, true); }
$device['os_text'] = $config['os'][$device['os']]['text'];

echo('  <tr class="'.$row_class.'" bgcolor="' . $bg . '" onmouseover="this.style.backgroundColor=\'#fdd\';" onmouseout="this.style.backgroundColor=\'' . $bg . '\';"
          onclick="location.href=\'device/'.$device['device_id'].'/\'" style="cursor: pointer;">
          <td style="width: 300;"><span class="'.$class.'">' . generate_device_link($device) . '</span></td>'
        );

echo('    <td>' . $device['hardware'] . ' ' . $device['features'] . '</td>');
echo('    <td>' . $device['os_text'] . ' ' . $device['version'] . '</td>');
echo('    <td>' . deviceUptime($device, 'short') . ' <br />');

echo('    ' . htmlspecialchars(truncate($device['location'],32, '')) . '</td>');

echo(' </tr>');

// EOF
