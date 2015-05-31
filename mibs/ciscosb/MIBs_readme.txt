Cisco Small Business Smart Switch (SF200-xx, SG200-xx) 1.3.5 MIB Release Read Me
Cisco Small Business Managed Switch (SF300-xx, SG300-xx, ESW2-SG350-xx) 1.3.5 MIB Release Read Me
Cisco Small Business Stackable Managed Switch (SF500-xx, SG500-xx, SG500X-xx, SG500XG-xx, ESW2-SG550x-xx) 1.3.5 MIB Release Read Me

Compilation 
===========
Please read the instruction below before you compile the MIBs.

1. These MIBs have been compiled with SilverCreek and MG-SOFT applications. 

2. With MG-Soft application, please do NOT use the batch compile option. Load MIBs files with "Scan For Source Files" option under Tools menu bar, and make sure Include subfolder is checked. After scan, select "Compile All" from Modules menu bar.  

3. If you encounter any compilation errors, please make sure application has clean environment before loading these MIBs into it.
  -- For MG-Soft, when the Compiler application is launched, make sure there are NO registered/loaded MIB modules shown on Modules Window. To clean these registered/loaded MIB modules, please follow the instructions below. 

   Step 1: Mouse over Modules Window, and right click. Select "Select All".
   Step 2: Click "Delete" from Modules menu bar.
   Step 3: Follow the on-screen instructions. Confirm Deletion and make sure Database file and Registry information are checked. 
   Step 4: Load MIBs files and include the files under the sub-folder into MG-SOFT MIB Compiler, and then compile. NOTE: Do NOT use the Batch Compile option. 
  -- For SilverCreek, when the application is launched, make sure there is no Enterprise MIBs shown on the MIB Testing tab. To clean these Enterprise MIBs, please follow the instructions below. 
   Step 1: Launch the Load and Delete MIBs window from the MIB menu. 
   Step 2: Select all the MIBs to be deleted. 
   Step 3: Click "Delete Definition Files". 
   Step 4: Confirm Deletion. 
   Step 5: Load MIB files from the parent folder of the zip file. The files will be compiled automatically. 


Device SNMP Configuration
=========================
Refer to product administration guide for more information.

Port to ifIndex mapping for Standalone mode
===========================================

In SF300 device family: 
The Fast Ethernet ports are mapped to ifIndex 1-48 (depending on number of FE ports in the specific SKU).
The Uplink Gigabit Ethernet ports (up to 4 uplink ports) are mapped to ifIndex 49 and above (depending on number of GE ports in the specific SKU).

In SG300 device family:
The ports are mapped to ifIndex 49 and above. Therefore, port GE1 is mapped to ifIndex 49, port GE2 is mapped to ifIndex 50 etc.
Note that per the definition of PortList in Q-BRIDGE-MIB, the not-present ports in the PortList are skipped using leading zeros, e.g. port GE1 in a PortList is written as 0x0000001 (each octet specifies a set of eight ports).

Example 1: SF300-24/P/PP/MP
---------------------------
-----------------------------------------------------------------------
|         |      Network Ports      |   Copper Uplinks  | Combo Uplink |
-----------------------------------------------------------------------
|         |   1 2 3             24  |    GE1     GE2    |  GE3    GE4  |
-----------------------------------------------------------------------
|ifIndex  |   1 2 3             24  |     49      50    |   51     52  |
-----------------------------------------------------------------------


Example 2: SF300-48/P
---------------------
-----------------------------------------------------------------------
|         |      Network Ports      |   Copper Uplinks  | Combo Uplink |
-----------------------------------------------------------------------
|         |   1 2 3             48  |    GE1     GE2    |  GE3    GE4  |
-----------------------------------------------------------------------
|ifIndex  |   1 2 3             48  |     49      50    |   51     52  |
-----------------------------------------------------------------------


Example 3: SG300-28/P/PP/MP
---------------------------
-----------------------------------------------------------------------
|         |      Network Ports      |   Copper Uplinks  | Combo Uplink |
-----------------------------------------------------------------------
|         |   1  2              24  |    GE25    GE26   |  GE27   GE28 |
-----------------------------------------------------------------------
|ifIndex  |  49 50              72  |     73      74    |   75     76  |
-----------------------------------------------------------------------


