#!/bin/bash
lessc html/css/bootstrap/less/bootstrap.less > html/css/bootstrap.css.new
sed -i 's|../../font-awesome/less/||g' html/css/bootstrap.css.new
mv html/css/bootstrap.css.new html/css/bootstrap.css

#lessc -x html/css/bootstrap/less/bootstrap-email.less > html/css/bootstrap-email.css

# FIXME. Adama, pls commit file variables-www.less or do not generate bootstrap-www.css for all ;)
lessc -x html/css/bootstrap/less/bootstrap-www.less > html/css/bootstrap-www.css

