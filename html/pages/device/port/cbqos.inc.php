<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$cbqos_array = dbFetchRows('SELECT * FROM `ports_cbqos` WHERE `port_id` = ?', array($port['port_id']));

foreach ($cbqos_array as $cbqos)
{

  echo generate_box_open(array('title' => $cbqos['policy_name'].' / '.$cbqos['object_name'].' ('.$cbqos['direction'].')'));

  echo('<table class="table table-hover table-condensed  table-striped">');

  $graph_array['id']     = $cbqos['cbqos_id'];

  echo('<tr><td>');
  echo('<h3>Packets</h3>');
  $graph_array['type']   = 'cbqos_pkts';
  print_graph_row($graph_array);

  echo('<h3>Bits</h3>');
  $graph_array['type']   = 'cbqos_bits';
  print_graph_row($graph_array);

  echo('</td></tr>');

  echo('</table>');

  echo generate_box_close();

}

// EOF
