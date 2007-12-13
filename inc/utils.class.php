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

/**
 * Utils module
 *
 * This class provides some utility functions used in various modules
 */
class Utils extends Module
{
	/**
	 * static reference to the purifier
	 *
	 * @var HTMLPurifier $mPurifier
	 **/
	private static $mPurifier = null;

	/**
	 * purifies the input html
	 * returns cleaned html with all malicious code removed
	 *
	 * @param string $aInput the input HTML
	 * @return string
	 **/
	public static function purify($aInput)
	{
		if(is_null(self::$mPurifier))
		{
			// load HTMLPurifier
			require_once('HTMLPurifier.php');
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML', 'Strict', true);
			self::$mPurifier = new HTMLPurifier($config);
			require_once 'HTMLPurifier/Filter/YouTube.php';
			self::$mPurifier->addFilter(new HTMLPurifier_Filter_YouTube());
		}
		return self::$mPurifier->purify($aInput);
	}

	/**
	 * converts a string to be used as a url
	 *
	 * @param string $aStr
	 * @return int
	 **/
	public static function fancyUrl($aStr)
	{
		// strtolower not utf8 aware
		$return = strtr(trim($aStr),
			array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'ae',
			'Ö' => 'oe', 'Ü' => 'ue', ' ' => '-'));
		$return = strtolower($return);
		$return = preg_replace('![^a-z0-9-]*!', '', $return);
		$return = preg_replace('!-+!', '-', $return);
		return $return;
	}

	/**
	 * returns the current page
	 *
	 * @return int
	 **/
	public static function getPage()
	{
		return !empty($_GET['page']) ? (int)$_GET['page'] : 1;
	}

	/**
	 * returns the sql LIMIT
	 *
	 * @param int $aPerPage to how many entries we want to limit
	 * @return string the sql LIMIT clause
	 **/
	public static function sqlLimit($aPerPage)
	{
		if(empty($aPerPage))
			return '';
		return 'LIMIT '.(self::getPage()-1)*(int)$aPerPage.','.(int)$aPerPage;
	}

	/**
	 * returns the html code for letter selection
	 *
	 * @param string $aFile first part of the link before the letter
	 * @param string $aFileAppend second part of the link after the letter
	 * @return string
	 **/
	public static function letterSelect($aFile, $aFileAppend ='')
	{
		$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$output = '<a href="'.$aFile.'all'.$aFileAppend.'"';
		if(empty($_GET['letter']) || $_GET['letter'] == 'all') $output.= ' class="selected"';
		$output.= '><span>'.l10n::_('All').'</span></a> <a href="'.$aFile.'special'.$aFileAppend.'"';
		if(!empty($_GET['letter']) && $_GET['letter'] == 'special') $output.= ' class="selected"';
		$output.= '><span>#</span></a>';
		foreach($letters as $letter)
		{
			$output.= '<a href="'.$aFile.$letter.$aFileAppend.'"';
			if(!empty($_GET['letter']) && $_GET['letter'] == $letter) $output.= ' class="selected"';
			$output.= '><span>'.$letter.'</span></a>';
		}
		return $output;
	}

	/**
	 * returns a sql WHERE for alphabetic selection
	 *
	 * @param string $aFieldName name of the sql column we want to use
	 * @return string
	 **/
	public static function letterSelectSql($aFieldName)
	{
		if(empty($_GET['letter']) || $_GET['letter'] == 'all')
		{
			return '1';
		}
		elseif($_GET['letter'] == 'special')
		{
			return $aFieldName.' NOT REGEXP "^[a-zA-Z]"';
		}
		else
		{
			return $aFieldName.' LIKE "'.self::$_db->escape($_GET['letter']).'%"';
		}
	}

	/**
	 * returns links to diferent pages
	 *
	 * @param int $aTotal total number of items
	 * @param int $aPerPage items per page
	 * @param string $aFile first part of the link before the page number
	 * @param string $aFileAppend second part of the link after the page number
	 * @param int $aShowPages How many page links we want to show
	 * @return string
	 **/
	public static function pages($aTotal, $aPerPage, $aFile, $aFileAppend = '', $aShowPages = 5)
	{
		$page = self::getPage();
		$spages = $aShowPages;
		$total = $aTotal;
		$perpage = $aPerPage;

		$i = ceil($total/$perpage);
		if($i < $spages) $spages = 0;

		$return = '<a><span>'.sprintf(l10n::_('Pages (%d):'), $i).'</span></a> ';
		if($page > 1)
		{
			$return.= '<span class="arrows"><a href="'.$aFile.'1'.$aFileAppend.'"><span>«</span></a> <a href="'.$aFile.($page-1).$aFileAppend.'"><span>‹</span></a></span> ';
		}
		if($spages == 0)
		{
			for($j=1;$j<=$i;$j++)
			{
				$return.='<a href="'.$aFile.$j.$aFileAppend.'" '.($page == $j ? ' class="selected"' : '').'><span>'.$j.'</span></a> ';
			}
		}
		else
		{
			$spages = ceil(($spages-1)/2);

			if($page <= $spages)
			{
				$bpages = $page-1;
				$spages+= $spages-$bpages;
			}
			elseif($page >= $i-$spages)
			{
				$bpages = $spages+$page-$i+$spages;
				$spages+= $i-$spages-$page;
			}
			else $bpages = $spages;
			if($bpages > 0)
			{
				for($j = $page-$bpages; $j <= $page-1; $j++)
				{
					$return.= '<a href="'.$aFile.$j.$aFileAppend.'"><span>'.$j.'</span></a> ';
				}
			}
			$return.='<a href="'.$aFile.$page.$aFileAppend.'" class="selected"><span>'.$page.'</span></a> ';

			$apages = $spages;
			if($apages > 0)
			{
				for($j = $page+1;$j <= $page+$apages;$j++)
				{
					$return.= '<a href="'.$aFile.$j.$aFileAppend.'"><span>'.$j.'</span></a> ';
				}
			}
		}
		if($i > $page)
		{
			$return.= '<span class="arrows"><a href="'.$aFile.($page+1).$aFileAppend.'"><span>›</span></a> <a href="'.$aFile.$i.$aFileAppend.'"><span>»</span></a></span>';
		}
		if($total > $perpage)
		{
			return $return;
		}
	}

	/**
	 * checks if the string is a comma seperated list of numbers
	 *
	 * @param string $str the string to check
	 * @param bool $return return an array of the numbers
	 * @return mixed
	 **/
	public static function commaSeperatedNumber($str, $return = null)
	{
		$res = preg_match('!^[0-9]+(,[0-9]+)*$!', $str, $matches);
		//var_dump($matches);
		if($return && $res)
		{
			return array_map('intval', explode(',', $str));
		}
		else
		{
			return $res;
		}
	}
}
?>