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

if ($_SESSION['userlevel'] < 5)
{
  print_error_permission();
  return;
}

// Export or save templates
$templates_export = FALSE;
if      ($vars['saveas'] == 'yes') { $templates_export = $vars['filename']; }
else if ($vars['export'] == 'yes') { $templates_export = 'print'; }
unset($vars['export'], $vars['saveas'], $vars['filename']);

include($config['html_dir']."/includes/alerting-navbar.inc.php");

// Page to display list of configured alert checks

$alert_check = cache_alert_rules($vars);
#$alert_assoc = cache_alert_assoc($vars);
$where = ' WHERE 1' . generate_query_permitted(array('alert'));

// Build header menu

foreach (dbFetchRows("SELECT * FROM `alert_assoc` WHERE 1") as $entry)
{
  $alert_assoc[$entry['alert_test_id']][$entry['alert_assoc_id']]['entity_type'] = $entry['entity_type'];
  $alert_assoc[$entry['alert_test_id']][$entry['alert_assoc_id']]['entity_attribs'] = json_decode($entry['entity_attribs'], TRUE);
  $alert_assoc[$entry['alert_test_id']][$entry['alert_assoc_id']]['device_attribs'] = json_decode($entry['device_attribs'], TRUE);
}

$navbar['class'] = "navbar-narrow";
$navbar['brand'] = "Alert Checks";

$types = dbFetchRows("SELECT DISTINCT `entity_type` FROM `alert_table`" . $where);

$navbar['options']['all']['url'] = generate_url($vars, array('page' => 'alert_checks', 'entity_type' => NULL));
$navbar['options']['all']['text'] = escape_html(nicecase('all'));
if (!isset($vars['entity_type'])) {
  $navbar['options']['all']['class'] = "active";
  $navbar['options']['all']['url'] = generate_url($vars, array('page' => 'alert_checks', 'entity_type' => NULL));
}

foreach ($types as $thing)
{
  if ($vars['entity_type'] == $thing['entity_type'])
  {
    $navbar['options'][$thing['entity_type']]['class'] = "active";
    $navbar['options'][$thing['entity_type']]['url'] = generate_url($vars, array('page' => 'alert_checks', 'entity_type' => NULL));
  } else {
    $navbar['options'][$thing['entity_type']]['url'] = generate_url($vars, array('page' => 'alert_checks', 'entity_type' => $thing['entity_type']));
  }
  $navbar['options'][$thing['entity_type']]['icon'] = $config['entities'][$thing['entity_type']]['icon'];
  $navbar['options'][$thing['entity_type']]['text'] = escape_html(nicecase($thing['entity_type']));
}

$navbar['options']['export']['text']  = 'Export';
$navbar['options']['export']['icon']  = 'icon-save';
$navbar['options']['export']['url']   = generate_url($vars, array('page' => 'alert_checks', 'export' => 'yes'));
$navbar['options']['export']['right'] = TRUE;
$navbar['options']['export']['userlevel'] = 7;

// Print out the navbar defined above
print_navbar($navbar);

if ($templates_export)
{
  //r($alert_check);
  //r($alert_assoc);
  // Export alert checkers as templates
  $for_export = array();
  foreach ($alert_check as $id => $entry)
  {
    foreach ($alert_assoc[$id] as $assoc)
    {
      // Multiple association sets (device+entity)
      $entry['associations'][] = $assoc;
    }
    $for_export[] = $entry;
  }
  $templates_xml = generate_templates('alert', $for_export);

  if ($templates_export == 'print')
  {
    $templates_filename = 'observium_templates_alert_';
    if ($vars['entity_type'])
    {
      $templates_filename .= $vars['entity_type'];
    } else{
      $templates_filename .= 'all';
    }
    $form = array('type'  => 'rows',
                  'space' => '10px',
                  'url'   => generate_url($vars));
    // Filename
    $form['row'][0]['filename'] = array(
                                    'type'        => 'text',
                                    'name'        => 'Filename',
                                    'value'       => $templates_filename . '.xml',
                                    //'div_class'   => 'col-md-6',
                                    'width'       => '100%',
                                    'placeholder' => TRUE);
    // Save as human formatted XML
    $form['row'][0]['formatted'] = array(
                                    'type'        => 'select',
                                    'value'       => (class_exists('DOMDocument') ? 'yes' : 'no'),
                                    'values'      => array('yes' => 'Formatted',
                                                           'no'  => 'Unformatted'));
    // search button
    $form['row'][0]['saveas']   = array(
                                    'type'        => 'submit',
                                    'name'        => 'Save as..',
                                    'icon'        => 'icon-save',
                                    'right'       => TRUE,
                                    'value'       => 'yes'
                                    );
    print_form($form);

    print_xml($templates_xml);
    //r(xml_to_array($templates_xml));
  } else {
    $templates_filename = $templates_export;
    download_as_file($templates_xml, $templates_filename, $vars);
  }

  unset($for_export, $templates_xml, $templates_export, $templates_filename, $form);
}

