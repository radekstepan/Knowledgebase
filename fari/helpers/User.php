<?php if (!defined('FARI')) die();

/**
 * User authentication.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_User {
	
	/**
	 * Session storage namespace
	 * @var const
	 */
	const SESSION_CREDENTIALS_STORAGE = 'Fari\User\Credentials';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'User authentication'; }
	
	/**
	 * Authenticate user (input will be escaped).
	 * @uses 'username', 'password'(sha1) in 'users' table
	 *
	 * @param string $username Username passed from a form
	 * @param string $password Password passed from a form
	 * @param string $token Token passed from a form
	 * @param string $credentialsColumn Optionally specify which column to use for credentials
	 * @return void
	 */
	public static function authenticate($username, $password, $token, $credentialsColumn='username') {
		// if credentials provided and token is valid
		if (isset($username, $password)
		    && (Fari_Token::isValid($token))) {
			// escape input, add slashes and encrypt
			$username = Fari_Escape::text($username);
			$password = self::_encrypt(Fari_Escape::text($password));
			
			// select a matching row from a table
			$whereClause = array('username' => $username, 'password' => $password);
			$user = Fari_Db::selectRow('users', $credentialsColumn, $whereClause);
			
			// user id is set
			if (isset($user[$credentialsColumn])) {
				// create and set credentials string
				$_SESSION[self::SESSION_CREDENTIALS_STORAGE] = $user[$credentialsColumn];
				unset($user);
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Check if user is authenticated.
	 *
	 * @param string $credentialsColumn Optionally specify which column to use for credentials
	 * @return boolean TRUE if user is authenticated, otherwise FALSE
	 */
	public static function isAuthenticated($credentialsColumn='username') {
		@$unsafe = self::getCredentials();
		// are credentials set in a session?
		if (isset($unsafe)) {
			//escape input
			$credentials = Fari_Escape::text($unsafe);
			// select a matching row from a table
			$whereClause = array($credentialsColumn => $credentials);
			$user = Fari_Db::selectRow('users', '*', $whereClause);
			if (isset($user[$credentialsColumn])) {
				// credentials match, user is authenticated
				unset($user);
				return TRUE;
			}
		}
		// no credentials in the session
		return FALSE;
	}
	
	/**
	 * Get credentials saved in a session.
	 *
	 * @return string Credentials stored during authentication
	 */
	public static function getCredentials() {
		return $_SESSION[self::SESSION_CREDENTIALS_STORAGE];
	}
	
	/**
	 * Check if user is in a specified role.
	 * Method is_authenticated() should have been called at this point.
	 * @uses 'role' in 'users' table
	 *
	 * @param string $userRole (e.g., admin)
	 * @param string $credentials Optionally specify which column to use for credentials
	 * @return boolean TRUE if user is in a role
	 */
	public static function isInRole($userRole, $credentialsColumn='username') {
		@$unsafe = self::getCredentials();
		// get credentials string
		if (isset($unsafe)) {
			//escape input
			$credentials = Fari_Escape::text($unsafe);
			// select a matching row from a table
			$whereClause = array($credentialsColumn => $credentials);
			$user = Fari_Db::selectRow('users', 'role', $whereClause);
			// check that user satisfies a role
			if ($user['role'] === $userRole) {
				unset($user);
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Sign out user from the system.
	 *
	 * @return void
	 */
	public static function signOut() {
		// sign out by destroying the session credentials
		unset($_SESSION[self::SESSION_CREDENTIALS_STORAGE]);
	}
	
	/**
	 * Encrypt input string.
	 *
	 * @param string $input String to encrypt
	 */
	private static function _encrypt($input) {
		// use 'SHA-1
		return sha1($input);
	}
	
}