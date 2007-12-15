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
 * Smilies module
 *
 * This class provides functions to handle smilies
 */
class Smilies extends Module
{
	/**
	 * static reference to the smilie table
	 *
	 * @var array $mSmilies
	 **/
	private static $mSmilies = null;

	/**
	 * loads the smilies into the static array
	 *
	 * @return void
	 **/
	private static function _loadSmilies()
	{
		self::$mSmilies = array();
		$smilies_res = self::$_db->query('SELECT code, file FROM '.self::$_db->pref.'smilies ORDER BY LENGTH(code) DESC');
		while($smilies_row = $smilies_res->fetch(PDO::FETCH_ASSOC))
		{
			self::$mSmilies[$smilies_row['code']] = '<img src="images/smilies/'.$smilies_row['file'].'" alt="'.$smilies_row['code'].'" />';
		}
		unset($smilies_res);
	}
	/**
	 * gets all the quotes
	 *
	 * @param string $aText the text in which we need to replace the smilie codes
	 * @return array
	 **/
	public static function replace($aText)
	{
		if(is_null(self::$mSmilies))
		{
			self::_loadSmilies();
		}
		return strtr($aText, self::$mSmilies);
	}
}
?>