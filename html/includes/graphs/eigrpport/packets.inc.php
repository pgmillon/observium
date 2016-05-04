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

$array = array('UMcasts'         => array('descr' => 'Unreliable Mcast', 'colour' => '22FF22'),
               'RMcasts'         => array('descr' => 'Reliable Mcast', 'colour' => '0022FF'),
               'UUcasts'         => array('descr' => 'Unreliable Ucast', 'colour' => 'FF0000'),
               'RUcasts'         => array('descr' => 'Reliable Ucast', 'colour' => '00AAAA'),
               'McastExcepts'    => array('descr' => 'Mcast Excepts', 'colour' => 'FF00FF'),
               'CRpkts'          => array('descr' => 'CR Packets', 'colour' => 'FFA500'),
               'AcksSuppressed'  => array('descr' => 'Acks Suppressed', 'colour' => 'CC0000'),
               'RetransSent'     => array('descr' => 'Retransmits Sent', 'colour' => '0000CC'),
               'OOSrvcd'         => array('descr' => 'Out-of-Sequence', 'colour' => '0080C0'),
);

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $entry)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $entry['descr'];
    $rrd_list[$i]['ds'] = $ds;
#    $rrd_list[$i]['colour'] = $entry['colour'];
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Packets";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
