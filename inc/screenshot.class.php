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
 * Screenshot module
 *
 * This class provides functions to handle screenshots
 */
class Screenshot extends Module
{
	/**
	 * return the details of X pictures associated with a specified tag
	 *
	 * @param int $aTagId only return the pictures tagged with this tag
	 * @param int $aPerPage how many pictures to return
	 * @return array
	 **/
	public static function getScreenshots($aTagId, $aPerPage = null)
	{
		return self::$_db->queryAll('
			SELECT screens.* FROM '.self::$_db->pref.'pictures AS screens
			LEFT JOIN '.self::$_db->pref.'tagstocontent AS tagstocontent
				ON screens.pic_id = tagstocontent.content_id
			WHERE tagstocontent.tag_id='.(int)$aTagId.' AND tagstocontent.content_type='.ContentType::IMAGE.'
			ORDER BY pic_id DESC '.Utils::sqlLimit($aPerPage).';');
	}

	/**
	 * return the details of the specified picture
	 *
	 * @param int $aPicId Id of the picture we want to get
	 * @return array
	 **/
	public static function getScreenshot($aPicId)
	{
		return self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'pictures WHERE pic_id='.(int)$aPicId.';');
	}

	/**
	 * increments the views of a specified picture
	 *
	 * @param int $aPicId Id of the picture whose views we want to increment
	 * @return void
	 **/
	public static function incrementViews($aPicId)
	{
		self::$_db->query('UPDATE '.self::$_db->pref.'pictures SET views=views+1 WHERE pic_id='.(int)$aPicId.';');
	}

	/**
	 * upload a screenshot
	 *
	 * @return mixed false on failure, id on success
	 **/
	/* TODO: rewrite this!
	public static function addscreenshot()
	{
		try
		{
			if(!is_uploaded_file($_FILES['pic']['tmp_name']))
			{
				throw new Exception('No picture uploaded.');
			}
			require_once('inc/screenshot_upload.class.php');
			$upload = new screenshot_upload(
			'images/screenshots/gallery'.(int)$_POST['cat_id'].'/picture%d%s.jpg',
			(int)$_POST['width'],(int)$_POST['height'],
			'INSERT INTO '.$this->_db->pref.'pictures SET title="'.$_POST['title'].'",
			cat_id='.(int)$_POST['cat_id'].', width=%d, height=%d;');
			$pic_id = $upload->upload('pic');
			if(!$pic_id)
			{
				throw new Exception('The picture could not be saved.');
			}
			$this->_tpl->clear_cache_byobject('screenshot');
			return $pic_id;
		}
		catch(Exception $e)
		{
			$this->_tpl->assignLang('error', $e->getMessage());
			return false;
		}
	}*/

	/**
	 * add all pictures in the specified directory
	 *
	 * @return mixed Exception on failure, number of uploaded pictures on success
	 **/
	public static function massAdd()
	{
		require_once('inc/screenshot_upload.class.php');
		$upload = new screenshot_upload(
		'images/pictures/picture%d.jpg',
		'images/thumbs/picture%d.jpg',
		//(int)$_POST['width'],(int)$_POST['height'],
		120, 90, // make this a setting?
		'INSERT INTO '.self::$_db->pref.'pictures SET title="'.self::$_db->escape($_POST['title']).'", width=%d, height=%d;');
		$insertIds = $upload->uploaddir($_POST['directory'], isset($_POST['delold']));
		if(is_a($insertIds, 'Exception'))
			return $insertIds;
		foreach($insertIds as $insertId)
		{
			Tags::addTagsForContent($insertId, ContentType::IMAGE, $_POST['tag_ids']);
		}
		return count($insertIds);
	}

	/**
	 * delete the specified picture
	 *
	 * @param int $aPicId Id of the picture we want to delete
	 * @return bool
	 **/
	public static function deleteScreenshot($aPicId)
	{
		// picture file does not exist -> exit function so we don't get errors unlinking the non-existing files
		if(!file_exists('images/pictures/picture'.(int)$aPicId.'.jpg'))
			return false;
		// delete the DB entry
		self::$_db->query('DELETE FROM '.self::$_db->pref.'pictures WHERE pic_id='.(int)$aPicId.';');
		// delete the associations with tags
		Tags::deleteTagsForContent((int)$aPicId, ContentType::IMAGE);
		// delete the picture files
		unlink('images/pictures/picture'.(int)$aPicId.'.jpg');
		unlink('images/thumbs/picture'.(int)$aPicId.'.jpg');
		return true;
	}
}
?>