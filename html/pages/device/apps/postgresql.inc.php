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

$app_sections = array('stats' => "Stats",
                      'live' => "Live");

$app_graphs['stats'] = array('postgresql_xact'  => 'Commit Count',
                         'postgresql_blks' => 'Blocks Count',
                         'postgresql_tuples' => 'Tuples Count',
                         'postgresql_tuples_query' => 'Tuples Count per Query');

$app_graphs['live'] = array('postgresql_connects' => 'Connection Count',
                        'postgresql_queries' => 'Query Types');

// EOF
