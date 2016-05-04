#! /usr/bin/env python
"""
 poller-wrapper A small tool which wraps around the Observium poller
                and tries to guide the polling process with a more modern
                approach with a Queue and workers

 Original:      Job Snijders <job.snijders@atrato.com>
 Rewritten:     Most code parts rewritten since 2014 by Observium developers

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of CPU count x2 threads.

                In /etc/cron.d/observium replace this (or the equivalent) poller entry:
                */5 *     * * *   root    /opt/observium/poller.php -h all >> /dev/null 2>&1
                with something like this:
                */5 * * * * root python /opt/observium/poller-wrapper.py 16 >> /dev/null 2>&1

 Read more:     http://postman.memetic.org/pipermail/observium/2012-November/001303.html

 Ubuntu Linux:  apt-get install python-mysqldb
 RHEL/CentOS:   yum install MySQL-python (Requires the EPEL repo!)
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean

 Tested on:     Python 2.7.3 / PHP 5.3.10-1ubuntu3.4 / Ubuntu 12.04 LTS
                Python 3.2.3 / PHP 5.3.27-1ubuntu3.6 / Ubuntu 12.04 LTS

 License:       To the extent possible under law, Job Snijders has waived all 
                copyright and related or neighboring rights to this script. 
                This script has been put into the Public Domain. This work is 
                published from: The Netherlands.
"""
try:
    import threading, sys, subprocess, time, os, json
except:
    print("ERROR: missing one or more of the following python modules:")
    print("threading, sys, subprocess, time, os, json")
    sys.exit(2)

# major/minor python version: (2,7), (3,2), etc
python_version = sys.version_info[:2]

if python_version < (3,0):
    try:
        import Queue
    except:
        print("ERROR: missing the Queue python module.")
        sys.exit(2)
    try:
        import MySQLdb
    except:
        print("ERROR: missing the MySQLdb python module:")
        print("On Ubuntu: apt-get install python-mysqldb")
        print("On RHEL/CentOS: yum install MySQL-python")
        print("On FreeBSD: cd /usr/ports/*/py-MySQLdb && make install clean")
        sys.exit(2)
else:
    try:
        import queue as Queue
    except:
        print("ERROR: missing the queue python module.")
        sys.exit(2)
    # MySQLdb not avialable for python3
    # http://stackoverflow.com/questions/4960048/python-3-and-mysql
    try:
        import pymysql as MySQLdb
        MySQLdb.install_as_MySQLdb()
    except:
        print("ERROR: missing the pymysql python module:")
        print(" On Ubuntu: sudo apt-get install python3-setuptools")
        print("            sudo easy_install3 pip")
        print("            sudo pip3 install PyMySQL")
        # FIXME. I not know how install on RHEL and FreeBSD
        print(" On other OSes install pip for python3 and than run from root user:")
        print("            pip3 install PyMySQL")
        #print(" On RHEL/CentOS: yum install MySQL-python")
        #print(" On FreeBSD: cd /usr/ports/*/py-MySQLdb && make install clean")
        sys.exit(2)


"""
    Parse Arguments
    Attempt to use argparse module.  Probably want to use this moving forward
    especially as more features want to be added to this wrapper.
    and
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default of 8
"""
try:
    import argparse
    parser = argparse.ArgumentParser(description='Poller Wrapper for Observium')
    parser.add_argument('workers', nargs='?', type=int, default=0, help='Number of workers to spawn. Defauilt: CPUs x 2')
    parser.add_argument('-s', '--stats', action='store_true', help='Store total polling times to RRD.', default=False)
    parser.add_argument('-d', '--debug', action='store_true', help='Enable debug output. WARNING, do not use this option unless you know what it does. This generates a lot of very huge files in TEMP dir.', default=False)
    parser.add_argument('--host', help='Poll hostname wildcard.')
    args = parser.parse_args()
    amount_of_workers = int(args.workers)
    stats = args.stats
    debug = args.debug
except ImportError:
    print("WARNING: missing the argparse python module:")
    print("On Ubuntu: apt-get install libpython%s.%s-stdlib" % python_version)
    print("On RHEL/CentOS: yum install python-argparse")
    print("On Debian: apt-get install python-argparse")
    print("Continuing with basic argument support.")
    stats = False
    debug = False
    try:
        amount_of_workers = int(sys.argv[1])
    except:
        amount_of_workers = 0

"""
    Fetch configuration details from the config_to_json.php script
"""

ob_install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = ob_install_dir + '/config.php'

