<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');
include(dirname(__FILE__) . '/../html/includes/functions.inc.php');

class HtmlIncludesFunctionsTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerNiceCase
  */
  public function testNiceCase($string, $result)
  {
    $this->assertSame($result, nicecase($string));
  }

  public function providerNiceCase()
  {
    return array(
      array('bgp_peer', 'BGP Peer'),
      array('cbgp_peer', 'BGP Peer (AFI/SAFI)'),
      array('netscaler_vsvr', 'Netscaler vServer'),
      array('netscaler_svc', 'Netscaler Service'),
      array('mempool', 'Memory'),
      array('ipsec_tunnels', 'IPSec Tunnels'),
      array('vrf', 'VRFs'),
      array('isis', 'IS-IS'),
      array('cef', 'CEF'),
      array('eigrp', 'EIGRP'),
      array('ospf', 'OSPF'),
      array('bgp', 'BGP'),
      array('ases', 'ASes'),
      array('vpns', 'VPNs'),
      array('dbm', 'dBm'),
      array('mysql', 'MySQL'),
      array('powerdns', 'PowerDNS'),
      array('bind', 'BIND'),
      array('ntpd', 'NTPd'),
      array('powerdns-recursor', 'PowerDNS Recursor'),
      array('freeradius', 'FreeRADIUS'),
      array('postfix_mailgraph', 'Postfix Mailgraph'),
      array('ge', 'Greater or equal'),
      array('le', 'Less or equal'),
      array('notequals', 'Doesn\'t equal'),
      array('notmatch', 'Doesn\'t match'),
      array('diskio', 'Disk I/O'),
      array('ipmi', 'IPMI'),
      array('snmp', 'SNMP'),
      array('mssql', 'SQL Server'),
      array('apower', 'Apparent power'),
      array('proxysg', 'Proxy SG'),
      array('', ''),

      array(' some text here ', ' some text here '),
      array('some text here ', 'Some text here '),
      array(NULL, ''),
      array(FALSE, ''),
      array(array('test'), NULL)
    );
  }

  /**
  * @dataProvider providerSafeBase64
  */
  public function testSafeBase64Encode($string, $result)
  {
    $this->assertSame($result, safe_base64_encode($string));
  }

  /**
  * @depends testSafeBase64Encode
  * @dataProvider providerSafeBase64
  */
  public function testSafeBase64Decode($result, $string)
  {
    $this->assertSame($result, safe_base64_decode($string));
  }

  /**
  * @depends testSafeBase64Encode
  * @dataProvider providerSafeBase64Random
  */
  public function testSafeBase64Random($string)
  {
    $encode = safe_base64_encode($string);
    $decode = safe_base64_decode($encode);
    $this->assertSame($decode, $string);
  }

  public function providerSafeBase64()
  {
    $result = array(
      array('Zlwv(,/E%>ieDr25Mr,-?ZOiL',                  'Wmx3digsL0UlPmllRHIyNU1yLC0_Wk9pTA'),
      array('w&8=K@.3}ULxnw"8+j`I\'yRQyL%RDijctN."',      'dyY4PUtALjN9VUx4bnciOCtqYEkneVJReUwlUkRpamN0Ti4i'),
      array('T_\\u[WGG6c{o;i*J1/}\'5"\'nJJ.RY',           'VF9cdVtXR0c2Y3tvO2kqSjEvfSc1IiduSkouUlk'),
      array('(?fY".Q/g7>=cjtK@p[m$v,',                    'KD9mWSIuUS9nNz49Y2p0S0BwW20kdiw'),
      array('kaoaDKPg;ek"rVi`4{mA,=KQZ%yOz<J;2~E',        'a2FvYURLUGc7ZWsiclZpYDR7bUEsPUtRWiV5T3o8SjsyfkU'),
      array('Bow[#R+\'A*\':gIpRsL{3q-*2s',                'Qm93WyNSKydBKic6Z0lwUnNMezNxLSoycw'),
      array('NG6JqTVjnZ>j}NP&#u%|e=i`n2@*QQ^T#o":xo/',    'Tkc2SnFUVmpuWj5qfU5QJiN1JXxlPWlgbjJAKlFRXlQjbyI6eG8v'),
      array('e\',n,5S/UJoVZOTCHZx6Tn9Hsk7Cn2p',           'ZScsbiw1Uy9VSm9WWk9UQ0haeDZUbjlIc2s3Q24ycA'),
      array('7+Wz}\'GgFUl=;=A8M]~b1GfS3P`mJCV#',          'NytXen0nR2dGVWw9Oz1BOE1dfmIxR2ZTM1BgbUpDViM'),
      array('}.X8sPK0D)./=mQmVw,!A|VG',                   'fS5YOHNQSzBEKS4vPW1RbVZ3LCFBfFZH'),
      array('cDlpvOGgnIlojBkDmU?:vHLVo9{oYaj7u0^jx',      'Y0RscHZPR2duSWxvakJrRG1VPzp2SExWbzl7b1lhajd1MF5qeA'),
      array('*loZQI@L[P?nq4f-px?J<~TDxK%BmLE,xdLs(C!]',   'KmxvWlFJQExbUD9ucTRmLXB4P0o8flREeEslQm1MRSx4ZExzKEMhXQ'),
      array('{Nx6#5tgz">e"gLh2\\wkqYOH/ZvX&U*97NBL',      'e054NiM1dGd6Ij5lImdMaDJcd2txWU9IL1p2WCZVKjk3TkJM'),
      array('ZGYP`R\\!{4`pZ^s1~4gSrbr^>mk',               'WkdZUGBSXCF7NGBwWl5zMX40Z1NyYnJePm1r'),
      array('"J4l*A8%6D<#Q;0F~m3~m[|D938',                'Iko0bCpBOCU2RDwjUTswRn5tM35tW3xEOTM4'),
      array('JzY:LY$(^0<Rv*TjAwAx[q/+mRGhA+I;,[2(y',      'SnpZOkxZJCheMDxSdipUakF3QXhbcS8rbVJHaEErSTssWzIoeQ'),
      array('GQ&>l5tMX!CA<?5Wo-dMuw',                     'R1EmPmw1dE1YIUNBPD81V28tZE11dw'),
      array('!V=K\\?NkP^4ruh_*?<.UA&L6\\',                'IVY9S1w_TmtQXjRydWhfKj88LlVBJkw2XA'),
      array('8,G(?\'A>_7p`>qbr!;9``1ssc$WZpc\'>KxD*?Py3', 'OCxHKD8nQT5fN3BgPnFiciE7OWBgMXNzYyRXWnBjJz5LeEQqP1B5Mw'),
      array('6K\')zm&][xm0m/}G}<I)u)',                    'NksnKXptJl1beG0wbS99R308SSl1KQ'),
    );
    return $result;
  }

  public function providerSafeBase64Random()
  {
    $charlist = ' 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~!@#$%^&*()_+-=[]{}\|/?,.<>;:"'."'";
    $result = array();
    for ($i=0; $i<20; $i++)
    {
      $string = generate_random_string(mt_rand(20, 40), $charlist);
      $result[] = array($string);
    }
    return $result;
  }

  /**
  * @depends testSafeBase64Random
  * @dataProvider providerEncrypt
  */
  public function testEncrypt($string, $key, $result)
  {
    $this->assertSame($result, encrypt($string, $key));
  }

  /**
  * @depends testEncrypt
  * @dataProvider providerEncrypt
  */
  public function testDecrypt($result, $key, $string)
  {
    $this->assertSame($result, decrypt($string, $key));
  }

  /**
  * @depends testEncrypt
  * @dataProvider providerEncryptRandom
  */
  public function testEncryptRandom($string, $key)
  {
    $encrypt = encrypt($string, $key);
    $decrypt = decrypt($encrypt, $key);
    $this->assertSame($decrypt, $string);
  }

  public function providerEncrypt()
  {
    $result = array(
      array('1)AEo@^Cq&n[i&K5Rbk)YmYto|iK6&:j,3w.9',  '1e78V2',   '22x_TJwDCwSvVmx5eGYgZwR70vN03nytSZGiAgNPYebjrQmwYs-oBAqUmRd5B9jHlk4Dmq6B45clMfEDZj4Amg'),
      array(',>3(K!$eu0QXr6SBW[$',                    'jPpz9',    'shwqhvs-EBr4cPKAhKwx8Tg6hSMSyNjPHDh8p-e94qU'),
      array('Xm+0JOu1pZ#mLu4k !h<J~nRC',              'q1I5LcMX', '3Ze4oHSqI5oN2AwGfFLqKerVIkIO8hcpTRmMFrpVMiY'),
      array('nU1I|X61$s WT \'{Ia)25|\'f.F',           'tTfKUX6',  'UBBFIuqdWz-D59B1W9v4axN5N5BONcVxK13E_cVj6jM'),
      array('3UrLXOOI/*/VW3\\@l8#DkLFpm(8U@$%bsKTmC', '803aRMNF', '7e7bKnx43eHNaz-BF3DcRDIyRXc_n7i8ANX4I0vtifzjpPPofy4GAuacKA5lWmhxLcPmm0hooEfR7NbR2RpqQA'),
      array('g&W$w[K(rt(jwWC{fYDw\'M;I/1gNo',         'MpRE',     '35tNZp_7BdOdl78Kr7g6C9l-tGUWsDGjnPaLSVvsdk4'),
      array('*R$oh3nu-pe2#}ovVT!Wr/Rk?hj<',           'EGqi',     'tp1QhPUl_OY_SdSXlEk8uUj06J2ODFMG069SfR1UWTY'),
      array('CUo<&\\Te-s2O[zsQg&m%_',                 'Jbyjh',    'FH1bgmum4IhTeWT_WUS_P4aKUXTBUux-UDdpBxoypmI'),
      array('y+U/0i&:Z$]+\\G`<rPc|\\{-7e^',           'LuMhrEPs', 'i9Q4ugsCpZS5M0xYwmm3rnvQcupBCNEzXvhYljpI6K8'),
      array('=V>00QV807A7*seug3 fh^n;7\'w&CX0x!3P~',  'zbOj0',    'DzzUC_QTjgm09jUP6opubGBec-_y2t_qTzznu6GDW5dQH-9OwogLsh8bv1E56gMtBzneWrgyb0zv9ljdKi0ssQ'),
      array('rl3yLUk<{bApLXJ@a@\\Y{M\\,z:4',          'QCGdqu',   'JTUFKcO0og8-NJtvl0PY8vQbLcjpBQzOI18doptwju8'),
      array('*rF@W)b9GOu<[`RR1/,#FnQCE3PgI',          'QQOd',     'PoWS8Yy7cjN8cfk44Wd_mz3wiwf_zVVDk-Sh75UAt94'),
      array('j$kY#JNym311~0hVo%HX@7Fsks(g',           'PxFdn97J', 'hWwJI1bh6LM6y6lsbekda6e4Gcedf7zkZRdKe882CAE'),
      array(']&rQB>~nOf!A4h7}X~G$\\!uD$zGc*a',        '9wlzwu78', 'uAL00RVr8X9MSU8Z4o63CpyoU8AQv8etF5aB1ZVayfU'),
      array('Y7RZTJV>U\\"mx3(C!5hKgBw-',              '7RyavjK',  'M55vgEwiLOKLMSPmLGldunnkBBRAPQAEcUwpgRRrxW0'),
      array('!t#.;(bK}k@kVrf;#}Q-jp|;?hE|+.O',        'i1txZiB',  'Db7ktjeBwCfp1ZNT1B3EuGN0zoJOA7Ie6C_0JINuLvU'),
      array('>1`k@Lr4|3ot4WrgA!g||8}vSZhBT=c13|,/_{', 'OGHuN',    'Vc9BEP_JFshoM65Cvn7cs82W9IybUZdUtn5iBin5QGgOgTc8Gko2bVrtYC9TZ7__3v4diH8tRdN4CmBQBt9GAg'),
      array('\\0TcEC6wGL# >JXv6 `eJ',                 '7zaLTGW',  'JPz__8TD8GIYX3IoLbicq67PU4BqC-3oyTniwirQvY8'),
      array(':r")v$eXry,13E!{7?K.U%-@SDD',            'hvHW',     'x_fL0dKsVf7Rv_cWYbyB_7FGniyZgFiI_VfdLRyPPRc'),
      array('q%S/wOQhM%f3G06C1#uJgjIMWf\\`',          '4Zlb',     'rscMSDQGtZBBEZtj9ToKAPp1oTPZuNFJYePmGNVQMyo'),
    );
    return $result;
  }

  public function providerEncryptRandom()
  {
    $charlist = ' 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~!@#$%^&*()_+-=[]{}\|/?,.<>;:"'."'";
    $result = array();
    for ($i=0; $i<20; $i++)
    {
      $string = generate_random_string(mt_rand(20, 40), $charlist);
      $key    = generate_random_string(mt_rand(4, 8));
      $result[] = array($string, $key);
    }
    return $result;
  }
}

// EOF
