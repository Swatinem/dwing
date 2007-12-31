<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2006-2008 Arpad Borsos <arpad.borsos@googlemail.com>
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