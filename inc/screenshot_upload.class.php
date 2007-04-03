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

/*
Rewrite this, possibly remove it in favor of something better
*/

/**
 * Screenshot upload class
 *
 * upload a screenshot or move a lot of screenshots from one directory in the
 * right ones and create the database entries.
 */
class screenshot_upload
{
	/**
	 * the path for new images
	 *
	 * @var string $mPicPath
	 **/
	private $mPicPath;
	/**
	 * the path for the thumbnails
	 *
	 * @var string $mThumbPath
	 **/
	private $mThumbPath;
	/**
	 * the sql query to use for the db inserts
	 *
	 * @var string $_query
	 **/
	private $_query;
	/**
	 * the max. width of the thumbnail
	 *
	 * @var int $_width
	 **/
	private $_width;
	/**
	 * the max. height of the thumbnail
	 *
	 * @var int $_height
	 **/
	private $_height;
	/**
	 * the info for the current screenshot
	 *
	 * @var int $_scr_info
	 **/
	private $_scr_info;
	/**
	 * the fullscreen image object
	 *
	 * @var mixed $_fullimage
	 **/
	private $_fullimage;
	/**
	 * the quality to use for recompression
	 *
	 * @var int $_quality
	 **/
	private $_quality = 85;

	/**
	 * the constructor
	 *
	 * @param string $path
	 * @param string $ratio
	 * @param int $size
	 * @param string $sql
	 * @return void
	 **/
	public function __construct($aPicPath, $aThumbPath, $width = null, $height = null, $sql = null)
	{
		$this->_width = $width;
		$this->_height = $height;
		$this->mPicPath = $aPicPath;
		$this->mThumbPath = $aThumbPath;
		$this->_query = $sql;
	}

	/**
	 * insert the screenshot into the db and return the id
	 *
	 * @param int $width
	 * @param int $height
	 * @return int
	 **/
	private function _insertdb($width, $height)
	{
		$GLOBALS['_db']->query(sprintf($this->_query,$width,$height));
		return $GLOBALS['_db']->insert_id;
	}

	/**
	 * move the screenshot to its new destination and autimatically convert it to jpg
	 *
	 * @param string $newname
	 * @param string $oldname
	 * @return bool
	 **/
	private function _movescreen($oldname, $newname)
	{
		if(empty($this->_scr_info))
		{
			$this->_scr_info = getimagesize($oldname);
		}
		if(!in_array($this->_scr_info[2],array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
		{
			$GLOBALS['_tpl']->assignLang('error', 'Unsupported imagetype.');
			return false;
		}
		if($this->_scr_info[2] == IMAGETYPE_GIF)
		{
			$this->_fullimage = imagecreatefromgif($oldname);
			imagejpeg($this->_fullimage,$newname,$this->_quality);
		}
		elseif($this->_scr_info[2] == IMAGETYPE_PNG)
		{
			$this->_fullimage = imagecreatefrompng($oldname);
			imagejpeg($this->_fullimage,$newname,$this->_quality);
		}
		elseif($this->_scr_info[2] == IMAGETYPE_JPEG)
		{
			copy($oldname,$newname);
			$this->_fullimage = imagecreatefromjpeg($newname);
		}
		return true;
	}

	/**
	 * upload a screen with no db insert and no thumbnail creation
	 *
	 * @param string $varname the $_FILES variable name
	 * @return bool
	 **/
	public function uploadnothumb($varname)
	{
		$success = $this->_movescreen($_FILES[$varname]['tmp_name'], $this->_path);
		if($success)
		{
			imagedestroy($this->_fullimage);
			return true;
		}
		return false;
	}

	/**
	 * create a thumbnail
	 *
	 * @param string $newname
	 * @return bool
	 **/
	private function _mkthumb($newname)
	{
		// pretend we allready have the fullscreen image and infos

		$width = $this->_width;
		$ratio = $this->_scr_info[0]/$width;
		$height = round($this->_scr_info[1]/$ratio);
		if($height > $this->_height)
		{
			$height = $this->_height;
			$ratio = $this->_scr_info[1]/$height;
			$width = round($this->_scr_info[0]/$ratio);
		}
		$thumb = imagecreatetruecolor($width,$height);
		imagecopyresampled($thumb,$this->_fullimage,0,0,0,0,$width,$height,$this->_scr_info[0],$this->_scr_info[1]);
		imagejpeg($thumb, $newname, $this->_quality);
		imagedestroy($thumb);
		return true;
	}

	/**
	 * upload a screen, insert it into the db and create a thumbnail
	 *
	 * @param string $oldname
	 * @return bool
	 **/
	private function _upload($oldname)
	{
		$this->_scr_info = getimagesize($oldname);
		// check this before insertdb is called
		if(!in_array($this->_scr_info[2],array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
		{
			$GLOBALS['_tpl']->assignLang('error', 'Unsupported imagetype.');
			return false;
		}
		$screen_id = $this->_insertdb($this->_scr_info[0], $this->_scr_info[1]);
		$this->_movescreen($oldname, sprintf($this->mPicPath,$screen_id)); // full
		$this->_mkthumb(sprintf($this->mThumbPath,$screen_id)); // thumb
		return $screen_id;
	}

	/**
	 * upload a screen with db insert and thumbnail creation
	 *
	 * @param string $varname the $_FILES variable name
	 * @return bool
	 **/
	public function upload($varname)
	{
		$success = $this->_upload($_FILES[$varname]['tmp_name']);
		if($success)
		{
			imagedestroy($this->_fullimage);
			return $success;
		}
		return false;
	}


	/**
	 * upload all screens in one directory with db insert and thumbnail creation
	 *
	 * @param string $dirname
	 * @return mixed false on failure, uploaded screenshots number on success
	 **/
	public function uploaddir($dirname, $aDeleteSource = false)
	{
		$insertIds = array();
		if(substr($dirname,-1,1) != '/')
		{
			$dirname.= '/';
		}
		if(($dir = @opendir($dirname)) == false)
		{
			return new Exception(l10n::_('The directory is invalid.'));
		}
		$pics = array();
		while(false !== ($file = readdir($dir)))
		{
			if(is_file($dirname.$file))
			{
				$pics[] = $file;
			}
		}
		rsort($pics); // we select the screens DESC so we need to use rsort
		foreach($pics as $pic)
		{
			if($insertId = $this->_upload($dirname.$pic))
			{
				if($aDeleteSource)
					unlink($dirname.$pic);
				$insertIds[] = $insertId;
			}
		}
		closedir($dir);
		return $insertIds;
	}
}
?>