Example 4: SG300-52/P/MP/ESW2-350G
----------------------------------
-----------------------------------------------------------------------
|         |      Network Ports      |   Copper Uplinks  | Combo Uplink |
-----------------------------------------------------------------------
|         |   1  2              48  |    GE49    GE50   |  GE51   GE52 |
-----------------------------------------------------------------------
|ifIndex  |  49 50              96  |     97      98    |   99     100 |
-----------------------------------------------------------------------


In SF200 device family: 
The Fast Ethernet ports are mapped to ifIndex 1-48 (depending on number of FE ports in the specific SKU).
The Uplink Gigabit Ethernet ports (2 Combo ports) are mapped to ifIndex 49 and 50.

In SG200 device family:
The ports are mapped to ifIndex 49 and above. Therefore, port GE1 is mapped to ifIndex 49, port GE2 is mapped to ifIndex 50 etc.
Note that per the definition of PortList in Q-BRIDGE-MIB, the not-present ports in the PortList are skipped using leading zeros, e.g. port GE1 in a PortList is written as 0x0000001 (each octet specifies a set of eight ports).

Example 1: SF200-24/P/FP
---------------------------
---------------------------------------------------
|         |      Network Ports      | Combo Uplink |
---------------------------------------------------
|         |   1 2 3             24  |  GE1     GE2 |
---------------------------------------------------
|ifIndex  |   1 2 3             24  |   49      50 |
---------------------------------------------------


Example 2: SF200-48/P
---------------------
---------------------------------------------------
|         |      Network Ports      | Combo Uplink |
---------------------------------------------------
|         |   1 2 3             48  |  GE1    GE2  |
---------------------------------------------------
|ifIndex  |   1 2 3             48  |   49     50  |
---------------------------------------------------


Example 3: SG200-26/P/FP
------------------------
---------------------------------------------------
|         |      Network Ports      | Combo Uplink |
---------------------------------------------------
|         |   1  2              24  |  GE25   GE26 |
---------------------------------------------------
|ifIndex  |  49 50              72  |   73     74  |
---------------------------------------------------


Example 4: SG200-50/P/FP
------------------------
---------------------------------------------------
|         |      Network Ports      | Combo Uplink |
---------------------------------------------------
|         |   1  2              48  |  GE49   GE50 |
---------------------------------------------------
|ifIndex  |  49 50              96  |   97     98  |
---------------------------------------------------

In SF500 device family: 
The Fast Ethernet ports are mapped to ifIndex 1-48 (depending on number of FE ports in the specific SKU).
The 4 Uplink Gigabit Ethernet ports are mapped to ifIndex 101 till 104.
As in the SG500 family, Uplinks are two sets of ports - Combo and Direct Attached (DAC).

Example 1: SF500-24/P Standalone
--------------------------------
------------------------------------------------------------------------
|         |      Network Ports      |  Combo Uplinks   |   DAC Uplinks  |
------------------------------------------------------------------------
|         |   1 2 3             24  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
------------------------------------------------------------------------
|ifIndex  |   1 2 3             24  |    101   102     |    103   104   |
------------------------------------------------------------------------

Example 2: SF500-48/P Standalone
--------------------------------
------------------------------------------------------------------------
|         |      Network Ports      |  Combo Uplinks   |   DAC Uplinks  |
------------------------------------------------------------------------
|         |   1 2 3             48  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
------------------------------------------------------------------------
|ifIndex  |   1 2 3             48  |    101   102     |    103   104   |
------------------------------------------------------------------------


In SG500 device family:
The ports are mapped to ifIndex 49 and above. Therefore, port GE1/1 is mapped to ifIndex 49, port GE1/2 is mapped to ifIndex 50 etc.
Uplinks are two sets of ports - Combo and Direct Attached (DAC).
Note that per the definition of PortList in Q-BRIDGE-MIB, the not-present ports in the PortList are skipped using leading zeros, 
e.g. port GE1/1 in a PortList is written as 0x0000001 (ifIndexes 1-48 which are not present are represented with 0 -
each octet specifies a set of eight ports).

