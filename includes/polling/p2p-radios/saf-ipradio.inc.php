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

echo(" SAF-IPRADIO ");

// Getting Radios

$radios_snmp = snmpwalk_cache_oid($device, "radioTable", array(), "SAF-IPRADIO", mib_dirs('saf'));
$radios_snmp = snmpwalk_cache_oid($device, "ModemEntry", $radios_snmp, "SAF-IPRADIO", mib_dirs('saf'));
$radios_snmp = snmpwalk_cache_oid($device, "modemStatistics", $radios_snmp, "SAF-IPRADIO", mib_dirs('saf'));

// Goes through the SNMP radio data
foreach ($radios_snmp as $radio_index => $radio)
{

  if ($radio_index == "remote" && $config['mibs']['SAF-IPRADIO']['enumerate_remote_radios'] != TRUE) { continue; }

  $radio['radio_name']           = ucfirst($radio['radioIndex']);
  $radio['radio_status']         = $radio['radioGenStatus'];
  $radio['radio_loopback']       = ($radio['radioLoopback'] == "on" ? '1' : ($radio['radioLoopback'] == "off" ? '0' : array('NULL')));
  $radio['radio_tx_mute']        = ($radio['radioTxMute']   == "on" ? '1' : ($radio['radioTxMute']   == "off" ? '0' : array('NULL')));
  $radio['radio_tx_freq']        = $radio['radioTxFrequency'];
  $radio['radio_rx_freq']        = $radio['radioRxFrequency'];
  $radio['radio_tx_power']       = $radio['radioTxPower'];
  $radio['radio_rx_level']       = $radio['radioRxLevel'];
  $radio['radio_e1t1_channels']  = $radio['radioE1T1Channels'];
  $radio['radio_bandwidth']      = $radio['modemBandwith'] * 1000;      // Convert to Hz
  $radio['radio_modulation']     = $radio['modemModulation'];
  $radio['radio_total_capacity'] = $radio['modemTotalCapacity'] * 1000; // Convert to BPS
  $radio['radio_eth_capacity']   = $radio['modemEthernetCapacity'] * 1000; // Convert to BPS
  $radio['radio_rmse']           = $radio['modemRadialMSE'] / 10;       // Convert to units
  $radio['radio_agc_gain']       = $radio['modemInternalAGCgain'];
  $radio['radio_carrier_offset'] = $radio['modemCarrierOffset'];
  $radio['radio_sym_rate_tx']    = $radio['modemSymbolRateTx'];
  $radio['radio_sym_rate_rx']    = $radio['modemSymbolRateRx'];
  $radio['radio_standard']       = $radio['modemStandard'];
  $radio['radio_cur_capacity']   = $radio['modemACMtotalCapacity'] * 1000;

  poll_p2p_radio($device, 'saf-ipradio', $radio_index, $radio);

}

// EOF
