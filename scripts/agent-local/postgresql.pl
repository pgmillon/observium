#!/usr/bin/env perl
# Postgresql stats scripts, basic functionality for now but will add features in the future/per request
# Requires perl module DBI and correct postgresql.conf file.
# Ivan Dimitrov <zlobber at gmail dot com> - Open for ideas/fixes anytime
# For Observium - Network management and monitoring

my $DEBUG=0; # Value greater than 0 sets debug mode. Useful for testing (not for production).
my $confFile='postgresql.conf';

# --- DO NOT EDIT BENEATH THIS LINE --- #

use warnings;
use strict;
use DBI;

my ($drvName, $hostName, $dbUser, $dbPass, $dbName, $conn, $query, $all, $version, $commit);
my $idle=0;
my $select=0;
my $update=0;
my $delete=0;
my $other=0;
my $cCount=0;
my (@dDbs, @dUsr, @dHst, @dup, @list);
my %seen;
my $cmd;

# display error messages and exit
sub debug($) {
    my $errMsg=shift;
    if ($DEBUG > 0) {
	print "$errMsg\n";
    }
    exit(0);	
}

# find duplicates in arrays, number of databases, users, hosts, etc
sub findDup {
    @list=@_;
    %seen = ();

    for (@list) { $seen{$_}++ };
    @dup= grep { $seen{$_} > 0 } keys %seen;
    
    return @dup;
}

sub sqlArray{
    $cmd=shift;
    
    $query=$conn->prepare($cmd) or debug("Query preparation failed");
    $query->execute() or debug("Execute failed");
    $all=$query->fetchrow_array();
    
    return $all;
}

sub sqlHashRef {
    $cmd=shift;
    $query=$conn->prepare($cmd) or debug("Query preparation failed");
    $query->execute() or debug("Execute failed");
    $all=$query->fetchrow_hashref();
    return $all;
}

# main opartion begins here
open(FF, "$confFile") or debug("Error opening $confFile: ". $!);
while (<FF>) {
    if (/^db_driver\=+(.*?)$/) { $drvName = $1; }
    if (/^db_host\=+(.*?)$/) { $hostName = $1; }
    if (/^db_user\=+(.*?)$/) { $dbUser = $1; }
    if (/^db_pass\=+(.*?)$/) { $dbPass = $1; }
    if (/^db_name\=+(.*?)$/) { $dbName = $1; }
}
close(FF);

$conn = DBI->connect ("DBI:$drvName:dbname=$dbName;host=$hostName;", $dbUser, $dbPass, {
   InactiveDestroy =>1, 
   PrintError => 0}) or debug ("Cannot connect to database: $dbName: ". $DBI::errstr);

# select version();
$cmd="select version()";
$all = sqlArray($cmd);
$all =~ /\w+ (\d\.\d)/;
$version=$1;

# get the stats
if ($version =~ /^(8|9)\.\d$/) {
    $cmd="select datname, usename, client_addr, current_query from pg_stat_activity";
}

$all=sqlHashRef($cmd);

for (; $all=$query->fetchrow_hashref() ;) {
    # count the total number of connection to the server (right now)
    $cCount++; # increment the connection count
    if ($all->{datname}) {
	push (@dDbs, $all->{datname});
    }
    if ($all->{usename}) {
	push (@dUsr, $all->{usename});
    }
    if ($all->{client_addr}) {
	push (@dHst, $all->{client_addr});
    }

    # parse query type. probably useless
    # find idle, select, update, delete, other
    if ($all->{current_query}) {
	if ($all->{current_query} =~ /<IDLE>/) {
	    $idle++;
	}
	elsif (lc($all->{current_query}) =~ /^select/) {
	    $select++;
	}
	elsif (lc($all->{current_query}) =~ /^update/) {
	    $update++;
	}
	elsif (lc($all->{current_query}) =~ /^delete/) {
	    $delete++;
	}
	else {
	    $other++;
	}
    }
}

# To get total number of commits, use query like SELECT SUM(xact_commit) FROM pg_stat_database \
# - this query would return total number of successful commits on all databases since server start. \
# Running this query every N minutes/seconds would allow you to draw nice graph \
# (subtracting previous value from the current one would give you number of commits finished in N minutes).

# postgresql version 8.x have fewer stats
if ($version =~ /^9\.\d$/) {
    $cmd="SELECT SUM(xact_commit) as xact_commit, SUM(xact_rollback) as xact_rollback, SUM(blks_read) as blks_read, 
  SUM(blks_hit) as blks_hit, SUM(tup_returned) as tup_returned, SUM(tup_fetched) as tup_fetched, 
  SUM(tup_inserted) as tup_inserted, SUM(tup_updated) as tup_updated, SUM(tup_deleted) as tup_deleted 
  FROM pg_stat_database";
}
$all=sqlHashRef($cmd);

# clean up
$query->finish();
$conn->disconnect;

print "<<<app-postgresql>>>\n";
print "version:$version\n";
print "cCount:$cCount\n";
print "tDbs:" . findDup(@dDbs) . "\n";
print "tUsr:" . findDup(@dUsr) . "\n";
print "tHst:" . findDup(@dHst) . "\n";
print "idle:$idle\n";
print "select:$select\n";
print "update:$update\n";
print "delete:$delete\n";
print "other:$other\n";
print "xact_commit:" . $all->{xact_commit} . "\n";
print "xact_rollback:" . $all->{xact_rollback} . "\n";
print "blks_read:" . $all->{blks_read} . "\n";
print "blks_hit:" . $all->{blks_hit} . "\n";
print "tup_returned:" . $all->{tup_returned} . "\n";
print "tup_fetched:" . $all->{tup_fetched} . "\n";
print "tup_inserted:" . $all->{tup_inserted} . "\n";
print "tup_updated:" . $all->{tup_updated} . "\n";
print "tup_deleted:" . $all->{tup_deleted} . "\n";