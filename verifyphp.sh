#!/bin/sh
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

WHICHPHP=`which php | grep -v "not found"`
if [ "x$WHICHPHP" = x ] ; then 
  echo "PHP command-line not found.  Please put php on the path and run make again." 
  exit 1 
fi 
