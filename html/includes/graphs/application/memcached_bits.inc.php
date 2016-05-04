<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include("memcached.inc.php");
include_once($config['html_dir']."/includes/graphs/common.inc.php");

$multiplier = 8;

$ds_in = "bytes_read";
$ds_out = "bytes_written";

include("includes/graphs/generic_data.inc.php");

// EOF
