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

$scale_min = "0";
$scale_max = "1";
$step      = TRUE;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                       Min     Last    Max\\n'";

$rrd_options .= " DEF:status=".$rrd_filename.":status:AVERAGE";
$rrd_options .= " DEF:code=".$rrd_filename.":code:AVERAGE";

$rrd_options .= " CDEF:percent=status,UN,UNKN,status,IF,100,* ";

$rrd_options .= " CDEF:unknown=status,UN,100,UNKN,IF";

$rrd_options .= " CDEF:percent10=10,percent,LE,0,100,IF ";
$rrd_options .= " CDEF:percent20=10,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent30=20,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent40=30,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent50=40,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent60=50,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent70=60,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent80=70,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent90=80,percent,GT,0,100,IF ";
$rrd_options .= " CDEF:percent100=90,percent,GT,0,100,IF ";

$rrd_options .= " AREA:percent10#d94c20:' 0-10%'";
$rrd_options .= " AREA:percent20#de6822:'11-20%'";
$rrd_options .= " AREA:percent30#eaa322:'21-30%'";
$rrd_options .= " AREA:percent40#f4bd1b:'31-40%'";
$rrd_options .= " AREA:percent50#fee610:'41-50%'";
$rrd_options .= " AREA:percent60#e4e11e:'51-60%'";
$rrd_options .= " AREA:percent70#b8d029:'61-70%'";
$rrd_options .= " AREA:percent80#90c22f:'71-80%'";
$rrd_options .= " AREA:percent90#75b731:'81-90%'";
$rrd_options .= " AREA:percent100#5ca53f:'91-100%'";
$rrd_options .= " AREA:unknown#e5e5e5:'Unknown \\n'";

$rrd_options .= " GPRINT:percent:AVERAGE:'Percent availability\: %8.3lf %%'";

?>
