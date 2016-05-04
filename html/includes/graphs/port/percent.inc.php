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

$defs  = ' DEF:in_octets='.$rrd_filename.':INOCTETS:AVERAGE';
$defs .= ' DEF:out_octets='.$rrd_filename.':OUTOCTETS:AVERAGE';
$defs .= ' CDEF:in_bits=in_octets,8,*';
$defs .= ' CDEF:out_bits=out_octets,8,*';

$defs .= ' CDEF:in=in_bits,'.$port['ifSpeed'].',/,100,*';
$defs .= ' CDEF:out=out_bits,'.$port['ifSpeed'].',/,100,*';

$defs .= ' CDEF:in_max=in';
$defs .= ' CDEF:out_max=out';

$defs .= ' HRULE:100#555:';
$defs .= ' HRULE:-100#555:';

$colour_area_out = '3E629F';
$colour_line_out = '070A64';

$colour_area_in = '72B240';
$colour_line_in = '285B00';

#$colour_area_in_max = 'cc88cc';
#$colour_area_out_max = 'FFefaa';

$graph_max = 0;

$scale_max = '100';
$scale_min = '-100';

$unit_text = '% of '.formatRates($port['ifSpeed'], 4, 4);

$args['nototal'] = 1; $print_total = 0; $nototal = 1;

include($config['html_dir'].'/includes/graphs/generic_duplex.inc.php');

// EOF
