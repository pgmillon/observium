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
  if (preg_match("/^Avocent/", $sysDescr)) { $os = "avocent"; }
  if (preg_match("/^AlterPath/", $sysDescr)) { $os = "avocent"; }
}

// EOF
