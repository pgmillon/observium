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

?>
    <div class="box box-solid">
      <div class="box-body no-padding">

<?php

if ($device['os'] == "ios") { formatCiscoHardware($device); } // FIXME or do this in a general function for all OS types with a switch($device['os']) ?

echo('<table class="table table-condensed table-striped table-hover">');

if ($config['overview_show_sysDescr'])
{
  echo '<tr>';
  echo '<td colspan=2 style="padding: 10px;">';

  if (is_file($config['html_dir'] . '/images/hardware/' . trim($device['sysObjectID'], ".") . '.png')) {
    // echo '<img style="height: 100px; float: right;" src="'.$config['site_url'] . '/images/hardware/' . trim($device['sysObjectID'], ".") . '.png'.'"></img>';
  }
  echo '<strong><i>' . escape_html($device['sysDescr']) . '</i></strong></td></tr>';
}

if ($device['purpose'])
{
  echo('<tr>
        <td class="entity">Description</td>
        <td>' . escape_html($device['purpose']) . '</td>
      </tr>');
}

if ($device['hardware'])
{
  echo('<tr>
        <td class="entity">Hardware</td>
        <td>' . escape_html($device['hardware']) . '</td>
      </tr>');
}

if ($device['os'] != 'generic')
{
  echo('<tr>
        <td class="entity">Operating system</td>
        <td>' . escape_html($device['os_text']) . ' ' . escape_html($device['version']) . ($device['features'] ? ' (' . escape_html($device['features']) . ')' : '') . ' </td>
      </tr>');
}

if ($device['sysName'])
{
  echo('<tr>
        <td class="entity">System name</td>');
  echo('
        <td>' . escape_html($device['sysName']). '</td>
      </tr>');
}

if ($device['sysContact'])
{
  echo('<tr>
        <td class="entity">Contact</td>');
  if (get_dev_attrib($device,'override_sysContact_bool'))
  {
    echo('
        <td>' . escape_html(get_dev_attrib($device,'override_sysContact_string')) . '</td>
      </tr>
      <tr>
        <td class="entity">SNMP Contact</td>');
  }
  echo('
        <td>' . escape_html($device['sysContact']). '</td>
      </tr>');
}

if ($device['location'])
{
  echo('<tr>
        <td class="entity">Location</td>
        <td>' . escape_html($device['location']) . '</td>
      </tr>');
  if (get_dev_attrib($device,'override_sysLocation_bool') && !empty($device['real_location']))
  {
    echo('<tr>
        <td class="entity">SNMP Location</td>
        <td>' . escape_html($device['real_location']) . '</td>
      </tr>');
  }
}

if ($device['asset_tag'])
{
  echo('<tr>
        <td class="entity">Asset tag</td>
        <td>' . escape_html($device['asset_tag']) . '</td>
      </tr>');
}

if ($device['serial'])
{
  echo('<tr>
        <td class="entity">Serial</td>
        <td>' . escape_html($device['serial']) . '</td>
      </tr>');
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
