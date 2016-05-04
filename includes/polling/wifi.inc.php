<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($device['type'] == 'network' || $device['type'] == 'firewall' || $device['type'] == 'wireless')
{
  print_cli_data_field("MIBs", 2);

  foreach (dbFetchRows("SELECT * FROM `wifi_radios` WHERE `device_id` = ?", array($device['device_id'])) as $radio)
  {
    $GLOBALS['cache']['wifi_radios'][$radio['radio_ap']][$radio['radio_number']] = $radio;
  }

  foreach (dbFetchRows("SELECT * FROM `wifi_wlans` WHERE `device_id` = ?", array($device['device_id'])) as $wlan)
  {
    $GLOBALS['cache']['wifi_wlans'][$wlan['wlan_index']] = $wlan;
  }

  $include_dir = "includes/polling/wifi";
  include("includes/include-dir-mib.inc.php");

  /// FIXME : everything below this point is horrible shit that needs to be moved elsewhere. These aren't wireless entities, they're just graphs. They go in graphs.

  /// FIXME. Move this to MIB based includes
  if ($device['os'] == 'airport')
  {
    echo("Checking Airport Wireless clients... ");

    $wificlients1 = snmp_get($device, "wirelessNumber.0", "-OUqnv", "AIRPORT-BASESTATION-3-MIB") +0;

    echo($wificlients1 . " clients\n");

    // FIXME Also interesting to poll? dhcpNumber.0 for number of active dhcp leases
  }

  if ($device['os'] == 'trapeze')
  {
    echo("Checking Trapeze Wireless clients... ");
    $wificlients1 = snmp_get($device, "trpzClSessTotalSessions.0", "-OUqnv", "TRAPEZE-NETWORKS-CLIENT-SESSION-MIB", $config['mib_dir'].':'.mib_dirs('trapeze'));
    echo($wificlients1 . " clients\n");
  }

  if ($device['os'] == 'ios' && $device['type'] == 'wireless')
  {
    echo("Checking Aironet Wireless clients... ");

    $wificlients1 = snmp_get($device, "cDot11ActiveWirelessClients.1", "-OUqnv", "CISCO-DOT11-ASSOCIATION-MIB");
    $wificlients2 = snmp_get($device, "cDot11ActiveWirelessClients.2", "-OUqnv", "CISCO-DOT11-ASSOCIATION-MIB");

    echo(($wificlients1 +0) . " clients on dot11Radio0, " . ($wificlients2 +0) . " clients on dot11Radio1\n");
  }

  # MikroTik RouterOS
  if ($device['os'] == 'routeros')
  {
    # Check inventory for wireless card in device. Valid types be here:
    $wirelesscards = array('Wireless', 'Atheros');
    foreach ($wirelesscards as $wirelesscheck)
    {
      if (dbFetchCell("SELECT COUNT(*) FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalDescr` LIKE ?", array($device['device_id'], "%".$wirelesscheck."%")) >= "1")
      {
        echo("Checking RouterOS Wireless clients... ");

        $wificlients1 = snmp_get($device, "mtxrWlApClientCount", "-OUqnv", "MIKROTIK-MIB", mib_dirs('mikrotik'));

        echo(($wificlients1 +0) . " clients\n");
        break;
      }
      unset($wirelesscards);
    }
  }

  # Senao/Engenius
  if ($device['os'] == 'engenius')
  {
    echo("Checking Engenius Wireless clients... ");

    $wificlients1 = count(explode("\n",snmp_walk($device, "entCLInfoIndex", "-OUqnv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao'))));

    echo(($wificlients1 +0) . " clients\n");
  }

  if ($device['os'] == 'symbol' AND (stristr($device['hardware'],"AP")))
  {
    echo("Checking Symbol Wireless clients... ");

    $wificlients1 = snmp_get($device, ".1.3.6.1.4.1.388.11.2.4.2.100.10.1.18.1", "-Ovq", "\"\"");

    echo(($wificlients1 +0) . " clients on wireless connector, ");
  }

  // RRD Filling Code
  if (isset($wificlients1) && is_numeric($wificlients1))
  {
    $wificlientsrrd = "wificlients-radio1.rrd";

    rrdtool_create($device, $wificlientsrrd," \
      DS:wificlients:GAUGE:600:-273:1000 ");

    rrdtool_update($device, $wificlientsrrd, array($wificlients1));

    $graphs['wifi_clients'] = TRUE;
  }

  if (isset($wificlients2) && is_numeric($wificlients2))
  {
    $wificlientsrrd = "wificlients-radio2.rrd";

    rrdtool_create($device, $wificlientsrrd, " \
      DS:wificlients:GAUGE:600:-273:1000 ");

    rrdtool_update($device, $wificlientsrrd, array($wificlients2));

    $graphs['wifi_clients'] = TRUE;
  }

  unset($wificlients2, $wificlients1);
}

// EOF
