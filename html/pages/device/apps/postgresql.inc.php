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

$app_sections = array('stats' => "Stats",
                      'live' => "Live");

$app_graphs['stats'] = array('postgresql_xact'  => 'Postgresql Commit Count',
                         'postgresql_blks' => 'Postgresql Blocks Count',
                         'postgresql_tuples' => 'Postgresql Tuples Count',
                         'postgresql_tuples_query' => 'Postgresql Tuples Count per Query');

$app_graphs['live'] = array('postgresql_connects' => 'Postgresql Connection Count',
                        'postgresql_queries' => 'Postgresql Query Types');

// EOF
