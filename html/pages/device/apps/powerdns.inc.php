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

$app_graphs['default'] = array('powerdns_latency'  => 'PowerDNS - Latency',
                'powerdns_fail' => 'PowerDNS - Corrupt / Failed / Timed out',
                'powerdns_packetcache' => 'PowerDNS - Packet Cache',
                'powerdns_querycache' => 'PowerDNS - Query Cache',
                'powerdns_recursing' => 'PowerDNS - Recursing Queries and Answers',
                'powerdns_queries' => 'PowerDNS - Total UDP/TCP Queries and Answers',
                'powerdns_queries_udp' => 'PowerDNS - Detail UDP IPv4/IPv6 Queries and Answers');

// EOF
