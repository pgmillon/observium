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

// First check the sysObjectID, then the sysDescr
if (strstr($sysObjectId, ".1.3.6.1.4.1.332.11.6"))
{
  // FIXME. I not know what it's, but this is not Digi:
  // T.I.M.: Itelsis Module which provides SNMP and HTTP telemanagement.
  // T.I.M.: Module which provides SNMP and HTTP telemanagement.
  if (!strstr($sysDescr, "T.I.M."))     { $os = "digios"; }
  if      (strstr($sysDescr, "AnywhereUSB")) { $os = "digi-anyusb"; }
  else if (strstr(snmp_get($device, "mdu12Ident.0", "-Oqv", "TSL-MIB", mib_dirs('tsl')), "MDU12")) { $os = "tsl-mdu12"; }
}

// EOF
