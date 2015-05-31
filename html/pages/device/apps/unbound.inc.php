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

$app_graphs['default'] = array('unbound_queries' => 'Unbound - DNS traffic and cache hits',
                'unbound_queue'   => 'Unbound - Queue statistics',
                'unbound_memory'  => 'Unbound - Memory statistics',
                'unbound_qtype'   => 'Unbound - Queries by Query type',
                'unbound_rcode'   => 'Unbound - Queries by Return code',
                'unbound_opcode'  => 'Unbound - Queries by Operation code',
                'unbound_class'   => 'Unbound - Queries by Query class',
                'unbound_flags'   => 'Unbound - Queries by Flags');

// EOF
