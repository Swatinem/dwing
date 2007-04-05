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
	 * clean the incoming string and return an array of cleaned tag names
	 *
	 * @param string $aTags
	 * @return array
	 **/
	public static function cleanTags($aTags)
	{
		$tagNames = strtr(trim($aTags),
			array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'ae',
			'Ö' => 'oe', 'Ü' => 'ue'));
		$tagNames = strtolower($tagNames);
		$tagNames = preg_replace('![^a-z0-9-\s]*!', '', $tagNames);
		$tagNames = preg_replace('!\s+!', ' ', $tagNames);
		return explode(' ', $tagNames);
	}

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
	 * @param string $aTags
	 * @return bool
	 **/
	public static function setTagsForContent($aContentId, $aContentType, $aTags)
	{
		// delete old tags
		self::deleteTagsForContent($aContentId, $aContentType);
		// names to id mapping
		$nameToIdMap = array();
		$tagsRes = self::$_db->queryAll('SELECT tag_id, name FROM '.self::$_db->pref.'tags;');
		foreach($tagsRes as $tagRow)
			$nameToIdMap[$tagRow['name']] = $tagRow['tag_id'];
		// filter the tag names out of the input string
		$tagNames = self::cleanTags($aTags);

		// link the tags with the content item, adding new tags when necessary
		$query = 'INSERT INTO '.self::$_db->pref.'tagstocontent (tag_id, content_id, content_type) VALUES ';
		$insertRows = array();
		foreach($tagNames as $tagName)
		{
			$tagId = !empty($nameToIdMap[$tagName]) ? $nameToIdMap[$tagName] : self::addTag($tagName);
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
	 * @param string $aTagName
	 * @return int insert_id of the newly created tag
	 **/
	private static function addTag($aTagName)
	{
		self::$_db->query('
			INSERT INTO '.self::$_db->pref.'tags SET
			name="'.self::$_db->escape($aTagName).'";');
		return self::$_db->insert_id;
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
	 * @param string $aTagName
	 * @param int $aContentType
	 * @return int
	 **/
	public static function getContentCount($aTagName = null, $aContentType = null)
	{
		$query = '
			SELECT COUNT(DISTINCT content_id) AS count FROM '.self::$_db->pref.'tagstocontent
			LEFT JOIN '.self::$_db->pref.'tags as tags USING (tag_id)
			';
		if($aTagName || $aContentType)
		{
			$query.= ' WHERE ';
			if($aTagName)
				$query.= ' tags.name="'.self::$_db->escape($aTagName).'" ';
			if($aTagName && $aContentType)
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