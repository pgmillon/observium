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

list($version) = explode(',', $poll_device['sysDescr']);

preg_match('/Version V(\S+) (.+) Software,/', $poll_device['sysDescr'], $matches);

$hardware = $matches[2];

// EOF
