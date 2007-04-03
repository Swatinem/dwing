<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2004-2006 Arpad Borsos
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

require_once('Savant3.php');

/*
Write a own template system to replace Savant
*/

/**
 * Template Class
 *
 * This class is a wrapper for Savant.
 **/
class tpl extends Savant3
{
	/**
	 * constructor
	 *
	 * Initiates parent template engine and configures it.
	 *
	 * @return void
	 **/
	public function __construct()
	{
		parent::__construct();
		$this->setExtract(true);
	}

	/**
	 * a fake method to assign a translated string
	 *
	 * @return bool
	 **/
	public function assignLang($var, $str)
	{
		$this->assign($var, l10n::_($str));
	}

	/**
	 * a wrapper for findFile
	 *
	 * @return bool
	 **/
	public function template_exists($tplname)
	{
		return (bool)$this->findFile('template', $tplname);
	}
}
?>