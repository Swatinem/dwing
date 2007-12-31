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
 * Localization class
 *
 * the class manages the translations
 */
class l10n
{
	public static $lang;
	public static $langTable;

	/**
	 * the constructor which is called directly
	 *
	 **/
	public static function init()
	{
		$lang = self::browserLang();
		self::$lang = $lang ? $lang : 'en'; // default lang: en;
		self::$langTable = self::getLangTable(); // do this JIT somehow?
		setlocale(LC_ALL, self::$lang);
	}

	public static function browserLang()
	{
		if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			return false;
		}
		@preg_match_all('/([a-z]+)[,;]?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
		return !empty($matches[1][0]) ? $matches[1][0] : false;
	}

	/**
	 * list all the language files
	 *
	 * @return array
	 **/
	public static function getLanguages()
	{
		$langs = array();

		if(($dir = @opendir('./lang')) == false)
		{
			return false;
		}
		while(false !== ($file = readdir($dir)))
		{
			if(substr($file,-4,4) == '.php')
			{
				$langs[] = substr($file,0,-4);
			}
		}
		return $langs;
	}

	/**
	 * get all the translations from one language file
	 *
	 * @param string $lang
	 * @return array
	 **/
	public static function getLangTable($lang = null)
	{
		if(!$lang)
			$lang = self::$lang;
		if($lang == self::$lang && !is_null(self::$langTable))
			return self::$langTable;
		if(!file_exists('./lang/'.$lang.'.php'))
		{
			return false;
		}
		include('./lang/'.$lang.'.php');
		return $__LANG;
	}

	/**
	 * translate a given String
	 *
	 * @param string $str
	 * @return string
	 **/
	public static function _($str)
	{
		if(!self::$lang || empty(self::$langTable[$str]))
			return $str;
		else
			return self::$langTable[$str];
	}

	/**
	 * save the language table
	 *
	 * @param string $lang
	 * @return bool
	 **/
	public static function saveLanguage($lang = null, $langTable = null)
	{
		if(!$lang || !$langTable)
		{
			$lang = self::$lang;
			$langTable = self::$langTable;
		}
		file_put_contents('./lang/'.$lang.'.php', "<?php\n\$__LANG = ".var_export($langTable, true).";\n?>");
		return true;
	}

	/**
	 * merge all untranslated items from lang1 into lang2 and return the resulting language table
	 *
	 * @param string $lang1 the untranslated language
	 * @param string $lang2 the already translated one
	 * @return array
	 **/
	public static function mergeLang($lang1, $lang2)
	{
		return array_merge(self::getLangTable($lang1), self::getLangTable($lang2));
	}
}
?>