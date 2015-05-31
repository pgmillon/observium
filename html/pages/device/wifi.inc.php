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

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'wifi');

$navbar = array('brand' => "Wifi", 'class' => "navbar-narrow");

$navbar['options']['overview']['text']   = 'Overview';
$navbar['options']['accesspoints']['text']   = 'Access Points';

foreach ($navbar['options'] as $option => $array)
{
  if (!isset($vars['view'])) { $vars['view'] = "overview"; }
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

if ($vars['view'] == "accesspoint") { $navbar['options']['accesspoints']['class'] .= " active"; }

print_navbar($navbar);
unset($navbar);

if ($vars['view'] == "accesspoints"  || $vars['view'] == "accesspoint" || $vars['view'] == "overview" )
{
  include("wifi/".$vars['view'].".inc.php");
} else {
  include("wifi/overview.inc.php");
}

$pagetitle[] = "Wifi";

// EOF
