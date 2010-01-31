<?php if (!defined('FARI')) die();

/**
 * View class contains presentation logic (templating).
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 
 * @package Fari
 */

class Fari_View {
	
	/**
	 * Directory where we store cached views
	 * @var const
	 */
	const CACHE_DIR = '/tmp/';
	
	/**
	 * Extension that cached views will have
	 * @var const
	 */
	const CACHE_EXT = '.html';
	
	/**
	 * Cache lifetime in minutes
	 * @var const
	 */
	const CACHE_LIFE = 5;
	
	/**
	 * Caching on/off switch.
	 * @var boolean
	 */
	private $cachingEnabled = FALSE;
	
	/**
	 * Array for storing values coming from the Controller
	 * @var array
	 */
	private $values = array();
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Displays a template'; }
	
	/**
	 * Magic __set method for saving values in a View.
	 *
	 * @param string $index Index key
	 * @param object $value Value saved
	 * @return void
	 */
	public function __set($index, $value) {
		$this->values[$index] = $value;
 	}
	
	/**
	 * Will prepend a WWW root directory to create a local link.
	 *
	 * @param string $link Link to follow WWW directory, with/without leading slash
	 * @return void echo of link into the View
	 */
	public function url($link) {
		echo ($link[0] == '/') ? WWW_DIR . $link : WWW_DIR . '/' . $link;
	}
	
	/**
	 * Call this function (early on) to display cached view.
	 *
	 * @param string $viewName View name to check if is cached
	 * @param string $contentType Specifies optional content type for the view
	 * @param string $extraParam Extra parameter to find the view by
	 * @return void
	 */
	public function cache($viewName, $contentType='text/html', $extraParam=NULL) {
		// build file id from (folder(s) and) file name
		$fileId = $this->_getFileId($viewName, $extraParam);
		// build cache filename (note no dir checking)
		$cacheFile = BASEPATH . self::CACHE_DIR . $fileId . self::CACHE_EXT;
		
		// serve the cached file if it exists and is not too old
		if (file_exists($cacheFile)
		    && !$this->_isCacheOld($cacheFile)) {
			// set content type
			if (!headers_sent()) @header('Content-type: ' . $contentType);
			
			readfile($cacheFile);
			die(); // end the agony, we don't need to process any data
		// we are going to enable caching
		} else $this->cachingEnabled = TRUE;
	}
	
 	/**
 	 * Displays the view.
 	 *
	 * @param string $viewName View name to display
	 * @param string $contentType Specifies optional content type for the view
	 * @param string $extraParam Extra parameter to find the view by
 	 * @param void
 	 */
 	public function display($viewName, $contentType='text/html', $extraParam=NULL) {
		// if caching is enabled
		if ($this->cachingEnabled) {
			// build file id from (folder(s) and) file name
			$fileId = $this->_getFileId($viewName, $extraParam);
			
			// check if dir writable
			$this->_isDirWritable(self::CACHE_DIR);
			// everything went OK, 'build' cache filename path
			$cacheFile = BASEPATH . self::CACHE_DIR . $fileId . self::CACHE_EXT;
			
			// cached file doesn't exist or is too old
			// import key:value array into symbol table
			extract($this->values, EXTR_SKIP);
			
			// start the output buffer
			ob_start();
				// set content type
				if (!headers_sent()) @header('Content-type: ' . $contentType);
				
				// create template file path
				$viewFile = BASEPATH . '/' . APP_DIR . '/views/' . $viewName . '.tpl' . EXT;
				// check if view exists
				$this->_isViewValid($viewFile);
				// all went fine, include
				include $viewFile;
				
				// get view/template contents to a variable
				$contentOutput = ob_get_contents();
				
				$this->_writeCache($contentOutput, $cacheFile);
			
			// send the output to the browser
			ob_end_flush();
		// don't use caching
		} else {
			// import key:value array into symbol table
			extract($this->values, EXTR_SKIP);
			
			// set content type
			if (!headers_sent()) @header('Content-type: ' . $contentType);
			
			// create template file path
			$viewFile = BASEPATH . '/' . APP_DIR . '/views/' . $viewName . '.tpl' . EXT;
			// check if view exists
			$this->_isViewValid($viewFile);
			// all went fine, include
			include $viewFile;
		}
	}
	
