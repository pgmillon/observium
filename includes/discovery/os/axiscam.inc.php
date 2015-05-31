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
  if (preg_match("/AXIS .* Network Camera/", $sysDescr)) { $os = "axiscam"; }
  if (preg_match("/AXIS .* Video Server/", $sysDescr)) { $os = "axiscam"; }
}

// EOF
