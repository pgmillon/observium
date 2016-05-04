<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if (!$os)
{
  //FIXME. Make it generic for printers group
  if (strstr(snmp_get($device, 'prtGeneralServicePerson.1', '-OQv', 'Printer-MIB', mib_dirs()), 'Samsung')) { $os = 'samsung'; }
}

// EOF
