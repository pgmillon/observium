<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/* Detection for JDSU OEM Erbium Dotted Fibre Amplifiers */

if (!$os)
{
  if (strstr(snmp_get($device, 'commonDeviceVendorInfo.1', '-OQv', 'NSCRTV-ROOT'), 'JDSU') &&
      strstr(snmp_get($device, 'commonDeviceName.1', '-OQv', 'NSCRTV-ROOT'), 'EDFA')) { $os = 'jdsu_edfa'; }
}

// EOF
