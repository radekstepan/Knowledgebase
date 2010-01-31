<?php if (!defined('FARI')) die();

/**
 * Main Controller class from which other Controllers are derived.
 * Controller decides what views to display and what information to populate them with.
 * Implement function AUTHORIZE in Controllers to protect user area (admin).
 *
 * @example calling returnUsers() and put the return array in the PHP variable $users, then call the
 *  corresponding view (i.e. include 'views/user_listing.php') and fill it with the data (i.e. $users)
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

// abstract classes can't be instantiated
abstract class Fari_Controller {

	/**
	 * View object ready to be used in the Controller
	 * @var Fari_View
	 */
	protected $view = NULL;
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Abstract Controller class'; }
	
	/**
	 * Set registry when new object gets instantiated and use classname when _init().
	 *
	 * @param string $className Name of the class instantiated so that we can call _init() easily
	 */
	function __construct($className) {
		// create a new View object in Controller var
		$this->view = new Fari_View();
		
		if (method_exists($className, _init)) $this->_init();
	}
	
	/**
	 * Abstract function that every child-class is required to have. Interface could be used as well.
	 *
	 * @param string $parameter Takes in a maximum of one parameter
	 * @return void
	 */
	abstract function index($parameter);
	
	/**
	 * Will redirect to a URL (/controller/action). Works with both synchronous/asynchronous calls.
	 *
	 * @param string $url URL to redirect to
	 * @return void
	 */
	function redirect($url) {
		// add forward slash if not specified
		if ($url[0] !== '/') $url .= '/';
		
		// is this an AJAX call?
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			// implement a browser-end redirect here
		} else {
			// if headers haven't been already sent provide redirect via header()
			if (!headers_sent()) header('Location: ' . WWW_DIR . $url);
		}
	}
	
}