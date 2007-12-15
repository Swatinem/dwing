<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2007-2007 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * Template Class
 **/
class TemplateSystem
{
	protected $mAssigns = array();
	protected $mPaths = array();
	
	public function assign($aVarName, $aValue)
	{
		$this->mAssigns[$aVarName] = $aValue;
	}
	public function __call($aMethod, $aArguments)
	{
		echo __METHOD__."\n";
		//echo '';
	}
	public function template_exists($aFileName)
	{
		return $this->template($aFileName) != false;
	}
	public function template($aFileName)
	{
		foreach($this->mPaths as $path)
		{
			$realPath = realpath($path.$aFileName);
			if(strpos($realPath, $path) !== false && file_exists($realPath))
				return $realPath;
		}
		return false;
	}
	public function setPath($aPath)
	{
		$aPath = realpath($aPath).DIRECTORY_SEPARATOR;
		if(file_exists($aPath))
			$this->mPaths = array($aPath);
	}
	public function addPath($aPath)
	{
		$aPath = realpath($aPath).DIRECTORY_SEPARATOR;
		if(file_exists($aPath))
			array_unshift($this->mPaths, $aPath);
	}
	public function display($aTemplate)
	{
		$fileName = $this->template($aTemplate);
		extract($this->mAssigns, EXTR_REFS);
		if(!empty($fileName))
			include($fileName);
	}
}
?>