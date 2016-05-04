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

$mib = 'F5-BIGIP-SYSTEM-MIB';
echo("$mib ");

$tmm_processor = snmpwalk_cache_multi_oid($device, "sysTmmStatTmUsageRatio5m", NULL, $mib, mib_dirs('f5'));

foreach ($tmm_processor as $index => $entry)
{
  # converts the index as string to decimal ascii codes
  foreach (str_split($index) as $char)
  {
    $decimal_char=ord($char);
    $decimal_index.=".$decimal_char";
  }
  $oid = ".1.3.6.1.4.1.3375.2.1.8.2.3.1.39.3" . $decimal_index;
  $descr = "TMM $index Processor";
  $usage = $entry['sysTmmStatTmUsageRatio5m'];

  discover_processor($valid['processor'], $device, $oid, $index, "f5-bigip-tmm", $descr, 1, $usage, NULL, NULL);
  unset($decimal_index);
}

unset ($tmm_processor, $index, $used);

// EOF
