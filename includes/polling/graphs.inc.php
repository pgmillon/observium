<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// Collect data for non-entity graphs

echo("Graphs ".PHP_EOL);

$include_dir = "includes/polling/graphs/";
include("includes/include-dir-mib.inc.php");

foreach ($table_defs as $mib_name => $mib_tables)
{
  echo(" $mib_name: ");
  foreach ($mib_tables as $table_name => $table_def)
  {
    echo("$table_name ");
    collect_table($device, $table_def, $graphs);
  }
  echo PHP_EOL;
}

// EOF
