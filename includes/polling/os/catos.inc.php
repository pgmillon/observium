<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

#Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 4.5(9) Copyright (c) 1995-2000 by Cisco Systems, Inc.
#Cisco Systems WS-C5509 Cisco Catalyst Operating System Software, Version 5.5(19) Copyright (c) 1995-2003 by Cisco Systems
#Cisco Systems WS-C5500 Cisco Catalyst Operating System Software, Version 5.5(18) Copyright (c) 1995-2002 by Cisco Systems
#Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 8.4(11)GLX Copyright (c) 1995-2006 by Cisco Systems, Inc.
#Cisco Systems, Inc. WS-C2948 Cisco Catalyst Operating System Software, Version 5.5(11) Copyright (c) 1995-2001 by Cisco Systems, Inc.
#Cisco Systems, Inc. WS-C4003 Cisco Catalyst Operating System Software, Version 6.4(13) Copyright (c) 1995-2004 by Cisco Systems, Inc.
#Cisco Systems, Inc. WS-C4006 Cisco Catalyst Operating System Software, Version 6.3(9) Copyright (c) 1995-2002 by Cisco Systems, Inc.

if (strstr($ciscomodel, "OID")) { unset($ciscomodel); }

if (!strstr($ciscomodel, " ") && strlen($ciscomodel) >= '3')
{
  $hardware = $ciscomodel;
}

$poll_device['sysDescr'] = str_replace(", Inc.", "", $poll_device['sysDescr']); // Make the two formats the same
$poll_device['sysDescr'] = str_replace("\n", " ", $poll_device['sysDescr']);

if (!strstr($poll_device['sysDescr'], "Cisco Systems Catalyst 1900"))
{
  list(,,$hardware,,,,,,,$version,,,$features) = explode(" ", $poll_device['sysDescr']);
  list(,$features) = explode("-", $features);
} else {
  list(,$version) = explode(',',$poll_device['sysDescr'],2);
  $hardware = "1900";
}

// EOF
