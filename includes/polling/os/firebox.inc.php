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

preg_match("/v(.*)/", $poll_device['sysDescr'], $matches);

$version = (isset($matches[1]) ? $matches[1] : "");

// EOF
