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

// First attempt at radio polling. Could do with some improvement perhaps

echo(" RUCKUS-RADIO-MIB ");

// Getting Radios

$radios_snmp = snmpwalk_cache_oid($device, "RuckusRadioTable", array(), "RUCKUS-RADIO-MIB", mib_dirs('ruckus'));
if ($GLOBALS['snmp_status'])
{
  $radios_snmp = snmpwalk_cache_oid($device, "ruckusRadioStatsNumSta", $radios_snmp, "RUCKUS-RADIO-MIB", mib_dirs('ruckus'));
  if (OBS_DEBUG > 1) { print_vars($radios_snmp); }
}

// Goes through the SNMP radio data
foreach ($radios_snmp as $radio_number => $radio)
{
  $radio['radio_mib']     = "RUCKUS-RADIO-MIB";
  $radio['radio_number']  = $radio_number;
  $radio['radio_ap']      = "0";                           // Hardcoded since the AP is self.
  $radio['radio_type']    = $radio['ruckusRadioMode'];
  $radio['radio_status']  = "unknown";                     // Hardcoded, data doesn't exist in this MIB
  $radio['radio_clients'] = $radio['ruckusRadioStatsNumSta'];
  $radio['radio_txpower'] = $radio['ruckusRadioTxPower'];
  $radio['radio_channel'] = $radio['ruckusRadioChannel'];

  if      ($radio['ruckusRadioBSSType'] == '1') { $radio['radio_status']  = "station"; }
  else if ($radio['ruckusRadioBSSType'] == '2') { $radio['radio_status']  = "master"; }
  else if ($radio['ruckusRadioBSSType'] == '3') { $radio['radio_status']  = "independent"; }
  else                                          { $radio['radio_bsstype'] = "unknown"; }

  $radio['radio_protection'] = $radio['ruckusRadioProtectionMode'];
  $radio['radio_mac']        = array('NULL');                 // Hardcoded, data doesnt' exist in this MIB

  if (OBS_DEBUG && count($radio)) { print_vars($radio); }

  discover_wifi_radio($device['device_id'], $radio);
  // $params   = array('radio_ap', 'radio_number', 'radio_type', 'radio_status', 'radio_clients', 'radio_txpower', 'radio_channel', 'radio_mac', 'radio_protection', 'radio_bsstype', 'radio_mib');
}

unset($radios_snmp);

// EOF
