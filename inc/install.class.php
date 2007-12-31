<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2004-2008 Arpad Borsos <arpad.borsos@googlemail.com>
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

/*
Rewrite the complete Install/Update System...
Per Module Installs/Revisions would be nice...
And dependencies and so on...
*/

/**
 * Install class
 *
 * This class handles the install and update process
 */
class install
{
	/**
	 * the new version
	 *
	 * @var string $version
	 **/
	public $version;
	/**
	 * the old version
	 *
	 * @var string $oldversion
	 **/
	public $oldversion;
	/**
	 * the _cfg cache
	 *
	 * @var array $_cfg
	 **/
	private $_cfg;
	/**
	 * all available versions
	 *
	 * @var array $_versions
	 **/
	private $_versions;
	/**
	 * all the available languages
	 *
	 * @var array $_langs
	 **/
	private $_langs;

	/**
	 * constructor fills out the _versions attribute
	 *
	 * @return void
	 **/
	public function __construct()
	{
		$this->_versions = array('0.0.1', '0.0.2', '0.0.3', '0.0.4', '0.0.5');
		$this->_langs = array('en', 'de');
		$this->version = $this->_versions[count($this->_versions)-1];
		// default config for install
		$this->_cfg = array(
			'default_theme' => 'default',
			'default_admintheme' => 'default',
		);
	}

	/**
	 * decide what to do. install or update?
	 *
	 * @return string
	 **/
	public function getinstallmethod()
	{
		if(!file_exists('inc/settings.php'))
		{
			$this->_installlang(); //  install the languages prior to install if possible
			return 'install';
		}
		require_once('inc/settings.php');
		$this->oldversion = $_cfg['version'];
		$this->_cfg = $_cfg;
		unset($_cfg);
		if(version_compare($this->version,$this->oldversion) == 1)
		{
			$this->_updatelang();
			return 'update';
		}
		return 'none';
	}

	/**
	 * update the language files
	 *
	 * @return void
	 **/
	private function _updatelang()
	{
		$_tpl = $GLOBALS['_tpl'];
		require_once('./inc/translation.class.php');
		foreach($this->_langs as $lang)
		{
			if(!file_exists('./lang/'.$lang.'.php')) // new language
			{
				copy('./lang/'.$lang.'_inst.php','./lang/'.$lang.'.php');
			}
			elseif(!file_exists('./lang/'.$lang.'_inst.php'))
			{
				continue;
			}
			else // language allready exists
			{
				$langtbl = l10n::mergeLang($lang, $lang.'_inst');
				file_put_contents('./lang/'.$lang.'.php', "<?php\n\$__LANG = ".var_export($langtbl, true).";\n?>");
				//unlink('./lang/'.$lang.'_inst.php');
			}
		}
	}

	/**
	 * install the language files
	 *
	 * @return void
	 **/
	private function _installlang()
	{
		if(file_exists('./lang/'.$this->_langs[0].'.php') || !is_writable('./lang'))
		{
			return; // language allready installed
		}
		foreach($this->_langs as $lang)
		{
			copy('./lang/'.$lang.'_inst.php','./lang/'.$lang.'.php');
		}
	}

	/**
	 * save the config with the new version
	 *
	 * @return void
	 **/
	public function updatecfg($version = null)
	{
		$this->_cfg['version'] = !empty($version) ? $version : $this->version;
		file_put_contents('inc/settings.php',"<?php\n\$_cfg = ".var_export($this->_cfg, true).";\n?>");
	}

	/**
	 * converts a version number to a int
	 *
	 * @param string $version
	 * @return int
	 **/
	private function _intversion($version)
	{
		$version = explode('.',$version);
		return 10000*(int)$version[0]+100*(int)$version[1]+(int)$version[2];
	}

	/**
	 * Update from 0.0.1 to 0.0.2
	 * 
	 * @return bool
	 **/
	/* This is just an example
	private function _1to2()
	{
		return true;
	}*/

	/**
	 * Update from 0.0.1 to 0.0.2
	 * 
	 * @return bool
	 **/
	private function _1to2()
	{
		// add ratings table
		$GLOBALS['_db']->query('CREATE TABLE `'.$GLOBALS['_db']->pref.'ratings` (
			`user_id` INT UNSIGNED NOT NULL ,
			`content_id` INT UNSIGNED NOT NULL ,
			`content_type` TINYINT UNSIGNED NOT NULL ,
			`rating` TINYINT UNSIGNED NOT NULL ,
			PRIMARY KEY ( `user_id` , `content_id` , `content_type` )
			) ENGINE = innodb;');
		return true;
	}

