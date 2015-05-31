<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$defs  = ' DEF:in_octets='.$rrd_filename.':INOCTETS:AVERAGE';
$defs .= ' DEF:out_octets='.$rrd_filename.':OUTOCTETS:AVERAGE';
$defs .= ' DEF:in_upkts='.$rrd_filename.':INUCASTPKTS:AVERAGE';
$defs .= ' DEF:out_upkts='.$rrd_filename.':OUTUCASTPKTS:AVERAGE';
$defs .= ' DEF:in_nupkts='.$rrd_filename.':INNUCASTPKTS:AVERAGE';
$defs .= ' DEF:out_nupkts='.$rrd_filename.':OUTNUCASTPKTS:AVERAGE';
$defs .= ' CDEF:in_bits=in_octets,8,*';
$defs .= ' CDEF:out_bits=out_octets,8,*';
$defs .= ' CDEF:in_pkts=in_upkts,in_nupkts,+';
$defs .= ' CDEF:out_pkts=out_upkts,out_nupkts,+';

$defs .= ' CDEF:in=in_octets,in_pkts,/';
$defs .= ' CDEF:out=out_octets,out_pkts,/';

$defs .= ' CDEF:in_max=in';
$defs .= ' CDEF:out_max=out';

$colour_area_in = '53BBAD';
$colour_line_in = '2D9284';
$colour_area_out = 'FFAC72';
$colour_line_out = 'C7763D';

$colour_area_out = 'FFF772';
$colour_line_out = 'C7BF3D';

#$colour_area_in_max = 'cc88cc';
#$colour_area_out_max = 'FFefaa';

$graph_max = 0;
$unit_text = 'Octets/Pkt';

$args['nototal'] = 1; $print_total = 0; $nototal = 1;

include('includes/graphs/generic_duplex.inc.php');

// EOF
