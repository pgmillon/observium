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

if (!$os)
{
  if (strstr($sysDescr, 'Samsung ML') || strstr($sysDescr, 'Samsung SC')) { $os = 'samsung'; }
  elseif (strstr(snmp_get($device, 'Printer-MIB::prtGeneralServicePerson.1', '-OQv'), 'Samsung')) { $os = 'samsung'; }
}

// EOF
