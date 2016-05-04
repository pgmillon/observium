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

// SMON-MIB

// Get monitoring ([e|r]span) ports
$smon_statuses = snmpwalk_cache_oid($device, "portCopyStatus", array(), "SMON-MIB", mib_dirs());
foreach ($smon_statuses as $smon_index => $smon)
{
  list(,$smon_index) = explode('.', $smon_index); // ifIndex
  if ($smon['portCopyStatus'] == 'active' && isset($port_stats[$smon_index]))
  {
    // rewrite the ifOperStatus (for active monitoring destinations)
    $port_stats[$smon_index]['ifOperStatus'] = 'monitoring';
  }
}

unset($smon, $smon_statuses, $smon_index);

// EOF
