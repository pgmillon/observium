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

if ($_SESSION['userlevel'] < 7)
{
  print_error_permission();
  return;
}

// User level 7-9 only can see config
$readonly = $_SESSION['userlevel'] < 10;

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'edit');

  $panes['device']   = 'Device Settings';
  $panes['snmp']     = 'SNMP';
  if ($config['geocoding']['enable'])
  {
    $panes['geo']     = 'Geolocation';
  }
  $panes['mibs']     = 'MIBs';
  $panes['graphs']   = 'Graphs';
  $panes['alerts']   = 'Alerts';
  if ($config['enable_libvirt'] && $device['os'] == 'linux')
  {
    $panes['ssh']    = 'SSH'; // For now this option used only by 'libvirt-vminfo' discovery module
  }
  $panes['ports']    = 'Ports';
  $panes['sensors']  = 'Sensors';

  if (count($config['os'][$device['os']]['icons']))
  {
    $panes['icon']   = 'Icon';
  }

  $panes['modules']  = 'Modules';

  if ($config['enable_services'])
  {
    $panes['services'] = 'Services';
  }

  if ($device_loadbalancer_count['netscaler_vsvr'])    { $panes['netscaler_vsvrs'] = 'NS vServers'; }
  if ($device_loadbalancer_count['netscaler_services']) { $panes['netscaler_svcs'] = 'NS Services'; }

  if ($device['os_group'] == 'unix' || $device['os'] == 'windows' || $device['os'] == 'generic' || $device['os'] == 'drac')
  {
    $panes['ipmi']     = 'IPMI';
  }

  if ($device['os'] == 'windows')
  {
    $panes['wmi']    = 'WMI';
  }

  if ($device['os_group'] == 'unix' || $device['os'] == 'generic')
  {
    $panes['agent']  = 'Agent';
  }
  if ($device['os_group'] == 'unix' || $device['os'] == 'windows')
  {
    $panes['apps']   = 'Applications'; /// FIXME. Deprecated?
  }

  $navbar['brand'] = "Edit";
  $navbar['class'] = "navbar-narrow";

  foreach ($panes as $type => $text)
  {
    if (!isset($vars['section'])) { $vars['section'] = $type; }

    if ($vars['section'] == $type) { $navbar['options'][$type]['class'] = "active"; }
    $navbar['options'][$type]['url']  = generate_url($link_array,array('section'=>$type));
    $navbar['options'][$type]['text'] = $text;
  }
  $navbar['options_right']['delete']['url']  = generate_url($link_array,array('section'=>'delete'));
  $navbar['options_right']['delete']['text'] = 'Delete';
  $navbar['options_right']['delete']['icon'] = 'oicon-server--minus';
  if ($vars['section'] == 'delete') { $navbar['options_right']['delete']['class'] = 'active'; }
  print_navbar($navbar);

  $filename = $config['html_dir'] . '/pages/device/edit/' . $vars['section'] . '.inc.php';
  if (is_file($filename))
  {
    $vars = get_vars('POST'); // Note, on edit pages use only method POST!

    include($filename);
  } else {
    print_error('<h3>Page does not exist</h4>
The requested page does not exist. Please correct the URL and try again.');
  }

unset($filename, $navbar, $panes, $link_array);

$page_title[] = "Settings";

// EOF
