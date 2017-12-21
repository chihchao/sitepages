<?php
//include
include_once('../../mainfile.php');
include_once('function.php');
include_once('class/spuser.php');

//init
setoff_magic_quotes_gpc();
$spUser = new SPUser;
//init global var xoops_module_header
set_module_header();
set_module_header('stylecss');
?>