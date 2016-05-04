<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (preg_match('/Clavister (?:cOS )?Core(?:Plus)? (?<version>\d[\d\.]+)/', $poll_device['sysDescr'], $matches))
{
  //Clavister cOS Core 10.21.02.01-25325
  //Clavister CorePlus 9.30.08.21-22257 TP
  //Clavister CorePlus 9.30.04.10-18175
  $version = $matches['version'];
}

// EOF
