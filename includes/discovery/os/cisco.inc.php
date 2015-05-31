<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (empty($os))
{
  $cisco_os_descr = array(
    'iosxe' => array('IOS-XE'),
    'iosxr' => array('IOS XR'),
    'ios'   => array('Cisco Internetwork Operating System Software',
                     'IOS (tm)', 'Cisco IOS Software', 'Global Site Selector'),
    'catos' => array('Cisco Catalyst Operating System Software',
                     'Cisco Systems Catalyst 1900'),
    'pixos' => array('Cisco PIX'),
    'asa'   => array('Cisco Adaptive Security Appliance'),
    'fwsm'  => array('Cisco Firewall Services Module'),
    'ciscoscos' => array('Cisco Service Control'),
    'cisco-acs' => array('Cisco Secure ACS'),
  );
  foreach ($cisco_os_descr as $cos => $cdescr)
  {
    foreach($cdescr as $descr)
    {
      if (strpos($sysDescr, $descr) !== FALSE)
      {
        $os = $cos;
        break 2;
      }
    }
  }
  
  if (!$os && strpos($sysObjectId, '.1.3.6.1.4.1.9.10.56') !== FALSE)
  {
    // This sysObjectId intersects with Cisco ACS
    $os = 'cisco-lms';
  }
}

unset($cos, $cdescr, $descr, $cisco_os_descr);

// EOF
