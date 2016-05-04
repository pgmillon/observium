<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Build alert table query from $vars
 * Returns queries for data, an array of parameters and a query to get a count for use in paging
 *
 * @param array $vars
 * @return array ($query, $param, $query_count)
 *
 */
// TESTME needs unit testing
function build_alert_table_query($vars)
{
  $args = array();
  $where = ' WHERE 1 ';

  // Loop through the vars building a sql query from relevant values
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        // Search by device_id if we have a device or device_id
        case 'device_id':
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'entity_type':
          if ($value != 'all')
          {
            $where .= generate_query_values($value, 'entity_type');
          }
          break;
        case 'entity_id':
          $where .= generate_query_values($value, 'entity_id');
          break;
        case 'alert_test_id':
          $where .= generate_query_values($value, 'alert_test_id');
          break;
        case 'status':
          if ($value == 'failed_delayed') {
            $where .= " AND `alert_status` IN (0,2)";
          } elseif ($value == 'failed')
            {
              $where .= " AND `alert_status` IN (0)";

          } elseif ($value == 'suppressed')
          {
            $where .= " AND `alert_status` = 3";
          }
          break;
      }
    }
  }

  // Permissions query
  $query_permitted = generate_query_permitted(array('device', 'alert'), array('hide_ignored' => TRUE));

  // Base query
  $query = 'FROM `alert_table` ';
  //$query .= 'LEFT JOIN `alert_table-state` USING(`alert_table_id`) ';
  $query .= $where . $query_permitted;

  // Build the query to get a count of entries
  $query_count = 'SELECT COUNT(`alert_table_id`) '.$query;

  // Build the query to get the list of entries
  $query = 'SELECT * '.$query;
  $query .= ' ORDER BY `device_id`, `alert_test_id`, `entity_type`, `entity_id` DESC ';

  if (isset($vars['pagination']) && $vars['pagination'])
  {
    pagination($vars, 0, TRUE); // Get default pagesize/pageno
    $vars['start'] = $vars['pagesize'] * $vars['pageno'] - $vars['pagesize'];
    $query .= 'LIMIT '.$vars['start'].','.$vars['pagesize'];
  }

  return array($query, $param, $query_count);
}

/**
 * Display alert_table entries.
 *
 * @param array $vars
 * @return none
 *
 */