Example 1: SG500-28/P/MPP Standalone
------------------------------------
------------------------------------------------------------------------
|         |      Network Ports      |  Combo Uplinks   |   DAC Uplinks  |
------------------------------------------------------------------------
|         |   1  2              24  |  G25/S1 G26/S2   |  G27/S3 G28/S4 |
------------------------------------------------------------------------
|ifIndex  |  49 50              72  |    73     74     |    75     76   |
------------------------------------------------------------------------

Example 2: SG500-52/P/MP Standalone
-----------------------------------
------------------------------------------------------------------------
|         |      Network Ports      |  Combo Uplinks   |   DAC Uplinks  |
------------------------------------------------------------------------
|         |   1  2              48  |  G49/S1 G50/S2   |  G51/S3 G52/S4 |
------------------------------------------------------------------------
|ifIndex  |  49 50              96  |    97    98      |   99     100   |
------------------------------------------------------------------------


In SG500X device family:
The 1G Ethernet ports are mapped to ifIndex 49 till 96 (depending on number of GE ports on specific SKU),
therefore, port GE1/1 is mapped to ifIndex 49, port GE1/2 is mapped to ifIndex 50 etc.
The 4 Uplink (AMCC ports) 10G Ethernet ports are not consecutive to the 1G Ethernet ports and mapped to ifIndex 107 till 110.
Note that per the definition of PortList in Q-BRIDGE-MIB, the not-present ports in the PortList are skipped using leading zeros, 
e.g. port GE1/1 in a PortList is written as 0x0000001 (ifIndexes 1-48 which are not present are represented with 0 -
each octet specifies a set of eight ports).

Example 1: SG500X-24/P Standalone
---------------------------------
--------------------------------------------------------------------
|         |      Network Ports      |    XG Uplinks   |  XG Uplinks |
--------------------------------------------------------------------
|         |   1  2              24  |    XG1    XG2   |  XG3   XG4  |
--------------------------------------------------------------------
|ifIndex  |  49 50              72  |    107    108   |  109   110  |
--------------------------------------------------------------------

Example 2: SG500X-48/P/ESW2-550X Standalone
-------------------------------------------
--------------------------------------------------------------------
|         |      Network Ports      |    XG Uplinks   |  XG Uplinks |
--------------------------------------------------------------------
|         |   1  2              48  |    XG1    XG2   |  XG3   XG4  |
--------------------------------------------------------------------
|ifIndex  |  49 50              96  |    107    108   |  109   110  |
--------------------------------------------------------------------


In SG500XG device family:
The XG Ethernet ports are mapped to ifIndex 1 till 16 and the 1G management ethernet port is mapped to ifIndex 101.

Example 1: SG500XG-8F8T Standalone
----------------------------------
------------------------------------------------
|         |   XG Network Ports      |  1G Port  |
------------------------------------------------
|         |   1  2              16  |  G1       |
------------------------------------------------
|ifIndex  |   1  2              16  |  101      |
------------------------------------------------



Port to ifIndex mapping for Stacking (native) mode
==================================================

In SF500 and SG500 native mode, stacking ports sets can be either Combo (S1-S2) or DAC ports (S3-S4).

In SG500X native mode, stacking ports sets can be either AMCC ports (S1-S2-xg) or DAC ports (S1-S2-5g).

In SG500XG native mode, stacking ports sets can be any of either the Copper ports (XG1-XG8) or Fiber ports (XG9-XG16).

Notes:
1)	ifIndex below is per unit Id (Sx500, SG500X and SG500XG):
	-	Unit Id 1 ifIndex range is 001-110
	-	Unit Id 2 ifIndex range is 111-220
	-	Unit Id 3 ifIndex range is 221-330
	-	Unit Id 4 ifIndex range is 331-440
	-	Unit Id 5 ifIndex range is 441-550
	-	Unit Id 6 ifIndex range is 551-660
	-	Unit Id 7 ifIndex range is 661-770
	-	Unit Id 8 ifIndex range is 771-880
