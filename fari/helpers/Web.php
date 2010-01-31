<?php if (!defined('FARI')) die();

/**
 * Creates breadcrumbs.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Web {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Creates breadcrumbs'; }
	
	/**
	 * Build a crumbtrail.
	 *
	 * @param string $separator Will be shown in between crumbs
	 * @param array $route Route to build crumbtrail from
	 * @return string Breadcrumbs string with links
	 */
	public static function breadcrumbs($separator, array $route) {
		// final breadcrumbs string storage
		$breadcrumbs = '';
		// url that gets bigger as we go through the route
		$url = '';
		
		// gimickry with last element that can't have a separator
		$lastElement = array_pop($route);
		
		// traverse
		foreach ($route as $part) {
			// separator for the url
			$url .= '/' . $part;
			$breadcrumbs .= '<a href="' . WWW_DIR . $url . '">' . $part . '</a>' . $separator;
		}
		// add last element without a separator
		$breadcrumbs .= '<a href="' . WWW_DIR . $url . '/' . $lastElement . '">' . $lastElement . '</a>';
		
		return $breadcrumbs;
	}
        
}