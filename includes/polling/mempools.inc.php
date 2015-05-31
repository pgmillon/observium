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

$db_version = get_db_version(); // Need for detect old (non-mib) mempools (CLEANME remove in r6000)

$sql  = "SELECT *, `mempools`.mempool_id as mempool_id";
$sql .= " FROM  `mempools`";
$sql .= " LEFT JOIN  `mempools-state` ON  `mempools`.mempool_id =  `mempools-state`.mempool_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $mempool)
{
  if ($db_version < 129) { $mempool['mempool_mib'] = $mempool['mempool_type']; } // CLEANME remove this line in r6000

  $mempool_rrd = "mempool-" . $mempool['mempool_mib'] . "-" . $mempool['mempool_index'] . ".rrd";

  $file = $config['install_dir']."/includes/polling/mempools/".$mempool['mempool_mib'].".inc.php";
  if ($db_version < 129) // CLEANME Remove in r6000
  {
    // Hard-coded renaming
    $mempool_rename = array(
      'dlink'           => 'AGENT-GENERAL-MIB',
      'aos-device'      => 'ALCATEL-IND1-HEALTH-MIB',
      'acme'            => 'APSYSMGMT-MIB',
      'asyncos'         => 'ASYNCOS-MAIL-MIB',
      'ciena'           => 'CIENA-TOPSECRET-MIB',
      'cemp'            => 'CISCO-ENHANCED-MEMPOOL-MIB',
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
    $file = str_replace($mempool['mempool_mib'].'.inc.php', strtolower($mempool_rename[$mempool['mempool_mib']]).'.inc.php', $file);
  }

  if (is_file($file))
  {
    $cache_mempool = NULL;
    $index         = $mempool['mempool_index'];

    include($file);
  } else {
    continue;
  }

  if (!$mempool['mempool_precision']) { $mempool['mempool_precision'] = 1; }
  if (isset($mempool['total']) && isset($mempool['used']))
  {
    $mempool['perc']   = round($mempool['used'] / $mempool['total'] * 100, 2);
    $mempool['total'] *= $mempool['mempool_precision'];
    $mempool['used']  *= $mempool['mempool_precision'];
  }
  elseif (isset($mempool['total']) && isset($mempool['perc']))
  {
    $mempool['total'] *= $mempool['mempool_precision'];
    $mempool['used']   = $mempool['total'] * $mempool['perc'] / 100;
  }
  elseif (isset($mempool['perc']))
  {
    $mempool['total']  = 100;
    $mempool['used']   = $mempool['perc'];
  } else {
    // Hrrmm.. it looks like empty snmp walk
    continue;
  }
  $mempool['free'] = $mempool['total'] - $mempool['used'];

  $hc = ($mempool['mempool_hc'] ? ' (HC)' : '');

  print_message("Mempool ". $mempool['mempool_descr'] . ': '.$mempool['perc'].'%%'.$hc);

  // Update StatsD/Carbon
  if ($config['statsd']['enable'] == TRUE)
  {
    StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'mempool'.'.'.$mempool['mempool_mib'] . "." . $mempool['mempool_index'].".used", $mempool['used']);
    StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'mempool'.'.'.$mempool['mempool_mib'] . "." . $mempool['mempool_index'].".free", $mempool['free']);
  }

  rrdtool_create($device, $mempool_rrd, " DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U ");
  rrdtool_update($device, $mempool_rrd,"N:".$mempool['used'].":".$mempool['free']);

  if (!is_numeric($mempool['mempool_polled'])) { dbInsert(array('mempool_id' => $mempool['mempool_id']), 'mempools-state'); }

  $mempool['state'] = array('mempool_polled' => time(),
                            'mempool_used' => $mempool['used'],
                            'mempool_perc' => $mempool['perc'],
                            'mempool_free' => $mempool['free'],
                            'mempool_total' => $mempool['total']);

  dbUpdate($mempool['state'], 'mempools-state', '`mempool_id` = ?', array($mempool['mempool_id']));
  $graphs['mempool'] = TRUE;

  check_entity('mempool', $mempool, array('mempool_perc' => $mempool['perc'], 'mempool_free' => $mempool['free'], 'mempool_used' => $mempool['used']));

  echo(PHP_EOL);
}

unset($cache_mempool, $mempool, $index);

// EOF
