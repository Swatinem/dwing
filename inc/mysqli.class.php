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
 * extended MySQLi Class
 * 
 * Extends the mysqli class with a few useful functions
 **/
class extmysqli extends mysqli
{
	/**
	 * debug informations
	 *
	 * @var array $debugdata
	 **/
	private $debugdata;
	/**
	 * toggles debugging
	 *
	 * @var bool $debug
	 **/
	private $debug = false;
	/**
	 * the whole time taken for queries
	 *
	 * @var float $qrytime
	 **/
	private $qrytime = 0;
	/**
	 * are we allready connected or not?
	 *
	 * @var bool $connected
	 **/
	private $mConnected = false;
	/**
	 * mysql server
	 *
	 * @var bool $mHost
	 **/
	private $mHost;
	/**
	 * mysql user
	 *
	 * @var bool $mHost
	 **/
	private $mUser;
	/**
	 * mysql password
	 *
	 * @var bool $mHost
	 **/
	private $mPassword;
	/**
	 * mysql database
	 *
	 * @var bool $mHost
	 **/
	private $mDatabase;
	/**
	 * table prefix
	 *
	 * @var string $pref
	 **/
	public $pref;

	/**
	 * constructor
	 *
	 * @param string $aHost mysql server
	 * @param string $aUser mysql user
	 * @param string $aPassword mysql password
	 * @param string $aDatabase mysql database
	 * @param string $aPrefix table prefix
	 * @return void
	 **/
	public function __construct($aHost, $aUser, $aPassword, $aDatabase, $aPrefix)
	{
		$this->debug = !empty($GLOBALS['_debug']);
		$this->debugdata = array();
		$this->pref = $aPrefix;
		$this->mHost = $aHost;
		$this->mUser = $aUser;
		$this->mPassword = $aPassword;
		$this->mDatabase = $aDatabase;
	}

	/**
	 * prints out debug info
	 *
	 * @return void
	 **/
	public function printdebuginfo()
	{
		if(!$this->debug)
		{
			return; // do not print anything when not in debug mode
		}
		echo count($this->debugdata).' queries executed in '.$this->qrytime.' seconds'."\n";
		foreach($this->debugdata as $query)
		{
			echo "\n".'Query: '.$query['query']."\n".'Time: '.$query['time']."\n";
			if(!empty($query['explain']))
			{
				echo 'Explain:'."\n";
				echo 'Select Type	Table		Type	Possible Keys	Key	Key Len	Ref	Rows	Extra'."\n";
				foreach($query['explain'] as $explain)
				{
					echo $explain['select_type'].'		'.$explain['table'].'	'.$explain['type'].'	'.$explain['possible_keys'].
					'		'.$explain['key'].'	'.$explain['key_len'].'	'.$explain['ref'].'	'.$explain['rows'].'	'.
					$explain['Extra']."\n";
				}
			}
		}
	}


	/**
	 * connect to the database
	 *
	 * @return void
	 **/
	private function connectParent()
	{
		if(!$this->mConnected)
		{
			parent::__construct($this->mHost, $this->mUser, $this->mPassword, $this->mDatabase);
			$this->set_charset('utf8');
			$this->mConnected = true;
		}
	}

	/**
	 * executes a query
	 *
	 * @param string $query
	 * @return object
	 **/
	public function query($query)
	{
		$this->connectParent();
		if($this->debug)
		{
			$time = new bench;
		}
		$res = parent::query($query);
		if($this->error)
		{
			echo '<br /><strong>Error in query:</strong><br />'.htmlspecialchars($query).'<br />
			<strong>MySQL says</strong><br />
			'.$this->error.'<br />';
		}
		if($this->debug)
		{
			$time = $time->gettime();
			$this->qrytime+= $time;
			if(isset($_GET['explain']) && strpos($query, 'SELECT') !== false)
			{
				$expl_res = parent::query('EXPLAIN '.$query);
				$expl_arr = array();
				while($expl_row = $expl_res->fetch_assoc())
				{
					$expl_arr[] = $expl_row;
				}
			}
			$this->debugdata[] = array('query' => $query, 'time' => $time, 'explain' => !empty($expl_arr) ? $expl_arr : '');
		}
		return $res;
	}

	/**
	 * executes a query and returns the first row
	 *
	 * @param string $query
	 * @return array
	 **/
	public function queryfirst($query)
	{
		$res = $this->query($query);
		$ret = $res->fetch_assoc();
		$res->close();
		return $ret;
	}

	/**
	 * executes a query and returns the first row
	 *
	 * @param string $query
	 * @return array
	 **/
	public function queryAll($query)
	{
		$res = $this->query($query);
		$ret = array();
		while($ret[] = $res->fetch_assoc());
		array_pop($ret); // last element is always null
		$res->close();
		return $ret;
	}

	/**
	 * returns the debug data
	 *
	 * @return array
	 **/
	public function getdebugdata()
	{
		return $this->debugdata;
	}

	/**
	 * returns the query time
	 *
	 * @return float
	 **/
	public function getqrytime()
	{
		return $this->qrytime;
	}
	/**
	 * wrapper for real_escape_string
	 *
	 * @param string $str
	 * @return string
	 **/
	public function escape($str)
	{
		$this->connectParent(); // we need a connection to get the charset correct
		return $this->real_escape_string($str);
	}
}
?>