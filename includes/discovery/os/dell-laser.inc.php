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
  if (strstr($sysDescr, "Dell Color Laser")) { $os = "dell-laser"; }
  elseif (strstr($sysDescr, "Dell Laser Printer")) { $os = "dell-laser"; }
  elseif (preg_match("/^Dell.*MFP/", $sysDescr)) { $os = "dell-laser"; }
}

// EOF
