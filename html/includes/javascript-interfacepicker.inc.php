<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>

<script type="text/javascript" src="js/tw-sack.js"></script>
<script type="text/javascript">

var ajax = new Array();

function getInterfaceList(sel, target_id)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        document.getElementById(target_id).options.length = 0;     // Empty city select box
        if (deviceId.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'ajax_listports.php?device_id='+deviceId;    // Specifying which file to get
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

</script>

