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
    if (preg_match("/^Supermicro Switch/", $sysDescr)) { $os = "supermicro-switch"; }
    else if (preg_match("/^SSE-/", $sysDescr)) { $os = "supermicro-switch"; }
    else if (preg_match("/^SBM-/", $sysDescr)) { $os = "supermicro-switch"; }
}

// EOF
