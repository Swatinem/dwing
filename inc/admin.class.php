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
 * Admin module
 *
 * A few helper functions only used in the admin
 */
class Admin extends Module
{
	/**
	 * prints a failed xml result with the error string
	 *
	 * @param string $aRight the right for which to check
	 * @return void
	 **/
	public static function checkRight($aRight)
	{
		if(!self::$_user->authed)
		{
			echo '
<results>
	<result success="0">'.l10n::_('Not logged in.').'</result>
</results>';
			exit;
		}
		elseif(!self::$_user->hasright($aRight))
		{
			echo '
<results>
	<result success="0">'.l10n::_('You are not allowed to access this site.').'</result>
</results>';
			exit;
		}
	}
}
?>