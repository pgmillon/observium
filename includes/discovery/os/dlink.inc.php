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
  if (preg_match("/D-Link DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/Dlink DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/^DES-/", $sysDescr)) { $os = "dlink"; }
  else if (preg_match("/^DGS-/", $sysDescr)) { $os = "dlink"; }
}

// EOF
