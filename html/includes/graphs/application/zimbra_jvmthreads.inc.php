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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-zimbra-threads.rrd");

$array = array(
               'ImapSSLServer' => array('descr' => 'IMAP SSL Server'),
               'ImapServer' => array('descr' => 'IMAP Server'),
               'LmtpServer' => array('descr' => 'LMTP Server'),
               'Pop3SSLServer' => array('descr' => 'POP3 SSL Server'),
               'Pop3Server' => array('descr' => 'POP3 Server'),
               'GC' => array('descr' => 'Garbage Collection'),
               'AnonymousIoService' => array('descr' => 'Anonymous I/O Service'),
               'CloudRoutingReader' => array('descr' => 'Cloud Routing Reader'),
               'ScheduledTask' => array('descr' => 'Scheduled Task'),
               'SocketAcceptor' => array('descr' => 'Socket Acceptor'),
               'Thread' => array('descr' => 'Thread'),
               'Timer' => array('descr' => 'Timer'),
               'btpool' => array('descr' => 'BT Pool'),
               'pool' => array('descr' => 'Pool'),
               'other' => array('descr' => 'Other'),
              );

$nototal = 1;
$colours = "mixed";
$unit_text = "Threads";

$i = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $variables)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $variables['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = ($variables['colour'] ? $variables['colour'] : $config['graph_colours'][$colours][$i]);
    $i++;
  }
} else { echo("file missing: $file");  }

include("includes/graphs/generic_multi_simplex_separated.inc.php");

unset($rrd_list);

$noheader = 1;

$array = array(
               'total' => array('descr' => 'Total', 'colour' => '000000'),
              );

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $variables)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $variables['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = ($variables['colour'] ? $variables['colour'] : $config['graph_colours'][$colours][$i]);
    $i++;
  }
} else { echo("file missing: $file"); }

include("includes/graphs/generic_multi_line.inc.php");

// EOF
