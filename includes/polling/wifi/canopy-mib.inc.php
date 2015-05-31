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

echo(" CANOPY-MIB ");

if (preg_match("/AP/", $version, $regexp_result))
{
  $wificlients1 = snmp_get($device, "regCount.0", "-Ovq", "WHISP-APS-MIB", mib_dirs('cambium'));
}

// EOF