	/**
	 * Update from 0.0.2 to 0.0.3
	 * 
	 * @return bool
	 **/
	private function _2to3()
	{
		$newsRes = $GLOBALS['_db']->query('
			SELECT news_id, text, COUNT(links.link_id) as linknum
			FROM '.$GLOBALS['_db']->pref.'news_news
			LEFT JOIN '.$GLOBALS['_db']->pref.'news_links AS links USING (news_id)
			GROUP BY news_id HAVING linknum > 0');
		while($news = $newsRes->fetch_assoc())
		{
			// hardcode german...
			$text = $news['text'].'<br /><br /><strong>Links zu dem Thema:</strong><ul>';
			$linksRes = $GLOBALS['_db']->query('SELECT * FROM '.$GLOBALS['_db']->pref.'news_links WHERE news_id='.(int)$news['news_id'].';');
			while($link = $linksRes->fetch_assoc())
			{
				$text.= '<li><a href="'.$link['url'].'">'.$link['name'].'</a></li>';
			}
			$text.= '</ul>';
			$GLOBALS['_db']->query('
				UPDATE '.$GLOBALS['_db']->pref.'news_news
				SET text="'.$GLOBALS['_db']->escape($text).'"
				WHERE news_id='.(int)$news['news_id'].';');
		}
		// drop the links table
		$GLOBALS['_db']->query('DROP TABLE `'.$GLOBALS['_db']->pref.'news_links`;');
		// rename the news table
		$GLOBALS['_db']->query('RENAME TABLE `'.$GLOBALS['_db']->pref.'news_news`  TO `'.$GLOBALS['_db']->pref.'news`;');
		return true;
	}

	/**
	 * Update from 0.0.3 to 0.0.4
	 * 
	 * @return bool
	 **/
	private function _3to4()
	{
		include_once('inc/modulesystem.class.php');
		include_once('inc/utils.class.php');
		$GLOBALS['_db']->query('ALTER TABLE `'.$GLOBALS['_db']->pref.'news` ADD `fancyurl` VARCHAR( 128 ) NOT NULL;');

		$cleanTitles = array();
		$newsRes = $GLOBALS['_db']->query('SELECT news_id, title FROM dw_news;');
		while($news = $newsRes->fetch_assoc())
		{
			$cleanTitle = Utils::fancyUrl($news['title']);
			if(in_array($cleanTitle, $cleanTitles))
				$cleanTitle.= '-'.$news['news_id'];
			$cleanTitles[] = $cleanTitle;
			$GLOBALS['_db']->query('
				UPDATE `'.$GLOBALS['_db']->pref.'news` SET fancyurl="'.$cleanTitle.'"
				WHERE news_id='.(int)$news['news_id'].';');
		}
		$GLOBALS['_db']->query('ALTER TABLE `'.$GLOBALS['_db']->pref.'news` ADD UNIQUE `fancyurl` ( `fancyurl` );');
		return true;
	}

	/**
	 * Update from 0.0.4 to 0.0.5
	 * 
	 * @return bool
	 **/
	private function _4to5()
	{
		// add comments table
		$GLOBALS['_db']->query('CREATE TABLE `'.$GLOBALS['_db']->pref.'comments` (
			`comment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`user_id` INT UNSIGNED NOT NULL ,
			`time` INT UNSIGNED NOT NULL ,
			`text` TEXT NOT NULL ,
			`content_id` INT UNSIGNED NOT NULL ,
			`content_type` TINYINT UNSIGNED NOT NULL ,
			INDEX ( `content_id` , `content_type` )
			) ENGINE = innodb;');
		$GLOBALS['_db']->query('ALTER TABLE `'.$GLOBALS['_db']->pref.'comments` ADD INDEX ( `user_id` );');
		return true;
	}

	/**
	 * automatically do all updates we need
	 *
	 * @return bool
	 **/
	public function update()
	{
		$this->_initdb();

		for($i = array_search($this->oldversion,$this->_versions), $max = count($this->_versions)-1; $i < $max; $i++)
		{
			$fromversion = $this->_versions[$i];
			$toversion = $this->_versions[$i+1];
			$methodname = '_'.$this->_intversion($fromversion).'to'.$this->_intversion($toversion);
			if(method_exists($this, $methodname))
			{
				if(!call_user_func(array($this,$methodname)))
				{
					// we successfully passed older updates so lets at least write that version to settings.
					$this->updatecfg($fromversion);
					return false;
				}
			}
		}
		// all updates done, write the new version to the settings
		$this->updatecfg();
		return true;
	}

	/**
	 * see if the entered db information are correct
	 *
	 * @return bool
	 **/
	public function trydb()
	{
		$_db = @mysqli_connect($_POST['server'], $_POST['user'], $_POST['password'], $_POST['database']);
		if(!$_db)
		{
			return false;
		}
		return true;
	}

	/**
	 * write the database init to the config.php
	 *
	 * @return void
	 **/
	public function writeconfig()
	{
		file_put_contents('inc/config.php',"<?php\n\$_db = new extmysqli('".addslashes($_POST['server'])."','".addslashes($_POST['user'])."','".addslashes($_POST['password'])."','".addslashes($_POST['database'])."','".addslashes($_POST['prefix'])."');\n?>");
	}

	/**
	 * insert the root admin
	 *
	 * @return bool
	 **/
	public function mkadmin()
	{
		$this->_initdb();
		$_db = $GLOBALS['_db'];
		$_tpl = $GLOBALS['_tpl'];
		require_once('inc/users.class.php');
		if($user_id = $_users->adduser())
		{
			$_users->changeusersgroup($user_id,3);
			return true;
		}
		return false;
	}

	/**
	 * initializes the database connection
	 *
	 * @return bool
	 **/
	private function _initdb()
	{
		if(empty($GLOBALS['_db']))
		{
			require_once('inc/mysqli.class.php');
			require_once('inc/config.php');
			$GLOBALS['_db'] = $_db;
		}
	}

	/**
	 * check for requirements (write permission, modules)
	 *
	 * @return array
	 **/
	public function check_requirements()
	{
		$reqs = array();
		$reqs['inc'] = (is_writable('./inc')) ? true : false;
		$reqs['pictures'] = (is_writable('./images/pictures')) ? true : false;
		$reqs['thumbs'] = (is_writable('./images/thumbs')) ? true : false;
		$reqs['mysqli'] = (class_exists('mysqli')) ? ((mysqli_get_client_version() >= 50006) ? mysqli_get_client_info() : false) : false;
		$reqs['all'] = (in_array(false,$reqs)) ? false : true;
		return $reqs;
	}

	/**
	 * installs the database tables and standard values
	 *
	 * @return bool
	 **/
	/* TODO: Rewrite!
	public function installdb()
	{
	}*/
}
?>