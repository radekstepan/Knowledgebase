<?php if (!defined('FARI')) die();

/**
 * Benchmarking of the system.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Benchmark {
	
	/**
	 * Session storage
	 * @var const
	 */
	const SESSION_STORAGE = 'Fari\Benchmark\\';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Benchmarking of the system'; }
	
	/**
         * Get used up memory, formatted.
         *
         * @return string Usage of memory in b, kb, etc.
         */
        public static function getMemory() {
                return Fari_Format::bytes(memory_get_usage());
        }
	
	/**
	 * Will start a new time sequence to benchmark.
	 * 'Fari\Benchmark\Total' is started automatically for you @ framework bootstrap
	 *
	 * @param string $activity What is the name of the activity you'd like to start timing?
	 * @return void
	 */
	public static function startTime($activity) {
		// save activity into the session
		$_SESSION[self::SESSION_STORAGE . $activity] = microtime();
	}
	
        /**
         * Get execution time of an activity in seconds.
         *
         * @param string $activity Specifies activity we want to stop benchmarking
         * @return string Execution time of the activity with 's' appended
         */
        public static function getTime($activity='Total') {
                $startTime = self::_fixMicrotime($_SESSION[self::SESSION_STORAGE . $activity]);
                $endTime = self::_fixMicrotime(microtime());
                
		// unset session to preserve resources
		unset($_SESSION[self::SESSION_STORAGE . $activity]);
		
                return round($endTime - $startTime, 4).' s';
        }
        
        /**
         * Prevent overflowing of microtime.
         *
         * @param string $time Microtime value
         * @return doubleval Microtime value split on space
         */
        private static function _fixMicrotime($time) {
                $time = explode(' ', $time);
                return doubleval($time[0]) + $time[1];
        }

}