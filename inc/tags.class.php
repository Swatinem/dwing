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
 * Tagging system
 *
 * This class provides functions to handle tags
 */
class Tags extends Module
{
	/**
	 * get all tags associated to one content item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return array
	 **/
	public static function getTagsForContent($aContentId, $aContentType)
	{
		$tagsRes = self::$_db->queryAll('
			SELECT tags.name
			FROM '.self::$_db->pref.'tagstocontent AS tagstocontent
			LEFT JOIN '.self::$_db->pref.'tags AS tags USING (tag_id)
			WHERE tagstocontent.content_id='.(int)$aContentId.' AND tagstocontent.content_type='.(int)$aContentType.'
			ORDER BY tags.name ASC;');
		$tagNames = array();
		foreach($tagsRes as $tagRow)
			$tagNames[] = $tagRow['name'];
		return $tagNames;
	}

	/**
	 * delete the associations for a content item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return bool
	 **/
	public static function deleteTagsForContent($aContentId, $aContentType)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'tagstocontent
			WHERE content_id='.(int)$aContentId.' AND content_type='.(int)$aContentType.';');
		return true;
	}

	/**
	 * set tags for a content item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @param array $aTagIds
	 * @return bool
	 **/
	public static function setTagsForContent($aContentId, $aContentType, $aTagIds)
	{
		// delete old tags
		self::deleteTagsForContent($aContentId, $aContentType);
		// add new tags
		$query = 'INSERT INTO '.self::$_db->pref.'tagstocontent (tag_id, content_id, content_type) VALUES';
		$insertRows = array();
		foreach($aTagIds as $tagId)
		{
			$insertRows[] = '('.(int)$tagId.','.(int)$aContentId.','.(int)$aContentType.')';
		}
		$query.= implode(',', $insertRows).';';
		self::$_db->query($query);
		return true;
	}

	/**
	 * get all tags
	 *
	 * @return array
	 **/
	public static function getTags()
	{
		$tagsRes = self::$_db->queryAll('SELECT name FROM '.self::$_db->pref.'tags ORDER BY name ASC;');
		$tagNames = array();
		foreach($tagsRes as $tagRow)
			$tagNames[] = $tagRow['name'];
		return $tagNames;
	}

	/**
	 * add a tag
	 *
	 * @return mixed Exception on failure, id on success
	 **/
	public static function addTag()
	{
		try
		{
			if(empty($_POST['name'])) throw new Exception(l10n::_('No name defined.'));
			self::$_db->query('
				INSERT INTO '.self::$_db->pref.'tags SET
				name="'.self::$_db->escape($_POST['name']).'";');
			return self::$_db->insert_id;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * get the name of a tag
	 *
	 * @param int $aTagId
	 * @return string
	 **/
	public static function tagName($aTagId)
	{
		$tag = self::$_db->queryfirst('SELECT name FROM '.self::$_db->pref.'tags WHERE tag_id='.(int)$aTagId.';');
		return $tag['name'];
	}

	/**
	 * get only the tags which have associated content of type
	 *
	 * @param int $aContentType
	 * @return array
	 **/
	public static function getTagsWithContentOfType($aContentType)
	{
		return self::$_db->queryAll('
			SELECT DISTINCT tags.name, COUNT(tagstocontent.content_id) AS content
			FROM '.self::$_db->pref.'tags AS tags
			LEFT JOIN '.self::$_db->pref.'tagstocontent AS tagstocontent USING (tag_id)
			WHERE tagstocontent.content_type='.(int)$aContentType.'
			GROUP BY tag_id
			ORDER BY tags.name ASC;');
	}

	/**
	 * how many items of a specified type are tagged with the specified tag
	 *
	 * @param int $aTagId
	 * @param int $aContentType
	 * @return int
	 **/
	public static function getContentCount($aTagId = null, $aContentType = null)
	{
		$query = 'SELECT COUNT(DISTINCT content_id) AS count FROM '.self::$_db->pref.'tagstocontent';
		if($aTagId || $aContentType)
		{
			$query.= ' WHERE ';
			if($aTagId)
				$query.= ' tag_id='.(int)$aTagId.' ';
			if($aTagId && $aContentType)
				$query.= ' AND ';
			if($aContentType)
				$query.= ' content_type='.(int)$aContentType.' ';
		}
		$query.= ';';
		$result = self::$_db->queryfirst($query);
		return $result['count'];
	}
}
?>