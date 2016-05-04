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

// SLA jitter graph too hard, can not convert to definitions

//$query = 'SELECT * FROM `slas` WHERE `rtt_type` IN (?, ?) AND `device_id` = ? AND `sla_id` = ?';
//$params = array('jitter', 'icmpjitter', $device['device_id'], $vars['id']);
//$sla = dbFetchRow($query, $params);

//$index        = $sla['sla_index'];
// Index gets from auth.inc.php
$rrd_filename = get_rrd_path($device, 'sla_jitter-'.$index.'.rrd');

//$unit_text    = 'SLA '.$index;
//if ($sla['sla_tag'])
//{
//  $unit_text .= ': '.$sla['sla_tag'];
//}
//if ($sla['sla_owner'])
//{
//  $unit_text .= " (Owner: ". $sla['sla_owner'] .")";
//}

//$scale_min = -0.5;
$scale_rigid = FALSE;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " DEF:rtt=".$rrd_filename.":rtt:AVERAGE ";
$rrd_options .= " DEF:rtt_success=".$rrd_filename.":rtt_success:AVERAGE ";
$rrd_options .= " DEF:rtt_loss=".$rrd_filename.":rtt_loss:AVERAGE ";
$rrd_options .= " CDEF:rtt_count=rtt_success,rtt_loss,+ ";
//$rrd_options .= " DEF:req_count=".$rrd_filename.":req_count:AVERAGE ";
$rrd_options .= " CDEF:ploss=rtt_loss,UNKN,EQ,1,rtt_loss,IF,rtt_count,/,100,*,CEIL ";

$rrd_options .= " DEF:rtt_minimum=".$rrd_filename.":rtt_minimum:AVERAGE ";
$rrd_options .= " CDEF:smoke_minimal=rtt_minimum,rtt,- ";
$rrd_options .= " LINE2:rtt#FFFFFF00:'' AREA:smoke_minimal#00000045:'':STACK ";

$rrd_options .= " DEF:rtt_maximum=".$rrd_filename.":rtt_maximum:AVERAGE ";
$rrd_options .= " CDEF:smoke_maximal=rtt_maximum,rtt,- ";
$rrd_options .= " LINE2:rtt#FFFFFF00:'' AREA:smoke_maximal#00000045:'':STACK ";

$rrd_options .= " COMMENT:'                   Now      Avg      Min      Max";
if (is_numeric($sla['rtt_stddev']))
{
  $rrd_options .= "    StdDev";
}
$rrd_options .= "\l'";

$rrd_options .= " COMMENT:'Median RTT\:  ' ";
$rrd_options .= " GPRINT:rtt:LAST:%4.1lf%sms ";
$rrd_options .= " GPRINT:rtt:AVERAGE:%4.1lf%sms ";
$rrd_options .= " GPRINT:rtt:MIN:%4.1lf%sms ";
$rrd_options .= " GPRINT:rtt:MAX:%4.1lf%sms";
if (is_numeric($sla['rtt_stddev']))
{
  $rrd_options .= " COMMENT:'" . $sla['rtt_stddev'] . " ms'";
}
$rrd_options .= "\\l ";

$rrd_options .= " COMMENT:'Packet loss\: ' ";
$rrd_options .= " GPRINT:ploss:LAST:%6.1lf%% ";
$rrd_options .= " GPRINT:ploss:AVERAGE:%6.1lf%% ";
$rrd_options .= " GPRINT:ploss:MIN:%6.1lf%% ";
$rrd_options .= " GPRINT:ploss:MAX:%6.1lf%%\\l ";

$rrd_options .= " COMMENT:'Loss colour\: ' ";

$loss_values = array(0, 2, 4, 6, 8, 10, 15, 20, 25, 40, 50, 100);
//for ($p = 0; $p < count($config['graph_colours']['percents']); $p++)
foreach ($loss_values as $p => $loss_value)
{
  //$loss_value = $config['sla']['loss_value'][$p];
  $loss_colour = $config['graph_colours']['percents'][$p];
  if ($loss_value == 0)
  {
    $rrd_options .= " CDEF:ploss".$loss_value."=ploss,0,EQ,rtt,UNKN,IF ";
    $line_text = "0%";
  } else {
    $loss_value_prev = $loss_values[$p-1];

    $rrd_options .= " CDEF:ploss_tmp".$loss_value."=ploss,".$loss_value_prev.",GT,ploss,UNKN,IF ";
    $rrd_options .= " CDEF:ploss".$loss_value."=ploss_tmp".$loss_value.",".$loss_value.",1,+,LT,rtt,UNKN,IF ";

    $line_text = ($loss_value_prev + 1).'..'.$loss_value.'%';
  }

  $rrd_options .= " CDEF:ploss".$loss_value."_1=COUNT,2,%,0,EQ,ploss".$loss_value.",UNKN,IF ";
  $rrd_options .= " CDEF:ploss".$loss_value."_2=COUNT,2,%,1,EQ,ploss".$loss_value.",UNKN,IF ";
  $rrd_options .= " LINE2:ploss".$loss_value."_1#".$loss_colour.":'".$line_text."' LINE2:ploss".$loss_value."_2#".$loss_colour.":'' ";
}
unset($loss_value, $loss_colour);

$rrd_options .= " COMMENT:\\l ";

//EOF