2)	In SG500X stack, on any stack mode, for both stacking ports (AMCC or DAC), the XG uplinks 
    ifIndex never changed (107-108, relative)
3)	When an interface acts as a stacking interface it does not have an ifIndex representation 
	(stacking ports can not be configured), so it marked as dashed string


Example 1: SF500-24 S3-S4 Native Stack
--------------------------------------
-------------------------------------------------------------------------------------
|                  |      Network Ports          |  Combo Uplinks   |   DAC Uplinks  |
-------------------------------------------------------------------------------------
|                  |   1   2   3             24  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
-------------------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2   3             24  |    101   102     |    ---   ---   |
|Unit id 2 ifIndex | 111 112 113            134  |    211   212     |    ---   ---   |
|Unit id 3 ifIndex | 221 222 223            244  |    321   322     |    ---   ---   |
|Unit id 4 ifIndex | 331 332 333            354  |    431   432     |    ---   ---   |
|Unit id 5 ifIndex | 441 442 443            464  |    541   542     |    ---   ---   |
|Unit id 6 ifIndex | 551 552 553            574  |    651   652     |    ---   ---   |
|Unit id 7 ifIndex | 661 662 663            684  |    761   762     |    ---   ---   |
|Unit id 8 ifIndex | 771 772 773            794  |    871   872     |    ---   ---   |
-------------------------------------------------------------------------------------

Example 2: SF500-48 S3-S4 Native Stack
--------------------------------------
-------------------------------------------------------------------------------------
|                  |      Network Ports          |  Combo Uplinks   |   DAC Uplinks  |
-------------------------------------------------------------------------------------
|                  |   1   2   3             48  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
-------------------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2   3             48  |    101   102     |    ---   ---   |
|Unit id 2 ifIndex | 111 112 113            158  |    211   212     |    ---   ---   |
|Unit id 3 ifIndex | 221 222 223            268  |    321   322     |    ---   ---   |
|Unit id 4 ifIndex | 331 332 333            378  |    431   432     |    ---   ---   |
|Unit id 5 ifIndex | 441 442 443            488  |    541   542     |    ---   ---   |
|Unit id 6 ifIndex | 551 552 553            598  |    651   652     |    ---   ---   |
|Unit id 7 ifIndex | 661 662 663            708  |    761   762     |    ---   ---   |
|Unit id 8 ifIndex | 771 772 773            818  |    871   872     |    ---   ---   |
-------------------------------------------------------------------------------------

Example 3: SG500-28 S3-S4 Native Stack
--------------------------------------
----------------------------------------------------------------------------------
|                  |      Network Ports      |  Combo Uplinks   |    DAC Uplinks  |
----------------------------------------------------------------------------------
|                  |   1   2              24  |  G25/S1 G26/S2   |  G27/S3 G28/S4 |
----------------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              72  |    73     74     |    --     --   |
|Unit id 2 ifIndex | 159 160             182  |   183    184     |    --     --   |
|Unit id 3 ifIndex | 269 270             292  |   293    294     |    --     --   |
|Unit id 4 ifIndex | 379 380             402  |   403    404     |    --     --   |
|Unit id 5 ifIndex | 489 490             512  |   513    514     |    --     --   |
|Unit id 6 ifIndex | 599 600             622  |   623    624     |    --     --   |
|Unit id 7 ifIndex | 709 710             732  |   733    734     |    --     --   |
|Unit id 8 ifIndex | 819 820             842  |   843    844     |    --     --   |
----------------------------------------------------------------------------------

Example 4: SG500-52 S3-S4 Native Stack
--------------------------------------
----------------------------------------------------------------------------------
|                  |      Network Ports       |  Combo Uplinks   |   DAC Uplinks  |
----------------------------------------------------------------------------------
|                  |   1   2              48  |  G49/S1 G50/S2   |  G51/S3 G52/S4 |
----------------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              96  |    97     98     |    --     --   |
|Unit id 2 ifIndex | 159 160             206  |   207    208     |    --     --   |
|Unit id 3 ifIndex | 269 270             316  |   317    318     |    --     --   |
|Unit id 4 ifIndex | 379 380             426  |   427    428     |    --     --   |
|Unit id 5 ifIndex | 489 490             536  |   537    538     |    --     --   |
|Unit id 6 ifIndex | 599 600             646  |   647    648     |    --     --   |
|Unit id 7 ifIndex | 709 710             756  |   757    758     |    --     --   |
|Unit id 8 ifIndex | 819 820             866  |   867    868     |    --     --   |
----------------------------------------------------------------------------------

