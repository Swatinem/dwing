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
		$statement = self::$_db->prepare('
			SELECT comment_id, user_id, time, text FROM '.self::$_db->pref.'comments
			WHERE content_id=:contentId AND content_type=:contentType
			ORDER BY time ASC;');
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		
		$comments = array();
		while($commentRow = $statement->fetch(PDO::FETCH_ASSOC))
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
		$statement = self::$_db->prepare('
			SELECT COUNT(comment_id) as commentnum FROM '.self::$_db->pref.'comments
			WHERE content_id=:contentId AND content_type=:contentType;');
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetchColumn();
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
		$statement = self::$_db->prepare('
		INSERT INTO
			'.self::$_db->pref.'comments
		SET
			user_id=:userId, time=:time, text=:text, content_id=:contentId,
			content_type=:contentType;');
		
		$statement->bindValue(':userId', (int)self::$_user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':time', time(), PDO::PARAM_INT);
		$statement->bindValue(':text', Utils::purify($_POST['text']), PDO::PARAM_STR);
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return self::$_db->lastInsertId();
	}

	/**
	 * delete a single comment
	 *
	 * @param int $aCommentId
	 * @return bool
	 **/
	public static function deleteComment($aCommentId)
	{
		$statement = self::$_db->prepare('DELETE FROM '.self::$_db->pref.'comments
			WHERE comment_id=:commentId;');
		$statement->bindValue(':commentId', (int)$aCommentId, PDO::PARAM_INT);
		$statement->execute();
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
		$statement = self::$_db->prepare('DELETE FROM '.self::$_db->pref.'comments
			WHERE content_id=:contentId AND content_type=:contentType;');
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return true;
	}
}
?>