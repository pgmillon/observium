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

include("includes/graphs/common.inc.php");

$rrd_options .= " DEF:pre=$rrd_filename:PrePolicyPkt:AVERAGE";
$rrd_options .= " DEF:drop=$rrd_filename:DropPkt:AVERAGE";
$rrd_options .= " DEF:bufdrop=$rrd_filename:NoBufDropPkt:AVERAGE";

#$rrd_options .= " CDEF:post_perc=post,pre,/,100,*";
#$rrd_options .= " CDEF:drop_perc=drop,pre,/,100,*";

$rrd_options .= " COMMENT:'Pkts/s           Current    Average   Maximum\\n'";

$rrd_options .= " AREA:pre#c02020:'Pre-policy  '";
$rrd_options .= " GPRINT:pre:LAST:' %6.2lf%s'";
$rrd_options .= " GPRINT:pre:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:pre:MAX:' %6.2lf%s\\n'";

$rrd_options .= " AREA:drop#ea8f00:'Dropped     ':";
$rrd_options .= " GPRINT:drop:LAST:' %6.2lf%s'";
$rrd_options .= " GPRINT:drop:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:drop:MAX:' %6.2lf%s\\n'";

$rrd_options .= " AREA:bufdrop#008f00:'Buffer Drops':";
$rrd_options .= " GPRINT:bufdrop:LAST:' %6.2lf%s'";
$rrd_options .= " GPRINT:bufdrop:AVERAGE:' %6.2lf%s'";
$rrd_options .= " GPRINT:bufdrop:MAX:' %6.2lf%s\\n'";
