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

// sysDescr: STRING:  LANCOM OAP-321 8.62.0086 / 07.11.2012 4002787218100104

list($os_type, $hardware, $version, , $release_date, $serial) = explode(" ", $poll_device['sysDescr']);

// EOF
