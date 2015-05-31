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
  if (preg_match("/^WatchGuard\ Fireware/", $sysDescr) ||  preg_match("/^XTM/", $sysDescr)) { $os = "firebox"; }
}

// EOF
