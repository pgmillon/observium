<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Include description parser (usually ifAlias) if config option set
$custom_port_parser = FALSE;
if (isset($config['port_descr_parser']) && is_file($config['install_dir'] . "/" . $config['port_descr_parser']))
{
  include_once($config['install_dir'] . "/" . $config['port_descr_parser']);

  if (function_exists('custom_port_parser'))
  {
    $port_attribs = array('type','descr','circuit','speed','notes');
    $custom_port_parser = TRUE;
  } else {
    print_warning("WARNING. Rewrite your custom ports parser in file [".$config['install_dir'] . "/" . $config['port_descr_parser']."], using a function custom_port_parser().");
    $custom_port_parser = 'old';
  }
}

// Build SNMP Cache Array

// IF-MIB OIDs that go into the ports table

$data_oids = array('ifName','ifDescr','ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifMtu', 'ifSpeed', 'ifHighSpeed', 'ifType', 'ifPhysAddress',
                   'ifPromiscuousMode','ifConnectorPresent','ifDuplex', 'ifTrunk', 'ifVlan');

// IF-MIB statistics OIDs that go into RRD

$stat_oids = array('ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInNUcastPkts', 'ifOutNUcastPkts',
                   'ifHCInMulticastPkts', 'ifHCInBroadcastPkts', 'ifHCOutMulticastPkts', 'ifHCOutBroadcastPkts',
                   'ifInOctets', 'ifOutOctets', 'ifHCInOctets', 'ifHCOutOctets', 'ifInDiscards', 'ifOutDiscards', 'ifInUnknownProtos',
                   'ifInBroadcastPkts', 'ifOutBroadcastPkts', 'ifInMulticastPkts', 'ifOutMulticastPkts');

// Subset of IF-MIB statistics OIDs that we put into the state table

$stat_oids_db = array('ifInOctets', 'ifOutOctets', 'ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts'); // From above for DB

// ETHERLIKE-MIB extended error stats oids

$etherlike_oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames',
                        'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions',
                        'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
                        'dot3StatsSymbolErrors');

// Cisco old locIf OIDs. Currently unused.

$cisco_oids = array('locIfHardType', 'locIfInRunts', 'locIfInGiants', 'locIfInCRC', 'locIfInFrame', 'locIfInOverrun', 'locIfInIgnored', 'locIfInAbort',
                    'locIfCollisions', 'locIfInputQueueDrops', 'locIfOutputQueueDrops');

// PAgP OIDs

$pagp_oids = array('pagpOperationMode', 'pagpPortState', 'pagpPartnerDeviceId', 'pagpPartnerLearnMethod', 'pagpPartnerIfIndex', 'pagpPartnerGroupIfIndex',
                   'pagpPartnerDeviceName', 'pagpEthcOperationMode', 'pagpDeviceId', 'pagpGroupIfIndex');

// PoE OIDs

$cpe_oids = array('cpeExtPsePortEnable', 'cpeExtPsePortDiscoverMode', 'cpeExtPsePortDeviceDetected', 'cpeExtPsePortIeeePd',
  'cpeExtPsePortAdditionalStatus', 'cpeExtPsePortPwrMax', 'cpeExtPsePortPwrAllocated', 'cpeExtPsePortPwrAvailable', 'cpeExtPsePortPwrConsumption',
  'cpeExtPsePortMaxPwrDrawn', 'cpeExtPsePortEntPhyIndex', 'cpeExtPsePortEntPhyIndex', 'cpeExtPsePortPolicingCapable', 'cpeExtPsePortPolicingEnable',
  'cpeExtPsePortPolicingAction', 'cpeExtPsePortPwrManAlloc');

$peth_oids = array('pethPsePortAdminEnable', 'pethPsePortPowerPairsControlAbility', 'pethPsePortPowerPairs', 'pethPsePortDetectionStatus',
  'pethPsePortPowerPriority', 'pethPsePortMPSAbsentCounter', 'pethPsePortType', 'pethPsePortPowerClassifications', 'pethPsePortInvalidSignatureCounter',
  'pethPsePortPowerDeniedCounter', 'pethPsePortOverLoadCounter', 'pethPsePortShortCounter', 'pethMainPseConsumptionPower');

$ifmib_oids = array_merge($data_oids, $stat_oids);
$ifmib_oids = array('ifEntry', 'ifXEntry');

echo("Caching Oids: ");
$port_stats = array();
foreach ($ifmib_oids as $oid)
{
  echo("$oid ");
  $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "IF-MIB", mib_dirs());
  if ($oid == 'ifXEntry') { $has_ifXEntry = $GLOBALS['snmp_status']; } // Needed for additional check HC counters
}

