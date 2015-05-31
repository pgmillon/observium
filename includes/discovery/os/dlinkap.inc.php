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
  if (preg_match("/D-Link .* AP/", $sysDescr)) { $os = "dlinkap"; }
  else if (preg_match("/D-Link DAP-/", $sysDescr)) { $os = "dlinkap"; }
  else if (preg_match("/D-Link Access Point/", $sysDescr)) { $os = "dlinkap"; }
}

// EOF
