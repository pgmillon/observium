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

if (!is_array($vars['id'])) { $vars['id'] = array($vars['id']); }

$auth = TRUE;

foreach ($vars['id'] as $storage_id)
{
  if (!$auth && !is_entity_permitted('storage', $storage_id))
  $auth = FALSE;
}

$title = "Multi Storage :: ";

// EOF

