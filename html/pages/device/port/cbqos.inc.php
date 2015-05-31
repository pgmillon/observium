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

$cbqos_array = dbFetchRows('SELECT * FROM `ports_cbqos` WHERE `port_id` = ?', array($port['port_id']));

echo('<table class="table table-hover table-condensed table-bordered table-striped">');

foreach ($cbqos_array as $cbqos)
{
  $graph_array['id']     = $cbqos['cbqos_id'];

  echo('<tr><th>');
  echo('<h4>'.$cbqos['policy_name'].' / '.$cbqos['object_name'].' ('.$cbqos['direction'].')</h4>');
  echo '</th></tr>';

  echo('<tr><td>');
  echo('<h4>Packets</h4>');
  $graph_array['type']   = 'cbqos_pkts';
  print_graph_row($graph_array);

  echo('<h4>Bits</h4>');
  $graph_array['type']   = 'cbqos_bits';
  print_graph_row($graph_array);

  echo('</td></tr>');
}

echo('</table>');

// EOF
