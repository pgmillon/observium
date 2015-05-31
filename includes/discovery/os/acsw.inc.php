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
  if (strstr($sysDescr, "Cisco Application Control Software")) { $os = "acsw"; }
  else if (strstr($sysDescr, "Application Control Engine")) { $os = "acsw"; }
  else if (strstr($sysDescr, "ACE")) { $os = "acsw"; }

}

// EOF
