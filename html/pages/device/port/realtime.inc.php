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

if (!isset($vars['interval']))
{
  if (isset($config['os'][$device['os']]['realtime']))
  {
    $vars['interval'] = $config['os'][$device['os']]['realtime'];
  } else {
    $vars['interval'] = $config['realtime_interval'];
  }
}

$navbar['class'] = "navbar-narrow";
$navbar['brand'] = "Polling Interval";

foreach (array(0.25, 1, 2, 5, 15, 60) as $interval)
{
  if ($vars['interval'] == $interval) { $navbar['options'][$interval]['class'] = "active"; }
  $navbar['options'][$interval]['url'] = generate_url($link_array,array('view'=>'realtime','interval'=>$interval));
  $navbar['options'][$interval]['text'] = $interval."s";
}

print_navbar($navbar);

?>

<div style="margin: 30px; text-align: center;">
<object data="graph-realtime.php?type=bits&amp;id=<?php echo($port['port_id'] . "&amp;interval=".$vars['interval']); ?>" type="image/svg+xml" width="1000" height="400">
<param name="src" value="graph.php?type=bits&amp;id=<?php echo($port['port_id'] . "&amp;interval=".$vars['interval']); ?>" />
Your browser does not support SVG! You need to either use Firefox or Chrome, or download the Adobe SVG plugin.
</object>
</div>
<?php

// EOF
