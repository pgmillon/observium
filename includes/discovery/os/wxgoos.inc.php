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
  if ($sysDescr == 'NETOS 6.0')
  {
    if (strstr($sysObjectId, ".1.3.6.1.4.1.901.1")) { $os = "wxgoos"; }
  }
}

// EOF
