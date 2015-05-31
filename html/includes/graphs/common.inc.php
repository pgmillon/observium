<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

# FIXME why are we mres()'ing these?! see below too
if ($vars['from'])    { $from   = mres($vars['from']); }
if ($vars['to'])      { $to     = mres($vars['to']); }

if ($vars['width'])   { $width  = mres($vars['width']); }
if ($config['trim_tobias']) { $width+=12; }
if ($vars['height'])  { $height = mres($vars['height']); }

if ($vars['inverse']) { $in = 'out'; $out = 'in'; $inverse = TRUE; } else { $in = 'in'; $out = 'out'; $inverse = FALSE; }

if ($vars['legend'] == 'no')  { $rrd_options .= ' -g'; $legend = 'no'; }
if ($vars['title'] == 'yes')  { $rrd_options .= " --title='".$graph_title."' "; }

if (isset($vars['graph_title']))  { $rrd_options .= " --title='".$vars['graph_title']."' "; }

if (isset($log_y)) { $rrd_options .= ' --logarithmic'; }  /// FIXME. Newer used

// Autoscale
if (!isset($scale_min) && !isset($scale_max))
{
  $rrd_options .= ' --alt-autoscale';
  if ($scale_rigid !== FALSE) { $rrd_options .= ' --rigid'; }
} else {
  if (isset($scale_min))
  {
    $rrd_options .= ' --lower-limit '.$scale_min;
    if (!isset($scale_max)) { $rrd_options .= ' --alt-autoscale-max'; }
  }
  if (isset($scale_max))
  {
    $rrd_options .= ' --upper-limit '.$scale_max;
    if (!isset($scale_min)) { $rrd_options .= ' --alt-autoscale-min'; }
  }
  if (isset($scale_rigid) && $scale_rigid) { $rrd_options .= ' --rigid'; }
}

if (is_numeric($from))
{
  if ($to-$from <= 172800) { $graph_max = 0; } // Do not graph MAX areas for intervals less then 48 hours
} elseif (preg_match('/\d(d(ay)?s?|h(our)?s?)$/', $from))
{
  $graph_max = 0; // Also for RRD style from (6h, 2day)
}

$rrd_options .= '  --start '.$from.' --end ' . $to . ' --width '.$width.' --height '.$height.' ';
$rrd_options .= $config['rrdgraph_def_text'];

# FIXME mres? that's not for fixing commandline injection... we don't pass this on commandline, luckily... :-)
if ($vars['bg']) { $rrd_options .= ' -c CANVAS#' . mres($vars['bg']) . ' '; }

#$rrd_options .= ' -c BACK#FFFFFF';

if ($height < '99' && $vars['draw_all'] != 'yes')  { $rrd_options .= ' --only-graph'; }

if ($width <= '350') { $rrd_options .= " --font LEGEND:7:'" . $config['mono_font'] . "' --font AXIS:6:'" . $config['mono_font']."'"; }
else {                 $rrd_options .= " --font LEGEND:8:'" . $config['mono_font'] . "' --font AXIS:7:'" . $config['mono_font']."'"; }

//$rrd_options .= ' --font-render-mode normal --dynamic-labels'; // dynamic-labels not supported in rrdtool < 1.4
$rrd_options .= ' --font-render-mode normal';

if($step != TRUE) {  $rrd_options .= ' -E'; }

// EOF