if (count($port_stats))
{
  // Check if we're dealing with a retarded ass-backwards OS which feels the need to dump standardised variables in its own MIB, apparently just for shits.
  if ($device['os'] == "netapp")
  {
    // Add NetApp's own table so we can get 64-bit values. They are checked later on.
    $port_stats = snmpwalk_cache_oid($device, "NetIfEntry", $port_stats, "NETAPP-MIB", mib_dirs("netapp"));
    $has_ifXEntry = $GLOBALS['snmp_status']; // Needed for additional check HC counters
  }

  // If etherlike extended error statistics are enabled, walk dot3StatsEntry else only dot3StatsDuplexStatus.
  $ports_module = 'enable_ports_etherlike';
  if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module])))
  {
    echo("dot3Stats "); $port_stats = snmpwalk_cache_oid($device, "dot3StatsEntry", $port_stats, "EtherLike-MIB", mib_dirs());
  } else {
    echo("dot3StatsDuplexStatus"); $port_stats = snmpwalk_cache_oid($device, "dot3StatsDuplexStatus", $port_stats, "EtherLike-MIB", mib_dirs());
  }

  // Find out if we have any ADSL ports
  $ports_module = 'enable_ports_adsl';
  if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module])))
  {
    $device['adsl_count'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ? AND `ifType` = 'adsl'", array($device['device_id']));
  }

  // Fetch the ADSL-LINE-MIB OIDs if we have more than one ADSL port.
  // This data is used in the per-port adsl include.
  if ($device['adsl_count'] > "0")
  {
    echo("ADSL ");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.1.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.2.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.3.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.4.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.5.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.2", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.3", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.4", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.5", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.6", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.7", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.6.1.8", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.1", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.2", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.3", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.4", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.5", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.6", $port_stats, "ADSL-LINE-MIB");
    $port_stats = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.10.94.1.1.7.1.7", $port_stats, "ADSL-LINE-MIB");
  }

  // Fetch POWER-ETHERNET-MIB and CISCO-POWER-ETHERNET-EXT-MIB if enable_ports_poe is enabled.
  // This data is used in the per-port poe include.
  $ports_module = 'enable_ports_poe';
  if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module])))
  {
    $port_stats = snmpwalk_cache_oid($device, "pethPsePortEntry", $port_stats, "POWER-ETHERNET-MIB", mib_dirs());
    if ($device['os_group'] == 'cisco')
    {
      $port_stats = snmpwalk_cache_oid($device, "cpeExtPsePortEntry", $port_stats, "CISCO-POWER-ETHERNET-EXT-MIB", mib_dirs('cisco'));
    }
  }

  // FIXME This probably needs re-enabled. We need to clear these things when they get unset, too.
  #foreach ($etherlike_oids as $oid) { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "EtherLike-MIB"); }
  #foreach ($cisco_oids as $oid)     { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "OLD-CISCO-INTERFACES-MIB"); }
  #foreach ($pagp_oids as $oid)      { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "CISCO-PAGP-MIB"); }

  // If the device is cisco, pull a few cisco-specific MIBs and try to get vlan data from CISCO-VTP-MIB
  if ($device['os_group'] == "cisco")
  {
    foreach ($pagp_oids as $oid)
    {
      $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "CISCO-PAGP-MIB", mib_dirs('cisco'));
      // Break if no PAGP tables on device
      if ($oid == 'pagpOperationMode' && $GLOBALS['snmp_status'] === FALSE) { break; }
    }
    $data_oids[] = "portName";

    // Grab data to put ports into vlans or make them trunks
    // FIXME we probably shouldn't be doing this from the VTP MIB, right?
    $port_stats = snmpwalk_cache_oid($device, "vmVlan", $port_stats, "CISCO-VLAN-MEMBERSHIP-MIB", mib_dirs('cisco'));
    $port_stats = snmpwalk_cache_oid($device, "vlanTrunkPortEncapsulationOperType", $port_stats, "CISCO-VTP-MIB", mib_dirs('cisco'));
    $port_stats = snmpwalk_cache_oid($device, "vlanTrunkPortNativeVlan", $port_stats, "CISCO-VTP-MIB", mib_dirs('cisco'));

  } else {

    // The port is not Cisco. Try to get VLAN data from Q-BRIDGE-MIB.

    $port_stats = snmpwalk_cache_oid($device, "dot1qPortVlanTable", $port_stats, "Q-BRIDGE-MIB", mib_dirs());

    $vlan_ports = snmpwalk_cache_oid($device, "dot1qVlanStaticUntaggedPorts", $vlan_stats, "Q-BRIDGE-MIB", mib_dirs());
    $vlan_ifindex_map = snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", $vlan_stats, "Q-BRIDGE-MIB", mib_dirs());

    foreach ($vlan_ports as $vlan_id => $instance)
    {
      $parts = explode(' ',$instance['dot1qVlanStaticUntaggedPorts']);
      $binary = '';
      foreach ($parts as $part)
      {
        $binary .= zeropad(base_convert($part, 16, 2),8);
      }
      $length = strlen($binary);
      for ($i = 0; $i < $length; $i++)
      {
        if ($binary[$i])
        {
          $ifindex = $i; // FIXME $vlan_ifindex_map[$i]
          $q_bridge_untagged[$ifindex] = $vlan_id;
        }
      }
    }
  }

  // Get monitoring ([e|r]span) ports
  //$ports_module = 'enable_ports_smon';
  //if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module])))  // FIXME
  //{
    $smon_ports = array();
    $smon_statuses = snmpwalk_cache_oid($device, "portCopyStatus", array(), "SMON-MIB", mib_dirs());
    foreach ($smon_statuses as $smon_index => $smon_status)
    {
      if ($smon_status['portCopyStatus'] == 'active')
      {
        list(,$smon_index) = explode('.', $smon_index);
        $smon_ports[$smon_index] = 1; // Store monitoring destination ifIndex
      }
    }
  //}

  // End Building SNMP Cache Array

  if ($debug) { print_vars($port_stats); }
}

