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

#ifndef _CONTROL_TSUNG_H_
#define _CONTROL_TSUNG_H_

#include <errno.h>
#include <fcntl.h>
#include <regex.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

#include <sys/types.h>
#include <sys/stat.h>

#define DEFAULT_INSTALL_PATH "/opt/sing-tsung"

int priv_mode(const uid_t euid, sigset_t *signalset, sigset_t *oldsignalset);
int unpriv_mode(uid_t *euid, sigset_t *orig_sigset);
int init_blocked_signals(sigset_t *signals_to_block);
int read_config_file(char **contents, char *prefix);
int get_logdir_from_config(char **ret_logdir, char *prefix);
int parse_argv(int argc, char **argv, char **path_prefix, int *daemonize);
#endif
