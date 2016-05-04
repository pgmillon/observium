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

if ($_SESSION['userlevel'] <= 7)
{
  print_error_permission();
  return;
}

// Fetch all MIBs we support for specific OSes
foreach ($config['os'] as $os => $data)
{
  foreach ($data['mibs'] as $mib)
  {
    $mibs[$mib]['oses'][$os] = TRUE;
  }
}

// Fetch all MIBs we support for specific OS groups
foreach ($config['os_group'] as $os => $data)
{
  foreach ($data['mibs'] as $mib)
  {
    $mibs[$mib]['oses'][$os] = TRUE;
  }
}

ksort($mibs);

$obs_attribs = get_obs_attribs('mib_');

// r($vars);

if($vars['toggle_mib'] && isset($mibs[$vars['toggle_mib']]))
{
  $mib = $vars['toggle_mib'];

  if (isset($obs_attribs['mib_'.$mib]))
  {
    del_obs_attrib('mib_' . $mib);
  } else {
    set_obs_attrib('mib_' . $mib, "0");
  }

  $obs_attribs = get_obs_attribs('mib_');

}

print_message("This page allows you to globally disable individual MIBs. This configuration disables all discovery and polling using this MIB.");

// r($obs_attribs);

?>

<div class="row"> <!-- begin row -->

  <div class="col-md-12">

<?php
   $box_args = array('title' => 'Global MIB Configuration',
                                'header-border' => TRUE,
                    );

  echo generate_box_open($box_args);

?>


<table class="table  table-striped table-condensed ">
  <thead>
    <tr>
      <th>Module</th>
      <th>Description</th>
      <th style="width: 100px;">Status</th>
      <th style="width: 100px;"></th>
    </tr>
  </thead>
  <tbody>

<?php

foreach ($mibs as $mib => $data)
{

  $attrib_set = isset($obs_attribs['mib_'.$mib]);

  echo('<tr><td><strong>'.$mib.'</strong></td>');

  if (isset($config['mibs'][$mib])) { $descr = $config['mibs'][$mib]['descr']; } else { $descr = '';

/*
echo('<pre>

$mib = "'.$mib.'";
$config[\'mibs\'][ $mib ][\'mib_dir\'] = "";
$config[\'mibs\'][ $mib ][\'descr\']   = "";

</pre>');

*/
}

  echo '<td>'.$descr.'</td>';

  echo '<td>';
  if ($attrib_set && $obs_attribs['mib_'.$mib] == 0)
  {
    $attrib_status = '<span class="label label-danger">disabled</span>'; $toggle = 'Enable';
    $btn_class = 'btn-success'; $btn_toggle = 'value="Toggle"';
  } else {
    $attrib_status = '<span class="label label-success">enabled</span>'; $toggle = "Disable"; $btn_class = "btn-danger";
  }

  echo($attrib_status.'</td><td>');

  echo('<form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="'.$mib.'">
  <button type="submit" class="btn btn-mini '.$btn_class.'" name="Submit" '.$btn_toggle.'>'.$toggle.'</button>
</form>');

  echo('</td></tr>');
}
?>
  </tbody>
</table>

<?php echo generate_box_close(); ?>

  </div> <!-- end row -->
</div> <!-- end container -->

<?php

// EOF