function print_alert_table($vars)
{
  global $alert_rules; global $config;

  // This should be set outside, but do it here if it isn't
  if (!is_array($alert_rules)) { $alert_rules = cache_alert_rules(); }
  /// WARN HERE

  if (isset($vars['device']) && !isset($vars['device_id'])) { $vars['device_id'] = $vars['device']; }
  if (isset($vars['entity']) && !isset($vars['entity_id'])) { $vars['entity_id'] = $vars['entity']; }

  // Short? (no pagination, small out)
  $short = (isset($vars['short']) && $vars['short']);

  list($query, $param, $query_count) = build_alert_table_query($vars);

  // Fetch alerts
  $count  = dbFetchCell($query_count, $param);
  $alerts = dbFetchRows($query, $param);

  // Set which columns we're going to show.
  // We hide the columns that have been given as search options via $vars
  $list = array('device_id' => FALSE, 'entity_id' => FALSE, 'entity_type' => FALSE, 'alert_test_id' => FALSE);
  foreach ($list as $argument => $nope)
  {
    if (!isset($vars[$argument]) || empty($vars[$argument]) || $vars[$argument] == "all") { $list[$argument] = TRUE; }
  }

  if($vars['format'] != "condensed")
  {
    $list['checked'] = TRUE;
    $list['changed'] = TRUE;
    $list['alerted'] = TRUE;
  }

  if($vars['short'] == TRUE) { $list['checked'] = FALSE; $list['alerted'] = FALSE; }

  // Hide device if we know entity_id
  if (isset($vars['entity_id'])) { $list['device_id'] = FALSE; }
  // Hide entity_type if we know the alert_test_id
  if (isset($vars['alert_test_id']) || TRUE) { $list['entity_type'] = FALSE; } // Hide entity types in favour of icons to save space

  if ($vars['pagination'] && !$short)
  {
    $pagination_html = pagination($vars, $count);
    echo $pagination_html;
  }

  echo generate_box_open($vars['header']);

  echo '<table class="table table-condensed  table-striped  table-hover">' ;

  if($vars['no_header'] == FALSE)
  {
    echo '
  <thead>
    <tr>
      <th class="state-marker"></th>
      <th style="width: 1px;"></th>';

    if ($list['device_id'])
    {
      echo('      <th style="width: 15%">Device</th>');
    }
    if ($list['entity_type'])
    {
      echo('      <th style="width: 10%">Type</th>');
    }
    if ($list['entity_id'])
    {
      echo('      <th style="">Entity</th>');
    }
    if ($list['alert_test_id'])
    {
      echo('      <th style="min-width: 15%;">Alert</th>');
    }

    echo '
      <th style="width: 100px;">Status</th>';

    if ($list['checked'])
    {
      echo '      <th style="width: 95px;">Checked</th>';
    }
    if ($list['changed'])
    {
      echo '      <th style="width: 95px;">Changed</th>';
    }
    if ($list['alerted'])
    {
      echo '      <th style="width: 95px;">Alerted</th>';
    }

    echo '    <th style="width: 45px;"></th>
    </tr>
  </thead>';
  }
  echo '<tbody>'.PHP_EOL;

  foreach ($alerts as $alert)
  {
    // Process the alert entry, generating colours and classes from the data
    humanize_alert_entry($alert);

    // Get the entity array using the cache
    $entity = get_entity_by_id_cache($alert['entity_type'], $alert['entity_id']);

    // Get the device array using the cache
    $device = device_by_id_cache($alert['device_id']);

    // Get the entity_name.
    ### FIXME - This is probably duplicated effort from above. We should pass it $entity
    $entity_name = entity_name($alert['entity_type'], $entity);

    // Set the alert_rule from the prebuilt cache array
    $alert_rule = $alert_rules[$alert['alert_test_id']];

    echo('<tr class="'.$alert['html_row_class'].'" style="cursor: pointer;" onclick="location.href=\''.generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'alert', 'alert_entry' => $alert['alert_table_id'])).'\'">');

    echo('<td class="state-marker"></td>');
    echo('<td style="width: 1px;"></td>');

    // If we know the device, don't show the device
    if ($list['device_id'])
    {
      echo('<td><span class="entity-title">'.generate_device_link($device).'</span></td>');
    }

    // If we're showing all entity types, print the entity type here
    if ($list['entity_type']) { echo('<td>'.nicecase($alert['entity_type']).'</td>'); }

    // Print a link to the entity
    if ($list['entity_id'])
    {
      echo('<td><span class="entity-title"><i class="' . $config['entities'][$alert['entity_type']]['icon'] . '"></i> '.generate_entity_link($alert['entity_type'], $alert['entity_id']).'</span></td>');
    }

    // Print link to the alert rule page
    if ($list['alert_test_id'])
    {
      echo '<td class="entity"><a href="', generate_url(array('page' => 'alert_check', 'alert_test_id' => $alert_rule['alert_test_id'])), '">', escape_html($alert_rule['alert_name']), '</a></td>';
    }

    echo('<td>');
    echo('<span class="label label-'.($alert['html_row_class'] != 'up' ? $alert['html_row_class'] : 'success').'">' . generate_tooltip_link('', $alert['status'], '<div class="small" style="max-width: 500px;"><strong>'.$alert['last_message'].'</strong></div>', $alert['alert_class']) . '</span>');
    echo('</td>');


    // echo('<td class="'.$alert['class'].'">'.$alert['last_message'].'</td>');

    if ($list['checked'])     { echo('<td>'.generate_tooltip_link('', $alert['checked'], format_unixtime($alert['last_checked'], 'r')).'</td>'); }
    if ($list['changed'])     { echo('<td>'.generate_tooltip_link('', $alert['changed'], format_unixtime($alert['last_changed'], 'r')).'</td>'); }
    if ($list['alerted'])     { echo('<td>'.generate_tooltip_link('', $alert['alerted'], format_unixtime($alert['last_alerted'], 'r')).'</td>'); }
    echo('<td>');

    // This stuff should go in an external entity popup in the future.

    $state = json_decode($alert['state'], true);

    $alert['state_popup'] = '';

    if (count($state['failed']))
    {
      $alert['state_popup'] .= generate_box_open(array('title' => 'Failed Tests')); //'<h4>Failed Tests</h4>';

      $alert['state_popup'] .= '<table style="min-width: 400px;" class="table   table-striped table-condensed">';
      $alert['state_popup'] .= '<thead><tr><th>Metric</th><th>Cond</th><th>Value</th><th>Measured</th></tr></thead>';

      foreach($state['failed'] as $test)
      {
        $alert['state_popup'] .= '<tr><td><strong>'.$test['metric'].'</strong></td><td>'.$test['condition'].'</td><td>'.$test['value'].'</td><td><i class="red">'.$state['metrics'][$test['metric']].'</i></td></tr>';
      }
      $alert['state_popup'] .= '</table>';
      $alert['state_popup'] .= generate_box_close();
    }

    $alert['state_popup'] .= generate_entity_popup_graphs($alert, array('entity_type' => 'alert_entry'));

    // Print (i) icon with popup of state.
    echo(overlib_link("", '<i class="icon-info-sign text-primary"></i>', $alert['state_popup'], NULL));

    echo('&nbsp;&nbsp;<a href="'.generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'alert', 'alert_entry' => $alert['alert_table_id'])).'"><i class="icon-cog text-muted"></i></a>');
    echo('</td>');
    echo('</tr>');

  }

  echo '  </tbody>'.PHP_EOL;
  echo '</table>'.PHP_EOL;

  echo generate_box_close();

  if ($vars['pagination'] && !$short)
  {
    echo $pagination_html;
  }
}

// EOF