$polled = time();

// Build array of ports in the database

// FIXME -- this stuff is a little messy, looping the array to make an array just seems wrong. :>
//       -- i can make it a function, so that you don't know what it's doing.
//       -- $ports = adamasMagicFunction($ports_db); ?

$query  = 'SELECT *, I.`port_id` AS `port_id` FROM `ports` AS I';
$query .= ' LEFT JOIN `ports-state` AS S ON I.`port_id` = S.`port_id`';
$query .= ' WHERE `device_id` = ?';

$ports_db = dbFetchRows($query, array($device['device_id']));
foreach ($ports_db as $port) { $ports[$port['ifIndex']] = $port; }

// New interface detection
foreach ($port_stats as $ifIndex => $port)
{
  if (is_port_valid($port, $device))
  {
    if (!is_array($ports[$port['ifIndex']]))
    {
      $port_id = dbInsert(array('device_id' => $device['device_id'], 'ifIndex' => $ifIndex), 'ports');
      $ports[$port['ifIndex']] = dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($port_id));
      echo("Adding: ".$port['ifName']."(".$ifIndex.")(".$ports[$port['ifIndex']]['port_id'].")");
    } elseif ($ports[$ifIndex]['deleted'] == "1") {
      dbUpdate(array('deleted' => '0'), 'ports', '`port_id` = ?', array($ports[$ifIndex]['port_id']));
      log_event("Interface DELETED mark removed", $device, 'port', $ports[$ifIndex]);
      $ports[$ifIndex]['deleted'] = "0";
    }
  } else {
    if (isset($ports[$port['ifIndex']]) && $ports[$port['ifIndex']]['deleted'] != "1")
    {
      dbUpdate(array('deleted' => '1', 'ifLastChange' => date('Y-m-d H:i:s', $polled)), 'ports', '`port_id` = ?', array($ports[$ifIndex]['port_id']));
      log_event("Interface was marked as DELETED", $device, 'port', $ports[$ifIndex]);
      $ports[$ifIndex]['deleted'] = "1";
    }
  }
}
// End New interface detection

