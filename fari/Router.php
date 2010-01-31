<?php if (!defined('FARI')) die();

/**
 * Router loads up the appropriate Controller and Action based on the route in the incoming URL.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Router {
	
	/**
	 * $_GET variable containing route (coming from .htaccess)
	 * @var const
	 */
	const ROUTE = 'route';
	
	/**
	 * Error controller if controller not found
	 * @var const
	 */
	const ERROR_CONTROLLER = 'error404';
	
	/**
	 * A directory with Fari_Controllers
	 * @var string
	 */
	private static $controllerDir = '';
	
	/**
	 * Controller requested
	 * @var string
	 */
	private static $controller = '';
	
	/**
	 * Action requested
	 * @var string
	 */
	private static $action = '';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Loads requested Controller & Action'; }
	
	/**
	 * Load the Controller and Action from route requested.
	 *
	 * @return void
	 */
	public static function loadRoute() {
		// first make sure we have the Controllers directory right
		$controllerDir = BASEPATH . '/' . APP_DIR . '/controllers/';
		try {
			// throw an error if is not a directory
			if (!is_dir($controllerDir)) {
				throw new Fari_Exception('Not a Controller directory: ' . $controllerDir . '.');
			// else set the path
			} else self::$controllerDir = $controllerDir;
		} catch (Fari_Exception $exception) { $exception->fire(); }
		
		// get route as passed by .htaccess
		$request = @$_GET[self::ROUTE];
		
		// if no route segments exist...
		if (empty($request)) {
			// ...use the default route
			// set default Controller
			self::$controller = DEFAULT_CONTROLLER;
			// set default Action
			self::$action = DEFAULT_ACTION;
		} else {
			// check the routes config file
			$request = self::_checkCustomRoutes($request);
			
			// explode the route into array with segments
			$route = self::_cleanupRequest($request);
			
			// we are going to work with a simple Controller/Action example here
			
			// at least the controller must have been specified
			// do a cleanup and set
			self::$controller = $route[0];
			// set custom Action or go default
			!empty($route[1]) ? self::$action = $route[1] : self::$action = DEFAULT_ACTION;
		}
		
		// by this time we have the Controller/Action combo but don't know if these are callable
		
		// set file requested
		$controllerFile = self::$controllerDir . self::$controller . EXT;
		
		// now check if the Controller file is readable... 
		if (!is_readable($controllerFile)) {
			// ... not, so update Controller path to the error page 404
			$controllerFile = self::$controllerDir . self::ERROR_CONTROLLER . EXT;
			
			// but 'achich ouvej' maybe that one doesn't exist either
			try { if (!is_readable($controllerFile)) {
				throw new Fari_Exception('Missing 404 Error Controller: ' . $controllerFile); }
			} catch (Fari_Exception $exception) { $exception->fire(); }
			
			// ... OK, so now update Controller to error page 404
			self::$controller = self::ERROR_CONTROLLER;
			// and Action to the default one
			$action = DEFAULT_ACTION;
		}
		// now we know the Controller path exists so include
		include($controllerFile);
		
		// load instance of the Controller
		// 'Name_Controller' naming convention
		$controllerClass = self::$controller . '_Controller';
		
		// initiate parametres array so that we don't see notices
		$parametres = array();
		// slice the route array to find out parametres requested, throw away controller and action
		if (isset($route)) $parametres = array_slice($route, 2);
		
		// from variable to instance of an object
		$controller = @new $controllerClass($controllerClass);
		
		// check that the Controller object extends Fari_Controller
		try { if (!$controller instanceof Fari_Controller) {
			throw new Fari_Exception('Controller object ' . $controllerClass .
						 ' does not extend Fari_Controller.'); }
		} catch (Fari_Exception $exception) { $exception->fire(); }
		
		// Controller is set, now check we can call the Action in it
		if (!is_callable(array($controller, self::$action))) {
			// ... nope, set action to default (index)
			self::$action = DEFAULT_ACTION;
		}
		$action = self::$action;
		
		// run the function (Action) in the Controller...
		if (empty($parametres) && !is_callable(array($controller, $action))) $controller->$action();
		// ... optionally passing parametres
		else {
			// determine the number of parametres...
			$parameterCount = count($parametres);
			// ... and call the function passsing 3 parametres as a string...
			switch ($parameterCount) {
				case 0:
					$controller->$action(NULL);
					break;
				case 1:
					$controller->$action($parametres[0]);
					break;
				case 2:
					// e.g., /albums/page/3/artist => albums->page('3', 'artist')
					$controller->$action($parametres[0], $parametres[1]);
					break;
				case 3:
					$controller->$action($parametres[0], $parametres[1], $parametres[2]);
					break;
				default:
					$extraParams = array_slice($parametres, 3);
					// ... and the rest as an array
					$controller->$action($parametres[0], $parametres[1], $parametres[2], $extraParams);
					break;
			}
		}
 	}
	
	/**
	 * Check if we have the routes config file and if so include it and check for routes matches on the request.
	 *
	 * @param string $request Original unsplit to check for match in custom routes
	 * @return string Optionally updated route with a custom one
	 */
	private static function _checkCustomRoutes($request) {
		// form the path to the routes file
		$routesFile = BASEPATH . '/config/routes' . EXT;
		// do we have the routes file?
		if (is_readable($routesFile)) {
			// include, now we have the $routes array
			include($routesFile);
			// if we have $customRoutes to traverse
			if (is_array($customRoutes)) {
				// traverse routes
				foreach ($customRoutes as $route) {
					// we have a match, first param in $route is always 'from' field
					if ($route[0] == $request) {
						// 'change' the request to 'to' field in route
						$request = $route[1];
						// don't bother with more routes, first come first served basis
						break;
					}
				}
			}
		}
		return $request;
	}
	
	/**
	 * Split URL request into segments and do a cleanup.
	 *
	 * @param string $request Input request
	 * @return array Request split and cleaned up
	 */
	private static function _cleanupRequest($request) {
        // explode the route into array with segments
		$route = explode('/', $request);
		
		// cleanup URL to only include the following:
		foreach ($route as &$part) {
			// Fari_Filter::url()
			$part = filter_var($part, FILTER_SANITIZE_ENCODED);
		}
		return $route;
	}
	
}