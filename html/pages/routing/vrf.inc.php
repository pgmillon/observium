<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($_SESSION['userlevel'] < 5)
{
  print_error_permission();
  return;
}

$link_array = array('page'    => 'routing',
                    'protocol' => 'vrf');

  $navbar = array('brand' => "VRFs", 'class' => "navbar-narrow");

  $navbar['options']['vrf']['text']   = 'All VRFs';
  if (!isset($vars['vrf'])) { $navbar['options']['vrf']['class'] .= " active"; }
  $navbar['options']['vrf']['url'] = generate_url($link_array,array('vrf' => NULL));

  $options['basic']['text']   = 'Basic';
  // $navbar['options']['details']['text'] = 'Details';
  $options['graphs']     = array('text' => 'Graphs', 'class' => 'pull-right', 'icon' => 'oicon-system-monitor');

  if (!isset($vars['view'])) { $vars['view'] = 'basic'; }

  foreach ($options as $option => $array)
  {
    $navbar['options'][$option] = $array;
    if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
    $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
  }

  foreach (array('graphs') as $type)
  {
    foreach ($config['graph_types']['port'] as $option => $data)
    {
      if ($vars['view'] == $type && $vars['view'] == $option)
      {
        $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
        $navbar['options'][$type]['text'] .= ' ('.$data['name'].')';
      }
      $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
      $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($link_array, array('view' => $type, 'graph' => $option));
    }

  }

  print_navbar($navbar);
  unset($navbar);

  if (!$vars['vrf'])
  {
    // Pre-Cache in arrays
    // That's heavier on RAM, but much faster on CPU (1:40)

    // Specifying the fields reduces a lot the RAM used (1:4) .
    $vrf_fields = "`vrf_id`, `mplsVpnVrfRouteDistinguisher`, `mplsVpnVrfDescription`, `vrf_name`";
    $dev_fields = "`device_id`, `hostname`, `os`, `hardware`, `version`, `features`, `location`, `status`, `ignore`, `disabled`";
    $port_fields = "`port_id`, `ifVrf`, `device_id`, `ifDescr`, `ifAlias`, `ifName`";

    foreach (dbFetchRows("SELECT $vrf_fields, $dev_fields FROM `vrfs` LEFT JOIN `devices` USING (`device_id`) WHERE 1".$GLOBALS['cache']['where']['devices_permitted']) as $vrf_device)
    {
      if (empty($vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']]))
      {
        $vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']][0] = $vrf_device;
      } else {
        array_push($vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']], $vrf_device);
      }
    }

    foreach (dbFetchRows("SELECT $port_fields FROM `ports` WHERE `ifVrf` <> 0".$GLOBALS['cache']['where']['ports_permitted']) as $port)
    {
      if (empty($vrf_ports[$port['ifVrf']][$port['device_id']]))
      {
        $vrf_ports[$port['ifVrf']][$port['device_id']][0] = $port;
      } else {
        array_push($vrf_ports[$port['ifVrf']][$port['device_id']], $port);
      }
    }

    echo generate_box_open();

    echo('<table class="table  table-striped">');
    foreach (dbFetchRows("SELECT * FROM `vrfs` WHERE 1".$GLOBALS['cache']['where']['devices_permitted']." GROUP BY `mplsVpnVrfRouteDistinguisher`") as $vrf)
    {
      echo('<tr>');
      echo('<td style="width: 240px;"><a class="entity" href="'.generate_url($link_array,array('vrf' => $vrf['mplsVpnVrfRouteDistinguisher'])).'">' . $vrf['vrf_name'] . '</a><br /><span class="small">' . $vrf['mplsVpnVrfDescription'] . '</span></td>');
      echo('<td style="width: 100px;" class="small">' . $vrf['mplsVpnVrfRouteDistinguisher'] . '</td>');
      #echo('<td style="width: 200px;" class="small">' . $vrf['mplsVpnVrfDescription'] . '</td>');
      echo('<td>');

      echo generate_box_open();

      echo('<table class="table table-striped  table-condensed">');
      $x=1;
      foreach ($vrf_devices[$vrf['mplsVpnVrfRouteDistinguisher']] as $device)
      {
        echo('<tr><td style="width: 150px;"><span class="entity">'.generate_device_link($device, short_hostname($device['hostname'])) .'</span>');

        if ($device['vrf_name'] != $vrf['vrf_name']) { echo("<a href='#' onmouseover=\" return overlib('Expected Name : ".$vrf['vrf_name']."<br />Configured : ".$device['vrf_name']."', CAPTION, '<span class=entity-title>VRF Inconsistency</span>' ,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"> <img align=absmiddle src=images/16/exclamation.png></a>"); }
        echo("</td><td>");
        unset($seperator);

        foreach ($vrf_ports[$device['vrf_id']][$device['device_id']] as $port)
        {
          $port = array_merge($device, $port);

          switch ($vars['graph'])
          {
            case 'bits':
            case 'upkts':
            case 'nupkts':
            case 'errors':
              $port['width'] = "130";
              $port['height'] = "30";
              $port['from'] = $config['time']['day'];
              $port['to'] = $config['time']['now'];
              $port['bg'] = "#".$bg;
              $port['graph_type'] = "port_".$vars['graph'];
              echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 135px; max-width:135px; min-height:75px; max-height:75px;
                 text-align: center; float: left; background-color: ".$list_colour_b_b.";'>
                 <div style='font-weight: bold;'>".short_ifname($port['ifDescr'])."</div>");
              generate_port_thumbnail($port);
              echo("<div style='font-size: 9px;'>".short_port_descr($port['ifAlias'])."</div>
                </div>");
              break;

            default:
              echo($seperator.generate_port_link($port, short_ifname($port['ifDescr'])));
              $seperator = ", ";
              break;
           }
         }
         echo("</td></tr>");
         $x++;
       } // End While

       echo '</table>';
       echo generate_box_close();
       echo '</td>';

       $i++;
     }
     echo("</table>");

     echo print_box_close();

  } else {

    // Print single VRF

    echo('<table class="table  table-striped">');
    $vrf = dbFetchRow("SELECT * FROM `vrfs` WHERE `mplsVpnVrfRouteDistinguisher` = ? ".$GLOBALS['cache']['where']['devices_permitted'], array($vars['vrf']));
    echo('<tr>');
    echo('<td style="width: 200px;" class="entity-title"><a href="'.generate_url($link_array,array('vrf' => $vrf['mplsVpnVrfRouteDistinguisher'])).'">' . $vrf['vrf_name'] . '</a></td>');
    echo('<td style="width: 100px;" class="small">' . $vrf['mplsVpnVrfRouteDistinguisher'] . '</td>');
    echo('<td style="width: 200px;" class="small">' . $vrf['mplsVpnVrfDescription'] . '</td>');
    echo('</table>');

    $vrf_devices = dbFetchRows("SELECT * FROM `vrfs` LEFT JOIN `devices` USING (`device_id`) WHERE `mplsVpnVrfRouteDistinguisher` = ? ".$GLOBALS['cache']['where']['devices_permitted'], array($vrf['mplsVpnVrfRouteDistinguisher']));
    foreach ($vrf_devices as $device)
    {
      $hostname = $device['hostname'];
      echo('<table cellpadding="10" cellspacing="0" class="table  table-striped" width="100%">');

      print_device_row($device);

      echo('</table>');
      unset($seperator);
      echo('<div style="margin: 10px;"><table class="table  table-striped">');
      $i=1;
      foreach (dbFetchRows("SELECT * FROM `ports` WHERE `ifVrf` = ? AND `device_id` = ?".$GLOBALS['cache']['where']['ports_permitted'], array($device['vrf_id'], $device['device_id'])) as $port)
      {
        print_port_row($port);

        $i++;
      }

      $x++;
      echo('</table></div>');
      echo('<div style="height: 10px;"></div>');
    }
  }

// EOF
