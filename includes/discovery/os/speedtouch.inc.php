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
  if (strpos($sysDescr, "TG585v7") !== FALSE) { $os = "speedtouch"; }
  else if (strpos($sysDescr, "SpeedTouch ") !== FALSE) { $os = "speedtouch"; }
  else if (preg_match("/^ST\d/", $sysDescr)) { $os = "speedtouch"; }
}

// EOF
