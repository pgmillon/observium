<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$app_graphs['default'] = array(
		'nsd_queries'	=> 'NSD - DNS traffic',
                'nsd_memory'	=> 'NSD - Memory statistics',
                'nsd_qtype'	=> 'NSD - Queries by Query type',
                'nsd_rcode'	=> 'NSD - Queries by Return code',
                'nsd_axfr'	=> 'NSD - Requests for AXFR',
		'nsd_zones'	=> 'NSD - Zones');
