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

echo(" NS-ROOT-MIB ");

// Collect HAMode & HAState

$sysHighAvailabilityMode = snmp_get($device, 'sysHighAvailabilityMode.0', '-Ovq', 'NS-ROOT-MIB', mib_dirs('citrix'));
$haCurState              = snmp_get($device, 'haCurState.0',              '-Ovq', 'NS-ROOT-MIB', mib_dirs('citrix'));

if ($sysHighAvailabilityMode !== '' && $haCurState !== '')
{
  $descr = 'High Availability Status';
  // $oid   = '.1.3.6.1.4.1.5951.4.1.1.6.0'; $oid   = '1.3.6.1.4.1.5951.4.1.1.23.24.0';
  // $value = $sysHighAvailabilityMode; $value = $haCurState;
  discover_status($device, 'ns-root-mib-ha-0', '0', 'ns-root-mib-ha', $descr, NULL, array('entPhysicalClass' => 'other'));
}

unset($sysHighAvailabilityMode);
unset($haCurState);

// EOF
