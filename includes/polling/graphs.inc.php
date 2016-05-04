<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Collect data for non-entity graphs

$include_dir = "includes/polling/graphs/";
include("includes/include-dir-mib.inc.php");

foreach ($table_defs as $mib_name => $mib_tables)
{
  print_cli_data_field("$mib_name", 2);
  foreach ($mib_tables as $table_name => $table_def)
  {
    echo("$table_name ");
    collect_table($device, $table_def, $graphs);
  }
  echo PHP_EOL;
}

// EOF
