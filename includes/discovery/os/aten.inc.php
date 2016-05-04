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

if (!$os && ($sysObjectId == '.1.3.6.1.4.1.21317' || strpos($sysObjectId, '.1.3.6.1.4.1.21317.') === 0))
{
  $os = 'aten';
  // ATEN-PE-CFG::modelName.0 = STRING: "PE8108G"
  if (strlen(snmp_get($device, "modelName.0", "-Osqnv", "ATEN-PE-CFG", mib_dirs('aten')))) { $os = "aten-pdu"; }
  // FIXME. Other possible: KVM over IP, and Serial over IP
}

// EOF
