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

preg_match("/v(.*)/", $poll_device['sysDescr'], $matches);

$version = (isset($matches[1]) ? $matches[1] : "");

// EOF
