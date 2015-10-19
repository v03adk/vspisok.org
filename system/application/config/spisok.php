<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// tables
$config['table_temp_users']		        = 'sys_users_temp';
$config['table_users']		            = 'sys_users';
$config['table_activation']		        = 'sys_users_activation';
$config['table_pasrecover']		        = 'sys_users_password_recover';
$config['table_userroles']		        = 'sys_userroles';
$config['table_notice_templates']		= 'aux_notice_templates';
$config['table_banlist']		        = 'sys_banlist';
$config['table_tasks']  		        = 'tasks_title';
$config['table_elems']  		        = 'tasks_elems';
$config['table_menu']     		        = 'sys_menu';
$config['table_feedback']     		    = 'aux_feedback';

// notice templates
$config['templates_register']              = 1;
$config['templates_pasrecover']            = 2;
$config['templates_pasrecover_send']       = 3;
$config['templates_anonymus_list_created'] = 4;