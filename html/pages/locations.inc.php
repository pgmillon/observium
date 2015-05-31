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

$pagetitle[] = "Locations";

if (!$vars['view']) { $vars['view'] = "basic"; }

$navbar['brand'] = 'Locations';
$navbar['class'] = 'navbar-narrow';

foreach (array('basic', 'traffic') as $type)
{
  if ($vars['view'] == $type) { $navbar['options'][$type]['class'] = 'active'; }
  $navbar['options'][$type]['url'] = generate_url(array('page' => 'locations', 'view' => $type));
  $navbar['options'][$type]['text'] = ucfirst($type);
}
print_navbar($navbar);
unset($navbar);

echo('<table class="table table-hover table-bordered table-striped table-condensed table-rounded">');

foreach (get_locations() as $location)
{
  if ($_SESSION['userlevel'] == '10')
  {
    $num = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ?", array($location));
    $net = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'network'", array($location));
    $srv = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'server'", array($location));
    $fwl = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND type = 'firewall'", array($location));
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices WHERE location = ? AND status = '0'", array($location));
  } else {
    $num = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ?", array($_SESSION['user_id'], $location));
    $net = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND D.type = 'network'", array($_SESSION['user_id'], $location));
    $srv = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND type = 'server'", array($_SESSION['user_id'], $location));
    $fwl = dbFetchCell("SELECT COUNT(D.device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND type = 'firewall'", array($_SESSION['user_id'], $location));
    $hostalerts = dbFetchCell("SELECT COUNT(device_id) FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? AND location = ? AND status = '0'", array($_SESSION['user_id'], $location));
  }

  if ($hostalerts) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }

  $value = base64_encode(json_encode(array($location)));
  $name  = ($location == '' ? '[[UNKNOWN]]' : htmlspecialchars($location));
  echo('      <tr class="locations">
           <td class="interface" style="width: 300px;">' . generate_link($name, array('page' => 'devices', 'location' => $value)) . '</td>
           <td style="width: 100px;">' . $alert . '</td>
           <td style="width: 100px;">' . $num . ' devices</td>
           <td style="width: 100px;">' . $net . ' networks</td>
           <td style="width: 100px;">' . $srv . ' servers</td>
           <td style="width: 100px;">' . $fwl . ' firewalls</td>
         </tr>
       ');

  if ($vars['view'] == "traffic")
  {
    echo('<tr></tr><tr class="locations"><td colspan="6">');

    $graph_array['type']   = "location_bits";
    $graph_array['height'] = "100";
    $graph_array['width']  = "220";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['legend'] = "no";
    $graph_array['id']     = $location;

    print_graph_row($graph_array);

    echo("</tr></td>");
  }
  $done = "yes";
}

echo("</table>");

// EOF
