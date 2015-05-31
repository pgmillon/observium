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
  if (preg_match("/^SunOS/", $sysDescr))
  {
    $os = "solaris";
    list(,,$version) = explode (" ", $sysDescr);
    if ($version > "5.10") { $os = "opensolaris"; }
    if ($version > "5.10") {
      if (preg_match("/oi_/", $sysDescr)) { $os = "openindiana"; }
    }
  }

  if (strstr($sysDescr, "Nexenta")) { $os = "nexenta"; }
}

// EOF
