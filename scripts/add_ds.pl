#!/usr/bin/perl 
#+-----------------------------------------------------------------------------+
#+ $Source: /var/repository/MONITOR/MiscScripts/add_ds.pl,v $
#+-----------------------------------------------------------------------------+
#+ Description: Type definitions for strings
#+-----------------------------------------------------------------------------+
#+ $Author: jrod $
#+-----------------------------------------------------------------------------+
#+ $Revision: 1.1.1.1 $
#+-----------------------------------------------------------------------------+
#+ Copyright (C) 2004  AetherSoft.org
#+-----------------------------------------------------------------------------+
#+ This program is free software; you can redistribute it and/or
#+ modify it under the terms of the GNU General Public License
#+ as published by the Free Software Foundation; either version 2
#+ of the License, or (at your option) any later version.
#+
#+ This program is distributed in the hope that it will be useful,
#+ but WITHOUT ANY WARRANTY; without even the implied warranty of
#+ MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#+ GNU General Public License for more details.
#+
#+ You should have received a copy of the GNU General Public License
#+ along with this program; if not, write to the Free Software
#+ Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#+-----------------------------------------------------------------------------+


# +----------------------------------------------------------------------------+
# + {{{ Modules
# +----------------------------------------------------------------------------+
use strict;
use warnings;
use RRDs;
use Pod::Usage;
use Getopt::Long;
# +----------------------------------------------------------------------------+
# + }}} Modules
# +----------------------------------------------------------------------------+

# +----------------------------------------------------------------------------+
# + {{{ Global Variables
# +----------------------------------------------------------------------------+
my %options               = ();
   $options{'source'}     = undef;
   $options{'ds'}         = undef;
my %extraOpts             = ();
   $extraOpts{'help'}     = 0;
   $extraOpts{'man'}      = 0;
   $extraOpts{'verbose'}  = 0;
   $extraOpts{'nobackup'} = 0;
my $getoptResult          = undef;
# +----------------------------------------------------------------------------+
# + }}} Global Variables
# +----------------------------------------------------------------------------+

# +----------------------------------------------------------------------------+
# + {{{ Get Options
# +----------------------------------------------------------------------------+
GetOptions( 'source=s' => \$options{'source'},
            'ds=s'     => \$options{'ds'},
            'help'     => \$extraOpts{'help'},
            'man'      => \$extraOpts{'man'},
            'verbose'  => \$extraOpts{'verbose'},
            'nobackup' => \$extraOpts{'nobackup'} ) or pod2usage(2);

pod2usage(1) if $extraOpts{'help'};
pod2usage(-exitstatus => 0, -verbose => 2) if $extraOpts{'man'};
# +----------------------------------------------------------------------------+
# + }}} Get Options
# +----------------------------------------------------------------------------+

# +----------------------------------------------------------------------------+
# + {{{ Validate Options
# +----------------------------------------------------------------------------+
# Source Defined
pod2usage(-exitstatus => 1, -verbose => 1, -msg => "No source RRD defined.")
  if ( not defined $options{'source'} );

# Datasource Defined
pod2usage(-exitstatus => 1, -verbose => 1, -msg => "No Datasource defined.")
  if ( not defined $options{'ds'} );

# Existing RRD
die "RRD does not exist: $options{'source'}\n" if ( not -e $options{'source'} );

# Writable RRD
die "Cannot write to: $options{'source'}\n" if ( not -e $options{'source'} );

# Backup file existence
if ( $extraOpts{'nobackup'} and -e $options{'source'})
{
  die "Backup file exists: $options{'source'}.bak\n";
}

# Datasource
if ( $options{'ds'} =~ /^DS:([a-zA-Z0-9_]{1,19}):(\w+):(\d+):([\dU]+):([\dU]+)/)
{
  # Variables
  my $dsName      = $1;
  my $dsType      = $2;
  my $dsHeartBeat = $3;
  my $dsMin       = $4;
  my $dsMax       = $5;

  # Sanity Checks
  no warnings;
  die "Bad DS Definition, min > max: $options{'ds'}\n" if $dsMin > $dsMax;
  use warnings;

  if ( $dsType ne "GAUGE"
       and $dsType ne "COUNTER"
       and $dsType ne "DERIVE"
       and $dsType ne "ABSOLUTE" )
  {
    die "Bad DS Definition, unknown DS type: $options{'ds'}\n";
  }
}
else
{
  die "Bad DS Definition: $options{'ds'}\n";
}
# +----------------------------------------------------------------------------+
# + }}} Validate Options
# +----------------------------------------------------------------------------+

