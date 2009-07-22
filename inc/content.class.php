<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2009 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * TODO: waiting for PHP5.3, figure out how to best work around the single
 * inheritance problem PHP has.
 * Ideally, we would just inherit from multiple abstract base classes. But
 * sadly, this ain't C++ :(
 */

/**
 * This is a ContentItem, which has a unique ContentType.
 * TODO: Until we require PHP5.3, use
 * call_user_func(array($ClassNameOrObject, 'ContentType')); to get the
 * ContentType
 */
interface ContentItem
{
	/**
	 * Returns the ContentType of this ContentItem
	 */
	public static function ContentType();
}

/**
 * Abstract Base implementation of ContentItem
 * TODO: With PHP5.3 this will use late static binding to move the
 * implementation into the base class. Until then, reimplement the method
 */
abstract class ContentItemBase implements ContentItem
{
	/**
	 * Until we require PHP5.3, just reimplement this method, returning the
	 * correct ContentType
	 */
	public static function ContentType()
	{
		return 0;
	}
}

/**
 * A ContentProvider that provides a single object for related ContentItems
 */
interface ContentProviderSingle
{
	/**
	 * This returns the single Object related to $aItem
	 */
	public static function getFor(ContentItem $aItem);
	/**
	 * This deletes the single Object related to $aItem
	 * Uses a new transaction when $aUseTransaction is true
	 */
	public static function deleteFor(ContentItem $aItem, $aUseTransaction = false);
}

/**
 * A ContentProvider that provides multiple object for related ContentItems
 */
interface ContentProvider
{
	/**
	 * This adds multiple objects $aItems to $aItem.
	 * Uses a new transaction when $aUseTransaction is true
	 * returns the added Items
	 */
	public static function addAllFor(ContentItem $aItem, $aItems, $aUseTransaction = false);
	/**
	 * This returns either an Array or an Object implementing Iterator and
	 * Countable which includes Objects related to $aItem
	 */
	public static function getAllFor(ContentItem $aItem);
	/**
	 * This deletes all Objects related to $aItem
	 * Uses a new transaction when $aUseTransaction is true
	 */
	public static function deleteAllFor(ContentItem $aItem, $aUseTransaction = false);
}

?>
