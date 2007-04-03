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
 * Article module
 *
 * This class provides functions to handle articles
 */
class Article extends Module
{
	/**
	 * return the details of all articles associated with a specified tag
	 *
	 * @param int $aTagId only return the articles tagged with this tag
	 * @return array
	 **/
	public static function getArticles($aTagId = null)
	{
		$query = '
		SELECT articles.art_id, articles.title, articles.time, articles.user_id
		FROM '.self::$_db->pref.'art_articles as articles';
		if(!empty($aTagId))
		{
			$query.= ' LEFT JOIN '.self::$_db->pref.'tagstocontent AS tagstocontent ON articles.art_id = tagstocontent.content_id
			WHERE tagstocontent.tag_id='.(int)$aTagId.' AND tagstocontent.content_type='.ContentType::ARTICLE;
		}
		$query.= ' ORDER BY articles.title ASC;';
		$arts_res = self::$_db->query($query);
		$arts = array();
		while($art = $arts_res->fetch_assoc())
		{
			$art['user'] = Users::getUser($art['user_id']);
			unset($art['user_id']);
			$arts[] = $art;
		}
		$arts_res->close();
		return $arts;
	}

	/**
	 * return the details of the specified article
	 *
	 * @param int $aArticleId Id of the article we want to get
	 * @return array
	 **/
	public static function getArticle($aArticleId)
	{
		$art = self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'art_articles WHERE art_id='.(int)$aArticleId.';');
		if(empty($art))
			return false; // if no article with this ID is found

		$pages_res = self::$_db->query('SELECT * FROM '.self::$_db->pref.'art_pages WHERE art_id='.(int)$aArticleId.' ORDER BY `order` ASC;');
		$art['pagedetails'] = array();
		while($page = $pages_res->fetch_assoc())
		{
			$art['pagedetails'][$page['order']] = array('subtitle' => $page['subtitle'], 'text' => $page['text']);
		}
		$pages_res->close();
		unset($page);
		$art['curpage'] = Utils::getPage()-1;
		$art['text'] = $art['pagedetails'][$art['curpage']]['text'];
		$art['subtitle'] = $art['pagedetails'][$art['curpage']]['subtitle'];
		$art['pages'] = count($art['pagedetails']);
		$art['user'] = Users::getUser($art['user_id']);
		unset($art['user_id']);
		return $art;
	}

	/**
	 * add an article
	 *
	 * @return mixed false on failure, id on success
	 **/
	/* TODO: rewrite this!
	public static function addArticle()
	{
		$_db = $this->_db;
		$_tpl = $this->_tpl;

		try
		{
			$pages = array();
			foreach($_POST['pages'] as $page)
			{
				$page['subtitle'] = stripslashes($page['subtitle']);
				$page['text'] = Utils::prepbbc(stripslashes($page['text']));
				$pages[] = $page;
			}
			if(empty($_POST['title'])) throw new Exception('No title defined.');
			if(empty($_POST['cat_id'])) throw new Exception('No category defined.');
			// check if it exists at all?
			$res = $_db->query('
			INSERT INTO
				'.$_db->pref.'art_articles
			SET
				cat_id='.(int)$_POST['cat_id'].',
				title="'.$_POST['title'].'",
				text="'.$_db->escape(serialize($pages)).'",
				user_id='.(int)$_SESSION['uid'].',
				time='.time().',
				replacements='.(!empty($_POST['replacements']) ? 1 : 0).';');
			if(!$res) throw new Exception('The article could not be saved.');
			$_tpl->clear_cache_byobject('article');
			return $_db->insert_id;
		}
		catch(Exception $e)
		{
			$_tpl->assignLang('error', $e->getMessage());
			return false;
		}

	}*/

	/**
	 * edit the details of a specified article
	 *
	 * @param int $aArticleId Id of the article we want to edit
	 * @return bool
	 **/
	/* TODO: rewrite this!
	public static function editArticle($aArticleId)
	{
		$_db = $this->_db;
		$_tpl = $this->_tpl;

		try
		{
			$pages = array();
			foreach($_POST['pages'] as $page)
			{
				$page['subtitle'] = stripslashes($page['subtitle']);
				$page['text'] = Utils::prepbbc(stripslashes($page['text']));
				$pages[] = $page;
			}
			if(empty($_POST['title'])) throw new Exception('No title defined.');
			if(empty($_POST['cat_id'])) throw new Exception('No category defined.');
			// check if it exists at all?
			$res = $_db->query('
			UPDATE
				'.$_db->pref.'art_articles
			SET
				cat_id='.(int)$_POST['cat_id'].',
				title="'.$_POST['title'].'",
				text="'.$_db->escape(serialize($pages)).'",
				time='.time().',
				replacements='.(!empty($_POST['replacements']) ? 1 : 0).'
			WHERE
				art_id='.(int)$art_id.';');
			if(!$res) throw new Exception('The article could not be saved.');
			$_tpl->clear_cache_byobject('article');
			return true;
		}
		catch(Exception $e)
		{
			$_tpl->assignLang('error', $e->getMessage());
			return false;
		}
	}*/

	/**
	 * deletes an article
	 *
	 * @param int $aArticleId Id of the article we want to delete
	 * @return bool
	 **/
	public static function deleteArticle($aArticleId)
	{
		// delete the Db entries
		self::$_db->query('DELETE FROM '.self::$_db->pref.'art_articles WHERE art_id='.(int)$aArticleId.';');
		self::$_db->query('DELETE FROM '.self::$_db->pref.'art_pages WHERE art_id='.(int)$aArticleId.';');
		// delete the associations with tags
		Tags::deleteTagsForContent((int)$aArticleId, ContentType::ARTICLE);
		return true;
	}
}
?>