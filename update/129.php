<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo ' Rename old mempools RRDs and DB: ';

// Update mempools table
dbQuery("ALTER TABLE `mempools` CHANGE `mempool_type` `mempool_mib` VARCHAR(64);");

$mempool_rename = array(
  'dlink'           => 'AGENT-GENERAL-MIB',
  'aos-device'      => 'ALCATEL-IND1-HEALTH-MIB',
  'acme'            => 'APSYSMGMT-MIB',
  'asyncos'         => 'ASYNCOS-MAIL-MIB',
  'ciena'           => 'CIENA-TOPSECRET-MIB',
  'cemp'            => 'CISCO-ENHANCED-MEMPOOL-MIB', // Ohh fuck.. 26 chars
  'qfp'             => 'CISCO-ENTITY-QFP-MIB',
  'cmp'             => 'CISCO-MEMORY-POOL-MIB',
  'powerconnect-cpu' => 'Dell-Vendor-MIB', // FASTPATH-SWITCHING-MIB
  'xos'             => 'EXTREME-BASE-MIB',
  'ftos-cseries'    => 'F10-C-SERIES-CHASSIS-MIB',
  'ftos-eseries'    => 'F10-CHASSIS-MIB',
  'ftos-sseries'    => 'F10-S-SERIES-CHASSIS-MIB',
  'fortigate'       => 'FORTINET-FORTIGATE-MIB',
  'ironware-dyn'    => 'FOUNDRY-SN-AGENT-MIB',
  'airos'           => 'FROGFOOT-RESOURCES-MIB',
  'hh3c'            => 'HH3C-ENTITY-EXT-MIB',
  'hrstorage'       => 'HOST-RESOURCES-MIB',
  'vrp'             => 'HUAWEI-ENTITY-EXTENT-MIB',
  'juniperive'      => 'JUNIPER-IVE-MIB',
  'junos'           => 'JUNIPER-MIB',
  //'junos'           => 'JUNIPER-SRX5000-SPU-MONITORING-MIB', # rewrited
  'screenos'        => 'NETSCREEN-RESOURCE-MIB',
  'hpLocal'         => 'NETSWITCH-MIB',
  //'hpGlobal'        => 'NETSWITCH-MIB', # excluded (same as hpLocal)
  'netscaler'       => 'NS-ROOT-MIB',
  'seos'            => 'RBN-MEMORY-MIB',
  'avaya-ers'       => 'S5-CHASSIS-MIB',
  'nos'             => 'SW-MIB',
  'trapeze'         => 'TRAPEZE-NETWORKS-SYSTEM-MIB'
  );

foreach ($mempool_rename as $old_mib => $new_mib)
{
  $new_mib = strtolower($new_mib);
  foreach (dbFetchRows("SELECT `hostname`, `mempool_id`, `mempool_mib`, `mempool_index` FROM `mempools` LEFT JOIN `devices` ON `mempools`.`device_id` = `devices`.`device_id` WHERE `mempools`.`mempool_mib` = ?;", array($old_mib)) as $entry)
  {
    $old_rrd  = $config['rrd_dir'] . "/" . $entry['hostname'] . "/" . safename("mempool-" . $old_mib . "-" . $entry['mempool_index'] . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/" . $entry['hostname'] . "/" . safename("mempool-" . $new_mib . "-" . $entry['mempool_index'] . ".rrd");

    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); }
    dbUpdate(array('mempool_mib' => $new_mib), 'mempools', '`mempool_id` = ?', array($entry['mempool_id']));
    echo('.');
  }
}

echo(PHP_EOL);

// EOF
