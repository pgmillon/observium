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

$ports['total']    = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($device['device_id']));
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'up' OR `ifOperStatus` = 'monitoring')", array($device['device_id']));
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'lowerLayerDown' OR `ifOperStatus` = 'down')", array($device['device_id']));
$ports['disabled'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'down'", array($device['device_id']));

if ($ports['down']) { $ports_colour = $warn_colour_a; } else { $ports_colour = $list_colour_a; }

if ($ports['total'])
{
?>

<div class="box box-solid">
  <div class="box-header ">
    <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'ports'))); ?>">
      <i class="oicon-network-ethernet"></i><h3 class="box-title">Ports</h3>
    </a>
  </div>
  <div class="body-box no-padding">

<?php
  $graph_array['height'] = "100";
  $graph_array['width']  = "512";
  $graph_array['to']     = $config['time']['now'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['type']   = "device_bits";
  $graph_array['from']   = $config['time']['day'];
  $graph_array['legend'] = "no";
  $graph_array['style'] = array('width: 100%', 'max-width: 593px'); // Override default width
  $graph = generate_graph_tag($graph_array);
  unset($graph_array['style']);

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width']);
  $link = generate_url($link_array);

  $graph_array['width']  = "210";
  $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - Device Traffic");

  echo('<table class="table table-condensed table-striped ">
  <tr><td colspan=4>');

  echo(overlib_link($link, $graph, $overlib_content, NULL));

  echo('</td></tr>
    <tr style="background-color: ' . $ports_colour . '; align: center;">
      <td style="width: 25%; text-align: center;"><i class="oicon-node-select-all" title="Total Ports"></i> ' . $ports['total'] . '</td>
      <td style="width: 25%; text-align: center;" class="green"><i class="oicon-network-status" title="Up Ports"></i> ' . $ports['up'] . '</td>
      <td style="width: 25%; text-align: center;" class="red"><i class="oicon-network-status-busy" title="Down Ports"></i> ' . $ports['down'] . '</td>
      <td style="width: 25%; text-align: center;" class="grey"><i class="oicon-network-status-offline" title="Disabled Ports"></i> ' . $ports['disabled'] . '</td>
    </tr>');

  echo('<tr><td colspan=4 style="padding-left: 10px; font-size: 11px; font-weight: bold;">');

  /**
   * Start ports sorting based on port type, name and number
   * Full sort order:
   *  1. Port type (phisical ports always first)
   *  2. Port base name (TenGig, Gigabit, etc)
   *  3. Module number
   *  4. Port number
   *  5. Port subinterface number
   */
  // FIXME. Make function on this logic and use where required
  // Custom order for port types. See human type names here: $rewrite_iftype
  $port_types = array(
    'Ethernet',
    'L2 VLAN (802.1Q)',
    'L3 VLAN (IP)',
    'L3 VLAN (IPX)',
    'Virtual/Internal',
    'Tunnel',
    'Loopback',
    'Other',
  );
  $port_links = array();
  foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` != ?;", array($device['device_id'], '1')) as $data)
  {
    humanize_port($data);
    if (!in_array($data['human_type'], $port_types))
    {
      $port_types[] = $data['human_type'];
    }

    // Index example for TenGigabitEthernet3/10.324:
    //  $ports_links['Ethernet'][] = array('label_base' => 'TenGigabitEthernet', 'label_num0' => '3', 'label_num1' => '10', 'label_num2' => '324')
    $label_num  = preg_replace('![^\d\.\/]!', '', substr($data['port_label'], strlen($data['port_label_base']))); // Remove base part and all not-numeric chars
    preg_match('!^(\d+)(?:\/(\d+)(?:\.(\d+))*)*!', $label_num, $label_nums); // Split by slash and point (1/1.324)
    $ports_links[$data['human_type']][$data['ifIndex']] = array(
      'label'      => $data['port_label'],
      'label_base' => $data['port_label_base'],
      'label_num0' => $label_nums[0],
      'label_num1' => $label_nums[1],
      'label_num2' => $label_nums[2],
      'link'       => generate_port_link($data, $data['port_label_short'])
    );
  }
  // First sort iteration (by port type)
  $all_links = array();
  foreach ($port_types as $port_type)
  {
    if (!isset($ports_links[$port_type])) { continue; }
    // Second sort iteration (by port label base name and port numbers)
    $ports_links[$port_type] = array_sort_by($ports_links[$port_type], 'label_base', SORT_DESC, SORT_STRING,
                                                                       'label_num0', SORT_ASC,  SORT_NUMERIC,
                                                                       'label_num1', SORT_ASC,  SORT_NUMERIC,
                                                                       'label_num2', SORT_ASC,  SORT_NUMERIC);
    /* FIXME. This part not completed, wait ;)
    if ($port_type == 'Ethernet')
    {
      // Try to use ports template div
      $hw = strtolower(safename($device['hardware']));
      if (is_file('ports/'.$device['os'].'_'.$hw.'.inc.php'))
      {
        print_debug('Include ports template for device: ports/'.$device['os'].'_'.$hw.'.inc.php');

        include('ports/'.$device['os'].'_'.$hw.'.inc.php');
      }
      else if (is_file('ports/'.$device['os'].'_generic.inc.php'))
      {
        print_debug('Include ports template for device: ports/'.$device['os'].'_generic.inc.php');

        include('ports/'.$device['os'].'_generic.inc.php');
      }
    }
    */
    foreach ($ports_links[$port_type] as $link)
    {
      $all_links[] = $link['link'];
    }
  }
  //r($ports_links);
  /* END ports sorting */
  echo(implode(', ', $all_links));

  echo('</td></tr>');
  echo('</table>');

  unset($ifsep);
  echo("</div></div>");
}

// EOF
