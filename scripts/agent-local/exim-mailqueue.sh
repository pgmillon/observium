#!/bin/bash
# Ivan Dimitrov https://github.com/dobber/ - Open for ideas/fixes anytime
# For Observium - Network management and monitoring

# Exim mailqueue for observium
# stolen from munin plugins: exim_mailqueue_alt

EXIM=$(which exim 2>/dev/null)
case $EXIM:$? in
    *:1|no*) EXIM=$(which exim4 2>/dev/null)
esac
case $EXIM:$? in
    *:1|no*) EXIM=''
esac

if [ -x "$EXIM" ] ; then
	PARSE=`$EXIM -bpr 2>/dev/null`
	# Check if there is output.
	# Sometimes you have installed exim but did not configure it, so it returns nothing.
	if [ -n "$PARSE" ] ; then
		echo '<<<app-exim-mailqueue>>>'
		echo "$PARSE" | awk 'BEGIN { bounces = 0; frozen = 0; total = 0 }
		$4 == "<>" { bounces++; }
		$6 == "frozen" { frozen++ }
		/<[^>]*>/ { total++ }
		END {
		  print "frozen:" frozen;
		  print "bounces:" bounces;
		  print "total:" total;
		  print "active:" total - frozen;
		}'
	fi
fi
