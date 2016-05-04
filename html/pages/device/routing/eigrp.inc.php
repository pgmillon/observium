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

$navbar = array();
$navbar['brand'] = "EIGRP";
$navbar['class'] = "navbar-narrow";

foreach (array("vpns", "ases", "ports", "neighbours") as $type)
{
  if (!$vars['view']) { $vars['view'] = $type; }
  $navbar['options'][$type]['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'routing', 'proto' => 'eigrp', 'view' => $type ));
  $navbar['options'][$type]['text'] = nicecase($type);
  if ($vars['view'] == $type) { $navbar['options'][$type]['class'] = "active"; }
}

print_navbar($navbar);
unset($navbar);

switch($vars['view'])
{
  case "ports":
    include("eigrp/ports.inc.php");
    break;

}

// EOF
