<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Collect data for non-entity graphs

echo("Graphs ".PHP_EOL);

$include_dir = "includes/polling/graphs/";
include("includes/include-dir-mib.inc.php");

foreach ($table_defs AS $mib_name => $mib_tables)
{
  echo("o $mib_name: ");
  foreach ($mib_tables AS $table_name => $table_def)
  {
    echo("$table_name ");
    collect_table($device, $table_def, $graphs);
  }
  echo PHP_EOL;
}

?>
