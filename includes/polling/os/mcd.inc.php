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

//VerAg:07.00.00.01.00;VerSw:9.0.0.41;VerHw:CX;VerPl:3300 ICP
//VerAg:07.00.00.01.00;VerSw:8.0.10.7_1;VerHw:MXe;VerPl:3300 ICP
//VerAg:07.00.00.01.00;VerSw:13.0.1.53;VerHw:MXe;VerPl:3300 ICP;VerMCD:7.0 SP1 PR2
//VerAg:07.00.00.01.00;VerSw:10.2.2.10;VerHw:CXi-II;VerPl:3300 ICP;VerMCD:4.2 SP2
//VerAg:07.00.00.01.00;VerSw:11.0.2.66;VerHw:MCD;VerPl:3300 ICP;HostSrv:192.168.100.121;VerMCD:5.0 SP2 PR2
//VerAg:07.00.00.01.00;VerSw:11.0.2.66;VerHw:MCD;VerPl:3300 ICP;HostSrv:10.25.187.38;VerMCD:5.0 SP2 PR2

if (preg_match('/VerAg:(.+);VerSw:(?<version>.+);VerHw:(?<hw2>.+);VerPl:(?<hw1>[^;\n]+)(?:(;HostSrv:.+)?;VerMCD:(?<mcd>.+))?/', $poll_device['sysDescr'], $matches))
{
  $hardware = 'MiVoice '.str_replace(' ICP', '', $matches['hw1']).' '.$matches['hw2'];
  // Convert to MCD version (see: http://www.prairiefyre.com/kb/KnowledgebaseArticle51645.aspx)
  $mcd      = explode('.', $matches['version']);
  // Examples: "13.0.1.53" >> "MCD 7.0 SP1 (Build: 13.0.1.53)"
  //           "13.0.1.53" >> "MCD 3.0 (Build: 9.0.0.41)"
  $version  = 'MCD '.($mcd[0] - 6).'.'.$mcd[1].($mcd[2] > 0 ? ' SP'.$mcd[2] : '').' (Build: '.$matches['version'].')';
  //if (isset($matches['mcd'])) { $version = 'MCD '.$matches['mcd'].' (Build: '.$matches['version'].')'; }
}

unset($matches, $mcd);

// EOF
