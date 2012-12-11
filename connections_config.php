<?php
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

$db_type='mysql';
$db_user='singtsungdb';
$db_pass='singtsungdb123';
$db_url='localhost';
$db_port='3306';
$db_name='brook_sing_tsung_test';

$dsn="$db_type://$db_user:$db_pass@$db_url/$db_name";

require_once('classes/singtsungdb.class.php');
$db = new SingTsungDB($dsn);

?>
