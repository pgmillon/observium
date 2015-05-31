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
  if (strstr($sysDescr, "JETDIRECT")) { $os = "jetdirect"; }
  else if (strstr($sysDescr, "HP ETHERNET MULTI-ENVIRONMENT")) { $os = "jetdirect"; }
}

// EOF