def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % ob_install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except:
        print("ERROR: Could not execute: %s" % config_cmd)
        sys.exit(2)
    return proc.communicate()[0].decode('utf-8') # decode required in python3

try:
    with open(config_file) as f: pass
except IOError as e:
    print("ERROR: Oh dear... %s does not seem readable" % config_file)
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except:
    print("ERROR: Could not load or parse observium configuration, are PATHs correct?")
    sys.exit(2)

db_username  = config['db_user']
db_password  = config['db_pass']
db_server    = config['db_host']
db_dbname    = config['db_name']
try:
    db_port  = int(config['db_port'])
except KeyError:
    db_port  = 3306
try:
    db_socket = config['db_socket']
except KeyError:
    db_socket = False

scriptname   = os.path.basename(sys.argv[0])
poller_path  = config['install_dir'] + '/poller.php'
alerter_path = config['install_dir'] + '/alerter.php'
#temp_path    = config['temp_dir']
temp_path    = '/tmp'
try:
    rrd_path = config['rrd_dir'] + '/poller-wrapper.rrd'
except KeyError:
    rrd_path = config['install_dir'] + '/rrd/poller-wrapper.rrd'
try:
    log_path = config['log_dir'] + '/observium.log'
except KeyError:
    log_path = config['install_dir'] + '/logs/observium.log'

if amount_of_workers < 1:
    try:
        # use config option if set and threads not passed as argument
        amount_of_workers = int(config['poller-wrapper']['threads'])
    except KeyError:
        pass
if amount_of_workers < 1:
    try:
        # use threads count based on cpus count
        import multiprocessing
        amount_of_workers = multiprocessing.cpu_count() * 2
    except (ImportError, NotImplementedError):
        amount_of_workers = 8
        print("WARNING: used default threads number %s. For change use number as argument" % (amount_of_workers))

if os.path.isfile(alerter_path):
    alerting = config['poller-wrapper']['alerter']
else:
    alerting = False

if stats == False:
    try:
        stats = bool(config['poller-wrapper']['stats'])
    except KeyError:
        pass

s_time = time.time()
real_duration = 0
per_device_duration = {}

devices_list = []

try:
    if bool(db_socket) == False:
        db = MySQLdb.connect (host=db_server, user=db_username, passwd=db_password, db=db_dbname, port=db_port)
    else:
        db = MySQLdb.connect (host=db_server, user=db_username, passwd=db_password, db=db_dbname, port=db_port, unix_socket=db_socket)
    cursor = db.cursor()
except:
    print("ERROR: Could not connect to MySQL database!")
    logfile("ERROR: Could not connect to MySQL database!")
    sys.exit(2)


""" 
    This query specifically orders the results depending on the last_polled_timetaken variable
    Because this way, we put the devices likely to be slow, in the top of the queue
    thus greatening our chances of completing _all_ the work in exactly the time it takes to 
    poll the slowest device! cool stuff he
    Additionally, if a hostname wildcard is passed, add it to the where clause.  This is
    important in cases where you have pollers distributed geographically and want to limit
    pollers to polling hosts matching their geographic naming scheme.
"""

query = """SELECT   device_id
           FROM     devices
           WHERE    disabled != '1'"""
order =  " ORDER BY last_polled_timetaken DESC"

try:
    host_wildcard = args.host.replace('*', '%')
    wc_query = query + " AND hostname LIKE %s " + order
    cursor.execute(wc_query, host_wildcard)
except:
    query = query + order
    cursor.execute(query)

devices = cursor.fetchall()
for row in devices:
    devices_list.append(int(row[0]))
db.close()

"""
    A seperate queue and a single worker for printing information to the screen prevents
    the good old joke:

        Some people, when confronted with a problem, think, 
        "I know, I'll use threads," and then two they hav erpoblesms.
"""

