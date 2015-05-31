<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$app_graphs['default'] = array('powerdns-recursor_queries' => 'PowerDNS Recursor - Questions and answers per second',
                'powerdns-recursor_tcpqueries' => 'PowerDNS Recursor - TCP Questions and answers per second, unauthorized packets/s',
                'powerdns-recursor_errors' => 'PowerDNS Recursor - Packet errors per second',
                'powerdns-recursor_limits' => 'PowerDNS Recursor - Limitations per second',
                'powerdns-recursor_latency' => 'PowerDNS Recursor - Questions answered within latency',
                'powerdns-recursor_outqueries' => 'PowerDNS Recursor - Questions vs Outqueries',
                'powerdns-recursor_qalatency' => 'PowerDNS Recursor - Question/Answer latency in ms',
                'powerdns-recursor_timeouts' => 'PowerDNS Recursor - Corrupt / Failed / Timed out',
                'powerdns-recursor_cache' => 'PowerDNS Recursor - Cache sizes',
                'powerdns-recursor_load' => 'PowerDNS Recursor - Concurrent Queries',
/*                'powerdns-recursor_hitrate' => 'PowerDNS Recursor - Cache hitrate',*/ // FIXME have to fix up the graph def before uncomment
/*                'powerdns-recursor_cpuload' => 'PowerDNS Recursor - CPU load',*/ // FIXME have to fix up the graph def before uncomment
               );

// EOF
