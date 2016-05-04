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

$page_title[] = 'Ports';

// Set Defaults here

if (!isset($vars['format']) || !is_file($config['html_dir'].'/pages/ports/'.$vars['format'].'.inc.php'))
{
  $vars['format'] = 'list';
}

if (OBS_DEBUG) { print_vars($vars); }

$param = array();

if (!isset($vars['sort'])) { $vars['sort'] = 'device'; }
//if (!isset($vars['ignore']))   { $vars['ignore'] = "0"; }
if (!isset($vars['disabled'])) { $vars['disabled'] = "0"; }
if (!isset($vars['deleted']))  { $vars['deleted'] = "0"; }

$select = "`ports`.`port_id` AS `port_id`, `devices`.`device_id` AS `device_id`";

$where_array = build_ports_where_array($vars);

$where = ' WHERE 1 ';
if (!$config['web_show_disabled'] && count($cache['devices']['disabled']) > 0)
{
  $where_array[] = generate_query_values($cache['devices']['disabled'], 'ports.device_id', '!=');
}
$where .= implode('', $where_array);
//r($where_array);

echo '<div class="row">';

$form_items = array();

foreach (get_locations() as $entry)
{
  if ($entry === '') { $entry = OBS_VAR_UNSET; }
  $form_items['location'][$entry] = $entry;
}

foreach (get_type_groups('port') as $entry)
{
  $form_items['group'][$entry['group_id']] = $entry['group_name'];
}

foreach (array('ifType', 'ifSpeed', 'port_descr_type') as $entry)
{
  $query  = "SELECT `$entry` FROM `ports`";

  if (isset($where_array[$entry]))
  {
    $tmp = $where_array[$entry];
    unset($where_array[$entry]);
    $query .= ' WHERE 1 ' . implode('', $where_array);
    $where_array[$entry] = $tmp;
  } else {
    $query .= $where;
  }

  $query .= " AND `$entry` != ''".$cache['where']['ports_permitted']." GROUP BY `$entry` ORDER BY `$entry`";

  foreach (dbFetchRows($query) as $data)
  {
    if ($entry == "ifType")
    {
      $form_items[$entry][$data['ifType']] = rewrite_iftype($data['ifType']) . ' ('.$data['ifType'].')';
    } elseif ($entry == "ifSpeed") {
      $form_items[$entry][$data[$entry]] = formatRates($data[$entry]);
    } else {
      $form_items[$entry][$data[$entry]] = nicecase($data[$entry]);
    }
  }
}

foreach (dbFetchRows('SELECT `device_id`, `hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`') as $data)
{
  if (device_permitted($data['device_id']))
  {
    $form_items['devices'][$data['device_id']] = $data['hostname'];
  }
}

 asort($form_items['ifType']);
 
$form_items['sort'] = array('device' => 'Device',
            'port' => 'Port',
            'speed' => 'Speed',
            'traffic' => 'Traffic In+Out',
            'traffic_in' => 'Traffic In',
            'traffic_out' => 'Traffic Out',
            'traffic_perc' => 'Traffic Percentage In+Out',
            'traffic_perc_in' => 'Traffic Percentage In',
            'traffic_perc_out' => 'Traffic Percentage Out',
            'packets' => 'Packets In+Out',
            'packets_in' => 'Packets In',
            'packets_out' => 'Packets Out',
            'errors' => 'Errors',
            'media' => 'Media',
            'descr' => 'Description');

$form = array('type'  => 'rows',
              'space' => '10px',
              //'brand' => NULL,
              //'class' => 'well',
              'submit_by_key' => TRUE,
              'url'   => generate_url($vars));
// First row

$form['row'][0]['device_id'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Device',
                                'value'       => $vars['device_id'],
                                'width'       => '100%', //'180px',
                                'values'      => $form_items['devices']);

$form['row'][0]['ifDescr'] = array(
                                'type'        => 'text',
                                'name'        => 'Port Name',
                                'value'       => $vars['ifDescr'],
                                'width'       => '100%', //'180px',
                                'placeholder' => TRUE);

$form['row'][0]['state'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Port State',
                                'width'       => '100%', //'180px',
                                'value'       => $vars['state'],
                                'values'      => array('up' => 'Up', 'down' => ' Down', 'admindown' => 'Admin Down'));

$form['row'][0]['ifType'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Port Media',
                                'width'       => '100%', //'180px',
                                'value'       => $vars['ifType'],
                                'values'      => $form_items['ifType']);

$form['row'][0]['group']    = array(
                                'type'        => 'multiselect',
                                'name'        => 'Select Groups',
                                'width'       => '100%', //'180px',
                                'value'       => $vars['group'],
                                'values'      => $form_items['group']);

// Select sort pull-right
$form['row'][0]['sort']     = array(
                                'type'        => 'select',
                                'icon'        => 'oicon-sort-alphabet-column',
                                'right'       => TRUE,
                                'width'       => '100%', //'150px',
                                'value'       => $vars['sort'],
                                'values'      => $form_items['sort']);

$form['row'][1]['hostname']  = array(
                                'type'        => 'text',
                                'name'        => 'Hostname',
                                'value'       => $vars['hostname'],
                                'width'       => '100%', //'180px',
                                'placeholder' => TRUE);

