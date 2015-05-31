<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage errorcodes
 * @author     Dennis de Houx <dennis@aio.be>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */


$errorcodes['100'] = array("code" => "100", "msg" => "Debug mode is enabled");
$errorcodes['101'] = array("code" => "101", "msg" => "User authentification succeeded");
$errorcodes['102'] = array("code" => "102", "msg" => "Demo module loaded successfully");

$errorcodes['200'] = array("code" => "200", "msg" => "Simple Observium API is not enabled");
$errorcodes['201'] = array("code" => "201", "msg" => "Module is not found");
$errorcodes['211'] = array("code" => "211", "msg" => "Billing module is not enabled");
$errorcodes['212'] = array("code" => "212", "msg" => "Inventory module is not enabled");
$errorcodes['213'] = array("code" => "213", "msg" => "Packages module is not enabled");

$errorcodes['301'] = array("code" => "301", "msg" => "User authentification failed");
$errorcodes['303'] = array("code" => "302", "msg" => "This IP is not allowed to use the Simple Observium API");
$errorcodes['310'] = array("code" => "310", "msg" => "This user is not allowed to access this data");

$errorcodes['401'] = array("code" => "401", "msg" => "Returned error code doesn't exists");
$errorcodes['402'] = array("code" => "402", "msg" => "Data returned is not a array, api aborted");
$errorcodes['403'] = array("code" => "403", "msg" => "This type is not found");

?>
