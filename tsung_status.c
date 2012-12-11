/*
 * Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 *   206 S. Fifth Avenue Suite 150
 *   Ann Arbor, MI 48104
 *   http://www.linuxbox.com
 * Written by Ryan Hughes (ryan@linuxbox.com)
 *  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version
 * 2 of the License, or (at your option) any later version.
 */

#include "control_tsung.h"

int main(const int argc, const char **argv) {
  char *prefix;
  int daemonize;

  if (!parse_argv(argc, argv, &prefix, &daemonize)) {
    print_help_for_start_or_stop();
  } // if we failed at parsing

  control_tsung("status", prefix);

  free(prefix);

  return 0;
} // int main
