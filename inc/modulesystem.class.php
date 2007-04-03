<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2006-2007 Arpad Borsos <arpad.borsos@googlemail.com>
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

/**
 * Module
 *
 * All modules inherit this class' static vars
 */
abstract class Module
{
	/**
	 * static reference to the database
	 *
	 * @var extmysqli $_db
	 **/
	protected static $_db;
	/**
	 * static reference to the config variable
	 *
	 * @var array $_cfg
	 **/
	protected static $_cfg;
	/**
	 * static reference to the current user
	 *
	 * @var user $_user
	 **/
	protected static $_user;
	/**
	 * assign the global objects to the static members
	 *
	 * @param extmysqli $aDb
	 * @param tpl $aTpl
	 * @param array $aCfg
	 * @param CurrentUser $aUser
	 * @return void
	 **/
	public static function assignGlobals($aDb, $aCfg, $aUser = null)
	{
		self::$_db = $aDb;
		self::$_cfg = $aCfg;
		self::$_user = $aUser;
	}
	/**
	 * assign the global objects to the static members
	 *
	 * @param CurrentUser $aUser
	 * @return void
	 **/
	public static function assignCurrentUser($aUser)
	{
		self::$_user = $aUser;
	}
}

/**
 * ContentType
 *
 * Contains the content types for usage in related content
 */
class ContentType
{
	const NEWS = 1;
	const ARTICLE = 2;
	const IMAGE = 3;
	const COMMENT = 4;
}
?>