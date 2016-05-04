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

$rrd_options .= " DEF:pre_B=$rrd_filename:PrePolicyByte:AVERAGE";
$rrd_options .= " DEF:post_B=$rrd_filename:PostPolicyByte:AVERAGE";
$rrd_options .= " DEF:drop_B=$rrd_filename:DropByte:AVERAGE";

$rrd_options .= " CDEF:pre=pre_B,8,*";
$rrd_options .= " CDEF:post=post_B,8,*";
$rrd_options .= " CDEF:drop=drop_B,8,*";

$rrd_options .= " CDEF:post_perc=post,pre,/,100,*";
$rrd_options .= " CDEF:drop_perc=drop,pre,/,100,*";
$rrd_options .= " CDEF:drop_i=drop,-1,*";

$rrd_options .= " COMMENT:'Bits/s            Cur        Avg        Max\\n'";

$rrd_options .= " AREA:pre#c02020:'Pre-policy '";
$rrd_options .= " GPRINT:pre:LAST:' %6.2lf%sb'";
$rrd_options .= " GPRINT:pre:AVERAGE:' %6.2lf%sb'";
$rrd_options .= " GPRINT:pre:MAX:' %6.2lf%sb\\n'";

$rrd_options .= " AREA:post#008f00:'Post-policy':";
$rrd_options .= " GPRINT:post:LAST:' %6.2lf%sb'";
$rrd_options .= " GPRINT:post:AVERAGE:' %6.2lf%sb'";
$rrd_options .= " GPRINT:post:MAX:' %6.2lf%sb\\n'";

$rrd_options .= " AREA:drop_i#ea8f00:'Dropped    ':";
$rrd_options .= " GPRINT:drop:LAST:' %6.2lf%sb'";
$rrd_options .= " GPRINT:drop:AVERAGE:' %6.2lf%sb'";
$rrd_options .= " GPRINT:drop:MAX:' %6.2lf%sb\\n'";

// EOF
