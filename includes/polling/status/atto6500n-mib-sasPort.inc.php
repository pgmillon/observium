<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */


echo " ATTO6500N-MIB-SAS-Port ";

$index = $status_db['status_oid'];
$opState = snmp_get($device, "sasPortOperationalState.".$index, '-Ovq', 'ATTO6500N-MIB');
$adminState = snmp_get($device, "sasPortAdminState.".$index, '-Ovq', 'ATTO6500N-MIB');

if($opState && $adminState){
  if($adminState == "enabled"){
    if($opState == "online"){
      $status_poll['status_event'] = 'ok';
      $status_poll['status_name']  = 'up';
      $status_value = "2";
    } elseif ($opState == "offline"){
      $status_poll['status_event'] = 'alert';
      $status_poll['status_name']  = 'enabled but down';
      $status_value = "1";
    } else {
      $status_poll['status_event'] = 'warning';
      $status_poll['status_name']  = 'unknown';
      $status_value = "0";
    }
  } elseif($adminState == "disabled"){
    if($opState == "online"){
      $status_poll['status_event'] = 'warning';
      $status_poll['status_name']  = 'up but disabled';
      $status_value = "0";
    } elseif ($opState == "offline"){
      $status_poll['status_event'] = 'ok';
      $status_poll['status_name']  = 'down';
      $status_value = "2";
    } else {
      $status_poll['status_event'] = 'warning';
      $status_poll['status_name']  = 'unknown';
      $status_value = "0";
    }
  } else { 
    $status_poll['status_event'] = 'warning';
    $status_poll['status_name']  = 'unknown';
    $status_value = "0";
  }
}
