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
  if (strstr($sysObjectId, ".1.3.6.1.4.1.1916.2"))
  {
    if (strstr($sysDescr, "XOS"))
    {
      $os = "xos";
    } else {
      $os = "extremeware";
    }
  }
}

// EOF
