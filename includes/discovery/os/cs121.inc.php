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

// Unable to detect via sysObjectID - device returns UPS-MIB base oid.

if (!$os)
{
  if (strpos($sysDescr, 'CS121 v') !== FALSE) { $os = "cs121"; }
}

// EOF
