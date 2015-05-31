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

list(,$hardware,$type,$version, $date, $revision, $rest) = split('; ',$device['sysDescr'],7);

// EOF
