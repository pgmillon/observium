<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$link_array = array('page'    => 'slas');

$navbar['brand'] = "SLAs";
$navbar['class'] = "navbar-narrow";

if (!isset($vars['rtt_type'])) { $navbar['options']['all']['class'] = "active"; }
$navbar['options']['all']['url']  = generate_url(array('page' => 'slas', 'rtt_type' => NULL));
$navbar['options']['all']['text'] = "All SLAs";

$vars_filter = $vars;
unset($vars_filter['rtt_type'], $vars_filter['owner']); // Do not filter rtt_type and owner for navbar

$sql = generate_sla_query($vars_filter);

foreach (dbFetchRows($sql) as $sla)
{
  $owner = ($sla['sla_owner'] == '' ? OBS_VAR_UNSET : $sla['sla_owner']);
  if (!isset($vars['rtt_type']) || $vars['rtt_type'] == $sla['rtt_type'])
  {
    if (!isset($sla_owners[$owner]))
    {
      $sla_owners[$owner] = nicecase($owner);
    }
  }

  if (!isset($vars['owner']) || $vars['owner'] == $owner)
  {
    $rtt_type = $sla['rtt_type'];

    if (isset($config['sla_type_labels'][$rtt_type]))
    {
      $rtt_label = $config['sla_type_labels'][$rtt_type];
    } else {
      $rtt_label = nicecase($rtt_type);
    }

    // Combinate different types with same label
    if (!in_array($rtt_type, $rtt_types[$rtt_label]))
    {
      $rtt_types[$rtt_label][] = $rtt_type;
    }
  }
}
ksort($rtt_types);
ksort($sla_owners);

foreach ($rtt_types as $text => $type)
{
  $type = implode(',', $type);

  if ($vars['rtt_type'] == $type) { $navbar['options'][$type]['class'] = "active"; }
  $navbar['options'][$type]['url']  = generate_url(array('page' => 'slas', 'rtt_type' => $type));
  $navbar['options'][$type]['text'] = $text;
}

// Owners
$navbar['options']['owner'] = array('text' => 'Owners', 'right' => 'true');

foreach ($sla_owners as $owner => $name)
{
  if ($owner == $vars['owner'] || in_array($owner, $vars['owner']) )
  {
    $navbar['options']['owner']['class'] = 'active';
    $navbar['options']['owner']['url']   = generate_url($vars, array('owner' => NULL));
    $navbar['options']['owner']['text'] .= " (".$name.')';
    $navbar['options']['owner']['suboptions'][$owner]['url'] = generate_url($vars, array('owner' => NULL));
    $navbar['options']['owner']['suboptions'][$owner]['class'] = 'active';
  } else {
    $navbar['options']['owner']['suboptions'][$owner]['url'] = generate_url($vars, array('owner' => $owner));
  }
  $navbar['options']['owner']['suboptions'][$owner]['text'] = $name;
}

// Groups
$groups = get_type_groups('sla');

$navbar['options']['group'] = array('text' => 'Groups', 'right' => TRUE, 'community' => FALSE);
foreach ($groups as $group)
{
  if ($group['group_id'] == $vars['group'] || in_array($group['group_id'], $vars['group']) )
  {
    $navbar['options']['group']['class'] = 'active';
    $navbar['options']['group']['url']   = generate_url($vars, array('group' => NULL));
    $navbar['options']['group']['text'] .= " (".$group['group_name'].')';
    $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => NULL));
    $navbar['options']['group']['suboptions'][$group['group_id']]['class'] = 'active';
  } else {
    $navbar['options']['group']['suboptions'][$group['group_id']]['url'] = generate_url($vars, array('group' => $group['group_id']));
  }
  $navbar['options']['group']['suboptions'][$group['group_id']]['text'] = $group['group_name'];
}

$navbar['options']['graphs']['text']  = 'Graphs';
$navbar['options']['graphs']['icon']  = 'oicon-chart-up';
$navbar['options']['graphs']['right'] = TRUE;

if ($vars['view'] == "graphs")
{
  $navbar['options']['graphs']['class'] = 'active';
  $navbar['options']['graphs']['url']   = generate_url($vars, array('view' => NULL));
} else {
  $navbar['options']['graphs']['url']    = generate_url($vars, array('view' => "graphs"));
}

print_navbar($navbar);

print_sla_table($vars);

// EOF
