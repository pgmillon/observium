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
  if ($sysDescr == "SNMP TME") { $os = "papouch"; }
  else if ($sysDescr == "TME") { $os = "papouch"; }
  else if ($sysDescr == "TH2E") { $os = "papouch"; }
}

// EOF
