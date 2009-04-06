<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2004-2009 Arpad Borsos <arpad.borsos@googlemail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if(true) // set to false for production systems
{
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', true);
}
else
{
	error_reporting(0);
	ini_set('display_errors', false);
}

abstract class Core
{
	public static $version = '0.0.7';
	public static $db;
	public static $user;
	public static $config;
	public static $tpl;
}

// start session management
session_start();

// gzip the output
if(!ini_get('zlib.output_compression'))
	ob_start('ob_gzhandler');
header('X-Powered-By: dWing cms/'.Core::$version.' (swatinemz.sourceforge.net)',false);

// disable magic quotes
if(function_exists('set_magic_quotes_runtime') && function_exists('get_magic_quotes_gpc'))
{
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
}

// init the translation system, this is independet from any module
require_once('inc/translation.class.php');
l10n::init();

// initiate template engine
require_once('inc/template.class.php');
$_tpl = new TemplateSystem();
Core::$tpl = $_tpl;
/*
TODO:
- separate Install and Update classes
- Install will be a standalone class
- Update will be a Module to include references to the DB
*/
// check if the script is installed yet
if(!file_exists('inc/settings.php'))
{
	// not installed -> include install class, display install template
	exit('not installed, installer currently disabled');
	$_tpl->setPath('./tpl/install');
	$_tpl->display('install.tpl.php');
}
// exit -> no else needed

// do we want dynamic settings at all?
require_once("inc/settings.php");
Core::$config = $_cfg;

// Database class include. init in the config
require_once('inc/database.class.php');
require_once('inc/config.php');
Core::$db = $_db;

// autoload classes
function dWingAutoload($aClassName)
{
	preg_match('!([A-Z]*[a-z]*)[A-Z]?!', $aClassName, $matches);
	if(file_exists('inc/'.strtolower($matches[1]).'.class.php'))
	{
		require_once('inc/'.strtolower($matches[1]).'.class.php');
	}
}
spl_autoload_register('dWingAutoload');

// init user
require_once('inc/rest.class.php'); // RESTful Interface
require_once('inc/user.class.php');
Core::$user = new CurrentUser();

/*
 * check if update is needed
 * we load the module system and the CurrentUser before the update
 * this may be needed as we only want to allow admins to begin the update
 * process but it may cause problems if the update touches any user code that
 * needs updating.
 */
if(version_compare(Core::$version, Core::$config['version']) == 1)
{
	// outdated -> include updater class, display update template
	exit('not installed, installer currently disabled');

	$_updater = new Updater();
	$_tpl->assign('updater', $_updater);
	
	$_tpl->setPath('./tpl/install');
	$_tpl->display('update.tpl.php');
	exit;
}
// exit -> no else needed

$_tpl->setPath('./tpl/default'); // add the default theme as a fallback
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
$_tpl->addPath('./tpl'.$_themedir);

$dispatcher = new RESTDispatcher();
?>
