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

//list(,$hardware,$version) = explode(' ',$poll_device['sysDescr']);

preg_match('/(?:Alcatel-Lucent\ |)(?P<hardware>[\w\-\ ]*)(?P<version>(?:\d+\.){2,}\w+)/', $poll_device['sysDescr'], $matches);
$hardware = trim($matches['hardware']);
if ($hardware === '') { $hardware = 'Generic'; }
$version  = $matches['version'];

// EOF
