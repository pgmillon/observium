<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$pagetitle[] = 'Ports';

// Set Defaults here

if (!isset($vars['format']) || !is_file($config['html_dir'].'/pages/ports/'.$vars['format'].'.inc.php'))
{
  $vars['format'] = 'list';
}

if ($vars['searchbar'] != 'hide')
{
  echo('<div class="well" style="padding: 10px;">');

?>
<form method="post" action="ports/" class="form form-inline" style="margin-bottom: 0;" id="ports-form">
  <div class="row">
    <div class="col-lg-2">
      <select name="device_id" id="device_id" class="selectpicker" multiple title="All Devices">
<?php

foreach (dbFetchRows('SELECT `device_id`, `hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`') as $data)
{
  if (device_permitted($data['device_id']))
  {
    echo('        <option value="'.$data['device_id'].'"');
    if ($data['device_id'] == $vars['device_id'] || in_array($data['device_id'], $vars['device_id']) ) { echo(' selected'); }
    echo('>'.$data['hostname'].'</option>');
  }
}
?>
      </select>
    </div>
    <div class="col-lg-2">
        <input placeholder="Port Name" title="Port Name" type="text" name="ifDescr" id="ifDescr" <?php if (strlen($vars['ifDescr'])) {echo('value="'.$vars['ifDescr'].'"');} ?> />
    </div>

    <div class="col-lg-2">
      <select name="state" id="state" class="selectpicker">
        <option value="">All States</option>
        <option value="up" <?php if ($vars['state'] == "up") { echo("selected"); } ?>>Up</option>
        <option value="down"<?php if ($vars['state'] == "down") { echo("selected"); } ?>>Down</option>
        <option value="admindown" <?php if ($vars['state'] == "admindown") { echo("selected"); } ?>>Shutdown</option>
      </select>
    </div>
    <div class="col-lg-2">
      <select name="ifType" id="ifType" class="selectpicker" title="All Media" multiple>
<?php
foreach (dbFetchRows("SELECT `ifType` FROM `ports` GROUP BY `ifType` ORDER BY `ifType`") as $data)
{
  if ($data['ifType'])
  {
    echo('        <option value="'.$data['ifType'].'"');
    if ($data['ifType'] == $vars['ifType'] || in_array($data['ifType'], $vars['ifType']) ) { echo(' selected'); }
    echo(">".$data['ifType']."</option>");
  }
}
?>
       </select>
    </div>
    <div class="col-lg-2">
      <select class="selectpicker" name="group" id="group" title="Select Groups" multiple>
<?php
// FIXME function?

foreach (get_type_groups('port') as $group)
{
  echo('<option value="'.$group['group_id'].'"');
  if ($group['group_id'] == $vars['group'] || in_array($group['group_id'], $vars['group']) ) { echo('selected'); }
  echo(">" . $group['group_name'] . "</option>");
}
?>
      </select>
    </div>

    <div class="col-lg-2 pull-right">
        <select name="sort" id="sort" class="selectpicker pull-right" title="Sort Order" style="width: 150px;" data-width="150px">
<?php
$sorts = array('device' => 'Device',
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

foreach ($sorts as $sort => $sort_text)
{

  if (!isset($vars['sort'])) { $vars['sort'] = $sort; }
  echo('<option value="'.$sort.'"');
  if ($vars['sort'] == $sort)  { echo(' selected'); }
  if ($vars['sort'] == $sort)  { echo(' data-icon="oicon-sort-alphabet-column"'); }
  echo('>'.$sort_text.'</option>');
}
?>
        </select>
    </div>

  </div>
  <div class="row" style="margin-top: 10px;">
    <div class="col-lg-2">
      <input placeholder="Hostname" type="text" name="hostname" id="hostname" title="Hostname" <?php if (strlen($vars['hostname'])) {echo('value="'.$vars['hostname'].'"');} ?> />
    </div>
    <div class="col-lg-2">
        <input placeholder="Port Description" title="Port Description" type="text" name="ifAlias" id="ifAlias" <?php if (strlen($vars['ifAlias'])) {echo('value="'.$vars['ifAlias'].'"');} ?> />
    </div>
    <div class="col-lg-2">
      <select name="ifSpeed" id="ifSpeed" class="selectpicker" title="All Speeds" multiple>
<?php

foreach (dbFetchRows("SELECT `ifSpeed` FROM `ports` GROUP BY `ifSpeed` ORDER BY `ifSpeed`") as $data)
{
  if ($data['ifSpeed'])
  {
    echo("<option value='".$data['ifSpeed']."'");
    if ($data['ifSpeed'] == $vars['ifSpeed'] || in_array($data['ifSpeed'], $vars['ifSpeed']) ) { echo(' selected'); }
    echo(">".humanspeed($data['ifSpeed'])."</option>");
  }
}
?>
       </select>
    </div>
    <div class="col-lg-2">
      <select name="port_descr_type" id="port_descr_type" class="selectpicker" title="All Port Types" multiple>
<?php
$ports = dbFetchRows("SELECT `port_descr_type` FROM `ports` GROUP BY `port_descr_type` ORDER BY `port_descr_type`");
$total = count($ports);
//echo("Total: $total"); // FIXME not displayed in output - remove?
foreach ($ports as $data)
{
  if ($data['port_descr_type'])
  {
    echo('        <option value="'.$data['port_descr_type'].'"');
    if ($data['port_descr_type'] == $vars['port_descr_type'] || in_array($data['port_descr_type'], $vars['port_descr_type']) ) { echo(' selected'); }
    echo(">".ucfirst($data['port_descr_type'])."</option>");
  }
}
?>
         </select>
    </div>
    <div class="col-lg-2">
        <select name="location" id="location" class="selectpicker" title="All Locations" multiple>
          <option value="">All Locations</option>
          <?php
           // fix me function?

          foreach (get_locations() as $location)
          {
            $value = base64_encode(json_encode(array($location)));
            $name  = ($location == '' ? '[[UNKNOWN]]' : htmlspecialchars($location));
            echo('<option value="'.$value.'"');
            if (array($location) === $vars['location']) { echo(" selected"); }
            echo(">" . $name . "</option>");
          }
         ?>
        </select>
    </div>

    <div class="col-lg-2 pull-right">
        <!-- <button type="submit" onClick="submitURL('ports-form');" class="btn pull-right"><i class="icon-search"></i> Search</button> -->
        <button type="button" onClick="form_to_path('ports-form');" class="btn pull-right"><i class="icon-search"></i> Search</button>
    </div>
  </div>
</form>
</div>

<?php }

$navbar = array('brand' => "Ports", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
// There is no detailed view for this yet.
//$navbar['options']['detail']['text']  = 'Details';

$navbar['options']['graphs']     = array('text' => 'Graphs');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['format'] == 'list' && !isset($vars['view'])) { $vars['view'] = 'basic'; }
  if ($vars['format'] == 'list' && $vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($vars,array('format' => 'list', 'view' => $option));
}

foreach (array('graphs') as $type)
{
  foreach ($config['graph_types']['port'] as $option => $data)
  {
    if ($vars['format'] == $type && $vars['graph'] == $option)
    {
      $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
      $navbar['options'][$type]['text'] .= " (".$data['name'].')';
    }
    $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
    $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($vars, array('view' => NULL, 'format' => $type, 'graph' => $option));
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

  $navbar['options_right']['reset']        = array('text' => 'Reset', 'url' => generate_url(array('page' => 'ports', 'section' => $vars['section'], 'bare' => $vars['bare'])));

print_navbar($navbar);
unset($navbar);

if ($debug) { print_vars($vars); }

$param = array();

if (!isset($vars['ignore']))   { $vars['ignore'] = "0"; }
if (!isset($vars['disabled'])) { $vars['disabled'] = "0"; }
if (!isset($vars['deleted']))  { $vars['deleted'] = "0"; }

$select = "`ports`.`port_id` AS `port_id`, `devices`.`device_id` AS `device_id`";
$where = " WHERE 1 ";

include($config['html_dir'].'/includes/port-sort-select.inc.php');

foreach ($vars as $var => $value)
{
  if ($value != '')
  {
    switch ($var)
    {
      case 'hostname':
      case 'location':
        $where .= generate_query_values($value, $var, '%LIKE%');
        break;
      case 'device_id':
        $where .= generate_query_values($value, 'ports.device_id');
        break;
      case 'group':
        $values = get_group_entities($value);
        $where .= generate_query_values($values, 'ports.port_id');
        break;
      case 'deleted':
      case 'ignore':
      case 'disable':
      case 'ifSpeed':
        $where .= generate_query_values($value, 'ports.'.$var);
        break;
      case 'ifType':
        $where .= generate_query_values($value, 'ports.ifType');
        break;
      case 'ifAlias':
      case 'ifDescr':
        $where .= generate_query_values($value, $var, '%LIKE%');
        break;
      case 'port_descr_type':
        $where .= generate_query_values($value, $var, 'LIKE');
        break;
      case 'errors':
        if ($value == 1 || $value == "yes")
        {
          $where .= " AND (`ifInErrors_delta` > '0' OR `ifOutErrors_delta` > '0')";
        }
        break;
      case 'alerted':
        if ($value == "yes")
        {
          $where .= "AND `ifAdminStatus` = ? AND ( `ifOperStatus` = ? OR `ifOperStatus` = ? )";
          $param[] = "up";
          $param[] = "LowerLayerDown";
          $param[] = "down";
        }
      case 'state':
        if ($value == "down")
        {
          $where .= "AND `ifAdminStatus` = ? AND `ifOperStatus` = ?";
          $param[] = "up";
          $param[] = "down";
        } elseif($value == "up") {
          $where .= "AND `ifAdminStatus` = ? AND ( `ifOperStatus` = ? OR `ifOperStatus` = ? )";
          $param[] = "up";
          $param[] = "up";
          $param[] = "monitoring";
        } elseif($value == "admindown") {
          $where .= "AND `ifAdminStatus` = ?";
          $param[] = "down";
        }
      break;
    }
  }
}

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
