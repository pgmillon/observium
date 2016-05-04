<?php // vim:fenc=utf-8:filetype=php:ts=4
/*
 * Copyright (C) 2099  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02150-1301, USA.
 */

error_reporting(E_ALL | E_NOTICE | E_WARNING);

require('includes/collectd/config.php');
require('includes/collectd/functions.php');
require('includes/collectd/definitions.php');

#require('config.php');
#require('functions.php');
#require('definitions.php');

load_graph_definitions();

/**
 * Send back new list content
 * @items Array of options values to return to browser
 * @method Name of Javascript method that will be called to process data
 */
function dhtml_response_list(&$items, $method) {
        header("Content-Type: text/xml");

        print('<?xml version="1.0" encoding="utf-8" ?>'."\n");
        print("<response>\n");
        printf(" <method>%s</method>\n", escape_html($method));
        print(" <result>\n");
        foreach ($items as &$item)
                printf('  <option>%s</option>'."\n", escape_html($item));
        print(" </result>\n");
        print("</response>");
}

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'collectd');

$plugins = collectd_list_plugins($device['hostname']);

#$navbar['brand'] = "CollectD";
$navbar['class'] = "navbar-narrow";

foreach ($plugins as &$plugin)
{
  if (!$vars['plugin']) { $vars['plugin'] = $plugin; }
  if ($vars['plugin'] == $plugin) { $navbar['options'][$plugin]['class'] = "active"; }
  $navbar['options'][$plugin]['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'collectd', 'plugin' => $plugin));
  $navbar['options'][$plugin]['text'] = escape_html(ucwords($plugin));
}

print_navbar($navbar);

echo generate_box_open();

echo '<table class="table table-condensed table-striped table-hover">';

   $i=0;

    $pinsts = collectd_list_pinsts($device['hostname'], $vars['plugin']);
    foreach ($pinsts as &$instance) {

     $types = collectd_list_types($device['hostname'], $vars['plugin'], $instance);
     foreach ($types as &$type) {

     $typeinstances = collectd_list_tinsts($device['hostname'], $vars['plugin'], $instance, $type);

     if ($MetaGraphDefs[$type]) { $typeinstances = array($MetaGraphDefs[$type]); }

     foreach ($typeinstances as &$tinst) {
       $i++;
       if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

       echo('<tr><td>');
       echo('<h3>'.$graph_title);

       if ($tinst) {
       echo($vars['plugin']." $instance - $type - $tinst");
       } else {
        echo($vars['plugin']." $instance - $type");
       }
       echo('</h3>');

       $graph_array['type']                    = "device_collectd";
       $graph_array['device']                      = $device['device_id'];

       $graph_array['c_plugin']           = $vars['plugin'];
       $graph_array['c_plugin_instance'] = $instance;
       $graph_array['c_type']                 = $type;
       $graph_array['c_type_instance']   = $tinst;

       print_graph_row($graph_array);

       echo('</tr></td>');

      }
     }

    }

echo '</table>';

echo generate_box_close();

$page_title[] = "CollectD";

// EOF
