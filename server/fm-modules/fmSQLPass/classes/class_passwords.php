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
 | fmSQLPass: Change database user passwords across multiple servers.      |
 +-------------------------------------------------------------------------+
 | http://www.facilemanager.com/modules/fmsqlpass/                         |
 +-------------------------------------------------------------------------+
*/

class fm_sqlpass_passwords {
	
	/**
	 * Displays the server group list
	 */
	function rows($result) {
		global $fmdb, $__FM_CONFIG, $fm_users;
		
		if (!$result) {
			echo '<p id="table_edits" class="noresult" name="servers">There are no active database server groups.</p>';
		} else {
			$num_rows = $fmdb->num_rows;
			$results = $fmdb->last_result;

			$table_info = array(
							'class' => 'display_results',
							'id' => 'table_edits'
						);

			$title_array = array(array(
								'title' => '<input type="checkbox" class="tickall" onClick="toggle(this, \'group[]\')" checked />',
								'class' => 'header-tiny'
							), 'Server Group', 'Last Changed');

			echo displayTableHeader($table_info, $title_array);
			echo '<form name="manage" id="manage" method="post" action="' . $GLOBALS['basename'] . '">' . "\n";
			
			for ($x=0; $x<$num_rows; $x++) {
				$this->displayRow($results[$x]);
			}
			
			echo "</tbody>\n</table>\n";
		}

		if (currentUserCan('manage_passwords', $_SESSION['module']) && $result) {
			echo <<<HTML
				<br /><br />
				<a name="#manage"></a>
				<input type="hidden" name="item_type" value="set_mysql_password" />
				<h2>Set User Password</h2>
			
HTML;
			if (!$pwd_strength = getOption('minimum_pwd_strength', $_SESSION['user']['account_id'], $_SESSION['module'])) $pwd_strength = $GLOBALS['PWD_STRENGTH'];
			echo $fm_users->printUsersForm(null, 'add', array('user_login', 'user_password' => $pwd_strength, 'verbose'), 'Set Password', 'set_sql_password', 'config-passwords', false, 'embed');
			echo '<div><input type="submit" id="set_sql_password" name="submit" value="Set Password" class="button primary" disabled /></div>';
			echo '</form>';
		}
	}

	function displayRow($row) {
		global $__FM_CONFIG;
		
		$last_changed = isset($row->group_pwd_change) ? date("m/d/Y H:i:s T", $row->group_pwd_change) : 'Never';
		
		echo <<<HTML
		<tr id="$row->group_id">
			<td><input type="checkbox" name="group[]" value="{$row->group_id}" checked /></td>
			<td>{$row->group_name}</td>
			<td>$last_changed</td>
		</tr>
HTML;
	}
	
	
	function setPassword() {
		global $fmdb, $__FM_CONFIG;
		
		sleep(1);
		extract($_POST);
		$error = $verbose_output = null;
		
		/** Initial error checking */
		if (!isset($group)) $error = 'No groups are selected.'; 
		if ($user_password != $cpassword) $error = 'Passwords do not match.'; 
		if (empty($user_password)) $error = 'No password is defined.'; 
		if (empty($user_login)) $error = 'No user login is defined.'; 
		
		if (!currentUserCan('manage_passwords', $_SESSION['module'])) $error = 'You do not have permission to perform this task.';
		
		if ($error) {
			return (!$verbose) ? '<p class="error">' . $error . '</p>' : $error;
		}
		
		/** Get default credentials */
		$default_admin_user = getOption('admin_username', $_SESSION['user']['account_id'], $_SESSION['module']);
		$default_admin_pass = getOption('admin_password', $_SESSION['user']['account_id'], $_SESSION['module']);
		
		/** Process password change */
		foreach ($group as $key => $server_group) {
			$verbose_output .= "Processing servers in group '" . getNameFromID($server_group, 'fm_' . $__FM_CONFIG['fmSQLPass']['prefix'] . 'groups', 'group_', 'group_id', 'group_name') . "':\n";
			$verbose_output .= str_repeat('=', 40) . "\n";
			basicGetList('fm_' . $__FM_CONFIG['fmSQLPass']['prefix'] . 'servers', 'server_name', 'server_', "AND server_groups=$server_group");
			$server_count = $fmdb->num_rows;
			if (!$server_count) $verbose_output .= "There are no database servers in this group.\n\n";
			$server_result = $fmdb->last_result;
			for ($i=0; $i<$server_count; $i++) {
				extract(get_object_vars($server_result[$i]));
				
				/** Get server credentials */
				list($admin_user, $admin_pass) = unserialize($server_credentials);
				if (empty($admin_user)) $admin_user = $default_admin_user;
				if (empty($admin_pass)) $admin_pass = $default_admin_pass;
				
				$verbose_output .= "$server_name\n";
				
				/** Change the user password */
				$passwd_function = 'change' . $server_type . 'UserPassword';
				if (function_exists($passwd_function)) {
					$verbose_output .= $passwd_function($server_name, $server_port, $admin_user, $admin_pass, str_replace(array('"', "'"), '', $user_login), $user_password, $server_group);
				} else {
					$verbose_output .= " --> Database server type '$server_type' is not currently supported.\n";
				}
				
				$verbose_output .= "\n";
			}
			$verbose_output .= "\nPassword change complete.\n";
		}
		
		if (!$verbose) {
			$return = strpos($verbose_output, '[failed] -') ? '<p class="error">One or more errors occurred during the password change.</p>' : '<p>Password has been changed.</p>';
		} else {
			$return = $verbose_output;
		}
		
		return $return;
	}
	
	
}

if (!isset($fm_sqlpass_passwords))
	$fm_sqlpass_passwords = new fm_sqlpass_passwords();

?>