Example 5: SF500-24 S1-S2 Stack
-------------------------------
-------------------------------------------------------------------------------------
|                  |      Network Ports          |  Combo Uplinks   |   DAC Uplinks  |
-------------------------------------------------------------------------------------
|                  |   1   2   3             24  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
-------------------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2   3             24  |    ---   ---     |    103   104   |
|Unit id 2 ifIndex | 111 112 113            134  |    ---   ---     |    213   214   |
|Unit id 3 ifIndex | 221 222 223            244  |    ---   ---     |    323   324   |
|Unit id 4 ifIndex | 331 332 333            354  |    ---   ---     |    433   434   |
|Unit id 5 ifIndex | 441 442 443            464  |    ---   ---     |    543   544   |
|Unit id 6 ifIndex | 551 552 553            574  |    ---   ---     |    653   654   |
|Unit id 7 ifIndex | 661 662 663            684  |    ---   ---     |    763   764   |
|Unit id 8 ifIndex | 771 772 773            794  |    ---   ---     |    873   874   |
-------------------------------------------------------------------------------------

Example 6: SF500-48 S1-S2 Native Stack
--------------------------------------
-------------------------------------------------------------------------------------
|                  |      Network Ports          |  Combo Uplinks   |   DAC Uplinks  |
-------------------------------------------------------------------------------------
|                  |   1   2   3             48  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
-------------------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2   3             48  |    ---   ---     |    103   104   |
|Unit id 2 ifIndex | 111 112 113            158  |    ---   ---     |    213   214   |
|Unit id 3 ifIndex | 221 222 223            268  |    ---   ---     |    323   324   |
|Unit id 4 ifIndex | 331 332 333            378  |    ---   ---     |    433   434   |
|Unit id 5 ifIndex | 441 442 443            488  |    ---   ---     |    543   544   |
|Unit id 6 ifIndex | 551 552 553            598  |    ---   ---     |    653   654   |
|Unit id 7 ifIndex | 661 662 663            708  |    ---   ---     |    763   764   |
|Unit id 8 ifIndex | 771 772 773            818  |    ---   ---     |    873   874   |
-------------------------------------------------------------------------------------

Example 7: SG500-28 S1-S2 Stack
-------------------------------
----------------------------------------------------------------------------------
|                  |      Network Ports      |  Combo Uplinks   |    DAC Uplinks  |
----------------------------------------------------------------------------------
|                  |   1   2              24  |  G25/S1 G26/S2   |  G27/S3 G28/S4 |
----------------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              72  |   ---    ---     |    75     76   |
|Unit id 2 ifIndex | 159 160             182  |   ---    ---     |   185    186   |
|Unit id 3 ifIndex | 269 270             292  |   ---    ---     |   295    296   |
|Unit id 4 ifIndex | 379 380             402  |   ---    ---     |   405    406   |
|Unit id 5 ifIndex | 489 490             512  |   ---    ---     |   515    516   |
|Unit id 6 ifIndex | 599 600             622  |   ---    ---     |   625    626   |
|Unit id 7 ifIndex | 709 710             732  |   ---    ---     |   735    736   |
|Unit id 8 ifIndex | 819 820             842  |   ---    ---     |   845    846   |
----------------------------------------------------------------------------------

