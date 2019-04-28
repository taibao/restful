<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */
$db = require_once __DIR__."/lib/db.php";
require_once __DIR__."/class/User.php";
require_once __DIR__."/class/Article.php";
require_once __DIR__."/class/Rest.php";
session_start();
$user = new User($db);
$ar = new Article($db);
$Rest = new Rest($user,$ar);

$Rest->run();
