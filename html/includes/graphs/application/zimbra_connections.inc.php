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

$rrd_filename = get_rrd_path($device, 'app-zimbra-mailboxd.rrd');

$array = array(
               'imapSslConn' => array('descr' => 'IMAP SSL', 'colour' => '6EB7FF'),
               'imapConn' => array('descr' => 'IMAP', 'colour' => '0082FF'),
               'popSslConn' => array('descr' => 'POP3 SSL', 'colour' => '8FCB73'),
               'popConn' => array('descr' => 'POP3', 'colour' => '3AB419'),
               'lmtpConn' => array('descr' => 'LMTP', 'colour' => 'CC7CCC'),
              );

$nototal = 1;
$colours = "mixed";
$unix_text = "Connections";

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

// EOF
