<?php if (!defined('FARI')) die();

/**
 * Various text and number formatting functions.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Format {
	
	/**
	 * Default currency formatting if unknown passed
	 * @var const
	 */
	const CURRENCY = 'GBP';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Currency, date, string etc. formatting'; }

        /**
         * Converts 'egg_and_ham' into 'Egg And Ham'
         *
         * @param string $string Input string in underscore format
         * @return string String in title format
         */
        public static function titleize($string) {
                // explode by underscore
                $array = explode('_', $string);
                $result = '';
                // add space and uppercase first letter
                foreach ($array as $word) {
                        $result .= ucwords($word) . ' ';
                }
                // remove trailing space and return
                return substr($result, 0, -1);
        }

        /**
         * Format input number based on currency settings to be used in HTML.
         *
         * @param int $number Number we want to format
         * @param string $currencyCode Currency code e.g., EUR
         * @return string Formatted number
         */
        public static function currency($number, $currencyCode) {
		// if the currency doesn't have a function defined for itself, give us a default
		$function = (!is_callable('self::_to' . $currencyCode)) ? '_to' . self::CURRENCY : '_to' . $currencyCode;
		
                return self::$function($number);
	}

        /**
         * Will format the date in tables as per our wishes.
         * Will leave date unchanged if $dateFormat not recognized
         *
         * @param string $date Date in 'standard' format YYYY-MM-DD
         * @param string $dateFormat Target formatting to use (YYYY-MM-DD, DD-MM-YYYY, D MONTH YYYY, RSS)
         * @return string Formatted date
         */
        public static function date($date, $dateFormat) {
                // check if input date is valid
		if (Fari_Filter::isDate($date)) {
			// split into params
			list ($year, $month, $day) = preg_split('/[-\.\/ ]/', $date);
		// else return input
		} else return $date;
                
                switch ($dateFormat) {
                        case 'DD-MM-YYYY':
                                return $day . '-' . $month . '-' . $year;
                                break;
                        case 'D MONTH YYYY':
				// get month's name
                                $month = date('F', mktime(0, 0, 0, $month, 1));
                                // make a nice day formatting, 9th, 10th etc.
				if ($day < 10) $day = substr($day, 1, 1);
                                return $day . ' ' . $month . ' ' . $year;
                                break;
			case 'RSS':
				return date(DATE_RSS, mktime(0, 0, 0, $month, $day, $year));
				break;
                        // for unknown formats or default, just return
                        default:
                                return $date;
                }
	}

	/**
	 * Convert a time to distance from now.
	 *
	 * @param string $time A timestamp of a date (or convert into one from YYYY-MM-DD)
	 * @return string A formatted string of a date from now, e.g.: '3 days ago'
	 */
    public static function age($time) {
        // convert YYYY-MM-DD into a timestamp
        if (Fari_Filter::isDate($time)) {
            list ($year, $month, $day) = preg_split('/[-\.\/ ]/', $time);
            $time = mktime('1', '1', '1', $month, $day, $year);
        }

        // time now
        $now = time();
        // the difference
        $difference = $now - $time;
        // in the past?
        $ago = ($difference > 0) ? 1 : 0;
        // absolute value
        $difference = abs($difference);

        // switch case textual difference
        switch ($difference) {
            case ($difference < 60): $result = 'a minute'; break;
            case ($difference < 60 * 60): $result = 'an hour'; break;
            case ($difference < 60 * 60 * 24): $result = 'a day'; break;
            case ($difference < 60 * 60 * 24 * 7): $result = 'a week'; break;
            case ($difference < 60 * 60 * 24 * 7 * 2): $result = 'two weeks'; break;
            case ($difference < 60 * 60 * 24 * 7 * 3): $result = 'three weeks'; break;
            case ($difference < 60 * 60 * 24 * 30): $result = 'a month'; break;
            case ($difference < 60 * 60 * 24 * 60): $result = 'two months'; break;
            case ($difference < 60 * 60 * 24 * 90): $result = 'three months'; break;
            case ($difference < 60 * 60 * 24 * 120): $result = 'four months'; break;
            case ($difference < 60 * 60 * 24 * 182): $result = 'half a year'; break;
            case ($difference < 60 * 60 * 24 * 365): $result = 'a year'; break;
            case ($difference < 60 * 60 * 24 * 365 * 2): $result = 'two years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 3): $result = 'three years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 4): $result = 'four years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 5): $result = 'five years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 6): $result = 'six years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 7): $result = 'seven years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 10): $result = 'a decade'; break;
            case ($difference < 60 * 60 * 24 * 365 * 20): $result = 'two decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 30): $result = 'three decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 40): $result = 'four decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 50): $result = 'half a century'; break;
            case ($difference < 60 * 60 * 24 * 365 * 100): $result = 'a century'; break;
            default: $result = 'more than a century'; break;
        }

        return ($ago) ? $result . ' ago' : 'in ' . $result;
    }

	/**
	 * Highlight word(s) in text(s) (e.g.: search results).
	 *
	 * @param mixed $string Array/string to apply highlighting to
     * @param mixed $highlight Array/string that we want to highlight
     * @param array $whitelist Array of keys we want to highlight in $string array (optional)
	 * @return mixed Text with <span class="highlight"> applied
	 */
    public static function highlight($string, $highlight, array $whitelist=NULL) {
        // multiple words to highlight
        if (is_array($highlight)) {
            // sort by length
            $lengths = array(); $match = '';
            foreach ($highlight as $word) $lengths []= strlen($word);
            arsort(&$lengths);
            // form string to match sorted by word length
            foreach ($lengths as $key => $word) $match .= $highlight[$key] . '|'; $highlight = substr($match, 0, -1);
        }

        // the input text is an array...
        if (is_array($string)) {
            // highlight words in the array
            foreach ($string as $key => &$value) {
                // if we have a whitelist... use it
                if (!isset($whitelist) OR in_array($key, $whitelist))
                    $value = preg_replace("/($highlight)/i", '<span class="highlight">\1</span>', $value);
            } // ... or is a string
        } else $string = preg_replace("/($highlight)/i", '<span class="highlight">\1</span>', $string);

        return $string;
    }

	/**
	 * Convert bytes to human readable format (based on CodeIgniter).
	 *
	 * @param int $bytes Value in bytes
	 * @return string Nicely formatted into b, kB, MB etc.
	 */
	public static function bytes($bytes) {
		if ($bytes >= 1000000000000) {
			$bytes = round($bytes / 1099511627776, 1);
			$unit = ('TB');
		} elseif ($bytes >= 1000000000) {
			$bytes = round($bytes / 1073741824, 1);
			$unit = ('GB');
		} elseif ($bytes >= 1000000) {
			$bytes = round($bytes / 1048576, 1);
			$unit = ('MB');
		} elseif ($bytes >= 1000) {
			$bytes = round($bytes / 1024, 1);
			$unit = ('kB');
		} else {
			return number_format($bytes) . ' B';
		}
		return number_format($bytes, 1) . ' ' . $unit;
	}
	
        /**
         * Format as GBP.
         *
         * @param int $number
         * @return string Nicely formatted
         */
        private static function _toGBP($number) {
                setlocale(LC_MONETARY, 'en_GB');
                $value = self::_formatCurrency($number);
                return '&pound;' . $value;
        }
        /**
         * Format as CZK.
         *
         * @param int $number
         * @return string Nicely formatted
         */
        private static function _toCZK($number) {
                setlocale(LC_MONETARY, 'cs_CZ.UTF-8');
                $value = self::_formatCurrency($number);
		return $value . '&nbsp;Kč';
        }
        /**
         * Format as EURO.
         *
         * @param int $number
         * @return string Nicely formatted
         */
        private static function _toEUR($number) {
                setlocale(LC_MONETARY, 'de_DE@euro');
                $value = self::_formatCurrency($number);
                return $value . '&nbsp;&euro;';
        }
	/**
         * Format as USD.
         *
         * @param int $number
         * @return string Nicely formatted
         */
        private static function _toUSD($number) {
                setlocale(LC_MONETARY, 'en_US');
                $value = self::_formatCurrency($number);
                return '&#36;' . $value;
        }
        
	/**
	 * Format our number after we've changed the locale.
	 *
	 * @param int $number
	 * @return string number in currency format
	 */
	private static function _formatCurrency($number) {
		return @number_format($number, 2, ',', ' ');
	}
	
}