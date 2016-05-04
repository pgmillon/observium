<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo(" NS-ROOT-MIB HA ");


// Collect HAMode & HAState

$sysHighAvailabilityMode = snmp_get($device, 'sysHighAvailabilityMode.0', '-Ovq', 'NS-ROOT-MIB', mib_dirs('citrix'));
$haCurState              = snmp_get($device, 'haCurState.0',              '-Ovq', 'NS-ROOT-MIB', mib_dirs('citrix'));

if ($sysHighAvailabilityMode !== '' && $haCurState !== '')
{

  switch ($sysHighAvailabilityMode)
  {
    case "standalone":
      $status_poll['status_event'] = 'ok';
      $status_poll['status_name']  = $sysHighAvailabilityMode.'/'.$haCurState;
      $status_value = "1";
      break;
    case "primary":
    case "secondary":
      switch ($haCurState)
      {
        case "up":
        case "monitorOk":
          $status_poll['status_event'] = 'up';
          $status_poll['status_name']  = $sysHighAvailabilityMode.'/'.$haCurState;
          $status_value = "1";
          break;
        case "init":
        case "dumb":
        case "disabled":
          $status_poll['status_event'] = 'warning';
          $status_poll['status_name']  = $sysHighAvailabilityMode.'/'.$haCurState;
          $status_value = "0";
          break;
        case "alert":
        case "down":
        case "partialFail":
        case "monitorFail":
        case "completeFail":
        case "partialFailSsl":
        case "routemonitorFail":
          $status_poll['status_event'] = 'alert';
          $status_poll['status_name']  = $sysHighAvailabilityMode.'/'.$haCurState;
          $status_value = "-1";
          break;
      }
    default:
      $status_poll['status_event'] = 'warning';
      $status_poll['status_name']  = $sysHighAvailabilityMode.'/'.$haCurState;
      $status_value = "0";
  }

} else {
  $status_poll['status_event'] = 'warning';
  $status_poll['status_name']  = 'unknown';
  $status_value = "0";
}

// EOF
