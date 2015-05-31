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
                    'tab'     => 'routing');

#$type_text['overview'] = "Overview";
$type_text['ipsec_tunnels'] = "IPSEC Tunnels";

// Cisco ACE
$type_text['loadbalancer_rservers'] = "Rservers";
$type_text['loadbalancer_vservers'] = "Serverfarms";

$pagetitle[] = "Routing";

$navbar = array();
$navbar['brand'] = "Routing";
$navbar['class'] = "navbar-narrow";

foreach ($routing_tabs as $type)
{

  if (!$vars['proto']) { $vars['proto'] = $type; }

  $navbar['options'][$type]['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'routing', 'proto' => $type ));
  $navbar['options'][$type]['text'] = nicecase($type);
  if ($vars['proto'] == $type) { $navbar['options'][$type]['class'] = "active"; }

}
print_navbar($navbar);
unset($navbar);

if (is_file("pages/device/routing/".$vars['proto'].".inc.php"))
{
  include("pages/device/routing/".$vars['proto'].".inc.php");
} else {
  foreach ($routing_tabs as $type)
  {
    if ($type != "overview")
    {
      if (is_file("pages/device/routing/overview/".$type.".inc.php"))
      {
        $g_i++;
        if (!is_integer($g_i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

        echo('<div style="background-color: '.$row_colour.';">');
        echo('<div style="padding:4px 0px 0px 8px;"><span class=graphhead>'.$type_text[$type].'</span>');

        include("pages/device/routing/overview/".$type.".inc.php");

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
