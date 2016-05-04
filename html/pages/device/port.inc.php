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

if (!isset($vars['view']) ) { $vars['view'] = "graphs"; }

if ($permit_tabs['ports'])
{
  $sql  = "SELECT *, `ports`.`port_id` AS `port_id`";
  $sql .= " FROM  `ports`";
  $sql .= " LEFT JOIN  `ports-state` ON  `ports`.port_id = `ports-state`.port_id";
  $sql .= " WHERE `ports`.`port_id` = ?";

  $port = dbFetchRow($sql, array($vars['port']));

  $port_details = 1;

  if ($port['ifPhysAddress']) { $mac = (string)$port['ifPhysAddress']; }

  $color = "black";
  if      ($port['ifAdminStatus'] == "down") { $status = "<span class='grey'>Disabled</span>"; }
  else if ($port['ifAdminStatus'] == "up")
  {
    if ($port['ifOperStatus'] == "down" || $port['ifOperStatus'] == "lowerLayerDown") { $status = "<span class='red'>Enabled / Disconnected</span>"; }
    else                                                                              { $status = "<span class='green'>Enabled / Connected</span>"; }
  }

  $i = 1;
  $show_all = 1;

  echo generate_box_open();
  echo '<table class="table table-hover table-striped table-condensed">';
  print_port_row($port, array_merge($vars, array('view' => 'details')));
  echo '</table>';
  echo generate_box_close();

  // Start Navbar

  $link_array = array('page'    => 'device',
                      'device'  => $device['device_id'],
                      'tab'     => 'port',
                      'port'    => $port['port_id']);

  $navbar['options']['graphs']['text']    = 'Graphs';

  $navbar['options']['alerts']['text']    = 'Alerts';
  $navbar['options']['alertlog']['text']  = 'Alert Log';

  if (dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `measured_class` = 'port' AND `measured_entity` = ? and `device_id` = ?", array($port['port_id'], $device['device_id'])))
  {
    $navbar['options']['sensors']['text'] = 'Sensors';
  }

  $navbar['options']['realtime']['text']  = 'Real time';   // FIXME CONDITIONAL

  if (dbFetchCell('SELECT COUNT(*) FROM `ip_mac` WHERE `port_id` = ?', array($port['port_id'])))
  {
    $navbar['options']['arp']['text']     = 'ARP/NDP Table';
  }

  if (dbFetchCell("SELECT COUNT(*) FROM `vlans_fdb` WHERE `port_id` = ?", array($port['port_id'])))
  {
    $navbar['options']['fdb']['text']    = 'FDB Table';
  }

  if (dbFetchCell("SELECT COUNT(*) FROM `ports_cbqos` WHERE `port_id` = ?", array($port['port_id'])))
  {
    $navbar['options']['cbqos']['text']    = 'CBQoS';
  }

  $navbar['options']['events']['text']   = 'Eventlog';

  if (dbFetchCell("SELECT COUNT(*) FROM `ports_adsl` WHERE `port_id` = ?", array($port['port_id'])))
  {
    $navbar['options']['adsl']['text'] = 'ADSL';
  }

  if (dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE `pagpGroupIfIndex` = ? and `device_id` = ?', array($port['ifIndex'], $device['device_id'])))
  {
    $navbar['options']['pagp']['text'] = 'PAgP';
  }

  if (dbFetchCell('SELECT COUNT(*) FROM `ports_vlans` WHERE `port_id` = ? and `device_id` = ?', array($port['port_id'], $device['device_id'])))
  {
    $navbar['options']['vlans']['text'] = 'VLANs';
  }

  if (dbFetchCell('SELECT count(*) FROM mac_accounting WHERE port_id = ?', array($port['port_id'])) > '0')
  {
    $navbar['options']['macaccounting']['text'] = 'MAC Accounting';
  }

  if (dbFetchCell('SELECT COUNT(*) FROM juniAtmVp WHERE port_id = ?', array($port['port_id'])) > '0')
  {

    // FIXME ATM VPs
    // FIXME URLs BROKEN

    $navbar['options']['atm-vp']['text'] = 'ATM VPs';

    $graphs = array('bits', 'packets', 'cells', 'errors');
    foreach ($graphs as $type)
    {
      if ($vars['view'] == "atm-vp" && $vars['graph'] == $type) { $navbar['options']['atm-vp']['suboptions'][$type]['class'] = "active"; }
      $navbar['options']['atm-vp']['suboptions'][$type]['text'] = ucfirst($type);
      $navbar['options']['atm-vp']['suboptions'][$type]['url']  = generate_url($link_array,array('view'=>'atm-vc','graph'=>$type));
    }
  }

  if (OBSERVIUM_EDITION != 'community' && $_SESSION['userlevel'] == '10' && $config['enable_billing'])
  {
    $navbar['options_right']['bills'] = array('text' => 'Create Bill', 'icon' => 'oicon-money-coin', 'url' => generate_url(array('page' => 'bills', 'view' => 'add', 'port' => $port['port_id'])));
  }

  if ($_SESSION['userlevel'] == '10' )
  {
    // This doesn't exist yet.
    $navbar['options_right']['data']['text'] = 'Data';
    $navbar['options_right']['data']['icon'] = 'oicon-application-list';
    $navbar['options_right']['data']['url'] = generate_url($link_array,array('view'=>'data'));
  }

  foreach ($navbar['options'] as $option => $array)
  {
    if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
    $navbar['options'][$option]['url'] = generate_url($link_array,array('view'=>$option));
  }

  $navbar['class'] = "navbar-narrow";
  $navbar['brand'] = "Port";

  print_navbar($navbar);
  unset($navbar);

  include($config['html_dir'] . '/pages/device/port/'.$vars['view'].'.inc.php');
} else {
  print_error('<h3>Invalid device/port combination</h3>
               The port/device combination was invalid. Please retype and try again.');
}

// EOF
