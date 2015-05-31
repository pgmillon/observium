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

$version = preg_replace("/.+ version (.+) running on .+ (\S+)$/", "\\1||\\2", $poll_device['sysDescr']);
list($version,$hardware) = explode("||", $version);

// EOF
