<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2004-2007 Arpad Borsos <arpad.borsos@googlemail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

$_version = '0.0.5';

session_start();
$_debug = false;

// show all errors
error_reporting(E_ALL /*& E_STRICT*/); // first check if the included libs are compatible or not
ini_set('display_errors', true);
// check querys for indexes
//mysqli_report(MYSQLI_REPORT_ALL);

// include bench class
require_once('inc/bench.class.php');
// start bench
$_bench = new bench;

// gzip the output
ini_set('zlib.output_compression_level', 3);
ob_start('ob_gzhandler');
header('X-Powered-By: dWing cms/'.$_version.' (swatinemz.sourceforge.net)',false);

// disable magic quotes
set_magic_quotes_runtime(0);
if(get_magic_quotes_gpc() == 1)
{
	function magicSlashes($element)
	{
		if(is_array($element))
			return array_map('magicSlashes', $element);
		else
			return stripslashes($element);
	}

	// strip slashes from all incoming GET/POST/COOKIE/REQUEST data.
	$_GET = array_map('magicSlashes', $_GET);
	$_POST = array_map('magicSlashes', $_POST);
	$_COOKIE = array_map('magicSlashes', $_COOKIE);
	$_REQUEST = array_map('magicSlashes', $_REQUEST);
}

// init the translation system, this is independet from any module
require_once('inc/translation.class.php');
l10n::init();

// initiate template engine
require_once('inc/snntpl.class.php');
$_tpl = new tpl();
$_tpl->assign('_bench',$_bench);
$_tpl->assign('_version',$_version);

/*
Todo:
- separate Install and Update classes
- Install will be a standalone class
- Update will be a Module to include references to the DB
*/
// check if the script is installed yet
if(!file_exists('inc/settings.php'))
{
	// not installed -> include install class, display install template
	exit('not installed, installer currently disabled');
	$_tpl->setPath('template', './tpl/install');
	$_tpl->display('install.tpl.php');
}
// exit -> no else needed

// do we want dynamic settings at all?
require_once("inc/settings.php");

// extended mysqli class include. init in the config
require_once('inc/mysqli.class.php');
require_once('inc/config.php');

// autoload classes
function __autoload($aClassName)
{
	if(file_exists('inc/'.strtolower($aClassName).'.class.php'))
	{
		require_once('inc/'.strtolower($aClassName).'.class.php');
	}
}

require_once('inc/modulesystem.class.php');

Module::assignGlobals($_db, $_cfg);

// init user
require_once('inc/user.class.php');
$_user = new CurrentUser();
$_tpl->assign('user', $_user);
if(is_a($loginError = $_user->init(), 'Exception'))
	$_tpl->assign('loginerror', $loginError->getMessage());

Module::assignCurrentUser($_user);

/*
 * check if update is needed
 * we load the module system and the CurrentUser before the update
 * this may be needed as we only want to allow admins to begin the update
 * process but it may cause problems if the update touches any user code that
 * needs updating.
 */
if(version_compare($_version,$_cfg['version']) == 1)
{
	// update needed -> include update class, display update template
	exit('needs update, updater currently disabled');
	$_tpl->setPath('template', './tpl/install');
	$_tpl->display('update.tpl.php');
}
// exit -> no else needed

$_tpl->setPath('template', './tpl/default'); // add the default theme as a fallback
if(!empty($_GET['settheme']) && preg_match('!^[_a-zA-Z0-9-.]+$!', $_GET['settheme']) && $_GET['settheme'] != 'install')
{
	$_theme = $_GET['settheme'];
}
if(!empty($_theme))
{
	if(file_exists('./tpl/'.$_theme))
	{
		$_SESSION['theme'] = $_theme;
	}
}
$_themedir = '/'.(!empty($_SESSION['theme']) ? $_SESSION['theme'] : $_cfg['default_theme']);
$_tpl->addPath('template', './tpl'.$_themedir);

if(!empty($_GET['site']))
{
	if(preg_match('!^[_a-zA-Z0-9-.]+$!', $_GET['site']) && $_tpl->template_exists($_GET['site'].'.tpl.php'))
	{
		$_tpl->display($_GET['site'].'.tpl.php');
	}
	else
	{
		$_tpl->display('nosite.tpl.php');
	}
}
else
{
	$_tpl->display('index.tpl.php');
}
// do we still want this?
if($_debug)
{
	echo '<!--'."\n".
	'Parsed in '.$_bench->gettime().' Seconds'."\n";
	$_db->printdebuginfo();
	echo "\n".'-->';
}
?>