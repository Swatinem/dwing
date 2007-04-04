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
 * Updater
 *
 * updater to change the db layout when necessary
 */
class Updater extends Module
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
	 * constructor
	 *
	 * @param string $aCurrentVersion
	 * @return void
	 **/
	public function __construct()
	{
		$this->_versions = array('0.0.1', '0.0.2', '0.0.3', '0.0.4', '0.0.5');
		$this->_langs = array('en', 'de');
		$this->version = $this->_versions[count($this->_versions)-1];
		$this->oldversion = self::$_cfg['version'];
	}

	/**
	 * update the language files
	 *
	 * @return void
	 **/
	private function _updatelang()
	{
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
			else // language already exists
			{
				$langtbl = l10n::mergeLang($lang, $lang.'_inst');
				file_put_contents('./lang/'.$lang.'.php', "<?php\n\$__LANG = ".var_export($langtbl, true).";\n?>");
			}
		}
	}

	/**
	 * save the config with the new version
	 *
	 * @param string $aVersion the version string to write to the settings
	 *  uses the latest version if empty
	 * @return void
	 **/
	public function updatecfg($aVersion = null)
	{
		self::$_cfg['version'] = !empty($aVersion) ? $aVersion : $this->version;
		file_put_contents('inc/settings.php',"<?php\n\$_cfg = ".var_export(self::$_cfg, true).";\n?>");
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
	 * automatically do all updates we need
	 *
	 * @return bool
	 **/
	public function update()
	{
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
	 * Update from x.y.z to x.y.z
	 * 
	 * @return bool
	 **/
	/* This is just an example
	private function _xyztoxyz()
	{
		return true;
	}*/
}
?>