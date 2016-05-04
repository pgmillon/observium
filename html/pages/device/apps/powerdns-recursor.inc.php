<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$app_graphs['default'] = array('powerdns-recursor_queries' => 'Questions and answers per second',
                'powerdns-recursor_tcpqueries' => 'TCP Questions and answers per second, unauthorized packets/s',
                'powerdns-recursor_errors' => 'Packet errors per second',
                'powerdns-recursor_limits' => 'Limitations per second',
                'powerdns-recursor_latency' => 'Questions answered within latency',
                'powerdns-recursor_outqueries' => 'Questions vs Outqueries',
                'powerdns-recursor_qalatency' => 'Question/Answer latency in ms',
                'powerdns-recursor_timeouts' => 'Corrupt / Failed / Timed out',
                'powerdns-recursor_cache' => 'Cache sizes',
                'powerdns-recursor_load' => 'Concurrent Queries',
/*                'powerdns-recursor_hitrate' => 'Cache hitrate',*/ // FIXME have to fix up the graph def before uncomment
/*                'powerdns-recursor_cpuload' => 'CPU load',*/ // FIXME have to fix up the graph def before uncomment
               );

// EOF
