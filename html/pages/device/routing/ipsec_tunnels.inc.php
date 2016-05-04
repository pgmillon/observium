<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (!isset($vars['view'])) { $vars['view'] = 'details'; }
unset($navbar);

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'ipsec_tunnels');

$navbar['options_right']['details']['text'] = 'No Graphs';
if ($vars['view'] == 'details') { $navbar['options_right']['details']['class'] .= ' active'; }
$navbar['options_right']['details']['url'] = generate_url($vars, array('view' => 'details', 'graph' => 'NULL'));

$navbar['options_right']['bits']['text'] = 'Bits';
if ($vars['graph'] == 'bits') { $navbar['options_right']['bits']['class'] .= ' active'; }
$navbar['options_right']['bits']['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => 'bits'));

$navbar['options_right']['pkts']['text'] = 'Packets';
if ($vars['graph'] == 'pkts') { $navbar['options_right']['pkts']['class'] .= ' active'; }
$navbar['options_right']['pkts']['url'] = generate_url($vars, array('view' => 'graphs', 'graph' => 'pkts'));

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'IPSec Tunnels';
print_navbar($navbar);

switch ($vars['view'])
{
  case 'bits':
  case 'pkts':
    $table_class = 'table-striped-two';
    break;
  default:
    $table_class = 'table-striped';
}

echo('<table class="table table-hover  '.$table_class.' table-condensed ">
<thead><tr><th>Local address</th><th></th><th>Peer address</th><th>Tunnel name</th><th>State</th></tr></thead>');

foreach (dbFetchRows("SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ? AND `peer_addr` != '' ORDER BY `peer_addr`", array($device['device_id'])) as $tunnel)
{
  if ($tunnel['tunnel_status'] == 'active') { $tunnel_class = 'green'; } else { $tunnel_class = 'red'; }

  // FIXME - Solves leading zeros in IPs - This should maybe be in ipsec polling instead
  $local_addr = preg_replace('/\b0+\B/','',$tunnel['local_addr']);
  $peer_addr  = preg_replace('/\b0+\B/','',$tunnel['peer_addr']);

  echo('<tr class="'.$tunnel['html_row_class'].'">
  <td style="width: 40px;">' . $local_addr . '</td>
  <td style="width: 30px;"><b>&#187;</b></td>
  <td style="width: 50px;">' . $peer_addr . '</td>
  <td style="width: 30px;">' . $tunnel['tunnel_name'] . '</td>
  <td style="width: 30px;"><strong><span class="'.$tunnel_class.'">' . $tunnel['tunnel_status'] . '</span></strong></td>
  </tr>');

  switch ($vars['graph'])
  {
    case 'bits':
    case 'pkts':
      $graph_array['type']   = 'ipsectunnel_' . $vars['graph'];
      $graph_array['id']     = $tunnel['tunnel_id'];
  }

  if ($vars['graph'] == 'bits' || $vars['graph'] == 'pkts') { $tunnel['graph'] = 1; }

  if ($tunnel['graph'])
  {
    $graph_array['to']     = $config['time']['now'];

    echo('<tr class="'.$tunnel['html_row_class'].'">');
    echo('<td colspan="5">');

    print_graph_row($graph_array);

    echo('</td></tr>');
  }
}

echo('</table>');

// EOF
