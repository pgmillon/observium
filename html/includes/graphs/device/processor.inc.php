<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$sql = "SELECT * FROM `processors` WHERE `processor_type` != 'hr-average' AND `device_id` = ?";
if (isset($vars['id']))
{
  if (!is_array($vars['id'])) { $vars['id'] = array($vars['id']); }
  $sql .= ' AND `processor_id` IN ('.implode(',', $vars['id']).')';
}
$procs = dbFetchRows($sql, array($device['device_id']));

if ($config['os'][$device['os']]['processor_stacked'] == 1)
{
  include("includes/graphs/device/processor_stack.inc.php");
} else {
  include("includes/graphs/device/processor_separate.inc.php");
}

// EOF