//foreach (dbFetchRows("SELECT * FROM `alert_table` LEFT JOIN `alert_table-state` ON `alert_table`.`alert_table_id` = `alert_table-state`.`alert_table_id`") as $entry)
foreach (dbFetchRows("SELECT * FROM `alert_table`" . $where) as $entry)
{
  $alert_table[$entry['alert_test_id']][$entry['alert_table_id']] = $entry;
}

  echo generate_box_open();

  echo '<table class="table table-condensed  table-striped  table-hover">
  <thead>
    <tr>
    <th class="state-marker"></th>
    <th style="width: 1px;"></th>
    <th style="width: 250px">Name</th>
    <th style="width: 40px"></th>
    <th style="width: 300px">Tests</th>
    <th>Device Match / Entity Match</th>
    <th style="width: 40px">Entities</th>
    </tr>
  </thead>
  <tbody>', PHP_EOL;

foreach ($alert_check as $check)
{

  // Process the alert checker to add classes and colours and count status.
  humanize_alert_check($check);

  echo('<tr class="'.$check['html_row_class'].'">');

  echo('
    <td class="state-marker"></td>
    <td style="width: 1px;"></td>');

  // Print the conditions applied by this alert

  echo '<td><strong>';
  echo '<a href="', generate_url(array('page' => 'alert_check', 'alert_test_id' => $check['alert_test_id'])), '">' . escape_html($check['alert_name']). '</a></strong><br />';
  echo '<small>',escape_html($check['alert_message']),'</small>';
  echo '</td>';

  echo '<td><i class="' . $config['entities'][$check['entity_type']]['icon'] . '"></i></td>';

  // Loop the tests used by this alert
  echo '<td>';
  $text_block = array();
  foreach ($check['conditions'] as $condition)
  {
    $text_block[] = escape_html($condition['metric'].' '.$condition['condition'].' '.$condition['value']);
  }
  echo('<code>'.implode($text_block,'<br />').'</code>');
  echo('</td>');

  echo('<td>');

  echo generate_box_open();
  echo('<table class="table table-condensed-more table-striped" style="margin-bottom: 0px;">');

  // Loop the associations which link this alert to this device
  foreach ($alert_assoc[$check['alert_test_id']] as $assoc_id => $assoc)
  {

    echo('<tr>');
    echo('<td style="width: 50%">');
    if (is_array($assoc['device_attribs']))
    {
      $text_block = array();
      foreach ($assoc['device_attribs'] as $attribute)
      {
        $text_block[] = escape_html($attribute['attrib'].' '.$attribute['condition'].' '.$attribute['value']);
      }
      echo('<code>'.implode($text_block,'<br />').'</code>');
    } else {
      echo '<code>*</code>';
    }

    echo('</td>');

    echo('<td>');
    if (is_array($assoc['entity_attribs']))
    {
      $text_block = array();
      foreach ($assoc['entity_attribs'] as $attribute)
      {
        $text_block[] = escape_html($attribute['attrib'].' '.$attribute['condition'].' '.$attribute['value']);
      }
      echo('<code>'.implode($text_block,'<br />').'</code>');
    } else {
      echo '<code>*</code>';
    }
    echo('</td>');

    echo('</tr>');

  }
  // End loop of associations

  echo '</table>';
  echo generate_box_close();

  echo '</td>';

  // Print the count of entities this alert applies to and a popup containing a list and Print breakdown of entities by status.
  // We assume each row here is going to be two lines, so we just <br /> them.
  echo '<td>';
  #echo overlib_link('#', count($entities), $entities_content,  NULL));
  echo '<b>', $check['num_entities'], '</b>';
  echo '<br />';
  echo $check['status_numbers'];
  echo '</td>';

}

echo '</table>';

echo generate_box_close();

// EOF
