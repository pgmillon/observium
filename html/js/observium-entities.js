/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage js
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2015 Observium Limited
 *
 */

var ajax = new Array();

function getInterfaceList(sel, target_id)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        document.getElementById(target_id).options.length = 0;     // Empty city select box
        if (deviceId.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'ajax/device_ports.php?device_id='+deviceId;    // Specifying which file to get
                ajax[index].onCompletion = function() { createInterfaces(index, target_id) };       // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createInterfaces(index, target_id)
{
        var obj = document.getElementById(target_id);
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
        $('.selectpicker').selectpicker('refresh');
}


function getEntityList(sel, target_id, entity_type)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        document.getElementById(target_id).options.length = 0;     // Empty city select box
        if (deviceId.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'ajax/device_entities.php?device_id='+deviceId+'&entity_type='+entity_type;    // Specifying which file to get
                ajax[index].onCompletion = function() { createInterfaces(index, target_id) };       // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}
