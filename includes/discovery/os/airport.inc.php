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
  if (strpos($sysDescr, "Apple AirPort") !== FALSE) { $os = "airport"; }
  else if (strpos($sysDescr, "Apple Base Station") !== FALSE) { $os = "airport"; }
  else if (strpos($sysDescr, "Base Station V3.84") !== FALSE) { $os = "airport"; }
}

// EOF
