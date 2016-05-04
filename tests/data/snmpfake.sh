#!/bin/bash

# Written by Mike Stupalov (mike@observium.org)
# Warning, this script work only with bash env

# Can pass fake data over cmd options
if [ "$1" == "fakedata" ];
then
  fakedata="$2"
  shift 2
fi

numargs="$#"
args="$@"

while :
do
  case "${1:0:2}" in
    -h)
      display_help  # Call your function
      # no shifting needed here, we're done.
      exit 0
      ;;
    -O)
      options="${1:2}"
      if [ -z "$options" ];
      then
        options="$2"
        shift
      fi
      shift
      ;;
    -m)
      mib="$2"
      shift 2
      ;;
    -M)
      mib_dirs="$2"
      hostname="${3#*:}"
      hostname="${hostname%:*}"
      shift 3
      oids="$@"
      break
      ;;
    -d)
      #  It's better to assign a string, than a number like "debug=1"
      #  because if you're debugging script with "bash -x" code like this:
      #
      #    if [ "$debug" ] ...
      #
      #  You will see:
      #
      #    if [ "debug" ] ...
      #
      #  Instead of cryptic
      #
      #    if [ "1" ] ...
      #
      debug="debug"
      shift
      ;;
    *) # skip all other
      shift
      ;;
  esac
done

if [ "$debug" ];
then
  echo " NUM ARGs: $numargs"
  echo "     ARGs: $args"
  echo " Hostname: $hostname"
  echo "  Options: $options"
  echo " MIB DIRs: $mib_dirs"
  echo "      MIB: $mib"
  echo "     OIDs: $oids"
  if [ "$fakedata" ];
  then
    echo "FAKE data:"
    echo "$fakedata"
  fi
  exit 1
fi

if [ "$fakedata" ];
then
  oid=$oids # currently only one oid per request

  # use data from cmd
  #IFS=$'\n' read -a data <<< "$fakedata"
  IFS=$'\n' data=($fakedata)
  for line in "${data[@]}"
  do
    #echo "$line"
    #echo "/${line%% = *}/"
    if [ "${line%% = *}" == "$oid" ];
    then
      echo "${line#* = }"
      exit 0
    fi
  done
else
  # use per dir/file data
  exit 0
fi

exit 1 # nothig found return error

# EOF
