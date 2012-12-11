# Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
#   206 S. Fifth Avenue Suite 150
#   Ann Arbor, MI 48104
#   http://www.linuxbox.com
# Written by Ryan Hughes (ryan@linuxbox.com)
#  
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version
# 2 of the License, or (at your option) any later version.

APACHE_GROUP=www-data
QPWD=`pwd | sed -e "s/\\\\//\\\\\\\\\\\//g"`
PHPBIN=`which php | sed -e "s/\\\\//\\\\\\\\\\\//g"`

install: tsung_status start_tsung_recorder stop_tsung_recorder tsung_launcher tsung_stats_new.pl
	chmod -R g+w smarty/templates_c
	chgrp -R $(APACHE_GROUP) smarty/templates_c
	chmod -R g+w smarty/cache
	chgrp -R $(APACHE_GROUP) smarty/cache
	rm -rf /opt/sing-tsung
	ln -s `pwd` /opt/sing-tsung
	cp tsung_launcher /usr/local/bin/

gentoo: install
	cp tsung_launcher_launcher /etc/init.d/tsung_launcher
	rm -f /etc/runlevels/default/tsung_launcher
	ln -s /etc/init.d/tsung_launcher /etc/runlevels/default

tsung_stats_new.pl: tsung_stats_new.pl.in
	sh verifyphp.sh
	sed -e "s/::INSTALL_DIR::/$(QPWD)/g" < tsung_stats_new.pl.in | sed -e "s/::PHP::/$(PHPBIN)/g" > tsung_stats_new.pl
	chmod a+x tsung_stats_new.pl
	cp tsung_stats_new.pl /opt/tsung/lib/tsung/bin/tsung_stats_new.pl

start_tsung_recorder: control_tsung.h start_tsung_recorder.c control_tsung.o
	gcc -o start_tsung_recorder start_tsung_recorder.c control_tsung.o
	chown root start_tsung_recorder
	chgrp $(APACHE_GROUP) start_tsung_recorder
	chmod u+s start_tsung_recorder
	chmod o-rx start_tsung_recorder

tsung_status: control_tsung.h tsung_status.c control_tsung.o
	gcc -o tsung_status tsung_status.c control_tsung.o
	chown root tsung_status
	chgrp $(APACHE_GROUP) tsung_status
	chmod u+s tsung_status
	chmod o-rx tsung_status

stop_tsung_recorder: control_tsung.h stop_tsung_recorder.c control_tsung.o
	gcc -o stop_tsung_recorder stop_tsung_recorder.c control_tsung.o
	chown root stop_tsung_recorder
	chgrp $(APACHE_GROUP) stop_tsung_recorder
	chmod u+s stop_tsung_recorder
	chmod o-rx stop_tsung_recorder

tsung_launcher: tsung_launcher.c control_tsung.o
	gcc -o tsung_launcher tsung_launcher.c control_tsung.o

control_tsung.o: control_tsung.c control_tsung.h
	gcc -c -o control_tsung.o -g control_tsung.c