$form['row'][1]['ifAlias'] = array(
                                'type'        => 'text',
                                'name'        => 'Port Description',
                                'value'       => $vars['ifAlias'],
                                'width'       => '100%', //'180px',
                                'placeholder' => TRUE);

$form['row'][1]['ifSpeed'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Port Speed',
                                'width'       => '100%', //'180px',
                                'value'       => $vars['ifSpeed'],
                                'values'      => $form_items['ifSpeed']);

$form['row'][1]['port_descr_type'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Port Type',
                                'width'       => '100%', //'180px',
                                'value'       => $vars['port_descr_type'],
                                'values'      => $form_items['port_descr_type']);

foreach (get_locations() as $entry)
{
  if ($entry === '') { $entry = OBS_VAR_UNSET; }
  $form_items['location'][$entry] = $entry;
}

$form['row'][1]['location'] = array(
                                'type'        => 'multiselect',
                                'name'        => 'Select Locations',
                                'width'       => '100%', //'180px',
                                'encode'      => TRUE,
                                'value'       => $vars['location'],
                                'values'      => $form_items['location']);

$form['row'][1]['search']   = array(
                                'type'        => 'submit',
                                //'name'        => 'Search',
                                //'icon'        => 'icon-search',
                                'right'       => TRUE,
                                );
?>
<div class="col-xl-4 visible-xl">
<?php

$panel_form = array('type'  => 'rows',
              'space' => '10px',
              //'brand' => NULL,
              'class' => '',
              'submit_by_key' => TRUE,
              'url'   => generate_url($vars));

$panel_form['row'][0]['device_id'] = $form['row'][0]['device_id'];
$panel_form['row'][0]['hostname'] = $form['row'][1]['hostname'];

$panel_form['row'][1]['ifDescr'] = $form['row'][0]['ifDescr'];
$panel_form['row'][1]['ifAlias'] = $form['row'][1]['ifAlias'];

$panel_form['row'][2]['state'] = $form['row'][0]['state'];
$panel_form['row'][2]['ifSpeed'] = $form['row'][1]['ifSpeed'];

$panel_form['row'][3]['ifType'] = $form['row'][0]['ifType'];
$panel_form['row'][3]['port_descr_type'] = $form['row'][1]['port_descr_type'];

$panel_form['row'][4]['group'] = $form['row'][0]['group'];
$panel_form['row'][4]['location'] = $form['row'][1]['location'];

$panel_form['row'][5]['sort'] = $form['row'][0]['sort'];
$panel_form['row'][5]['search'] = $form['row'][1]['search'];

echo '<div class="box box-solid">';
echo '<div class="box-header with-border"><h3 class="box-title">Search Ports</h3></div>';
print_form($panel_form);
echo '</div>';

?>
</div>
<div class="col-xl-8">

<?php

if ($vars['searchbar'] != "hide")
{

  echo '<div class="box box-solid hidden-xl">';
  $form['class'] = '';
  print_form($form);
  echo '</div>';

  unset($form, $form_items);
}

$navbar = array('brand' => "Ports", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
// There is no detailed view for this yet.
//$navbar['options']['detail']['text']  = 'Details';

$navbar['options']['graphs']     = array('text' => 'Graphs');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['format'] == 'list' && !isset($vars['view'])) { $vars['view'] = 'basic'; }
  if ($vars['format'] == 'list' && $vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url(array('page' => 'ports', 'format' => 'list', 'view' => $option));
}

foreach (array('graphs') as $type)
{
  foreach ($config['graph_types']['port'] as $option => $data)
  {
    if ($vars['view'] == $type && $vars['graph'] == $option)
    {
      $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
      $navbar['options'][$type]['text'] .= " (".$data['name'].')';
    }
    $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
    $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($vars, array('view' => $type, 'graph' => $option));
  }
}

  if ($vars['searchbar'] == "hide")
  {
    $navbar['options_right']['searchbar']     = array('text' => 'Show Search', 'url' => generate_url($vars, array('searchbar' => NULL)));
  } else {
    $navbar['options_right']['searchbar']     = array('text' => 'Hide Search' , 'url' => generate_url($vars, array('searchbar' => 'hide')));
  }

  if ($vars['bare'] == "yes")
  {
    $navbar['options_right']['header']     = array('text' => 'Show Header', 'url' => generate_url($vars, array('bare' => NULL)));
  } else {
    $navbar['options_right']['header']     = array('text' => 'Hide Header', 'url' => generate_url($vars, array('bare' => 'yes')));
  }

  $navbar['options_right']['reset']        = array('text' => 'Reset', 'url' => generate_url(array('page' => 'ports', 'section' => $vars['section'])));

print_navbar($navbar);
unset($navbar);

include($config['html_dir'].'/includes/port-sort-select.inc.php');

$sql  = "SELECT " . $select;
$sql .= " FROM `ports`";
$sql .= " INNER JOIN `devices` ON `ports`.`device_id` = `devices`.`device_id`";
$sql .= " LEFT JOIN `ports-state` ON `ports`.`port_id` = `ports-state`.`port_id`";
$sql .= " ".$where;

$row = 1;

$ports = dbFetchRows($sql, $param);
port_permitted_array($ports);
$ports_count = count($ports);

include($config['html_dir'].'/includes/port-sort.inc.php');
include($config['html_dir'].'/pages/ports/'.$vars['format'].'.inc.php');

// EOF
