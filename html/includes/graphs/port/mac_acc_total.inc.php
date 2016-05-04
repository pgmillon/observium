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

$port      = (int)$_GET['id'];
if ($_GET['stat']) { $stat      = $_GET['stat']; } else { $stat = "bits"; }
$sort      = $_GET['sort'];

if (is_numeric($_GET['topn'])) { $topn = $_GET['topn']; } else { $topn = '10'; }

include_once($config['html_dir']."/includes/graphs/common.inc.php");

if ($stat == "pkts")
{
  $units='pps'; $unit = 'p'; $multiplier = '1';
  $colours_in  = 'purples';
  $colours_out = 'oranges';
  $prefix = "P";
  if ($sort == "in")
  {
    $sort = "pkts_input_rate";
  } elseif ($sort == "out") {
    $sort = "pkts_output_rate";
  } else {
    $sort = "bps";
  }
} elseif ($stat == "bits") {
  $units='bps'; $unit='B'; $multiplier='8';
  $colours_in  = 'greens';
  $colours_out = 'blues';
  if ($sort == "in")
  {
     $sort = "bytes_input_rate";
  } elseif ($sort == "out") {
     $sort = "bytes_output_rate";
  } else {
    $sort = "bps";
  }
}

$mas = dbFetchRows("SELECT *, (bytes_input_rate + bytes_output_rate) AS bps,
        (pkts_input_rate + pkts_output_rate) AS pps
        FROM `mac_accounting`
        LEFT JOIN  `mac_accounting-state` ON  `mac_accounting`.ma_id =  `mac_accounting-state`.ma_id
        WHERE `mac_accounting`.port_id = ?
        ORDER BY $sort DESC LIMIT 0," . $topn, array($port));

$port    = get_port_by_id($port);
$device =  device_by_id_cache($port['device_id']);

$pluses = ""; $iter = '0';
$rrd_options .= " COMMENT:'                                     In\: Current     Maximum      Total      Out\: Current     Maximum     Total\\\\n'";

foreach ($mas as $ma)
{
  $this_rrd = get_rrd_path($device, "mac_acc-" . $port['ifIndex'] . "-" . $ma['vlan_id'] ."-" . $ma['mac'] . ".rrd");

  if (is_file($this_rrd))
  {
    $mac = format_mac($ma['mac']);
    $name = $mac;
    $addy = dbFetchRow("SELECT * FROM ipv4_mac where mac_address = ? AND port_id = ?", array($ma['mac'], $ma['port_id']));

    if ($addy)
    {
      $name = $addy['ipv4_address'] . " (".$mac.")";
      $peer = dbFetchRow("SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D
              WHERE A.ipv4_address = ? AND I.port_id = A.port_id AND D.device_id = I.device_id", array($addy['ipv4_address']));
      if ($peer)
      {
        $name = $peer['hostname'] . " " . short_ifname($port['ifDescr'], NULL, FALSE) . " (".$mac.")";
      }

      if (dbFetchCell("SELECT COUNT(*) FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?", array($ma['device_id'], $addy['ipv4_address'])))
      {
        $peer_info = dbFetchRow("SELECT * FROM bgpPeers WHERE device_id = ? AND bgpPeerIdentifier = ?", array($ma['device_id'], $addy['ipv4_address']));
        $name .= " - AS".$peer_info['bgpPeerRemoteAs'];
      }

      if ($peer_info)
      {
        $asn = "AS".$peer_info['bgpPeerRemoteAs']; $astext = $peer_info['astext'];
      } else {
        unset ($as); unset ($astext); unset($asn);
      }
    }

    $this_id = str_replace(".", "", $ma['mac']);
    if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) { $iter = 0; }

    $colour_in  = $config['graph_colours'][$colours_in][$iter];
    $colour_out = $config['graph_colours'][$colours_out][$iter];
    $descr = rrdtool_escape($name, 36);
    $rrd_options .= " DEF:in".$this_id."=$this_rrd:".$prefix."IN:AVERAGE ";
    $rrd_options .= " DEF:out".$this_id."temp=$this_rrd:".$prefix."OUT:AVERAGE ";
    $rrd_options .= " CDEF:inB".$this_id."=in".$this_id.",$multiplier,* ";
    $rrd_options .= " CDEF:outB".$this_id."temp=out".$this_id."temp,$multiplier,*";
    $rrd_options .= " CDEF:outB".$this_id."=outB".$this_id."temp,-1,*";
    $rrd_options .= " CDEF:octets".$this_id."=inB".$this_id.",outB".$this_id."temp,+";
    $rrd_options .= " VDEF:totin".$this_id."=inB".$this_id.",TOTAL";
    $rrd_options .= " VDEF:totout".$this_id."=outB".$this_id."temp,TOTAL";
    $rrd_options .= " VDEF:tot".$this_id."=octets".$this_id.",TOTAL";
    $rrd_options .= " AREA:inB".$this_id."#" . $colour_in . ":'" . $descr . "':STACK";
    if ($rrd_optionsb) { $stack="STACK"; }
    $rrd_optionsb .= " AREA:outB".$this_id."#" . $colour_out . "::$stack";
    $rrd_options .= " GPRINT:inB".$this_id.":LAST:%6.2lf%s$units";
    $rrd_options .= " GPRINT:inB".$this_id.":MAX:%6.2lf%s$units";
    $rrd_options .= " GPRINT:totin".$this_id.":%6.2lf%s$unit";
    $rrd_options .= " COMMENT:' '";
    $rrd_options .= "  HRULE:999999999999999#" . $colour_out . ":' ':";
    $rrd_options .= " GPRINT:outB".$this_id."temp:LAST:%6.2lf%s$units";
    $rrd_options .= " GPRINT:outB".$this_id."temp:MAX:%6.2lf%s$units";
    $rrd_options .= " GPRINT:totout".$this_id.":%6.2lf%s$unit\\\\n";
    $iter++;
  }
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#999999";

// EOF
