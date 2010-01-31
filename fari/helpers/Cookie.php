<?php if (!defined('FARI')) die();

/**
 * Set, get and delete cookies.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Cookie {
	
	/**
	 * Cookie storage namespace
	 * @var const
	 */
	const COOKIE_STORAGE = 'Fari\Cookie\\';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Manages cookies'; }
	
        /**
         * Set a cookie under our namespace.
         *
         * @param string $name Name of the cookie we want to save it under
         * @param string $value Value we want to set
         * @param int $expiry Expiry in seconds from now
         * @return boolean FALSE if cookie set unsuccesfuly
         */
        public static function set($name, $value, $expiry) {
                // check we have data
                if (isset($name)
		    && isset($value)
		    && Fari_Filter::isInt($expiry)) {
                        setcookie(self::COOKIE_STORAGE . $name, $value, time() + $expiry);
                } else return FALSE;
        }
        
        /**
         * Retrieve a cookie from the storage.
         *
         * @param string $name Name of the cookie we want to save it under
         * @return string Returned value of the cookie
         */
        public static function get($name) {
                if (isset($_COOKIE[self::COOKIE_STORAGE . $name])) return $_COOKIE[self::COOKIE_STORAGE . $name];
        }
        
        /**
         * Delete a cookie from the storage.
         *
         * @param string $name Name of the cookie we want to save it under
         * @return bollean FALSE if cookie deleted unsuccesfuly
         */
        public static function delete($name) {
                // set expiry in the past
		return self::set($name, '', -86500);
        }
	
}