#!/usr/bin/perl

use File::Basename;
use File::Copy;
use Getopt::Std;

usage() unless (@ARGV);

my %options=();
getopts("hse:", \%options);

usage() if defined $options{h};

for my $file (@ARGV)
{
	my $from = $file;
	my $dir  = dirname($file);
	my $to;
	my $ext  = '';

	if (defined $options{e} && $options{e}) {
		$ext = '.'.$options{e};
	}
	
	if (open (FILEIN, $file))
	{
		while (<FILEIN>)
		{
			if (/\s*(\S+)\s*DEFINITIONS\s*\:\:\=\s*BEGIN/)
			{
				$to = $1;
				last;
			}
		}
		close (FILEIN);

		if (defined $to)
		{
 			if (basename($from) eq basename($to.$ext))
			{
				print "skipping $file -- name is already correct\n";
			}
			else
			{
				if ($dir)
				{
					$to = $dir . '/' . $to;
				}
				if (defined $options{s})
				{
					system("/usr/bin/svn mv $from $to".$ext);
					#print "/usr/bin/svn mv $from $to\n";
				}
				else
				{
					move($from, $to.$ext);
				}
			}
		}
		else
		{
			warn "no definition found inside $file\n";
		}
	}
	else
	{
		warn "skipping $file -- unable to open ($!)";
	}
}

sub usage
{
	print <<END;
usage: $0 [OPTION] <MIB1> [MIB2 .. MIBn]

  Renames one or more MIB files to match the definition inside the file.

OPTIONS

  -h display this help and exit
  -s use 'svn mv' instead system mv command
  -e 'ext' use this file extension for renamed MIBs, by default without extension

END

	exit 0;
}
