<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2004-2008 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * TODO: rewrite l10n::mergeInto($aNewFile, $aLangFiles)
 * that generates the language files inside runtime/lang/ that consist of
 * all the strings from modules and custom strings...
 */

/**
 * Localization class
 *
 * the class manages the translations
 */
class l10n
{
	/**
	 * This array contains both general and specific language names
	 * general may be "de" while specific would be "de_DE", "de_AT" or something
	 * like that
	 * general is always set. It is equal to "C" if the language could not be
	 * detected.
	 * specific may not exist at all, pay attention to that
	 */
	public static $lang = array('general' => 'C', 'specific' => 'C');
	public static $langName = 'C';
	public static $langTable = array();

	/**
	 * the constructor which is called directly
	 */
	public static function init()
	{
		$lang = self::browserLang();
		if(!empty($lang))
		{
			self::$lang = $lang;
			if(!empty($lang['specific']) && file_exists('lang/'.$lang['specific'].'.php'))
				require_once('lang/'.$lang['specific'].'.php');
			else if(file_exists('lang/'.$lang['general'].'.php'))
				require_once('lang/'.$lang['general'].'.php');
			else
				return;
			
			self::$langName = $langName;
			self::$langTable = $langTable;
			setlocale(LC_ALL, explode(',', $langLocales));
		}
	}

	/**
	 * Tries to detect the language as set up in the users browser
	 */
	public static function browserLang()
	{
		if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			return array();
		}
		@preg_match_all('/([a-z]+)[-_]?([a-z]+)?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_SET_ORDER);
		$lang = array();
		if(!empty($matches[0][1]))
		{
			$lang['general'] = strtolower($matches[0][1]);
			if(!empty($matches[0][2]))
				$lang['specific'] = $lang['general'].'_'.strtoupper($matches[0][2]);
		}
		return $lang;
	}

	/**
	 * translate a given String
	 *
	 * @param string $str
	 * @return string
	 **/
	public static function _($str)
	{
		return !empty(self::$langTable[$str]) ? self::$langTable[$str] : $str;
	}
}
?>
