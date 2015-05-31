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

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'loadbalancer');

// Cisco ACE
$type_text['loadbalancer_rservers'] = "Rservers";
$type_text['loadbalancer_vservers'] = "Serverfarms";

// Citrix Netscaler
$type_text['netscaler_vsvr'] = "VServers";
$type_text['netscaler_services'] = "Services";

$pagetitle[] = "Load Balancer";

$navbar['brand'] = "Load Balancer";
$navbar['class'] = "navbar-narrow";

foreach ($loadbalancer_tabs as $type)
{
  if (!$vars['type']) { $vars['type'] = $type; }
  if ($vars['type'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  $navbar['options'][$type]['text'] = $type_text[$type]." (".$device_loadbalancer_count[$type].")";
  $navbar['options'][$type]['url'] = generate_url($link_array,array('type'=>$type));
}

print_navbar($navbar); unset($navbar);

if (is_file("pages/device/loadbalancer/".$vars['type'].".inc.php"))
{
   include("pages/device/loadbalancer/".$vars['type'].".inc.php");
} else {
  foreach ($loadbalancer_tabs as $type)
  {
    if ($type != "overview")
    {
      if (is_file("pages/device/loadbalancer/overview/$type.inc.php"))
      {
        $g_i++;
        if (!is_integer($g_i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

        echo('<div style="background-color: '.$row_colour.';">');
        echo('<div style="padding:4px 0px 0px 8px;"><span class=graphhead>'.$type_text[$type].'</span>');

        include("pages/device/loadbalancer/overview/$type.inc.php");

        echo('</div>');
        echo('</div>');
      } else {
        $graph_title = $type_text[$type];
        $graph_type = "device_".$type;

        include("includes/print-device-graph.php");
      }
    }
  }
}

// EOF
