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
  if (preg_match("/8VD-X20/", $sysDescr)) { $os = "minkelsrms"; }
  if (preg_match("/SensorProbe/i", $sysDescr)) { $os = "sensorprobe"; }
}

// EOF