echo("\n");
// Loop ports in the DB and update where necessary
foreach ($ports as $port)
{
  if ($port['deleted']) { continue; } // Skip updating RRDs and DB if interface marked as DELETED (also skipped bad_if's)

  echo("Port " . $port['ifDescr'] . "(".$port['ifIndex'].") ");
  if ($port_stats[$port['ifIndex']] && $port['disabled'] != "1")
  { // Check to make sure Port data is cached.
    $this_port = &$port_stats[$port['ifIndex']];

    if ($device['os'] == "vmware" && preg_match("/Device ([a-z0-9]+) at .*/", $this_port['ifDescr'], $matches)) { $this_port['ifDescr'] = $matches[1]; }

    if ($device['os'] == 'zxr10') { $this_port['ifAlias'] = preg_replace("/^" . str_replace("/", "\\/", $this_port['ifName']) . "\s*/", '', $this_port['ifDescr']); }

    if ($device['os'] == 'ciscosb' && $this_port['ifType'] =='propVirtual' && is_numeric($this_port['ifDescr']))
    {
      $this_port['ifName'] = 'Vl'.$this_port['ifDescr'];
      //$this_port['ifDescr'] = 'Vlan'.$this_port['ifDescr'];
    }

    $polled_period = $polled - $port['poll_time'];

    $port['update'] = array();
    $port['state'] = array();

    $port['state']['poll_time'] = $polled;
    $port['state']['poll_period'] = $polled_period;

    // Store original port walked OIDs for debugging later
    if ($config['debug_port']['spikes'] || $config['debug_port'][$port['port_id']])
    {
      $debug_port = $this_port; // DEBUG
    }

#    // Copy ifHC[In|Out] values to non-HC if they exist
#    // Check if they're greater than zero to work around stupid devices which expose HC counters, but don't populate them. HERPDERP. - adama
#    if ($device['os'] == "netapp") { $hc_prefixes = array('HC', '64'); } else { $hc_prefixes = array('HC'); }
#    foreach ($hc_prefixes as $hc_prefix)
#    {
#      foreach (array('Octets', 'UcastPkts', 'BroadcastPkts', 'MulticastPkts') as $hc)
#      {
#        $hcin = 'if'.$hc_prefix.'In'.$hc;
#        $hcout = 'if'.$hc_prefix.'Out'.$hc;
#        if (is_numeric($this_port[$hcin]) && $this_port[$hcin] > 0 && is_numeric($this_port[$hcout]) && $this_port[$hcout] > 0)
#        {
#          echo(" ".$hc_prefix." $hc, ");
#          $this_port['ifIn'.$hc]  = $this_port[$hcin];
#          $this_port['ifOut'.$hc] = $this_port[$hcout];
#        }
#      }
#    }

    // If we're not using SNMPv1, assumt there are 64-bit values and overwrite the 32-bit OIDs.
    if ($device['snmpver'] != 'v1')
    {
      // NetApp are dumb. Very Dumb. They invent their own random OIDs for 64-bit values. Bad netapp, why you so dumb?
      if ($device['os'] == "netapp") { $hc_prefix = '64'; } else { $hc_prefix = 'HC'; }

      $port_has_64bit = is_numeric($this_port['if'.$hc_prefix.'InOctets']) && is_numeric($this_port['if'.$hc_prefix.'OutOctets']);

      // We've never tested for 64bit. Lets do it now. Lots of devices seem to not support 64bit counters for all ports.
      if ($port['port_64bit'] == NULL)
      {
        // We have 64-bit traffic counters. Lets set port_64bit
        if ($port_has_64bit)
        {
          $port['port_64bit'] = 1;
          $port['update']['port_64bit'] = 1;
        } else {
          $port['port_64bit'] = 0;
          $port['update']['port_64bit'] = 0;
        }
      }
      else if ($has_ifXEntry && $port_has_64bit && !$port['port_64bit'])
      {
        // Port changed to 64-bit
        $port['port_64bit'] = 1;
        $port['update']['port_64bit'] = 1;
        log_event('Interface changed: [HC] -> Counter64 (may cause disposable spike)', $device, 'port', $port);
      }
      //else if (!$port_has_64bit && $port['port_64bit'])
      //{
      //  // Port changed to 32-bit
      //  $port['port_64bit'] = 0;
      //  $port['update']['port_64bit'] = 0;
      //  log_event('Interface changed: [HC] -> Counter32', $device, 'port', $port);
      //}

      if ($port['port_64bit'])
      {
        print_debug("replacing with 64-bit...");
        foreach (array('Octets', 'UcastPkts', 'BroadcastPkts', 'MulticastPkts') as $hc)
        {
          $hcin = 'if'.$hc_prefix.'In'.$hc;
          $hcout = 'if'.$hc_prefix.'Out'.$hc;
          $this_port['ifIn'.$hc]  = $this_port[$hcin];
          $this_port['ifOut'.$hc] = $this_port[$hcout];
        }
      }
    }

    // rewrite the ifOperStatus (for active monitoring destinations)
    if ($smon_ports[$port['ifIndex']]) { $this_port['ifOperStatus'] = 'monitoring'; }

    // rewrite the ifPhysAddress
    if (strpos($this_port['ifPhysAddress'], ":"))
    {
      list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(":", $this_port['ifPhysAddress']);
      $this_port['ifPhysAddress'] = zeropad($a_a).zeropad($a_b).zeropad($a_c).zeropad($a_d).zeropad($a_e).zeropad($a_f);
    }

    // Overwrite ifSpeed with ifHighSpeed if it's over 10G
    if (is_numeric($this_port['ifHighSpeed']) && $this_port['ifSpeed'] > "1000000000")
    {
      echo("HighSpeed, ");
      $this_port['ifSpeed'] = $this_port['ifHighSpeed'] * 1000000;
    }

    // Overwrite ifDuplex with dot3StatsDuplexStatus if it exists
    if (isset($this_port['dot3StatsDuplexStatus']))
    {
      echo("dot3Duplex, ");
      $this_port['ifDuplex'] = $this_port['dot3StatsDuplexStatus'];
    }

    // Set VLAN and Trunk from Cisco
    if (isset($this_port['vlanTrunkPortEncapsulationOperType']) && $this_port['vlanTrunkPortEncapsulationOperType'] != "notApplicable")
    {
      $this_port['ifTrunk'] = $this_port['vlanTrunkPortEncapsulationOperType'];
      if (isset($this_port['vlanTrunkPortNativeVlan'])) { $this_port['ifVlan'] = $this_port['vlanTrunkPortNativeVlan']; }
    }

    if (isset($this_port['vmVlan']))
    {
      $this_port['ifVlan']  = $this_port['vmVlan'];
    }

    // Set VLAN and Trunk from Q-BRIDGE-MIB
    if (!isset($this_port['ifVlan']) && $q_bridge_untagged[$this_port['ifIndex']])
    {
      $this_port['ifVlan'] = $q_bridge_untagged[$this_port['ifIndex']];
    }
    if (!isset($this_port['ifVlan']) && isset($this_port['dot1qPvid']))
    {
      $this_port['ifVlan'] = $this_port['dot1qPvid'];
      if ((isset($this_port['dot1qPortIngressFiltering']) && $this_port['dot1qPortIngressFiltering'] == 'true') ||
          (isset($this_port['dot1qPortAcceptableFrameTypes']) && $this_port['dot1qPortAcceptableFrameTypes'] == 'admitOnlyVlanTagged'))
      {
        $this_port['ifTrunk'] = 'dot1Q';
      }
    }

    echo("VLAN == ".$this_port['ifVlan']);

    // Process ifAlias if needed
    if ($config['os'][$device['os']]['ifAliasSemicolon'])
    {
      list($this_port['ifDescr']) = explode(';', $this_port['ifDescr']);
    }

    // Update IF-MIB data
    $log_event = array();
    foreach ($data_oids as $oid)
    {
      if ($port[$oid] != $this_port[$oid])
      {
        if (isset($this_port[$oid]))
        {
          $port['update'][$oid] = $this_port[$oid];
          $msg = "[$oid] " . $port[$oid] . " -> " . $this_port[$oid];
        } else {
          $port['update'][$oid] = array('NULL');
          $msg = "[$oid] " . $port[$oid] . " -> NULL";
        }
        $log_event[] = $msg;
        if ($debug) { echo($msg." "); } else { echo($oid . " "); }
      }
    }
    if ((bool)$log_event) { log_event('Interface changed: ' . implode('; ', $log_event), $device, 'port', $port); }

    // Parse description (usually ifAlias) if config option set
    if ($custom_port_parser)
    {
      $log_event = array();
      if ($custom_port_parser !== 'old')
      {
        $port_ifAlias = custom_port_parser($this_port);
      } else {
        $port_attribs = array('type','descr','circuit','speed','notes');

        include($config['install_dir'] . "/" . $config['port_descr_parser']);
      }

      foreach ($port_attribs as $attrib)
      {
        $attrib_key = "port_descr_".$attrib;
        if ($port_ifAlias[$attrib] != $port[$attrib_key])
        {
          if (isset($port_ifAlias[$attrib]))
          {
            $port['update'][$attrib_key] = $port_ifAlias[$attrib];
            $msg = "[$attrib] " . $port[$attrib_key] . " -> " . $port_ifAlias[$attrib];
          } else {
            $port['update'][$attrib_key] = array('NULL');
            $msg = "[$attrib] " . $port[$attrib_key] . " -> NULL";
          }
          $log_event[] = $msg;
        }
      }
      if ((bool)$log_event) { log_event('Interface changed (attrib): ' . implode('; ', $log_event), $device, 'port', $port); }
    }
    // End parse ifAlias

    // Update IF-MIB metrics
    foreach ($stat_oids_db as $oid)
    {
      $port['state'][$oid] = $this_port[$oid];
      if (isset($port[$oid]))
      {
        $oid_diff = $this_port[$oid] - $port[$oid];
        $oid_rate  = $oid_diff / $polled_period;
        if ($oid_rate < 0)
        {
          print_warning("Negative $oid. Possible spike on next poll!");
          $port['stats'][$oid.'_negative_rate'] = $oid_rate;
          $oid_rate = "0";
        }
        $port['stats'][$oid.'_rate'] = $oid_rate;

        // Perhaps need to protect these from false polls.
        $port['alert_array'][$oid.'_rate']  = $oid_rate;
        $port['alert_array'][$oid.'_delta'] = $oid_diff;

        $port['stats'][$oid.'_diff'] = $oid_diff;
        $port['state'][$oid.'_rate'] = $oid_rate;
        $port['state'][$oid.'_delta'] = $oid_diff;
        print_debug("\n $oid ($oid_diff B) $oid_rate Bps $polled_period secs");
      }

    }

    foreach ($stat_oids as $oid)
    {
      // Update StatsD/Carbon
      if ($config['statsd']['enable'] == TRUE && !strpos($oid, "HC"))
      {
        StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'port'.'.'.$port['ifIndex'].'.'.$oid, $this_port[$oid]);
      }
    }

    $port['stats']['ifInBits_rate']  = round($port['stats']['ifInOctets_rate']  * 8);
    $port['stats']['ifOutBits_rate'] = round($port['stats']['ifOutOctets_rate'] * 8);

    $port['alert_array']['ifInBits_rate'] =  $port['stats']['ifInBits_rate'];
    $port['alert_array']['ifOutBits_rate'] =  $port['stats']['ifOutBits_rate'];

    // If we have been told to debug this port, output the counters we collected earlier, with the rates stuck on the end.
    if ($config['debug_port'][$port['port_id']])
    {
      print_debug("Wrote port debugging data");
      $debug_file   = "/tmp/port_debug_".$port['port_id'].".txt";
      //FIXME. I think formatted debug out (as for spikes) more informative, but output here more parsable as CSV
      $port_msg  = $port['port_id']."|".$polled."|".$polled_period."|".$debug_port['ifInOctets']."|".$debug_port['ifOutOctets']."|".$debug_port['ifHCInOctets']."|".$debug_port['ifHCOutOctets'];
      $port_msg .= "|".formatRates($port['stats']['ifInOctets_rate'])."|".formatRates($port['stats']['ifOutOctets_rate'])."|".$device['snmpver']."\n";
      file_put_contents($debug_file, $port_msg, FILE_APPEND);
    }

    // If we see a spike above ifSpeed or negative rate, output it to /tmp/port_debug_spikes.txt
    // Example how to read usefull info from this debug by grep:
    // grep -B 1 -A 6 'ID:\ 520' /tmp/port_debug_spikes.txt
    if ($config['debug_port']['spikes'] && $this_port['ifSpeed'] > "0" &&
        ($port['stats']['ifInBits_rate'] > $this_port['ifSpeed'] || $port['stats']['ifOutBits_rate'] > $this_port['ifSpeed'] ||
         isset($port['stats']['ifInOctets_negative_rate'])       || isset($port['stats']['ifOutOctets_negative_rate'])))
    {
      if (!$port['port_64bit']) { $hc_prefix = ''; }
      print_warning("Spike above ifSpeed or negative rate detected! See debug info here: ");
      $debug_file   = "/tmp/port_debug_spikes.txt";
      $debug_format = "| %20s | %20s | %20s |\n";
      $debug_msg  = sprintf("+%'-68s+\n", '');
      $debug_msg .= sprintf("|%67s |\n", $device['hostname']." ".$debug_port['ifDescr']." (ID: ".$port['port_id'].") ".formatRates($debug_port['ifSpeed'])." ".($port['port_64bit'] ? 'Counter64' : 'Counter32'));
      $debug_msg .= sprintf("+%'-68s+\n", '');
      $debug_msg .= sprintf("| %-20s | %-20s | %-20s |\n", 'Polled time', 'if'.$hc_prefix.'OutOctets', 'if'.$hc_prefix.'InOctets');
      $debug_msg .= sprintf($debug_format, '(prev) '.$port['poll_time'], $port['ifOutOctets'], $port['ifInOctets']);
      $debug_msg .= sprintf($debug_format, '(now)  '.$polled, $this_port['ifOutOctets'], $this_port['ifInOctets']);
      $debug_msg .= sprintf($debug_format, format_unixtime($polled), formatRates($port['stats']['ifOutBits_rate']*8), formatRates($port['stats']['ifInBits_rate']));
      $debug_msg .= sprintf("%'+70s\n", '');
      $debug_msg .= sprintf("| %-67s|\n", 'Port dump:');
      // Added full original port variable dump
      foreach ($debug_port as $debug_key => $debug_var)
      {
        $debug_msg .= sprintf("|  %-66s|\n", "'$debug_key' => '$debug_var',");
      }
      $debug_msg .= sprintf("+%'-68s+\n\n", '');
      file_put_contents($debug_file, $debug_msg, FILE_APPEND);
    }

    // Put States into alert array
    foreach (array('ifOperStatus', 'ifAdminStatus', 'ifMtu', 'ifDuplex') as $oid)
    {
      if (isset($this_port[$oid]))
      {
        $port['alert_array'][$oid] = $this_port[$oid];
      }
    }

    // If we have a valid ifSpeed we should populate the percentage stats for checking.
    if (is_numeric($this_port['ifSpeed']))
    {
      $port['stats']['ifInBits_perc'] = round($port['stats']['ifInBits_rate'] / $this_port['ifSpeed'] * 100);
      $port['stats']['ifOutBits_perc'] = round($port['stats']['ifOutBits_rate'] / $this_port['ifSpeed'] * 100);
      $port['alert_array']['ifSpeed'] = $this_port['ifSpeed'];
    }

    $port['state']['ifInOctets_perc'] = $port['stats']['ifInBits_perc'];
    $port['state']['ifOutOctets_perc'] = $port['stats']['ifOutBits_perc'];

    $port['alert_array']['ifInOctets_perc'] = $port['stats']['ifInBits_perc'];
    $port['alert_array']['ifOutOctets_perc'] = $port['stats']['ifOutBits_perc'];

    $port['alert_array']['rx_ave_pktsize']   = $port['state']['ifInOctets_delta'] / ($port['state']['ifInUcastPkts_delta'] + $port['state']['ifInNUcastPkts_delta']);
    $port['alert_array']['tx_ave_pktsize']   = $port['state']['ifOutOctets_delta'] / ($port['state']['ifOutUcastPkts_delta'] + $port['state']['ifOutNUcastPkts_delta']);

    echo('bps('.formatRates($port['stats']['ifInBits_rate']).'/'.formatRates($port['stats']['ifOutBits_rate']).')');
    echo('bytes('.formatStorage($port['stats']['ifInOctets_diff']).'/'.formatStorage($port['stats']['ifOutOctets_diff']).')');
    echo('pkts('.format_si($port['stats']['ifInUcastPkts_rate']).'pps/'.format_si($port['stats']['ifOutUcastPkts_rate']).'pps)');

    // Store aggregate in/out state
    $port['state']['ifOctets_rate']    = $port['stats']['ifOutOctets_rate'] + $port['stats']['ifInOctets_rate'];
    $port['state']['ifUcastPkts_rate'] = $port['stats']['ifOutUcastPkts_rate'] + $port['stats']['ifInUcastPkts_rate'];
    $port['state']['ifErrors_rate'] = $port['stats']['ifOutErrors_rate'] + $port['stats']['ifInErrors_rate'];

    // Update RRDs
    $rrdfile = get_port_rrdfilename($port);
    rrdtool_create($device, $rrdfile,"\
      DS:INOCTETS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTOCTETS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INERRORS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTERRORS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INUCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTUCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INNUCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTNUCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INDISCARDS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTDISCARDS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INUNKNOWNPROTOS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INBROADCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTBROADCASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:INMULTICASTPKTS:DERIVE:600:0:" . $config['max_port_speed'] . " \
      DS:OUTMULTICASTPKTS:DERIVE:600:0:" . $config['max_port_speed']);

    $this_port['rrd_update']  = array($this_port['ifInOctets'],        $this_port['ifOutOctets'],
                                      $this_port['ifInErrors'],        $this_port['ifOutErrors'],
                                      $this_port['ifInUcastPkts'],     $this_port['ifOutUcastPkts'],
                                      $this_port['ifInNUcastPkts'],    $this_port['ifOutNUcastPkts'],
                                      $this_port['ifInDiscards'],      $this_port['ifOutDiscards'],
                                      $this_port['ifInUnknownProtos'],
                                      $this_port['ifInBroadcastPkts'], $this_port['ifOutBroadcastPkts'],
                                      $this_port['ifInMulticastPkts'], $this_port['ifOutMulticastPkts']);

    rrdtool_update($device, $rrdfile, $this_port['rrd_update']);
    // End Update IF-MIB

    // Update PAgP
    if ($this_port['pagpOperationMode'] || $port['pagpOperationMode'])
    {
      $log_event = array();
      foreach ($pagp_oids as $oid)
      { // Loop the OIDs
        if ($this_port[$oid] != $port[$oid])
        { // If data has changed, build a query
          $port['update'][$oid] = $this_port[$oid];
          $log_event[] = "[$oid] " . $port[$oid] . " -> " . $this_port[$oid];
        }
      }
      if ((bool)$log_event) { log_event('Interface changed (pagp): ' . implode('; ', $log_event), $device, 'port', $port); }
    }
    // End Update PAgP

    // FIXME. Is that true include for EACH port? -- mike
    // Yes, but it's not expensive computationally. Use php-xcache. :) -- adama
    // Do EtherLike-MIB
    $ports_module = 'enable_ports_etherlike';
    if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module]))) { include("port-etherlike.inc.php"); }

    // Do ADSL MIB
    $ports_module = 'enable_ports_adsl';
    if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module]))) { include("port-adsl.inc.php"); }

    // Do PoE MIBs
    $ports_module = 'enable_ports_poe';
    if ($attribs[$ports_module] || ($config[$ports_module] && !isset($attribs[$ports_module]))) { include("port-poe.inc.php"); }

