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
  if (stristr($sysDescr, "Blade Network Technologies")) { $os = "bnt"; }
  if (preg_match("/^BNT /", $sysDescr)) { $os = "bnt"; }
}

// EOF
