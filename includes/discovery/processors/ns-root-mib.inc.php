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

#root@alpha:/home/observium/dev# snmpwalk -v2c -c // -M mibs -m +NS-ROOT-MIB netscaler.test nsCPUTable
#NS-ROOT-MIB::nsCPUname."cpu0" = STRING: "cpu0"
#NS-ROOT-MIB::nsCPUusage."cpu0" = Gauge32: 0

echo("NS-ROOT-MIB ");

//echo(" Caching OIDs:");

if (!is_array($nsaarray))
{
  $nsarray = array();
  //echo(" nsCPUTable ");
  $nsarray = snmpwalk_cache_multi_oid($device, "nsCPUTable", $nsarray, "NS-ROOT-MIB", mib_dirs('citrix'));
}

foreach ($nsarray as $descr => $data)
{

  $current = $data['nsCPUusage'];

  $oid = ".1.3.6.1.4.1.5951.4.1.1.41.6.1.2." . string_to_oid($descr);
  $descr = $data['nsCPUname'];

  discover_processor($valid['processor'], $device, $oid, $descr, "netscaler", $descr, 1, $current, NULL, NULL);
}

unset($nsarray, $oid, $descr, $current);

// EOF