# +----------------------------------------------------------------------------+
# + {{{ Parse XML & add DS
# +----------------------------------------------------------------------------+
# Datasource
if ( $options{'ds'} =~ /^DS:([a-zA-Z0-9_]{1,19}):(\w+):(\d+):([\dU]+):([\dU]+)/)
{
  # Variables
  my $dsName      = $1;
  my $dsType      = $2;
  my $dsHeartBeat = $3;
  my $dsMin       = $4;
  my $dsMax       = $5;

  # Get XML Output
  if ( my $xml = `rrdtool dump $options{'source'}` )
  {

	$xml=addDSToRRD($dsName,$dsType,$dsHeartBeat,$dsMin,$dsMax,$xml);

    # Move the old source
    rename($options{'source'}, $options{'source'} . ".bak") or
      die "Could not move $options{'source'} to $options{'source'}.bak";

    # Write output
    open(OUTF, ">$options{'source'}.xml") or
      die "Could not open $options{'source'}.xml for writing";
    print OUTF $xml;
    close(OUTF);

    # Re-import
	print "Restore...\n";
    `rrdtool restore $options{'source'}.xml $options{'source'}` ;
  }
  else
  {
    die "Could not dump: $!\n";
  }
}
# +----------------------------------------------------------------------------+
# + }}} Parse XML & add DS
# +----------------------------------------------------------------------------+

sub addDSToRRD{
	my ($dsName,$dsType,$dsHeartBeat,$dsMin,$dsMax,$xml) =@_;
	my $line;	
	my $outXML="";
	foreach $line (split(/\n/,$xml)) {
		if ( $line=~ /Round Robin Archives/ ){
			$outXML=$outXML."	<ds>
		<name> $dsName </name>
		<type> $dsType </type>
		<minimal_heartbeat> $dsHeartBeat </minimal_heartbeat>
		<min> $dsMin </min>
		<max> $dsMax </max>
		
		<!-- PDP Status -->
		<last_ds> UNKN </last_ds>
		<value> 0.0000000000e+00 </value>
		<unknown_sec> 0 </unknown_sec>
	</ds>\n".$line."\n";	
		}elsif ($line =~ /^(.+?<row>)(.+?)(<\/row>.*)$/){
			my @datasources_in_entry = split(/<\/v>/, $2);
			splice(@datasources_in_entry, 999, 0, "<v> 0 ");
			my $new_line = join("</v>", @datasources_in_entry);
			$outXML=$outXML."$1$new_line</v>$3\n";
		}elsif ($line =~ /<\/cdp_prep>/){
			$outXML=$outXML."
		 <ds><value> NaN </value>  <unknown_datapoints> 0 </unknown_datapoints></ds>\n".$line."\n";			
		}else{
			$outXML=$outXML.$line."\n";	
		}
	}
	
	return $outXML;
}

# +----------------------------------------------------------------------------+
# + {{{ POD
# +----------------------------------------------------------------------------+
__END__

=head1 NAME

add_ds.pl - Add datasource to an existing RRD

=head1 SYNOPSIS

add_ds.pl [options] --source="source.rrd" --ds="DS:new:GAUGE:600:U:U"

 Options:
   --help            brief help message
   --man             full documentation
   --verbose         describe what is happening
   --nobackup        do not create a .rrd.bak file

=head1 OPTIONS

=over 8

=item B<--help>

Print a brief help message and exits.

=item B<--man>

Prints the manual page and exits.

=back

=head1 DESCRIPTION

B<This program> will backup an existing rrd and add a new datasource.

=cut
# +----------------------------------------------------------------------------+
# + }}} POD
# +----------------------------------------------------------------------------+
