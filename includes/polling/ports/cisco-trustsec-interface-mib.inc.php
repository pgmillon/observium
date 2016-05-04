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

// Cisco TrustSec OIDs
// CISCO-TRUSTSEC-INTERFACE-MIB::ctsiIfControllerState.27 = INTEGER: open(6)

// Get TrustSec port status
$trustsec_statuses = snmpwalk_cache_oid($device, "ctsiIfControllerState", array(), "CISCO-TRUSTSEC-INTERFACE-MIB", mib_dirs('cisco'));

// print_r($trustsec_statuses);
foreach ($trustsec_statuses as $ts_index => $ts)
{
  if ($ts['ctsiIfControllerState'] === 'open' && isset($port_stats[$ts_index]))
  {
    // set port at encrypted
    $port_stats[$ts_index]['encrypted'] = '1';
  }
}

unset($ts, $ts_statuses, $ts_index);

// EOF
