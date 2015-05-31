<?php

// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
// Copyright 2015 Paul Haggart
// based on Auth_POP3 by David Buchmann and Auth_LDAP by Ryan Lane

// Add these lines to the bottom of your LocalSettings.php
// require_once('extensions/Auth_SeAT.php');
// $wgAuth_Config['api_user'] = 'mediawiki_auth';
// $wgAuth_Config['api_pass'] = 'mypassword';
// $wgAuth_Config['api_url'] = 'http://myseatapiurl/api/v1/authenticate';
// $wgAuth = new Auth_SeAT($wgAuth_Config);
 
if (!defined('MEDIAWIKI')) exit;

require_once('includes/AuthPlugin.php');

class Auth_SeAT extends AuthPlugin {

	private $api_url;
	private $api_user;
	private $api_pass;

	public function _construct($config) {

		$this->api_url = $config['api_url']
		$this->api_user = $config['api_user'];
		$this->api_pass = $config['api_pass'];
	}

	/**
	* Attempt to authenticate the user via SeAT API
	*
	* @param $username String: username.
	* @param $password String: user password.
	* @return bool
	* @public
	*/
	public function authenticate($user, $pass) {

		$curl = curl_init($this->api_url);
		
		curl_setopt($curl, CURLOPT_USERNAME, $this->api_user);
		curl_setopt($curl, CURLOPT_PASSWORD, $this->api_pass);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		
		$post_data = array(
			"username" => $user,
			"password" => $pass
		);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		
		$curl_response = curl_exec($curl);
		
		if (curl_errno($curl)) {
			die("CURL error: ". curl_error($curl));
		}

		curl_close($curl);

		$auth = json_decode($curl_response);

		if ($err = json_last_error()) {
			die("JSON decode error from SeAT: {$err}");
		}

		if ($auth['error'] === true) {
			return false;
		}

		return true;
	}

	/**
	* When creating a user account, optionally fill in preferences and such.
	* For instance, you might pull the email address or real name from the
	* external user database.
	*
	* @param $user User object.
	* @public
	*/
	public function initUser(&$user) {

		$username = $_REQUEST['wpName'];

		// Using your own methods put the users real name here.
		$user->setRealName('');
		
		// Using your own methods put the users email here.
		$user->setEmail('');

		$user->mEmailAuthenticated = wfTimestampNow();
		$user->setToken();

		// turn off e-mail notifications by default
		$user->setOption('enotifwatchlistpages', 0);
		$user->setOption('enotifusertalkpages', 0);
		$user->setOption('enotifminoredits', 0);
		$user->setOption('enotifrevealaddr', 0);

		$user->saveSettings();
	}

	/**
	* Modify options in the login template.  This shouldn't be very important
	* because no one should really be bothering with the login page.
	*
	* @param $template UserLoginTemplate object.
	* @public
	*/
	public function modifyUITemplate(&$template) {
		$template->set('useemail', false);
		$template->set('create', false);
		$template->set('domain', false);
		$template->set('usedomain', false);
	}

	/**
	* Normalize user names to the mediawiki standard to prevent duplicate
	* accounts.
	*
	* @param $username String: username.
	* @return string
	* @public
	*/
	public function getCanonicalName($username) {
		// uppercase first letter to make mediawiki happy
		return ucfirst(strtolower($username));
	}

	/**
	* Disallow password change.
	*
	* @return bool
	*/
	public function allowPasswordChange() {
		return false;
	}

	/**
	* This should not be called because we do not allow password change.  Always
	* fail by returning false.
	*
	* @param $user User object.
	* @param $password String: password.
	* @return bool
	* @public
	*/
	public function setPassword($user, $password) {
		return false;
	}

	/**
	* We don't support this but we have to return true for preferences to save.
	*
	* @param $user User object.
	* @return bool
	* @public
	*/
	public function updateExternalDB($user) {
		return true;
	}

	/**
	* We can't create external accounts so return false.
	*
	* @return bool
	* @public
	*/
	public function canCreateAccounts() {
		return false;
	}

	/**
	* We don't support adding users
	*
	* @param User $user
	* @param string $password
	* @return bool
	* @public
	*/
	public function addUser($user, $password) {
		return false;
	}


	/**
	* Pretend all users exist.  This is checked by authenticateUserData to
	* determine if a user exists in our 'db'.  By returning true we tell it that
	* it can create a local wiki user automatically.
	*
	* @param $username String: username.
	* @return bool
	* @public
	*/
	public function userExists($username) {
		return true;
	}


	/**
	* Check to see if the specific domain is a valid domain.
	*
	* @param $domain String: authentication domain.
	* @return bool
	* @public
	*/
	public function validDomain($domain) {
		return true;
	}

	/**
	* When a user logs in, optionally fill in preferences and such.
	* For instance, you might pull the email address or real name from the
	* external user database.
	*
	* The User object is passed by reference so it can be modified; don't
	* forget the & on your function declaration.
	*
	* @param User $user
	* @public
	*/
	public function updateUser(&$user) {
		return true;
	}

	/**
	* Return true because the wiki should create a new local account
	* automatically when asked to login a user who doesn't exist locally but
	* does in the external auth database.
	*
	* @return bool
	* @public
	*/
	public function autoCreate() {
		return true;
	}

	/**
	* Return true to prevent logins that don't authenticate here from being
	* checked against the local database's password fields.
	*
	* @return bool
	* @public
	*/
	public function strict() {
		return false;
	}
}

// BLOOD FOR THE BLOOD GOD
// ORE FOR THE ORE THRONE
// MILK FOR THE KHORNE FLAKES

