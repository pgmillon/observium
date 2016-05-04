<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$db_revs = dbFetchRows("SELECT * FROM `observium_attribs` WHERE attrib_type='dbSchema' ORDER BY `attrib_value` DESC");

if (count($db_revs) > 1)
{
  $db_rev = $db_revs[0]['attrib_value'];
  echo(" Removing duplicate dbSchema attribute entries - resetting to single $db_rev");

  dbDelete('observium_attribs', "attrib_type = ?", array("dbSchema"));
  dbInsert(array('attrib_type' => 'dbSchema', 'attrib_value' => $db_rev), 'observium_attribs');
  dbQuery("ALTER TABLE `observium_attribs` ADD PRIMARY KEY (`attrib_type`)");
}

echo(PHP_EOL);

// EOF
