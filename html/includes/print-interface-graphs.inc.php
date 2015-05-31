<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

global $config;

$graph_array['to']     = $config['time']['now'];
$graph_array['id']     = $port['port_id'];
$graph_array['type']   = $graph_type;

print_graph_row($graph_array);

?>
