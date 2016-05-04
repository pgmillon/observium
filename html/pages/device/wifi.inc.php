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

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'wifi');

$navbar = array('brand' => "WiFi", 'class' => "navbar-narrow");

$navbar['options']['overview']['text']       = 'Overview';
if ($device_ap_count > 0) { $navbar['options']['accesspoints']['text'] = 'Access Points'; }
if ($device_radio_count > 0) { $navbar['options']['radios']['text']       = 'Radios'; }
if ($device_wlan_count > 0) { $navbar['options']['wlans']['text']        = 'WLANs'; }
$navbar['options']['clients']['text']      = 'Clients';

foreach ($navbar['options'] as $option => $array)
{
  if (!isset($vars['view'])) { $vars['view'] = "overview"; }
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

if ($vars['view'] == "accesspoint") { $navbar['options']['accesspoints']['class'] .= " active"; }

print_navbar($navbar);
unset($navbar);

$page_title[] = "Wifi";

print_warning("Please be aware that the WiFi section is currently under development and is subject to change and breakage.");

switch ($vars['view'])
{
  case 'overview':
  case 'accesspoints':
  case 'radios':
  case 'wlans':
  case 'clients':
    include($config['html_dir']."/pages/device/wifi/".$vars['view'].".inc.php");
    break;
  default:
    echo('<h2>Error. No section '.$vars['view'].'.<br /> Please report this to observium developers.</h2>');
    break;
}

// EOF
