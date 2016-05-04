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

$app_graphs['default'] = array('unbound_queries' => 'DNS traffic and cache hits',
                'unbound_queue'   => 'Queue statistics',
                'unbound_memory'  => 'Memory statistics',
                'unbound_qtype'   => 'Queries by Query type',
                'unbound_rcode'   => 'Queries by Return code',
                'unbound_opcode'  => 'Queries by Operation code',
                'unbound_class'   => 'Queries by Query class',
                'unbound_flags'   => 'Queries by Flags');

// EOF
