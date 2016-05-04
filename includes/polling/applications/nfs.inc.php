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

if (!empty($agent_data['app']['nfs']))
{
  $app_id = discover_app($device, 'nfs');

  $rrd_filename = "app-nfs-$app_id.rrd";
  /* Based DIRECTLY on nfsstats.c labels */
  $nfsLabel = array();
  $nfsLabel['proc2'] = array(
        "null", "getattr", "setattr", "root",   "lookup",  "readlink",
        "read", "wrcache", "write",   "create", "remove",  "rename",
        "link", "symlink", "mkdir",   "rmdir",  "readdir", "fsstat"
  );

  $nfsLabel['proc3'] = array(
        "null",   "getattr", "setattr",  "lookup", "access",  "readlink",
        "read",   "write",   "create",   "mkdir",  "symlink", "mknod",
        "remove", "rmdir",   "rename",   "link",   "readdir", "readdirplus",
        "fsstat", "fsinfo",  "pathconf", "commit"
  );

  $nfsLabel['proc4'] = array(
        "null",      "read",      "write",   "commit",      "open",        "open_conf",
        "open_noat", "open_dgrd", "close",   "setattr",     "fsinfo",      "renew",
        "setclntid", "confirm",   "lock",
        "lockt",     "locku",     "access",  "getattr",     "lookup",      "lookup_root",
        "remove",    "rename",    "link",    "symlink",     "create",      "pathconf",
        "statfs",    "readlink",  "readdir", "server_caps", "delegreturn", "getacl",
        "setacl",    "fs_locations",
        "rel_lkowner", "secinfo",
        /* nfsv4.1 client ops */
        "exchange_id",
        "create_ses",
        "destroy_ses",
        "sequence",
        "get_lease_t",
        "reclaim_comp",
        "layoutget",
        "getdevinfo",
        "layoutcommit",
        "layoutreturn",
        "secinfo_noname",
        "test_stateid",
        "free_stateid",
        "getdevlist",
        "bind_contoses",
        "dstr_clientid"
  );

  foreach ($nfsLabel as $key => $values)
  {
    foreach ($values as $name)
    {
      $definition.=" DS:".($key.$name).":DERIVE:600:0:12500000 ";
    }
  }

  rrdtool_create($device, $rrd_filename, $definition." ");

  $datas = array();
  foreach ($nfsLabel as $key => $values)
  {
    foreach ($values as $name)
    {
      $datas[$key.$name] = "U";
    }
  }

  $lines = explode("\n", $agent_data['app']['nfs']);
  foreach ($lines as $line)
  {
    $tokens = explode(" ", $line);
    if (isset($tokens[0]) && isset($nfsLabel[strtolower($tokens[0])]))
    {
      $base = strtolower($tokens[0]);
      array_shift($tokens);
      array_shift($tokens);
      foreach ($tokens as $k => $v)
      {
        $datas[$base.($nfsLabel[$base][$k])] = $v;
      }
    }
  }

  rrdtool_update($device, $rrd_filename,  "N:".implode(':', $datas));
}

// EOF
