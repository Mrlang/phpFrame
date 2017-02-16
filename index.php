<?php
//框架的入口文件


require_once('frame/db.class.php');
require_once('frame/parser.class.php');
require_once('frame/route.class.php');
require_once('frame/conF.class.php');

new route();
//将框架的核心文件引入
