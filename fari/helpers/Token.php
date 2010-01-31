<?php if (!defined('FARI')) die();

/**
 * A helper creating and checking a token from a form to try to prevent Cross-Site Request Forgeries.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Token {
	
	/**
	 * Session storage namespace
	 * @var const
	 */
	const SESSION_STORAGE = 'Fari\Token\\';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Create and check form token'; }
	
	/**
	 * Create a token for a form of ours. Will be saved in session.
	 *
	 * @param string $name Optional name of session variable
	 * @return string Token
	 */
	public static function create($name='Default') {
		// create a token
		if (!isset($_SESSION[self::SESSION_STORAGE . $name])) {
			// store it in a session
			$_SESSION[self::SESSION_STORAGE . $name] = md5(uniqid(rand(), TRUE));
		}
		// return it so that we can store it in a hidden form param
		return $_SESSION[self::SESSION_STORAGE . $name];
	}
	
	/**
	 * Check that a form token is valid
	 *
	 * @param string $unsafeAnswer Unsafe form token to check
	 * @param string $name Optional name of session variable where token is stored
	 * @return boolean TRUE if token and answer match, FALSE otherwise
	 */
	public static function isValid($unsafeAnswer, $name='Default') {
		// escape unsafe token input
		$unsafeAnswer = Fari_Escape::text($unsafeAnswer);
		
		// check if token is valid
		return ($unsafeAnswer == $_SESSION[self::SESSION_STORAGE . $name]) ? TRUE : FALSE;
	}
	
}