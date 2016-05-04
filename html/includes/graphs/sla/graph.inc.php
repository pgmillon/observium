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

if (empty($sla['sla_graph']))
{
  // Compatability
  $sla['sla_graph'] = ((stripos($sla['rtt_type'], 'jitter') !== FALSE) ? 'jitter' : 'echo');
}

switch($sla['sla_graph'])
{
  case 'jitter':
    include("jitter.inc.php");
    break;
  default:
    $subtype = "echo";
    include($config['html_dir'] . "/includes/graphs/generic_definition.inc.php");
}

// EOF
