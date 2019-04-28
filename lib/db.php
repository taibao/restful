<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */
require_once __DIR__."\conf.php";
return new PDO("mysql:host=".HOST.";dbname=".DBNAME,DBUSER,DBPASS,array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \'UTF8\'' ));
