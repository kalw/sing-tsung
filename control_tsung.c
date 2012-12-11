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

/**
 * This is written in an somewhat paranoid fashion because it is setuid-root.
 * It must be setuid-root so that we can start tsung-recorder, which requires
 * root for some reason.
 */

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

#include "control_tsung.h"

/**
 * Enter privileged mode.  Set the euid to the superuser one; block signals.
 * @param[in] euid The user to turn into.
 * @param[out] oldeuid A place to store the old euid.
 * @param[out] oldsignalset What signals used to be blocked.
 * @return 0 or exit if there were problems.
 */
int priv_mode(
    const uid_t euid, 
    sigset_t *signalset,
    sigset_t *oldsignalset) 
{
  if (sigprocmask(SIG_BLOCK, signalset, oldsignalset)) {
    exit(-1);
  } // if bloking didn't work

  // Actuall enter privileged mode.
  seteuid(euid);

  return 0;
} // int priv_mode


/**
 * Drop to unprivileged mode:  Set euid to real uid, unblock signals.
 * The orig_sigset is the original set of blocked signals to return to.  If you
 * call priv_mode, it will start blocking signals, and it will return the old
 * set of blocked signals.  You can put them here to return to the old
 * behavior.  If you leave this as NULL, the set of blocked signals will not be
 * changed.  This is useful if calling unprib_mode for the first time, without
 * ever having set it to block anything.
 * @param[out] euid Where to store the privileged uid.
 * @param[in] orig_sigset The set of blocked signals to return to.
 * @return 0, or exit(-1) on failure.
 */
int unpriv_mode(
    uid_t *euid, 
    sigset_t *orig_sigset) 
{
  *euid = geteuid();
  seteuid(getuid());

  if (orig_sigset) {
    if (sigprocmask(SIG_SETMASK, orig_sigset, NULL)) {
      exit(-1);
    } // if bloking didn't work
  } // if orig_sigset

  return 0;
} // int unpriv mode


/**
 * Init which signals to block when we are superuser.
 * @param[out] signals_to_block The sigset that will store the data.
 */
int init_blocked_signals(sigset_t *signals_to_block){
  int signals[] = {
      SIGHUP, SIGINT, SIGQUIT, SIGILL, SIGTRAP, SIGABRT, SIGIOT, SIGFPE,
      SIGBUS, SIGSYS, SIGPIPE, SIGALRM, SIGTERM, SIGUSR1, SIGUSR2, SIGPOLL,
      SIGTSTP, SIGTTIN, SIGTTOU, SIGVTALRM, SIGPROF, SIGXCPU, SIGXFSZ
  };
  int num_signals = 23;
  int i;

  if (sigemptyset(signals_to_block)) { exit(-1); }

  for (i=0; i<num_signals; i++) {
    if (sigaddset(signals_to_block, signals[i])) { exit(-1); }
  } // for all the signals

  return 0;
} // int set_blocked_signals



/**
 * Reads the config.php file into a newly-allocated string.
 * @param[in] file_contents Place to put the pointer to the new string.
 * @return The length of the string.
 */
int read_config_file(char **contents, char *prefix) {
  int fd;
  off_t size;
  size_t read_howmany;
  struct stat stats;
  FILE *config_file;
  char *file_contents;

  char *path_prefix;
  char *path_to_config_php;

  if (!prefix) {
    path_prefix = DEFAULT_INSTALL_PATH;
  } // if they didn't specify a prefix
  else {
    path_prefix = prefix;
  } // else they said where it was installed

  path_to_config_php = (char *)malloc(
      sizeof(char) * (strlen(path_prefix) + strlen("/config.php") + 1));
  path_to_config_php[0] = '\0';
  strcat(path_to_config_php, path_prefix);
  strcat(path_to_config_php, "/config.php");
  
  fd = open(path_to_config_php, O_RDONLY);

  if (fd == -1) {
    perror("Error");
    fprintf(stderr, "Could not open config file '%s'\n", path_to_config_php);
    free(path_to_config_php);
    exit(-1);
  } // if file didn't open

  free(path_to_config_php);
  
  if (fstat(fd, &stats) == -1) {
    perror("Error");
    fprintf(stderr, "Couldn't stat file '%s'", path_to_config_php);
    exit(-1);
  } // if fstat failed

  if (!(file_contents = (char *)malloc(stats.st_size))) {
    perror("Failed to alloc space for the config file");
    exit(-1);
  } // if malloc failed

  if (!(config_file = fdopen(fd, "r"))) {
    perror("Couldn't stream data from config file");
    exit(-1);
  } // if I couldn't get a stream from the file

  read_howmany = fread(file_contents, 1, stats.st_size, config_file);
  if (read_howmany != stats.st_size) {
    fprintf(
        stderr, 
        "Expected %d bytes from config file, got %d\n", 
        stats.st_size,
        read_howmany);
  } // if I didn't get what I expected

  if (fclose(config_file)) {
    perror("couldn't close the config file?");
    exit(-1);
  } // if the file wouldn't close

  *contents = file_contents;
  return read_howmany;
} // char *read_config_file


