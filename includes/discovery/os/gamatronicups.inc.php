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
