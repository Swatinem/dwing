<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2007 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * Comments module
 *
 * Comment any type of content.
 */
class Comments extends Module
{
	/**
	 * get all the comments for a Content Item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return array
	 **/
	public static function getComments($aContentId, $aContentType)
	{
		$commentsRes = self::$_db->query('
			SELECT comment_id, user_id, time, text FROM '.self::$_db->pref.'comments
			WHERE content_id='.(int)$aContentId.' AND
				content_type='.(int)$aContentType.'
			ORDER BY time ASC;');
		$comments = array();
		while($commentRow = $commentsRes->fetch_assoc())
		{
			$commentRow['user'] = Users::getUser($commentRow['user_id']);
			unset($commentRow['user_id']);
			$comments[] = $commentRow;
		}
		return $comments;
	}

	/**
	 * get the number of comments for a Content Item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return int
	 **/
	public static function getCommentNum($aContentId, $aContentType)
	{
		$commentNum = self::$_db->queryFirst('
			SELECT COUNT(comment_id) as commentnum FROM '.self::$_db->pref.'comments
			WHERE content_id='.(int)$aContentId.' AND
				content_type='.(int)$aContentType.';');
		return $commentNum['commentnum'];
	}

	/**
	 * add a comment to a Content Item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return mixed false on failure, id on success
	 **/
	public static function addComment($aContentId, $aContentType)
	{
		if(empty($_POST['text']))
			return;
		self::$_db->query('
		INSERT INTO
			'.self::$_db->pref.'comments
		SET
			user_id='.(int)self::$_user->user_id.',
			time='.time().',
			text="'.self::$_db->escape(Utils::purify($_POST['text'])).'",
			content_id='.(int)$aContentId.',
			content_type='.(int)$aContentType.';');
		$insertId = self::$_db->insert_id;
		return $insertId;
	}

	/**
	 * delete a single comment
	 *
	 * @param int $aCommentId
	 * @return bool
	 **/
	public static function deleteComment($aCommentId)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'comments
			WHERE comment_id='.(int)$aCommentId.';');
		return true;
	}

	/**
	 * delete all comments for a Content Item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return bool
	 **/
	public static function deleteComments($aContentId, $aContentType)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'comments
			WHERE content_id='.(int)$aContentId.' AND
				content_type='.(int)$aContentType.';');
		return true;
	}
}
?>