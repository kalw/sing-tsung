If you have just checked out sing-tsung from the svn repository, and you wish
to check out its dependencies and continue developing, type:
  make checkout

If you have just checked it out, and you wish to make a tarball for release,
type:
  make release

If you just untarred the tarball and you want to install sing-tsung on your
system, follow these instructions:

1. The Makefile assumes that the name of your apache group is "apache".  If
   this is not the case, edit the makefile and change the line that says
   "APACHE="

2. Now, if you want to actually run sing-tsung, make sure you have tsung
   installed at /opt/tsung, and Erlang installed on your system.  Also, you
   must install the PEAR DB class.

3. You must make sure that the "root" user can ssh to localhost without using
   a password.
   a) log-in as the root user.
   b) ssh-keygen -t dsa  (default file, password=blank)
   c) cd ~/.ssh
   d) cat id_dsa.pub >> authorized_keys2

4. If you wanna generate status reports, please place the perl script
   "tsung_stats_new.pl" in a folder such as "/opt/tsung/lib/tsung/bin/" and
   specify the path in the "config.php".

5. Generating status reports also requires that the Perl Template module be
   installed.  To install it, try this command:
    cpan Template

6. Your erl binary must be accessible at /usr/bin/erl.

7. Also, your DBMS must be mysql, right now, because sing-tsung uses mysql
   functions to get the last-inserted id.

8. Create your database: Log into mysql as root and issue these commands:
    CREATE DATABASE <db-name>;
    GRANT ALL ON <db-name>.* TO '<username>'@'localhost' 
      IDENTIFIED BY '<password>';

9. Create the table structure:
    % cat sql/make_tables.sql | mysql -u <username> -p --database=<db-name>

10. Edit your connections_config.php and add the values you chose for <db-name>,
   <username>, and <password>

11. Check your Makefile.  At the top is a variable called APACHE_GROUP.  Make
   sure to set this to the name of the group that apache runs as.  It is
   probably either apache or daemon.

12. If you don't have a gentoo system, run make install (as root), to get all
   the files set up.  Then you'll have to figure out how to get
   /usr/local/bin/tsung_launcher started (as root) on your own.
   To start it on your own, you should do:
    /usr/local/bin/tsung_launcher --prefix <dir>
   where <dir> is the directory that sing-tsung is installed in.  config.php
   should be found in <dir>.
   IMPORTANT:  Remember to somehow arrange for this command to be run at system
   startup, or tsung will not work when you reboot the system, until you
   execute that command again.
13a. If you are using gentoo, you can run make gentoo (as root), which is just
    like make install, except that it adds a gentoo startup script for
    tsung_launcher.  You'll still have to run /usr/local/bin/tsung_launcher (as
    root) for now, but it'll be started automatically on boot.

14. Copy config.php-dist to config.php and connections_config.php-dist to
    connections_config.php.

15. Edit connections_config.php to tell it how you connect to your database.

16. Edit config.php.
  * $TSUNG_CONFIG['install_dir'] should be set to the installation directory of
    sing-tsung.  config.php should be in that directory.

  * $TSUNG_CONFIG['log_dir'] is the directory that tsung will write its logs
    to, and also its session configuration file that it needs to run.
    This log directory should be writeable and readable to the apache user.
    For example, you might chmod g+rw daemon <log-dir> ; chgrp daemon <log-dir>
    if your apache runs with "daemon" as its group.

  * $TSUNG_CONFIG['report_dir'] should be a directory with the same permissions
    as log_dir, and should also be visible to people on the web.


Enjoy!
