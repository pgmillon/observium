<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (strstr($poll_device['sysDescr'], "AlterPath"))
{
  list($hardware, $version) = explode("-", trim(str_replace("version:", "", (str_replace("V_", "", $poll_device['sysDescr'])))), 2);
  $hardware = trim($hardware);
  $version  = trim($version);
  $features = trim(str_replace("#1", "", $version));
}

// EOF
