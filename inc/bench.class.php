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
 * Benchmark class
 * 
 * This class provides functions for benchmarking
 */
class bench
{
	/**
	 * the start timestamp
	 * 
	 * @var float $start
	 **/
	private $start;
	/**
	 * the time elapsed
	 * 
	 * @var float $time
	 **/
	private $time;

	/**
	 * constructor, starts benchmark automatically
	 * 
	 * @return void
	 **/
	public function __construct()
	{
		$this->start();
	}

	/**
	 * starts the benchmark, this gets called on construct
	 * 
	 * @return void
	 **/
	public function start()
	{
		$timetemp = explode(' ',microtime());
		$this->start = $timetemp[0]+$timetemp[1];
	}

	/**
	 * ends the benchmark
	 * 
	 * @return void
	 **/
	public function end()
	{
		$timetemp = explode(' ',microtime());
		$this->time = $timetemp[0]+$timetemp[1]-$this->start;
	}

	/**
	 * returns the seconds elapsed as a string
	 * 
	 * @return string
	 **/
	public function __toString()
	{
		if(empty($time)) $this->end();
		return 'seconds elapsed: '.$this->time;
	}

	/**
	 * returns the seconds elapsed as a float
	 * 
	 * @return float
	 **/
	public function gettime()
	{
		if(empty($time)) $this->end();
		return $this->time;
	}
}
?>