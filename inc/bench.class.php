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
		$this->start = microtime(true);
	}

	/**
	 * ends the benchmark
	 * 
	 * @return void
	 **/
	public function end()
	{
		$this->time = microtime(true)-$this->start;
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