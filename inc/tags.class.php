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
 * Tagging system
 *
 * This class provides functions to handle tags
 */
class Tags
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
		$statement = Core::$db->prepare('
			SELECT tags.name
			FROM '.Core::$db->pref.'tagstocontent AS tagstocontent
			LEFT JOIN '.Core::$db->pref.'tags AS tags USING (tag_id)
			WHERE tagstocontent.content_id=:contentId AND
				tagstocontent.content_type=:contentType
			ORDER BY tags.name ASC;');
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
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
		$statement = Core::$db->prepare('DELETE FROM '.Core::$db->pref.'tagstocontent
			WHERE content_id=:contentId AND content_type=:contentType;');
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
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
		$tagsRes = Core::$db->queryAll('SELECT tag_id, name FROM '.Core::$db->pref.'tags;');
		foreach($tagsRes as $tagRow)
			$nameToIdMap[$tagRow['name']] = $tagRow['tag_id'];
		// filter the tag names out of the input string
		$tagNames = self::cleanTags($aTags);

		// link the tags with the content item, adding new tags when necessary
		$statement = Core::$db->prepare('
			INSERT INTO '.Core::$db->pref.'tagstocontent
			SET tag_id=:tagId, content_id=:contentId, content_type=:contentType;');
		$statement->bindParam(':tagId', $tagId, PDO::PARAM_INT);
		$statement->bindParam(':contentId', $aContentId, PDO::PARAM_INT);
		$statement->bindParam(':contentType', $aContentType, PDO::PARAM_INT);
		foreach($tagNames as $tagName)
		{
			$tagId = !empty($nameToIdMap[$tagName]) ? $nameToIdMap[$tagName] : self::addTag($tagName);
			$statement->execute();
		}
		return true;
	}

	/**
	 * get all tags
	 *
	 * @return array
	 **/
	public static function getTags()
	{
		$statement = Core::$db->prepare('SELECT name FROM '.Core::$db->pref.'tags ORDER BY name ASC;');
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * add a tag
	 *
	 * @param string $aTagName
	 * @return int insert_id of the newly created tag
	 **/
	private static function addTag($aTagName)
	{
		$statement = Core::$db->prepare('
			INSERT INTO '.Core::$db->pref.'tags SET
			name=:name;');
		$statement->bindValue(':name', $aTagName, PDO::PARAM_STR);
		$statement->execute();
		return Core::$db->lastInsertId();
	}

	/**
	 * get only the tags which have associated content of type
	 *
	 * @param int $aContentType
	 * @return array
	 **/
	public static function getTagsWithContentOfType($aContentType)
	{
		$statement = Core::$db->prepare('
			SELECT DISTINCT tags.name, COUNT(tagstocontent.content_id) AS content
			FROM '.Core::$db->pref.'tags AS tags
			LEFT JOIN '.Core::$db->pref.'tagstocontent AS tagstocontent USING (tag_id)
			WHERE tagstocontent.content_type=:contentType
			GROUP BY tag_id
			ORDER BY tags.name ASC;');
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
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
			SELECT COUNT(DISTINCT content_id) AS count FROM '.Core::$db->pref.'tagstocontent
			LEFT JOIN '.Core::$db->pref.'tags as tags USING (tag_id)
			';
		if($aTagName || $aContentType)
		{
			$query.= ' WHERE ';
			if($aTagName)
				$query.= ' tags.name='.Core::$db->quote($aTagName).' ';
			if($aTagName && $aContentType)
				$query.= ' AND ';
			if($aContentType)
				$query.= ' content_type='.(int)$aContentType.' ';
		}
		$query.= ';';
		$result = Core::$db->query($query);
		return $result->fetchColumn();
	}
}
?>
