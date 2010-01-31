<?php

/**
 * Fari MVC Framework 1.2.1.5 (Jan 29, 2010)
 *
 * Main point of access, a bootstrapping file. Here we declare variables and set configurations.
 *
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari MVC Framework
 */

// start session
if (!isset($_SESSION)) session_start();
// start benchmarking
$_SESSION['Fari\Benchmark\Total'] = microtime();
$_SESSION['Fari\Benchmark\Queries'] = 0;

// set so that we can check if PHP pages have been accessed directly
if (!defined('FARI')) define('FARI', 'Fari MVC Framework 1.2.1.5');

// get absolute pathname and define it as a constant (server install path)
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__));
// www root dir (path for links in your views)
if (!defined('WWW_DIR')) {
	// now we can have the app in the root
	dirname($_SERVER['SCRIPT_NAME']) == '/' ? define('WWW_DIR', '') : define('WWW_DIR', dirname($_SERVER['SCRIPT_NAME']));
}

// default file extension (.php)
if (!defined('EXT')) define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));

// default Action for the application (index() is always implemented as is abstract)
if (!defined('DEFAULT_ACTION')) define('DEFAULT_ACTION', 'index');

// include database and application settings
require BASEPATH . '/config/config' . EXT;

// initialize Error & Exceptions handling
require BASEPATH . '/fari/Diagnostics' . EXT;

// initialize the Router (load Controller)
require BASEPATH . '/fari/Router' . EXT;
// initialize the 'optional' Model (business logic)
require BASEPATH . '/fari/Model' . EXT;
// initialize the Controller (load Actions)
require BASEPATH . '/fari/Controller' . EXT;
// initialize the View (templating)
require BASEPATH . '/fari/View' . EXT;

// check that we have a high enough version of PHP (5.2.0)
try { if (version_compare(phpversion(), '5.2.0', '<=') == TRUE) {
	throw new Fari_Exception('Fari MVC Framework requires PHP 5.2.0, you are using ' . phpversion() . '.'); }
} catch (Fari_Exception $exception) { $exception->fire(); }

// check if user is using Apache user-directory found on temporary links to web hosting (e.g., http://site.com/~user/)
try { if (substr_count(WWW_DIR, '~') > 0) {
	throw new Fari_Exception('Apache user-directory ' . WWW_DIR . ' not supported by Fari Framework.'); }
} catch (Fari_Exception $exception) { $exception->fire(); }

// autoload Model classes when needed (before exception is thrown)
function __autoload($className) {
        // are we working with a Fari Helper?
        if (strpos($className, 'Fari_') === FALSE) {
		$modelFilePath = BASEPATH . '/'. APP_DIR . '/models/' . strtolower($className) . EXT;
        } else {
		// remove fari_ and build path
		$modelFilePath = BASEPATH . '/fari/helpers/' . substr($className, 5) . EXT;
	}
        try {
		// check that model class exists
		if (!file_exists($modelFilePath)) {
			throw new Fari_Exception('Missing Model Class: ' . $modelFilePath . '.');
		} else include($modelFilePath); // include file
	} catch (Fari_Exception $exception) { $exception->fire(); }
}

// load Controller on static Router
Fari_Router::loadRoute();
