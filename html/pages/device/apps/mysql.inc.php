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

$app_sections = array('system' => "System",
                      'queries' => "Queries",
                      'innodb' => "InnoDB");

$app_graphs['system'] = array(
                'mysql_connections' => 'Connections',
                'mysql_status' => 'Process List',
                'mysql_files_tables' => 'Files and Tables',
                'mysql_myisam_indexes' => 'MyISAM Indexes',
                'mysql_network_traffic' => 'Network Traffic',
                'mysql_table_locks' => 'Table Locks',
                'mysql_temporary_objects' => 'Temporary Objects'
                );

$app_graphs['queries'] = array(
                'mysql_command_counters' => 'Command Counters',
                'mysql_query_cache' => 'Query Cache',
                'mysql_query_cache_memory' => 'Query Cache Memory',
                'mysql_select_types' => 'Select Types',
                'mysql_slow_queries' => 'Slow Queries',
                'mysql_sorts' => 'Sorts',
                );

$app_graphs['innodb'] = array(
                'mysql_innodb_buffer_pool' => 'InnoDB Buffer Pool',
                'mysql_innodb_buffer_pool_activity' => 'InnoDB Buffer Pool Activity',
                'mysql_innodb_insert_buffer' => 'InnoDB Insert Buffer',
                'mysql_innodb_io' => 'InnoDB IO',
                'mysql_innodb_io_pending' => 'InnoDB IO Pending',
                'mysql_innodb_log' => 'InnoDB Log',
                'mysql_innodb_row_operations' => 'InnoDB Row Operations',
                'mysql_innodb_semaphores' => 'InnoDB semaphores',
                'mysql_innodb_transactions' => 'InnoDB Transactions',
                );

// EOF
