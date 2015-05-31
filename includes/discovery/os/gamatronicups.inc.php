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
  if ($sysDescr == "")
  {
    if (snmp_get($device, "GAMATRONIC-MIB::psUnitManufacture.0", "-Oqv", "") == "Gamatronic")
    {
      $os = "gamatronicups";
    }
  }
}

// EOF
