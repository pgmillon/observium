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

if (!empty($agent_data['app']['freeradius']))
{
  $app_id = discover_app($device, 'freeradius');

  $data = explode("\n",$agent_data['app']['freeradius']);

  $rrd_filename = "app-freeradius-$app_id.rrd";

  $map = array();
  foreach ($data as $str)
  {
    list($key, $value) = explode(":", $str);
    $map[$key] = (float)trim($value);
  }

  // General Stats
  $mapping = array(
    'AccessAccepts' => 'FreeRADIUS-Total-Access-Accepts',
    'AccessChallenges' => 'FreeRADIUS-Total-Access-Challenges',
    'AccessRejects' => 'FreeRADIUS-Total-Access-Rejects',
    'AccessReqs' => 'FreeRADIUS-Total-Access-Requests',
    'AccountingReqs' => 'FreeRADIUS-Total-Accounting-Requests',
    'AccountingResponses' => 'FreeRADIUS-Total-Accounting-Responses',
    'AcctDroppedReqs' => 'FreeRADIUS-Total-Acct-Dropped-Requests',
    'AcctDuplicateReqs' => 'FreeRADIUS-Total-Acct-Duplicate-Requests',
    'AcctInvalidReqs' => 'FreeRADIUS-Total-Acct-Invalid-Requests',
    'AcctMalformedReqs' => 'FreeRADIUS-Total-Acct-Malformed-Requests',
    'AcctUnknownTypes' => 'FreeRADIUS-Total-Acct-Unknown-Types',
    'AuthDroppedReqs' => 'FreeRADIUS-Total-Auth-Dropped-Requests',
    'AuthDuplicateReqs' => 'FreeRADIUS-Total-Auth-Duplicate-Requests',
    'AuthInvalidReqs' => 'FreeRADIUS-Total-Auth-Invalid-Requests',
    'AuthMalformedReqs' => 'FreeRADIUS-Total-Auth-Malformed-Requests',
    'AuthResponses' => 'FreeRADIUS-Total-Auth-Responses',
    'AuthUnknownTypes' => 'FreeRADIUS-Total-Auth-Unknown-Types',
  );

  $values = array();
  foreach ($mapping as $key)
  {
    $values[] = isset($map[$key]) ? $map[$key] : -1;
  }

  rrdtool_create($device, $rrd_filename, " \
        DS:AccessAccepts:COUNTER:600:0:125000000000 \
        DS:AccessChallenges:COUNTER:600:0:125000000000 \
        DS:AccessRejects:COUNTER:600:0:125000000000 \
        DS:AccessReqs:COUNTER:600:0:125000000000 \
        DS:AccountingReqs:COUNTER:600:0:125000000000 \
        DS:AccountingResponses:COUNTER:600:0:125000000000 \
        DS:AcctDroppedReqs:COUNTER:600:0:125000000000 \
        DS:AcctDuplicateReqs:COUNTER:600:0:125000000000 \
        DS:AcctInvalidReqs:COUNTER:600:0:125000000000 \
        DS:AcctMalformedReqs:COUNTER:600:0:125000000000 \
        DS:AcctUnknownTypes:COUNTER:600:0:125000000000 \
        DS:AuthDroppedReqs:COUNTER:600:0:125000000000 \
        DS:AuthDuplicateReqs:COUNTER:600:0:125000000000 \
        DS:AuthInvalidReqs:COUNTER:600:0:125000000000 \
        DS:AuthMalformedReqs:COUNTER:600:0:125000000000 \
        DS:AuthResponses:COUNTER:600:0:125000000000 \
        DS:AuthUnknownTypes:COUNTER:600:0:125000000000 ");

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $values));
}

// EOF
