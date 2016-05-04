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

$app_graphs['default'] = array('powerdns_latency'  => 'Latency',
                'powerdns_fail' => 'Corrupt / Failed / Timed out',
                'powerdns_packetcache' => 'Packet Cache',
                'powerdns_querycache' => 'Query Cache',
                'powerdns_recursing' => 'Recursing Queries and Answers',
                'powerdns_queries' => 'Total UDP/TCP Queries and Answers',
                'powerdns_queries_udp' => 'Detail UDP IPv4/IPv6 Queries and Answers');

// EOF
