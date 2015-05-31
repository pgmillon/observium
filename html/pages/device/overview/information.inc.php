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
<div class="well info_box">
    <div class="title"><i class="oicon-server"></i> Device Information</div>
    <div class="content">
<?php

if ($config['overview_show_sysDescr'])
{
  echo('<div style="font-family: courier, serif; margin: 3px"><strong>' . $device['sysDescr'] . "</strong></div>");
}

if ($device['os'] == "ios") { formatCiscoHardware($device); } // FIXME or do this in a general function for all OS types with a switch($device['os']) ?

echo('<table class="table table-condensed-more table-striped table-bordered">');

if ($device['purpose'])
{
  echo('<tr>
        <td class="entity">Description</td>
        <td>' . htmlspecialchars($device['purpose']) . '</td>
      </tr>');
}

if ($device['hardware'])
{
  echo('<tr>
        <td class="entity">Hardware</td>
        <td>' . htmlspecialchars($device['hardware']) . '</td>
      </tr>');
}

if ($device['os'] != 'generic')
{
  echo('<tr>
        <td class="entity">Operating System</td>
        <td>' . htmlspecialchars($device['os_text']) . ' ' . htmlspecialchars($device['version']) . ($device['features'] ? ' (' . htmlspecialchars($device['features']) . ')' : '') . ' </td>
      </tr>');
}

if ($device['asset_tag'])
{
  echo('<tr>
        <td class="entity">Asset tag</td>
        <td>' . htmlspecialchars($device['asset_tag']) . '</td>
      </tr>');
}

if ($device['serial'])
{
  echo('<tr>
        <td class="entity">Serial</td>
        <td>' . htmlspecialchars($device['serial']) . '</td>
      </tr>');
}

if ($device['sysContact'])
{
  echo('<tr>
        <td class="entity">Contact</td>');
  if (get_dev_attrib($device,'override_sysContact_bool'))
  {
    echo('
        <td>' . htmlspecialchars(get_dev_attrib($device,'override_sysContact_string')) . '</td>
      </tr>
      <tr>
        <td class="entity">SNMP Contact</td>');
  }
  echo('
        <td>' . htmlspecialchars($device['sysContact']). '</td>
      </tr>');
}

if ($device['location'])
{
  echo('<tr>
        <td class="entity">Location</td>
        <td>' . htmlspecialchars($device['location']) . '</td>
      </tr>');
  if (get_dev_attrib($device,'override_sysLocation_bool') && !empty($device['real_location']))
  {
    echo('<tr>
        <td class="entity">SNMP Location</td>
        <td>' . htmlspecialchars($device['real_location']) . '</td>
      </tr>');
  }
}

if ($device['uptime'])
{
  echo('<tr>
        <td class="entity">Uptime</td>
        <td>' . deviceUptime($device) . '</td>
      </tr>');
}

echo("</table>");
echo("</div></div>");

// EOF