/**
 * Reads the config.php file and dumb-parses it for $TSUNG_CONFIG['log_dir'].
 * The value of $TSUNG_CONFIG['log_dir'] will be stored in a newly-allocated
 * string, and a pointer to that string will be returned.  It is the caller's
 * responsibility to free that string.
 * @param[out] ret_logdir Where to put the string that is the log directory.
 * @return Size of the logdir string.
 */
int get_logdir_from_config(char **ret_logdir, char *path_prefix) {
  int file_size;
  char *txt;
  regex_t preg;
  int err;
  char errmsg[1000];
  regmatch_t matches[3];
  char *logdir;
  int sz;
  char regex[] = 
      "^(//){0}.*\\$TSUNG_CONFIG\\['log_dir'\\][ \t\n]*=[ \t\n]*\"([^\"]*)\""; 

  file_size = read_config_file(&txt, path_prefix);

  err = regcomp(&preg, &(regex[0]), REG_EXTENDED);

  if (err) { 
    regerror(err, &preg, &(errmsg[0]), 1000);
    fprintf(stderr, "Regex error: %s\n", errmsg);
    regfree(&preg);
    free(txt);
    exit(-1);
  } // if there was an error

  if (regexec(&preg, txt, 3, matches, 0)) {
    fprintf(stderr, "Couldn't find $TSUNG_CONFIG['log_dir']\n");
    fprintf(stderr, "File:%s\n", txt);
    fprintf(stderr, "Regex: (%s)\n", regex);
    regfree(&preg);
    free(txt);
    exit(-1);
  } // if we couldn't find it

  sz = matches[2].rm_eo - matches[2].rm_so;
  logdir = (char *)malloc(sizeof(char) * (sz + 1));
  if (!logdir) {
    perror("Could not allocate memory for logdir string.");
    regfree(&preg);
    free(txt);
    exit(-1);
  } // if we couldn't alloc memory

  memcpy((void *)logdir, (void *)(txt + matches[2].rm_so), sz);
  logdir[sz] = '\0';

  regfree(&preg);
  free(txt);

  *ret_logdir = logdir;
  return sz;
} // char *get_logdir_from_config


int control_tsung(char *pass_str, char *prefix) {
  sigset_t signals_to_block;
  sigset_t normalsignalset;
  char *environ[] = {NULL, NULL};
  uid_t euid; 
  char *log_directory;
  int logdir_size;

  unpriv_mode(&euid, NULL);
  init_blocked_signals(&signals_to_block);

  logdir_size = get_logdir_from_config(&log_directory, prefix);

  environ[0] = (char *)malloc(sizeof(char) * (logdir_size + 5));
  environ[0][0] = '\0';
  strncat(environ[0], "HOME=", 5);
  strncat(environ[0], log_directory, logdir_size);

  priv_mode(euid, &signals_to_block, &normalsignalset);
  execle("/opt/tsung/bin/tsung", "tsung", pass_str, NULL, environ);
  unpriv_mode(&euid, &normalsignalset);

  free(log_directory);
  return 0;
} // int control_tsung


/**
 * Parse the command-line args. 
 * If they specified a --prefix, then it will be stored in path_prefix.  If
 * they said --help, or something unintelligible, 0 will be returned, and you
 * should probably print a help screen or something.  In all other cases, 1
 * will be returned.
 * The path_prefix will be allocated here, and it is the caller's
 * responsibility to free it.
 * @param argc argc.
 * @param argv argv.
 * @param[out] path_prefix Any --prefix they said, or NULL.
 * @return 0 if you should print a help screen and quit.  1 else.
 */
int parse_argv(int argc, char **argv, char **path_prefix, int *daemonize) {
  int i;
  int okay = 1;

  path_prefix[0] = NULL;
  daemonize[0] = 1;

  for (i=1; i<argc; i++) {
    if (!strcmp(argv[i], "--prefix")) {
      if (argc <= i+1) { okay = 0; break; }
      path_prefix[0] = strdup(argv[i+1]);
      i += 1;
    } // if they said prefix
    else if (!strcmp(argv[i], "--help")) { okay = 0; break; } 
    else if (!strcmp(argv[i], "-f")) { daemonize[0] = 0; }
    else { okay = 0; break; }
  } // for all the args

  if (!path_prefix[0]) {
    path_prefix[0] = strdup(DEFAULT_INSTALL_PATH);
  } // if we didn't find a useful path prefix

  return okay;
} // int parse_argv


void print_help_for_start_or_stop(int argc, char **argv) {
  printf("%s: %s [-f] [--prefix <dir>]\n", argv[0], argv[0]);
  printf("  -f:  Do not become a daemon -- run in the foreground.\n");
  printf("  --prefix <dir>:  the tsung installation is at <dir>.\n");
  printf("       We will look for config.php here.\n");
  printf("       (default: %s)\n", DEFAULT_INSTALL_PATH);
  printf("\n");
  printf("  --help:  print this screen.\n");

  exit(0);
} // void print_help_for_start_or_stop
