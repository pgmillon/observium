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

$version = preg_replace("/.+ version (.+) running on .+ (\S+)$/", "\\1||\\2", $poll_device['sysDescr']);
list($version,$hardware) = explode("||", $version);

// EOF
