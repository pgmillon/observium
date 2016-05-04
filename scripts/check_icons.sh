#!/usr/bin/env bash
#
# Observium
#
#   This file is part of Observium.
#
# Simple os icons checker script
#
# @copyright (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited 
# @author    Mike Stupalov <mike@observium.org>
#

cd /opt/observium/html/images/os

# Standart icon sizes
SIZE_1X_NORMAL="32 x 32"
SIZE_2X_NORMAL="64 x 64"
SIZE_1X_WIDE="48 x 32"
SIZE_2X_WIDE="96 x 64"

# stats
STAT_WIDE=0
STAT_NORMAL=0

STAT_1X=0 # This is also total
STAT_1X_OK=0
STAT_1X_FALSE=0

STAT_2X=0
STAT_2X_OK=0
STAT_2X_FALSE=0

for FN in `ls *.png | grep -v '_2x\.png'`; do
  let STAT_1X++

  DIM=`file $FN | awk -F ', ' '{print $2}'`
  if [ "$DIM" = "$SIZE_1X_WIDE" ]; then
    # Wide (3:2) icon
    let STAT_WIDE++
    let STAT_1X_OK++

    STYLE="wide"
    DIM2_SIZE="$SIZE_2X_WIDE"
    DIM_STATUS="ok"
  elif [ "$DIM" = "$SIZE_1X_NORMAL" ]; then
    # Normal (1:1) icon
    let STAT_NORMAL++
    let STAT_1X_OK++

    STYLE="normal"
    DIM2_SIZE="$SIZE_2X_NORMAL"
    DIM_STATUS="ok"
  else
    let STAT_1X_FALSE++

    STYLE="unknown"
    DIM_STATUS="false"
  fi

  if [ -f ${FN/%.png/_2x.png} ];
  then
    let STAT_2X++

    DIM2=`file ${FN/%.png/_2x.png} | awk -F ', ' '{print $2}'`
    if [ "$STYLE" != "unknown" ]; then
      # 2X icon must be in same ratio
      if [ "$DIM2" = "$DIM2_SIZE" ]; then
        let STAT_2X_OK++
        DIM2_STATUS="ok"
      else
        let STAT_2X_FALSE++
        DIM2_STATUS="false"
      fi
    elif [ "$DIM2" = "$SIZE_2X_NORMAL" -o "$DIM2" = "$SIZE_2X_WIDE" ]; then
      # 1X size false, just check 2X size
      let STAT_2X_OK++

      DIM2_STATUS="ok"
    else
      let STAT_2X_FALSE++
      DIM2_STATUS="false"
    fi
  else
    DIM2="NOT exists"
    DIM2_STATUS="unknown"
  fi

  printf "File: %18s, size: %7s, 1X: %10s (%-7s), 2X: %10s (%-7s)\n" "$FN" "$STYLE" "$DIM" "$DIM_STATUS" "$DIM2" "$DIM2_STATUS"
  #file $FN
done

# calculate unknowns
let STAT_1X_UNKNOWN=STAT_1X-STAT_WIDE-STAT_NORMAL
let STAT_2X_UNKNOWN=STAT_1X-STAT_2X

# print statistics
printf "\nOS icon statistics:\n"
printf "Total 1X: %d (ok: %d, false: %d),\n      2X: %d (ok: %d, false: %d, not exist: %d).\n" "$STAT_1X" "$STAT_1X_OK" "$STAT_1X_FALSE" "$STAT_2X" "$STAT_2X_OK" "$STAT_2X_FALSE" "$STAT_2X_UNKNOWN"
printf "Total normal: %d, wide: %d, unknown: %d.\n" "$STAT_NORMAL" "$STAT_WIDE" "$STAT_1X_UNKNOWN"

# EOF
