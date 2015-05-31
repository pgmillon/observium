#!/usr/bin/python
"""rrdtool extended commands.

Usage:
  %(cmd)s summary <filename>
  %(cmd)s addrra <filename> <outfile> [RRA:CF:cf args] ...

Adds "summary" and "addrra" rrdtool commands. The "summary" command
will output a short summary of the RRD's DSs and RRAs. The "addrra"
command will create a new rrd with added RRAs. These RRAs will be
populated with as much data as can be derived by rrdxport from the
existing RRAs.
"""

__author__ = "Donovan Baarda <abo@minkirri.apana.org.au>"
__license__ = "LGPL"
__version__ = "$Revision: b358159c47eb $"
__date__ = "$Date: 2013/09/24 11:14:56 $"
__url__ = "http://minkirri.apana.org.au/~abo/projects/rrdcollect/"
__requires__ = "rrdtool"

import os
import re
import sys
import subprocess
import tempfile

# Note we don't bother using the python rrdtool module because it doesn't
# support dump, restore, or xport.
def rrdtool(cmd):
  """Run rrdtool and return output."""
  # We set LC_NUMERIC=C to ensure eval() parses numbers right.
  return subprocess.check_output('LC_ALL=; LC_NUMERIC=C; rrdtool ' + cmd, shell=True)


class DS(object):
  """A simple DS object."""

  def __init__(self, ds=None):
    if ds:
      _, self.name, self.type, heartbeat, ds_min, ds_max = ':'.split(ds)
      self.minimal_heartbeat = int(heartbeat)
      self.min = ds_min is 'U' and None or int(ds_min)
      self.max = ds_max is 'U' and None or int(ds_max)

  def __str__(self):
    ds_min = self.min is None and 'U' or self.min
    ds_max = self.max is None and 'U' or self.max
    return 'DS:%s:%s:%s:%s:%s' % (self.name, self.type, self.minimal_heartbeat, ds_min, ds_max)

  def __repr__(self):
    return '%s.%s("%s")' % (self.__module__, self.__class__.__name__, self)


class RRA(object):
  """A simple RRA object"""

  def __init__(self, rra=None):
    if rra:
      _, self.cf, xff, steps, rows = rra.split(':')
      self.xff = float(xff)
      self.pdp_per_row = int(steps)
      self.rows = int(rows)

  def __str__(self):
    return 'RRA:%s:%s:%s:%s' % (self.cf, self.xff, self.pdp_per_row, self.rows)

  def __repr__(self):
    return '%s.%s("%s")' % (self.__module__, self.__class__.__name__, self)


class RRD(object):
  """A simple RRD object"""
  INFO_RE = re.compile(r'^(.*?)\[(.*?)\]\.(.*)$')

  def __init__(self, rrdpath):
    """Initiallise an RRD by parsing its info."""
    info = rrdtool('info %s' % rrdpath)
    ds={}
    rra={}
    for k,v in (l.split(' = ') for l in info.splitlines()):
      # Evaluate values and use None for NaN.
      try:
        v = eval(v)
      except NameError:
        v = None
      try:
        kind, key, field = self.INFO_RE.match(k).groups()
        if kind == 'ds':
          setattr(ds.setdefault(key, DS()), field, v)
        elif kind == 'rra':
          setattr(rra.setdefault(int(key), RRA()), field, v)
      except AttributeError:
        setattr(self, k, v)
    # Set ds.name attribute from dict keys.
    for k,v in ds.items(): v.name = k
    # Turn ds into a dict keyed by index.
    ds = dict((d.index,d) for d in ds.values())
    # Set self.ds and self.rra as lists in index order.
    self.ds = [ds[i] for i in sorted(ds)]
    self.rra = [rra[i] for i in sorted(rra)]

  def getSummary(self):
    """Returns a string summary of the RRD."""
    return '%s step=%s last_update=%s\n%s\n%s\n' % (
        rrd.filename, rrd.step, rrd.last_update,
        '\n'.join(str(d) for d in rrd.ds),
        '\n'.join(str(d) for d in rrd.rra))

  def _getRRADataXml(self, rra):
    """Get the xml data for an added RRA from existing RRAs."""
    step = rra.pdp_per_row * self.step
    end = self.last_update / step * step
    start = end - rra.rows * step
    defs = ' '.join('DEF:%s=%s:%s:%s' % (ds.name, self.filename, ds.name, rra.cf) for ds in self.ds)
    xports = ' '.join('XPORT:%s' % ds.name for ds in self.ds)
    cmd = 'xport -s %s -e %s -m %s --step %s %s %s' % (
        start, end, rra.rows, step, defs, xports)
    xml = rrdtool(cmd)
    # Get stuff between <data> tags and drop the last past end time row.
    data_xml = re.search(r'<data>\n(.*)^.+?^\s*</data>', xml, flags=re.M|re.S).group(1)
    # turn it into rrdtool dump database format.
    data_xml = re.sub(r'^\s*<row><t>(.*)</t>', r'\t\t\t<!-- \1 --><row>', data_xml, flags=re.M)
    # Put in <database> tags.
    data_xml = '\t\t<database>\n%s\t\t</database>' % data_xml
    return data_xml

  def _getRRAXml(self, rra):
    """Get the xml for an added RRA."""
    # Get the xml for a new rrd with the existing DSs and the new RRA.
    filename = tempfile.mkstemp('rrd')[1]
    dss = ' '.join(str(ds) for ds in self.ds)
    rrdtool('create %s -b 0 -s %s %s %s' % (filename, self.step, dss, rra))
    xml = rrdtool('dump %s' % filename)
    os.unlink(filename)
    # Get the new RRA definition between the <rra> tags.
    rra_xml = re.search(r'^\s*<rra>.*</rra>\n', xml, flags=re.M|re.S).group(0)
    # If possible replace <database> with data generated from existing RRAs.
    if rra.cf in (r.cf for r in self.rra):
      database_xml = self._getRRADataXml(rra)
      rra_xml = re.sub(r'^\s*<database>.*</database>', database_xml, rra_xml, flags=re.M|re.S)
    return rra_xml

  def _getAddRRAXml(self, rras):
    """Get the xml for an rrd with added RRAs."""
    xml = rrdtool('dump %s' % rrd.filename)
    for rra in rras:
      # Generate and append the new RRAs.
      rra_xml = self._getRRAXml(rra)
      xml = re.sub(r'(^</rrd>)', r'%s\1' % rra_xml, xml, flags=re.M|re.S)
    return xml

  def addRRA(self, filename, rras):
    """Create a new rrd file with the added RRAs."""
    xml = self._getAddRRAXml(rras)
    xmlfile = tempfile.mkstemp('rrd.xml')[1]
    open(xmlfile, 'w+b').write(xml)
    rrdtool('restore %s %s' % (xmlfile, filename))
    os.unlink(xmlfile)


if __name__ == "__main__":
  if len(sys.argv)<3 or sys.argv[1] in ("-?", "-h", "help"):
    print __doc__ % dict(cmd=os.path.basename(sys.argv[0]))
    sys.exit(1)
  cmd = sys.argv[1]
  rrd=RRD(sys.argv[2])
  if cmd == "addrra":
    filename = sys.argv[3]
    rras = [RRA(r) for r in sys.argv[4:]]
    rrd.addRRA(filename, rras)
  elif cmd == "summary":
    print rrd.getSummary()
  else:
    sys.stderr.write('Error: Unknown command %r.\n' % cmd)
    sys.exit(1)
