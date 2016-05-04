

Description of files:
  CALIX-PRODUCT-MIB.my     - defines the e7 registration point, used for E7,
                             E5-400, and E5-312 products
  CALIX-SMI.my             - defines the calixNetworks registration point
  E7-Fault-MIB.txt         - e7 current alarm table and alarm counts
  E7-Notifications-MIB.txt - e7 notifications
  E7-TC.my                 - e7 support definitions

In addition to the current alarm table and alarm counts, the E7, E5-400, and
E5-312 products support the following standard MIBs:
  SNMPv2 (mib-2)     - system group
  SNMP-FRAMEWORK     - snmpEngine group
  SNMP-MPD           - snmpMPDStats
  SNMP-USER-BASED-SM - usmStats and usmUserTable
  HOST-RESOURCES     - hrMemorySize, hrStorageTable, and hrProcessorTable
  IF                 - ifTable and ifXTable

For the IF-MIB, the following numbering scheme is used for E7-20:
   101-  124 - SFP   1GigE ports, card 1, GE-24x cards
   201-  224 - SFP   1GigE ports, card 2, GE-24x cards
   301-  324 - SFP   1GigE ports, card 3, GE-24x cards
   ...
  2101- 2104 - SFP   1GigE ports, scp a, SCP-10GE cards
  2201- 2204 - SFP   1GigE ports, scp b, SCP-10GE cards
 12101-12101 - SFP+ 10GigE ports, scp a, SCP-10GE cards
 12102-12102 - XFP  10GigE ports, scp a, SCP-10GE cards
 12201-12201 - SFP+ 10GigE ports, scp b, SCP-10GE cards
 12202-12202 - XFP  10GigE ports, scp b, SCP-10GE cards
 30101-30104 - SFP     Pon ports, card 1, GPON-4 cards
 30201-30204 - SFP     Pon ports, card 2, GPON-4 cards
   ...
100001-755360 - ONT FE, GE, and HPNA Ethernet ports

For the IF-MIB, the following numbering scheme is used for E7-2:
   101-  112 - SFP   1GigE ports, shelf 1, card 1, 10GE-4 and GE-12 cards
   101-  108 - SFP   1GigE ports, shelf 1, card 1, GPON-4 cards
   101-  124 - SFP   1GigE ports, shelf 1, card 1, GE-24 cards
   201-  212 - SFP   1GigE ports, shelf 1, card 2, 10GE-4 and GE-12 cards
   201-  208 - SFP   1GigE ports, shelf 1, card 2, GPON-4 cards
   201-  224 - SFP   1GigE ports, shelf 1, card 2, GE-24 cards
   301-  312 - SFP   1GigE ports, shelf 2, card 1, 10GE-4 and GE-12 cards
   301-  308 - SFP   1GigE ports, shelf 2, card 1, GPON-4 cards
   301-  324 - SFP   1GigE ports, shelf 2, card 1, GE-24 cards
   ...
  2001- 2012 - SFP   1GigE ports, shelf 10, card 2, 10GE-4 and GE-12 cards
  2001- 2008 - SFP   1GigE ports, shelf 10, card 2, GPON-4 cards
  2001- 2024 - SFP   1GigE ports, shelf 10, card 2, GE-24 cards
 10101-10102 - XFP  10GigE ports, shelf 1, card 1, 10GE-4 and GPON-4 cards
 10201-10202 - XFP  10GigE ports, shelf 1, card 2, 10GE-4 and GPON-4 cards
 10103-10104 - SFP+ 10GigE ports, shelf 1, card 1
 10203-10204 - SFP+ 10GigE ports, shelf 1, card 2
   ...
 11901-11902 - XFP  10GigE ports, shelf 10, card 1, 10GE-4 and GPON-4 cards
 12001-12002 - XFP  10GigE ports, shelf 10, card 2, 10GE-4 and GPON-4 cards
 11903-11904 - SFP+ 10GigE ports, shelf 10, card 1
 12003-12004 - SFP+ 10GigE ports, shelf 10, card 2
 20101-20148 -         DSL ports, shelf 1, card 1,  VDSL-48C and VDSL-48 cards
 20201-20248 -         DSL ports, shelf 1, card 2,  VDSL-48C and VDSL-48 cards
   ...
 21901-21948 -         DSL ports, shelf 10, card 1,  VDSL-48C and VDSL-48 cards
 22001-22048 -         DSL ports, shelf 10, card 2,  VDSL-48C and VDSL-48 cards
   ...
 30101-30104 - SFP     Pon ports, shelf 1, card 1, GPON-4 cards
 30101-30108 - SFP     Pon ports, shelf 1, card 1, GPON-8 cards
 30201-30204 - SFP     Pon ports, shelf 1, card 2, GPON-4 cards
 30201-30208 - SFP     Pon ports, shelf 1, card 2, GPON-8 cards
   ...
 31901-31904 - SFP     Pon ports, shelf 10, card 1, GPON-4 cards
 31901-31908 - SFP     Pon ports, shelf 10, card 1, GPON-8 cards
 32001-32004 - SFP     Pon ports, shelf 10, card 2, GPON-4 cards
 32001-32008 - SFP     Pon ports, shelf 10, card 2, GPON-8 cards
   ...
100001-755360 - ONT FE, GE, and HPNA Ethernet ports

For the IF-MIB, the following numbering scheme is used for E5-400 and E5-312:
   101-  112 - SFP   1GigE ports
 10101-10102 - XFP  10GigE ports (E5-400 only)
 10103-10104 - SFP+ 10GigE ports

For the IF-MIB, the following numbering scheme is used for E3-48C:
   101-  103 - SFP   1GigE ports
 10103-10104 - SFP+ 10GigE ports
 20101-20148 -         DSL ports

For the IF-MIB, the following numbering scheme is used for E5-48C and E5-48:
   101-  104 - SFP   1GigE ports
 20101-20148 -         DSL ports