def printworker():
    while True:
        worker_id, device_id, elapsed_time = print_queue.get()
        global real_duration
        global per_device_duration
        real_duration += elapsed_time
        per_device_duration[device_id] = elapsed_time
        if elapsed_time < 300:
            print("INFO: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time))
        else:
            print("WARNING: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time))
        print_queue.task_done()

"""
    This class will fork off single instances of the poller.php process, record
    how long it takes, and push the resulting reports to the printer queue
"""

def poll_worker():
    while True:
        device_id = poll_queue.get()
        try:
            start_time = time.time()
            if debug == True:
                debug_file = temp_path + '/observium_poller_' + str(device_id) + '.debug'
                command = "/usr/bin/env php %s -d -h %s >> %s 2>&1" % (poller_path, device_id, debug_file)
            else:
                command = "/usr/bin/env php %s -q -h %s >> /dev/null 2>&1" % (poller_path, device_id)
            subprocess.check_call(command, shell=True)
            if alerting == True:
                print("INFO starting alerter.php for %s" % device_id)
                if debug == True:
                    debug_file = temp_path + '/observium_alerter_' + str(device_id) + '.debug'
                    command = "/usr/bin/env php %s -d -h %s >> %s 2>&1" % (alerter_path, device_id, debug_file)
                else:
                    command = "/usr/bin/env php %s -q -h %s >> /dev/null 2>&1" % (alerter_path, device_id)
                print("INFO finished alerter.php for %s" % device_id)
                subprocess.check_call(command, shell=True)
            elapsed_time = int(time.time() - start_time)
            print_queue.put([threading.current_thread().name, device_id, elapsed_time])
        except (KeyboardInterrupt, SystemExit):
            raise
        except:
            pass
        poll_queue.task_done()

"""
    Write msg to log file
"""

def logfile(msg):
    msg = "[%s] %s(%s): %s/%s: %s\n" % (time.strftime("%Y/%m/%d %H:%M:%S %z"), scriptname, os.getpid(), config['install_dir'], scriptname, msg)
    f = open(log_path,'a')
    f.write(msg)
    f.close()

"""
    Search if same poller proccesses running
    Protect from race condition
"""

try:
    ps_list = subprocess.check_output('ps ax | grep %s | grep -v grep' % (scriptname), shell=True)
    # divide by 2 because cron starts 2 processes (/bin/sh and main process)
    ps_count = len(ps_list.splitlines()) / 2
    # Why 3? I not know, but I think 3 already running poller-wrapper it's big trouble!
    if ps_count > 3:
        print("URGENT: %s not started because already running %s processes" % (scriptname, ps_count))
        logfile("URGENT: polling not started because already running %s processes" % (ps_count))
        sys.exit(2)
except:
    # Skip searching, something wrong
    pass

poll_queue = Queue.Queue()
print_queue = Queue.Queue()

print("INFO: starting the poller at %s with %s threads, slowest devices first" % (time.strftime("%Y/%m/%d %H:%M:%S"), amount_of_workers))
if debug == True:
    print("WARNING: DEBUG enabled, each device poller store output to %s/observium_poller_id.debug (where id is device_id)" % (temp_path))

for device_id in devices_list:
    poll_queue.put(device_id)
 
for i in range(amount_of_workers):
    t = threading.Thread(target=poll_worker)
    t.setDaemon(True)
    t.start()

p = threading.Thread(target=printworker)
p.setDaemon(True)
p.start()

try:
    poll_queue.join()
    print_queue.join()
except (KeyboardInterrupt, SystemExit):
    raise

total_time = int(time.time() - s_time)

msg = "polled %s devices in %s seconds with %s workers" % (len(devices_list), total_time, amount_of_workers)
print("INFO: %s %s\n" % (scriptname, msg))
logfile(msg)

if stats == True:
    # Write poller statistics to RRD
    if os.path.isfile(rrd_path) == False:
        # Create RRD
        rrd_dst = ':GAUGE:' + str(config['rrd']['step'] * 2) + ':0:U'
        cmd_create = config['rrdtool'] + ' create ' + rrd_path + ' DS:devices' + rrd_dst + ' DS:totaltime' + rrd_dst + ' DS:threads' + rrd_dst
        cmd_create += ' --step ' + str(config['rrd']['step']) + ' ' + ' '.join(config['rrd']['rra'].split())
        os.system(cmd_create)
        logfile(cmd_create)
        if debug == True:
            print("DEBUG: " + cmd_create)
    cmd_update = config['rrdtool'] + ' update ' + rrd_path + ' N:%s:%s:%s' % (len(devices_list), total_time, amount_of_workers)
    os.system(cmd_update)
    if debug == True:
        print("DEBUG: " + cmd_update)
    
show_stopper = False

if total_time > 300:
    recommend = int(total_time / 300.0 * amount_of_workers + 1)
    print("WARNING: the process took more than 5 minutes to finish, you need faster hardware or more threads")
    print("INFO: in sequential style polling the elapsed time would have been: %s seconds" % real_duration)
    for device in per_device_duration:
        if per_device_duration[device] > 300:
            print("WARNING: device %s is taking too long: %s seconds" % (device, per_device_duration[device]))
            show_stopper = True
    if show_stopper == True:
        print("ERROR: Some devices are taking more than 300 seconds, the script cannot recommend you what to do.")
    if show_stopper == False:
        print("WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)" % recommend)
    sys.exit(1)