	/**
	 * Return an array with PHP classes calls defined in a View.
	 *
	 * @param string $viewName Name of the template we want to parse
	 * @param string $className Optional class name that we only want to include
	 * @return array array('class', 'method', array('params'))
	 */
	public function getClasses($viewName, $className=NULL) {
		// build a view filename and check that is valid
		$viewFile = BASEPATH . '/' . APP_DIR . '/views/' . $viewName . '.tpl' . EXT;
		$this->_isViewValid($viewFile);
		
		// tokenize the input file
		$viewString = file_get_contents($viewFile);
		$tokens = token_get_all($viewString);
		unset($viewString);
		
		$classes = array(); $i = 0; $count = count($tokens);
		while ($i<$count) {
			$token = $tokens[$i];
			// only work with PHP
			if (is_array($token) && $token[0] !== T_INLINE_HTML) {
				// find class/model name
				if ($token[1] == '::' || $token[1] == '->') {
					$class = $tokens[$i-1][1];
					// only continue if we find a specific class name (if set)
					if (!isset($className)
					    || ($class == $className)) {
						// function/method
						$method = (isset($tokens[$i+1])) ? $tokens[$i+1][1] : '';
						// parametres
						$params = array();
						while ($tokens[$i+2][0] !== T_CLOSE_TAG) {
							if (isset($tokens[$i+2])
							    && $tokens[$i+2][0] != 371
							    && is_array($tokens[$i+2])) {
								// push parameter on the stack
								array_push($params, $tokens[$i+2][1]);
							}
							$i++;
						}
						
						// push the call to the main array
						array_push($classes, array('class' => $class,
									   'method' => $method,
									   'params' => $params));
					}
				}
			}
			$i++;
		}
		unset($tokens);
		return $classes;
	}
	
	/**
	 * Build file id as file might be in a sub directory etc.
	 *
	 * @param string $viewName View name that we are working with
	 * @param string $extraParam Extra parameter to find the view by
	 * @return string MD5 hashed view file id
	 */
	private function _getFileId($viewName, $extraParam) {
		// calculate based on md5
		if (isset($extraParam)) return md5($viewName.$extraParam);
		return md5($viewName);
	}
	
	/**
 	 * Write contents of a view/template to a file.
 	 *
 	 * @param string $contentOutput Content of the view we want to write
 	 * @param string $cacheFile Filename we will write to
 	 * @return void
 	 */
 	private function _writeCache($contentOutput, $cacheFile) {
		// cache the contents to a file
		// open file for writing
		$cacheFile = fopen($cacheFile, 'w');
		// write file
		fwrite($cacheFile, $contentOutput);
		// close file
		fclose($cacheFile);
	}
	
	/**
	 * Will check if a cache directory is writable. Will throw Fari_Exception on error.
	 *
	 * @param string $cacheDir Directory that stores our cached files
	 * @return void
	 */
	private function _isDirWritable($cacheDir) {
		try {
			if (!is_writable(BASEPATH . $cacheDir)) {
				throw new Fari_Exception('Cache directory ' . $cacheDir . ' is not writable.');
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
	/**
	 * Check if view file can be included. Will throw Fari_Exception on error.
	 *
	 * @param string $viewFile to check for existence
	 * @return void
	 */
	private function _isViewValid($viewFile) {
		try {
			// check if file path exists
			if (!file_exists($viewFile)) {
				throw new Fari_Exception('View not located in: ' . $viewFile);
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
	/**
	 * Determines whether cache file is too old to be used.
	 *
	 * @param string $cacheFile Filename to check for 'freshness'
	 * @return boolean Whether we need to get a fresh copy (TRUE) or not (FALSE)
	 */
	private function _isCacheOld($cacheFile) {
		// time now - cache lifetime
		$goodTime = time() - (self::CACHE_LIFE * 60);
		$cacheAge = filemtime($cacheFile);
		// return TRUE if file age 'within' good time
		return ($goodTime < $cacheAge) ? FALSE : TRUE;
	}
	
}