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

// First attempt at wlan polling. Could do with some improvement perhaps

echo(" RUCKUS-WLAN-MIB ");

// Getting WLANs

// Entries in this table are indexed by ifIndex.

$wlan_table = snmpwalk_cache_oid($device, "RuckusWLANTable", array(), "RUCKUS-WLAN-MIB", mib_dirs('ruckus'));

if (OBS_DEBUG > 1) { print_vars($wlan_table); }

// Goes through the SNMP wlan data
foreach ($wlan_table as $wlan_ifIndex => $wlan)
{
  $wlan['wlan_mib']           = "RUCKUS-WLAN-MIB";
  $wlan['wlan_index']         = $wlan_ifIndex;                                // Interface index.
  $wlan['wlan_vlan_id']       = $wlan['ruckusWLANVlanID'];                      // Specifies the VLAN ID of the WLAN.  If VLAN ID is 1, packets from this WLAN will be untagged.
  $wlan['wlan_name']          = $wlan['ruckusWLANName'];                    // Name of the WLAN
  $wlan['wlan_ssid']          = $wlan['ruckusWLANSSID'];                    // Specifies the name of the SSID.
  $wlan['wlan_ssid_bcast']    = $wlan['ruckusWLANSSIDBcastDisable'];        // Setting to 1, cause  the ssid will not be broadcast in the beacons. True/False
  $wlan['wlan_bssid']         = $wlan['ruckusWLANBSSID'];                   // This attribute is the unique identifier in this BSS. It is the 48-bit MAC address of the wireless interface.
  $wlan['wlan_bss_type']    = $wlan['ruckusWLANBSSType'];               // Specifies the bss type. station(1), master(2), independent(3)
  $wlan['wlan_channel']       = $wlan['ruckusWLANChannel'];                 // Specifies the current operating channel.
  $wlan['wlan_radio_mode']    = $wlan['ruckusWLANRadioMode'];               // Specifies the radio mode. ieee802dot11b(1), ieee802dot11g(2), auto(3), ieee802dot11a(4), ieee802dot11ng(5), ieee802dot11na(6), ieee802dot11ac(7)
  //$wlan['wlan_admin_status'] = $wlan['ruckusWLANAdminStatus'];             // Administrative status of the WLAN interface. up(1), down(2)
  if ($wlan['ruckusWLANAdminStatus'] == 'down') { $wlan['wlan_admin_status']  = "0"; } else { $wlan['wlan_admin_status'] = "1"; }
  $wlan['wlan_beacon_period'] = $wlan['ruckusWLANBeaconPeriod'];            // The number of milliseconds that a station will use for scheduling Beacon transmissions.
  $wlan['wlan_dtim_period']   = $wlan['ruckusWLANDTIMPeriod'];              // The number of TU that a station will use for scheduling Beacon transmissions.
  $wlan['wlan_frag_thresh']   = $wlan['ruckusWLANFragmentationThreshold'];  // The current maximum size, in octets, of the MPDU that may be delivered to the PHY.
  $wlan['wlan_igmp_snoop']    = $wlan['ruckusWLANIGMPSnooping'];            // enable(1), disable(2)
  $wlan['wlan_prot_mode']     = $wlan['ruckusWLANProtectionMode'];          // Enabled when 11g and 11b clients exist on the same network. none(1), ctsOnly(2), ctsRts(3)
  $wlan['wlan_wds_enable']    = $wlan['ruckusWLANWDSEnable'];               // Specifies if the WDS is enabled or disabled on this interface. True/False
  $wlan['wlan_rts_thresh']    = $wlan['ruckusWLANRTSThreshold'];            // The number of octets in an MPDU, below which an RTS/CTS handshake will not be performed.

  if (OBS_DEBUG && count($wlan)) { print_vars($wlan); }

  discover_wifi_wlan($device['device_id'], $wlan);
}

unset($wlans_snmp);