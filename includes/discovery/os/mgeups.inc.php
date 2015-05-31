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
  if (strstr($sysDescr, "Pulsar M")) { $os = "mgeups"; }
  else if (preg_match("/^Galaxy /i", $sysDescr)) { $os = "mgeups"; }
  else if (preg_match("/^Evolution /", $sysDescr)) { $os = "mgeups"; }
  else if ($sysDescr == "MGE UPS SYSTEMS - Network Management Proxy") { $os = "mgeups"; }
}

// EOF
