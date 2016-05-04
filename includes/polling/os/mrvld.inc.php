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

$hardware = trim(snmp_get($device, "oaLdCardBackplanePN.0", "-OQv", "OADWDM-MIB"),'"');

$serial = trim(snmp_get($device, "oaLdCardBackplaneSN.0", "-OQv", "OADWDM-MIB"),'"');

$version = trim(snmp_get($device, "oaLdSoftVersString.0", "-OQv", "OADWDM-MIB"),'"');
# Version is a null termianted hex-string, convert to normal string and strip null bytes
$version = trim(snmp_hexstring($version),"\x00");

// EOF