#    if ($debug || TRUE) { print_vars($port['alert_array']); echo(PHP_EOL); print_vars($this_port);}
#    print_vars($port['alert_array']);

    check_entity('port', $port, $port['alert_array']);

    // Send statistics array via AMQP/JSON if AMQP is enabled globally and for the ports module
    if ($config['amqp']['enable'] == TRUE && $config['amqp']['modules']['ports'])
    {
      $json_data = array_merge($this_port, $port['state']) ;
      unset($json_data['rrd_update']);
      messagebus_send(array('attribs' => array('t' => $polled, 'device' => $device['hostname'], 'device_id' => $device['device_id'], 'e_type' => 'port', 'e_index' => $port['ifIndex']), 'data' => $json_data));
      unset($json_data);
    }

#    // Do Alcatel Detailed Stats
#    if ($device['os'] == "aos") { include("port-alcatel.inc.php"); }

    //if ($this_port['ifOperStatus'] == 'down' && $this_port['ifAdminStatus'] == 'up')
    if (isset($port['update']['ifOperStatus']) || isset($port['update']['ifAdminStatus']))
    {
      $if_lastchange = $polled; // by default set ifLastChange as current polled time

      /** The value of sysUpTime at the time the interface entered
       * its current operational state. If the current state was
       * entered prior to the last re-initialization of the local
       * network management subsystem, then this object contains a
       * zero value.
       *
       * NOTE. But observium uses last change timestamp.
       */
      // Convert ifLastChange from timetick to timestamp
      if (isset($this_port['ifLastChange']) && $this_port['ifLastChange'] != '' && $this_port['ifLastChange'] != '0:0:00:00.00')
      {
        $this_port['ifLastChange'] = timeticks_to_sec($this_port['ifLastChange']);
        if (isset($cache['devices']['uptime'][$device['device_id']]))
        {
          $device_uptime = $cache['devices']['uptime'][$device['device_id']];
          if ($device_uptime['uptime'] <= $this_port['ifLastChange']) { $this_port['ifLastChange'] = 0; }
          print_debug('IFLASTCHANGE = '.$device_uptime['polled'].' - '.$device_uptime['uptime'].' + '.$this_port['ifLastChange']);
          $if_lastchange = $device_uptime['polled'] - $device_uptime['uptime'] + $this_port['ifLastChange'];
        }
      }

      $port['update']['ifLastChange'] = date('Y-m-d H:i:s', $if_lastchange); // Convert to timestamp
      print_debug('IFLASTCHANGE: '.$port['update']['ifLastChange']);
    }

    // Update Database
    if (count($port['update']))
    {
      $updated = dbUpdate($port['update'], 'ports', '`port_id` = ?', array($port['port_id']));
      print_debug("PORT updated rows=$updated");
    }

    // Update State
    if (count($port['state']))
    {
      if (empty($port['poll_time']))
      {
        $insert = dbInsert(array('port_id' => $port['port_id']), 'ports-state');
        if ($insert === FALSE) { print_error("Certain MEMORY DB error for table 'ports-state'."); }
        else { print_debug("STATE inserted port_id=".$port['port_id']); }
      }
      $updated = dbUpdate($port['state'], 'ports-state', '`port_id` = ?', array($port['port_id']));
      print_debug("STATE updated rows=$updated");
    }

    // End Update Database
  }
  elseif ($port['disabled'] != "1")
  {
    print_message("Port Deleted."); // Port missing from SNMP cache.
    if (isset($port['ifIndex']) && $port['deleted'] != "1")
    {
      dbUpdate(array('deleted' => '1', 'ifLastChange' => date('Y-m-d H:i:s', $polled)), 'ports',  '`device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $port['ifIndex']));
      log_event("Interface was marked as DELETED", $device, 'port', $port);
    }
  } else {
    print_message("Port Disabled.");
  }

  echo("\n");

  // Clear Per-Port Variables Here
  unset($this_port);
}

// Clear Variables Here
unset($port_stats);

// EOF