Example 8: SG500-52 S1-S2 Stack
-------------------------------
----------------------------------------------------------------------------------
|                  |      Network Ports       |  Combo Uplinks   |   DAC Uplinks  |
----------------------------------------------------------------------------------
|                  |   1   2              48  |  G49/S1 G50/S2   |  G51/S3 G52/S4 |
----------------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              96  |   ---    ---     |    99    100   |
|Unit id 2 ifIndex | 159 160             206  |   ---    ---     |   209    210   |
|Unit id 3 ifIndex | 269 270             316  |   ---    ---     |   319    320   |
|Unit id 4 ifIndex | 379 380             426  |   ---    ---     |   429    430   |
|Unit id 5 ifIndex | 489 490             536  |   ---    ---     |   539    540   |
|Unit id 6 ifIndex | 599 600             646  |   ---    ---     |   649    650   |
|Unit id 7 ifIndex | 709 710             756  |   ---    ---     |   759    760   |
|Unit id 8 ifIndex | 819 820             866  |   ---    ---     |   869    870   |
----------------------------------------------------------------------------------

Example 9: SG500X-24 Stack
---------------------------
-----------------------------------------------------------------------------
|                  |      Network Ports       |   XG Uplinks   |  XG Uplinks |
-----------------------------------------------------------------------------
|                  |   1   2              24  |   XG1    XG2   |  XG3   XG4  |
-----------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              72  |   107    108   |  ---   ---  |
|Unit id 2 ifIndex | 159 160             182  |   217    218   |  ---   ---  |
|Unit id 3 ifIndex | 269 270             292  |   327    328   |  ---   ---  |
|Unit id 4 ifIndex | 379 380             302  |   437    438   |  ---   ---  |
|Unit id 5 ifIndex | 489 490             512  |   547    548   |  ---   ---  |
|Unit id 6 ifIndex | 599 600             622  |   657    658   |  ---   ---  |
|Unit id 7 ifIndex | 709 710             732  |   767    768   |  ---   ---  |
|Unit id 8 ifIndex | 819 820             842  |   877    878   |  ---   ---  |
-----------------------------------------------------------------------------

Example 10: SG500X-48 Stack
---------------------------
-----------------------------------------------------------------------------
|                  |      Network Ports       |   XG Uplinks   |  XG Uplinks |
-----------------------------------------------------------------------------
|                  |   1   2              48  |   XG1    XG2   |  XG3   XG4  |
-----------------------------------------------------------------------------
|Unit id 1 ifIndex |  49  50              96  |   107    108   |  ---   ---  |
|Unit id 2 ifIndex | 159 160             206  |   217    218   |  ---   ---  |
|Unit id 3 ifIndex | 269 270             316  |   327    328   |  ---   ---  |
|Unit id 4 ifIndex | 379 380             426  |   437    438   |  ---   ---  |
|Unit id 5 ifIndex | 489 490             536  |   547    548   |  ---   ---  |
|Unit id 6 ifIndex | 599 600             646  |   657    658   |  ---   ---  |
|Unit id 7 ifIndex | 709 710             756  |   767    768   |  ---   ---  |
|Unit id 8 ifIndex | 819 820             866  |   877    878   |  ---   ---  |
-----------------------------------------------------------------------------

Example 11: SG500XG-8F8T Stack
------------------------------
-----------------------------------------------------------------------------
|                  |     XG Network Ports     |      1G managment Ports      |
-----------------------------------------------------------------------------
|                  |   1   2              16  |              G1              |
-----------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2              16  |             101              |
|Unit id 2 ifIndex | 111 112             126  |             211              |
|Unit id 3 ifIndex | 221 222             236  |             321              |
|Unit id 4 ifIndex | 331 332             346  |             431              |
|Unit id 5 ifIndex | 441 442             456  |             541              |
|Unit id 6 ifIndex | 551 552             566  |             651              |
|Unit id 7 ifIndex | 661 662             676  |             761              |
|Unit id 8 ifIndex | 771 772             786  |             871              |
-----------------------------------------------------------------------------

For a mixed stack (native SF500-xx with SG500-xx), use proper line from below tables, according to each device unit id.
For example: Stack of 4 units, with stacking ports S3-S4, consist of SF500-24 (master, unit 1), SG500-52 (backup, unit 2), 
SF500-48 (slave, unit 3) and SG500-28 (slave, unit 4), the following ifIndexes are available:

Example 12: Sx500 / SG500X mixed stack
--------------------------------------
----------------------------------------------------------------------------------------------------
|                  |      Network Ports                         |  Combo Uplinks   |   DAC Uplinks  |
----------------------------------------------------------------------------------------------------
|                  |   1   2               24               48  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |
----------------------------------------------------------------------------------------------------
|Unit id 1 ifIndex |   1   2               24                   |    101   102     |    ---   ---   |
|Unit id 2 ifIndex | 159 160                               206  |    207   208     |    ---   ---   |
|Unit id 3 ifIndex | 221 222                               268  |    321   322     |    ---   ---   |
|Unit id 4 ifIndex | 379 380              402                   |    403   404     |    ---   ---   |
----------------------------------------------------------------------------------------------------


Port to ifIndex mapping for Stacking (basic/advanced-hybrid) mode
=================================================================
In basic/advanced-hybrid mode (Sx500 with SG500X), stacking ports sets can only be DAC ports (S3-S4).

For example: Basic-hybrid Stack of 4 units, consist of SG500X-24 (master, unit 1), SG500-52 (backup, unit 2), 
SF500-48 (slave, unit 3) and SG500X-48 (slave, unit 4), the following ifIndexes are available:

Example 1: Sx500 / SG500X mixed stack
-------------------------------------
----------------------------------------------------------------------------------------------------------------------------------- 
|                                                               |                Sx500              |             SG500X           |
|                  |      Network Ports                         |  Combo Uplinks   |   DAC Uplinks  |   XG Uplinks   |  XG Uplinks |
----------------------------------------------------------------------------------------------------------------------------------- 
|                  |   1   2               24               48  |   G1/S1 G2/S2    |   G3/S3 G4/S4  |   XG1    XG2   |  XG3   XG4  |
----------------------------------------------------------------------------------------------------------------------------------- 
|Unit id 1 ifIndex |  49  50               72                   |    ---   ---     |    ---   ---   |   107    108   |  ---   ---  |
|Unit id 2 ifIndex | 159 160                               206  |    207   208     |    ---   ---   |   ---    ---   |  ---   ---  |
|Unit id 3 ifIndex | 221 222                               268  |    321   322     |    ---   ---   |   ---    ---   |  ---   ---  |
|Unit id 4 ifIndex | 379 380                               426  |    ---   ---     |    ---   ---   |   437    438   |  ---   ---  |
----------------------------------------------------------------------------------------------------------------------------------- 

Port to ifIndex mapping for Stacking (advanced-hybrid-xg) mode
==============================================================
In advanced-hybrid-xg mode (SG500X with SG500XG):
- In SG500X, stacking ports sets can only be the AMCC ports (S1-S2-xg)
- In SG500XG, stacking ports sets can be any of either the Copper ports (XG1-XG8) or Fiber ports (XG9-XG16)

For example: Basic-hybrid Stack of 4 units, consist of SG500X-24 (master, unit 1), SG500XG (backup, unit 2), 
SG500XG (slave, unit 3) and SG500X-48 (slave, unit 4), the following ifIndexes are available:

Example 1: Sx500 / SG500X mixed stack
-------------------------------------
------------------------------------------------------------------------------------------------ 
|                                                               ||             SG500X           |
|                  |             1G/XG Network Ports            ||   XG Uplinks   |  XG Uplinks |
------------------------------------------------------------------------------------------------ 
|                  |   1   2       16      24               48  ||   XG1    XG2   |  XG3   XG4  |
------------------------------------------------------------------------------------------------ 
|Unit id 1 ifIndex |  49  50               72                   ||   107    108   |  ---   ---  |
|Unit id 2 ifIndex | 111 112      126                           ||   ---    ---   |  ---   ---  |
|Unit id 3 ifIndex | 221 222      236                           ||   ---    ---   |  ---   ---  |
|Unit id 4 ifIndex | 379 380                               426  ||   437    438   |  ---   ---  |
------------------------------------------------------------------------------------------------ 


Copyright © 2010, 2011, 2012. 2013 Cisco Systems, Inc. All rights reserved.

   
