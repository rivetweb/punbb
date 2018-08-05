<?php

defined('_JEXEC') or die;

// forum includes
define('FORUM_ROOT', JPATH_ROOT . '/forum/');

class plgUserPunbb extends JPlugin {

	private $userCurrentData = null;

	protected function initForumData() {
		global $forum_config, $forum_db, $db_type,  
				$cookie_name, $cookie_path, $cookie_domain, $cookie_secure;

		require FORUM_ROOT . 'config.php';
		require_once FORUM_ROOT . 'include/dblayer/common_db.php';
		require_once FORUM_ROOT . 'include/functions.php';
		require FORUM_ROOT . 'cache/cache_config.php';
	}

	public function onUserAfterDelete($user, $succes, $msg) {
		/*
		global $forum_db, $db_type, $forum_config;
		
		ob_start();
		$this->initForumData();

		error_log('ON DELETE: do delete user from forum');
		error_log(print_r($user, true));
		error_log(print_r($options, true));

		delete_user($user['id']);
		
		ob_end_clean();
		*/
		
		return true;
	}

	public function onUserBeforeSave($user, $isnew) {
		// copy old data
		$this->userCurrentData = $user;
	}

	public function onUserAfterSave($user, $isnew, $success, $msg) {
		global $forum_config, $forum_db;

		//if (!$success) {
		//	return;
		//}

		ob_start();
		$this->initForumData();

		if ($isnew) {
			// create new user
			// code from /forum/register.php

			$language = 'Russian';
			$email1 = $user['email'];
			$username = $user['username'];
			$password1 = $user['password_clear'];
			$initial_group_id = $forum_config['o_default_user_group'];
			$salt = random_key(12);
			$password_hash = forum_hash($password1, $salt);
			$timezone = $forum_config['o_default_timezone'];
			// Validate timezone — on error use default value
			if (($timezone > 14.0) || ($timezone < -12.0)) {
				$timezone = $forum_config['o_default_timezone'];
			}
			// DST
			$dst = (isset($_POST['dst']) && intval($_POST['dst']) === 1) ? 1 : $forum_config['o_default_dst'];

			$user_info = array(
				'username' => $username,
				'group_id' => $initial_group_id,
				'salt' => $salt,
				'password' => $password1,
				'password_hash' => $password_hash,
				'email' => $email1,
				'email_setting' => $forum_config['o_default_email_setting'],
				'timezone' => $timezone,
				'dst' => $dst,
				'language' => $language,
				'style' => $forum_config['o_default_style'],
				'registered' => time(),
				'registration_ip' => get_remote_address(),
				'activate_key' => 'NULL',
				'require_verification' => false,
				'notify_admins' => false
			);
			add_user($user_info, $new_uid);
		}
		else {
			// update username, email, password
			// code from /forum/profile.php

			$fields = "
				username = '" . $forum_db->escape($user['username']) . "',
				email = '" . $forum_db->escape($user['email']) . "',
				activate_key = NULL";
			if ($user['password_clear'] != '') {
				$salt = random_key(12);
				$password_hash = forum_hash($user['password_clear'], $salt);
				$fields .= ",
						salt = '" . $forum_db->escape($salt) . "',
						password = '" . $forum_db->escape($password_hash) . "'";
			}

			$query = array(
				'UPDATE' => 'users',
				'SET' => $fields,
				'WHERE' => "username = '" . $forum_db->escape($this->userCurrentData['username']) . "'"
			);

			$forum_db->query_build($query);
		}

		ob_end_clean();
	}

	public function onUserLogin($user, $options = array()) {
		global $forum_config, $forum_db, 
				$cookie_name, $cookie_path, $cookie_domain, $cookie_secure;

		ob_start();
		$this->initForumData();

		$config = JFactory::getConfig();
		$site_session_lifetime = $config->get('lifetime') * 60;
		
		// login to forum
		// code from /forum/login.php
		
		$form_username = $user['username'];
		$form_password = $user['password'];
		$save_pass = $options['remember'];
		// Get user info matching login attempt
		$query = array(
			'SELECT' => 'u.id, u.group_id, u.password, u.salt',
			'FROM' => 'users AS u'
		);
		$query['WHERE'] = "username='" . $forum_db->escape($form_username) . "'";

		$result = $forum_db->query_build($query);
		if ($result) {
			list($user_id, $group_id, $db_password_hash, $salt) = $forum_db->fetch_row($result);
			if ($user_id) {				
				$form_password_hash = forum_hash($form_password, $salt);
				$expire = ($save_pass) ? time() + 1209600 : time() + $site_session_lifetime;
				forum_setcookie($cookie_name,
						base64_encode($user_id . '|' . $form_password_hash . '|' . $expire . '|' .
								sha1($salt . $form_password_hash . forum_hash($expire, $salt))), $expire);
			}
		}

		ob_end_clean();

		return true;
	}

	public function onUserLogout($user, $options = array()) {
		global $forum_config, $forum_db, 
				$cookie_name, $cookie_path, $cookie_domain, $cookie_secure;

		ob_start();
		$this->initForumData();

		// logout from forum
		// code from /forum/login.php

		$expire = time() + 1209600;
		forum_setcookie($cookie_name, base64_encode('1|'.random_key(8, false, true).'|'.$expire.'|'.random_key(8, false, true)), $expire);

		ob_end_clean();

		return true;
	}

}
