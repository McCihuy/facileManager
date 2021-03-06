<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2013 The facileManager Team                               |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | facileManager: Easy System Administration                               |
 +-------------------------------------------------------------------------+
 | http://www.facilemanager.com/                                           |
 +-------------------------------------------------------------------------+
 | Common AJAX processing functions                                        |
 | Author: Jon LaBass                                                      |
 +-------------------------------------------------------------------------+
*/

/**
 * Displays an error
 *
 * @since 1.0
 * @package facileManager
 */
function returnError($window = true) {
	$msg = _('There was a problem with your request.'); 
	if ($window) {
		echo buildPopup('header', 'Error');
		echo "<p>$msg</p>\n";
		echo buildPopup('footer', _('OK'), array('cancel_button' => 'cancel'));
	} else {
		echo '<p class="error">' . $msg . "</p>\n";
	}
	exit;
}


/**
 * Displays unauthorized message
 *
 * @since 1.0
 * @package facileManager
 */
function returnUnAuth($window = true) {
	$msg = _('You do not have permission to make these changes.');
	if ($window) {
		echo buildPopup('header', _('Error'));
		echo "<p>$msg</p>\n";
		echo buildPopup('footer', _('OK'), array('cancel_button' => 'cancel'));
	} else {
		echo '<p class="error">' . $msg . "</p>\n";
	}
	exit;
}

?>
