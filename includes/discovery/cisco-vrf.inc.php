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

if ($config['enable_vrfs'])
{
  if ($device['os_group'] == "cisco" || $device['os'] == "junos" || $device['os'] == "ironware" || $device['os'] == "zxr10")
  {
    unset($vrf_count);

    echo("VRFs : ");

    /*
      There are 2 MIBs for VPNs : MPLS-VPN-MIB (oldest) and MPLS-L3VPN-STD-MIB (newest)
      Unfortunately, there is no simple way to find out which one to use, unless we reference
      all the possible devices and check what they support.
      Therefore we start testing the MPLS-L3VPN-STD-MIB that is preferred if available.
    */

    // Grab all the info first, then use it in the code.
    // It removes load on the device and makes things much faster

    $rds = snmp_walk($device, "mplsL3VpnVrfRD", "-Osqn", "MPLS-L3VPN-STD-MIB", NULL);

    if (empty($rds))
    {
      $rds = snmp_walk($device, "mplsVpnVrfRouteDistinguisher", "-Osqn", "MPLS-VPN-MIB", NULL);
      $vpnmib = "MPLS-VPN-MIB";
      $rds = str_replace(".1.3.6.1.3.118.1.2.2.1.3.", "", $rds);

      $descrs_oid = ".1.3.6.1.3.118.1.2.2.1.2";
      $ports_oid = ".1.3.6.1.3.118.1.2.1.1.2";
    }
    else
    {
      $vpnmib = "MPLS-L3VPN-STD-MIB";
      $rds = str_replace(".1.3.6.1.2.1.10.166.11.1.2.2.1.4.", "", $rds);

      $descrs_oid = ".1.3.6.1.2.1.10.166.11.1.2.2.1.3";
      $ports_oid = ".1.3.6.1.2.1.10.166.11.1.2.1.1.2";
    }

    print_debug("\n[DEBUG]\nUsing $vpnmib\n[/DEBUG]" .
                "\n[DEBUG OIDS]\n$rds\n[/DEBUG]");

    $rds = trim($rds);

    $descrs = snmp_walk($device, $descrs_oid, "-Osqn", $vpnmib, NULL);
    $ports  = snmp_walk($device, $ports_oid, "-Osqn", $vpnmib, NULL);

    $descrs = trim(str_replace("$descrs_oid.", "", $descrs));
    $ports  = trim(str_replace("$ports_oid.", "", $ports));

    $descr_table = array();
    $port_table = array();

    foreach (explode("\n", $descrs) as $descr)
    {
      $t = explode(" ", $descr, 2);
      $descr_table[$t[0]] = $t[1];
    }

    foreach (explode("\n", $ports) as $port)
    {
      $t = explode(" ", $port);
      $dotpos = strrpos($t[0], ".");
      $vrf_oid = substr($t[0], 0, $dotpos);
      $port_id = substr($t[0], $dotpos+1);

      if (empty($port_table[$vrf_oid])) { $port_table[$vrf_oid][0] = $port_id; }
        else {array_push($port_table[$vrf_oid], $port_id);}
    }

    foreach (explode("\n", $rds) as $oid)
    {
      echo "\n";
      if ($oid)
      {
        // 8.49.53.48.56.58.49.48.48 "1508:100"
        // First digit gives number of chars in VRF Name, then it's ASCII

        list($vrf_oid, $vrf_rd) = explode(" ", $oid);
        $oid_values = explode(".", $vrf_oid);
        $vrf_name = "";
        for ($i = 1; $i <= $oid_values[0]; $i++) {$vrf_name .= chr($oid_values[$i]);}

        echo "\n  [VRF $vrf_name] OID   - ".$vrf_oid;
        echo "\n  [VRF $vrf_name] RD    - ".$vrf_rd;
        echo "\n  [VRF $vrf_name] DESC  - ".$descr_table[$vrf_oid];

        if (dbFetchCell('SELECT COUNT(*) FROM `vrfs` WHERE `device_id` = ? AND `vrf_oid` = ?', array($device['device_id'], $vrf_oid)))
        {
          $update_array = array('mplsVpnVrfDescription' => $descr_table[$vrf_oid], 'mplsVpnVrfRouteDistinguisher' => $vrf_rd);
          dbUpdate($update_array, 'vrfs', '`device_id` = ? AND `vrf_oid` = ?', array($device['device_id'], $vrf_oid));
        }
        else
        {
          $insert_array = array('device_id' => $device['device_id'], 'vrf_oid' => $vrf_oid, 'vrf_name' => $vrf_name, 'mplsVpnVrfDescription' => $descr_table[$vrf_oid], 'mplsVpnVrfRouteDistinguisher' => $vrf_rd);
          dbInsert($insert_array, 'vrfs');
        }

        $vrf_id = dbFetchCell('SELECT vrf_id FROM `vrfs` WHERE `device_id` = ? AND `vrf_oid` = ?', array($device['device_id'], $vrf_oid));
        $valid_vrf[$vrf_id] = 1;

        echo "\n  [VRF $vrf_name] PORTS - ";
        foreach ($port_table[$vrf_oid] as $if_id)
        {
          $interface = dbFetchRow('SELECT ifDescr, port_id FROM ports WHERE ifIndex = ? AND device_id = ?', array($if_id, $device['device_id']));
          echo(short_ifname($interface['ifDescr']) . " ");
          dbUpdate(array('ifVrf' => $vrf_id), 'ports', '`port_id` = ?', array($interface['port_id']));
          $if = $interface['port_id'];
          $valid_vrf_if[$vrf_id][$if] = 1;
        }
      }
    }

    echo "\n";

    foreach (dbFetchRows('SELECT * FROM ports WHERE device_id = ?', array($device['device_id'])) as $row)
    {
      $if = $row['port_id'];
      $vrf_id = $row['ifVrf'];
      if ($row['ifVrf'])
      {
        if (!$valid_vrf_if[$vrf_id][$if])
        {
          echo("-");
          dbUpdate(array('ifVrf' => NULL), 'ports', '`port_id` = ?', array($if));
        }
        else
        {
          echo(".");
        }
      }
    }

    foreach (dbFetchRows('SELECT * FROM vrfs WHERE device_id = ?', array($device['device_id'])) as $row)
    {
      $vrf_id = $row['vrf_id'];
      if (!$valid_vrf[$vrf_id])
      {
        echo("-");
        dbDelete('vrfs', '`vrf_id` = ?', array($vrf_id));
      }
      else
      {
        echo(".");
      }
    }

    unset($valid_vrf_if);
    unset($valid_vrf);

    echo(PHP_EOL);
  } # cisco/junos/ironware
} # enable_vrfs

// EOF
