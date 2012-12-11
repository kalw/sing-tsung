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

#include <errno.h>
#include <netdb.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

#include <sys/types.h>
#include <sys/socket.h>
#include <sys/stat.h>
#include <sys/un.h>

#include "control_tsung.h"

#define SOCKNAME "/tmp/tsung_start_stop"


int handle_client(int connfd, char *prefix);
int start_tsung_player(char *prefix);
int stop_tsung_player(char *prefix);
void daemonize(void);

int main(int argc, char **argv){
  int socketfd;
  int connfd;
  struct protoent *tcp_protocol;
  struct sockaddr_un addr;
  socklen_t addrsize;
  int keepgoing;
  char *prefix;
  int shouldidaemonize = 1;

  if (!parse_argv(argc, argv, &prefix, &shouldidaemonize)) {
    print_help_for_start_or_stop();
  } // if we failed at parsing

  // We need prefix till the end.  I'm not going to bother to free it.  I'm too
  // lazy.

  if (shouldidaemonize) { 
    daemonize();
  } // if we should daemonize

  if (unlink(SOCKNAME)) {
    if (errno != ENOENT) {
      perror("Couldn't unlink socket");
      exit(errno);
    } // if we failed for some reason other than that the file wasn't there
  } // if we couldn't unlink the socket

  socketfd = socket(PF_UNIX, SOCK_STREAM, 0);
  if (socketfd == -1) { perror("Socket creation failed"); exit(errno); }

  memset(&addr, 0, sizeof(struct sockaddr_un));
  addr.sun_family = AF_UNIX;
  strcpy(addr.sun_path, SOCKNAME);
  if (bind(socketfd, (struct sockaddr *)&addr, sizeof(struct sockaddr_un)) == -1) {
    perror("bind failed");
    exit(errno);
  } // if bind failed

  if (chmod(
        SOCKNAME,
          S_ISUID | S_IRUSR | S_IWUSR | S_IXUSR
        | S_IRGRP | S_IWGRP | S_IXGRP
        | S_IROTH | S_IWOTH | S_IXOTH) == -1) 
  {
    perror("Chmod failed");
    exit(errno);
  } // if chmod failed

  if (listen(socketfd, 255)) {
    perror("Listen failed");
    exit(errno);
  } // if listen failed

  // Event loop!
  keepgoing = 1;
  while (keepgoing) {
    addrsize = sizeof(struct sockaddr_un);
    connfd = accept(socketfd, (struct sockaddr *)&addr, &addrsize);
    if (connfd == -1) {
      perror("Accept failed");
      exit(errno);
    } // if accept failed

    // We now have a connection.
    keepgoing = handle_client(connfd, prefix);

    close(connfd);
  } // while keepgoing in event loop


  close(socketfd);

  if (unlink(SOCKNAME)) {
    if (errno != ENOENT) {
      perror("Couldn't unlink socket");
      exit(errno);
    } // if we failed for some reason other than that the file wasn't there
  } // if we couldn't unlink the socket
} // int main


/**
 * Handle a client.  Expects STRT, STOP, or STDN.
 * @param connfd The file-descriptor of the connection.
 * @return 0 to shut server down.  1 if no err.  2 if err.
 */
int handle_client(int connfd, char *prefix){
  char buf[5];
  int recved=0;
  int n=0;
  int keepgoing = 1;

  while(keepgoing) {
    n = recv(connfd, buf+recved, 4-recved, 0);
    if (n < 0) {
      perror("Recv failed");
      return 2;
    } // if recv failed

    recved += n;
    if (recved == 4) { keepgoing = 0; }
  } // while keepgoing

  buf[4] = '\0';
  if (strcmp(buf, "STRT") == 0) {
    if (start_tsung_player(prefix)) {
      if (send(connfd, "NO\n", 3, 0) < 0) {
        perror("Send failed");
        return 2;
      } // if we couldn't send
    } // if start_tsung_player failed

    if (send(connfd, "OK\n", 3, 0) < 0) {
      perror("Send failed");
      return 2;
    } // if we couldn't send
  } else if (strcmp(buf, "STOP") == 0) {
    if (stop_tsung_player(prefix)) {
      if (send(connfd, "NO\n", 3, 0) < 0) {
        perror("Send failed");
        return 2;
      } // if we couldn't send
    } // if start_tsung_player failed

    if (send(connfd, "OK\n", 3, 0) < 0) {
      perror("Send failed");
      return 2;
    } // if we couldn't send
  } else if (strcmp(buf, "STDN") == 0) {
    printf("Shutting down the server.\n");
    if (send(connfd, "BY\n", 3, 0) < 0) {
      perror("Send failed");
      return 2;
    } // if we couldn't send
    return 0;
  } else {
    printf("I would close the connection.\n");
    return 2;
  } // if whether it's start or stop

  return 1;
} // void handle_client


/**
 * Start tsung player.
 * @return 0 unless error.
 */
int start_tsung_player(char *prefix) {
  pid_t child;
  int status;
  int logdir_size;
  char *logdir;
  char *child_environ[] = {NULL, NULL, NULL};
  int i;
  extern char **environ;

  printf("Starting tsung player.\n");

  logdir_size = get_logdir_from_config(&logdir, prefix);

  child_environ[0] = (char *)malloc(sizeof(char) * (logdir_size + 6));
  child_environ[0][0] = '\0';
  strcat(child_environ[0], "HOME=");
  strcat(child_environ[0], logdir);

  // We should actually just COPY your path from your
  // child_environment into this, in case you're doing anything funky, like
  // putting ssh in /usr/local/bin or something.
  for(i=0; environ[i] != NULL; i++) {
    if (strstr(environ[i], "PATH=") == environ[i]) {
      child_environ[1] = strdup(environ[i]);
    } // if we found a path
  } // for looping in the environment

  if (!child_environ[1]) {
    child_environ[1] = strdup("PATH=/usr/bin:/bin:/usr/local/bin");
  } // if we didn't find a PATH

  child = fork();
  if (child == 0) {
    // I am the child
    if (execle(
          "/opt/tsung/bin/tsung", 
          "/opt/tsung/bin/tsung", 
          "start", 
          NULL,
          child_environ))
    {
      perror("Exec failed");
      exit(errno);
    } // if exec failed
    wait(&status);
  } else if (child == -1) {
    // fork failed
    perror("Fork failed");
    return 1;
  } else {
    // I am the parent
    free(child_environ[0]);
    free(child_environ[1]);
    return 0;
  } // if which side of the fork are we on?

  return 1;
} // int start_tsung_player


/**
 * Stop the player.
 * @return 0 unless error.
 */
int stop_tsung_player(char *prefix) {
  printf("Stopping tsung player is not implemented.\n");
  return 1;
} // int stop_tsung_player


/**
 * Become a daemon.
 */
void daemonize(void) {
  pid_t child;
  child = fork();
  if (child == -1) {
    perror("Could not daemonize");
  } else if (child != 0) {
    // parent should exit -- lose that controlling terminal
    exit(0);
  } // if whether fork worked

  // I am a daemon!  Muahahahaha!
  setsid(); // become a session leader
  chdir("/"); // Don't hang on to any directories -- we must have no attachments
  umask(0); // No unexpected perms are allowed
} // void daemonize
