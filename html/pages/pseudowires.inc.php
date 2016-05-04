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

$page_title[] = "Pseudowires";

$link_array = array('page'    => 'pseudowires');
$link_array = array_merge($link_array, $vars);

$navbar = array('brand' => "Pseudowires", 'class' => "navbar-narrow");

if (!isset($vars['type'])) { $navbar['options']['all']['class'] = "active"; }
$navbar['options']['all']['url']  = generate_url($link_array, array('pwtype' => NULL));
$navbar['options']['all']['text'] = "All Types";

$vars_filter = $vars;
unset($vars_filter['pwtype']); // Do not filter type

$sql = generate_pseudowire_query($vars_filter);

foreach (dbFetchRows($sql) as $pw)
{
    $pw_type  = $pw['pwType'];
    $pw_label = nicecase($pw_type);

    // Combinate different types with same label
    if (!in_array($pw_type, $pw_types[$pw_label]))
    {
      $pw_types[$pw_label][] = $pw_type;
    }
}
ksort($pw_types);

foreach ($pw_types as $text => $type)
{
  $type = implode(',', $type);

  if ($vars['pwtype'] == $type)
  {
    $navbar['options'][$type]['class'] = "active";
    unset($navbar['options']['all']['class']);
  }
  $navbar['options'][$type]['url']  = generate_url($link_array, array('pwtype' => $type));
  $navbar['options'][$type]['text'] = $text;
}

// Groups
$groups = get_type_groups('pseudowire');

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

// Graphs
$navbar['options']['graphs']['text']  = 'Graphs';
$navbar['options']['graphs']['icon']  = 'oicon-chart-up';
$navbar['options']['graphs']['right'] = TRUE;

if ($vars['view'] == "graphs")
{
  if (!$vars['graph']) { $vars['graph'] = 'pseudowire_bits'; }
  unset($vars['view']);
} else {
  $navbar['options']['graphs']['url']    = generate_url($vars, array('view' => "graphs"));
}

foreach ($cache['graphs'] as $entry)
{
  if (preg_match('/^(pseudowire_(\w+))/', $entry, $matches))
  {
    $graph = $matches[1];
    if (!isset($navbar['options']['graphs']['suboptions'][$graph]))
    {
      $navbar['options']['graphs']['suboptions'][$graph] = array('text' => nicecase($matches[2]));
      if ($graph == $vars['graph'])
      {
        $navbar['options']['graphs']['class'] = 'active';
        $navbar['options']['graphs']['url']   = generate_url($vars, array('view' => NULL));
        $navbar['options']['graphs']['text'] .= " (".nicecase($matches[2]).')';
        $navbar['options']['graphs']['suboptions'][$graph]['url'] = generate_url($vars, array('graph' => NULL));
        $navbar['options']['graphs']['suboptions'][$graph]['class'] = 'active';
      } else {
        $navbar['options']['graphs']['suboptions'][$graph]['url'] = generate_url($vars, array('graph' => $graph));
      }
    }
  }
}

/*
if ($vars['view'] == "graphs")
{
  $navbar['options']['graphs']['class'] = 'active';
  $navbar['options']['graphs']['url']   = generate_url($vars, array('view' => NULL));
} else {
  $navbar['options']['graphs']['url']    = generate_url($vars, array('view' => "graphs"));
}
*/
print_navbar($navbar);
unset($navbar);

// Pagination
$vars['pagination'] = TRUE;

// Print pseudowires
print_pseudowire_table($vars);

// EOF
