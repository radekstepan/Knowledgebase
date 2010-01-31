<?php if (!defined('FARI')) die();

/**
 * Escaping, filtering of input.
 * Based on http://phpfashion.com/escapovani-definitivni-prirucka.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Escape {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Escaping of input'; }
	
	/**
	 * Use in HTML context.
	 * 
	 * @param string $input
	 * @return string
	 */
	public static function HTML($input) {
		return htmlspecialchars($input, ENT_QUOTES);
	}
	
	/**
	 * Use to get a string (e.g., "<script>\"'foo'\"</script>"; will strip <script> and encode quotes).
	 *
	 * @param string $input
	 * @return string
	 */
	public static function text($input) {
		return filter_var($input, FILTER_SANITIZE_STRING);
	}

	/**
	 * Use to get only alphanumeric characters.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function alpha($input) {
        return preg_replace("/[^a-zA-Z0-9\s]/", "", $input);
	}
	
	/**
	 * Use in XML context.
	 * 
	 * @param string $input
	 * @return string
	 */
	public static function XML($input) {
		return htmlspecialchars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', $input), ENT_QUOTES);
	}
	
	/**
	 * Use in Regular Expression context.
	 *
	 * @param string $input
	 * @param string $delimiter Delimiter string (e.g., #)
	 * @return string
	 */
	public static function regex($input, $delimiter) {
		return preg_quote($input, $delimiter);
	}
	
	/**
	 * Use when inserting data to db. Will add slashes if magic quotes are off.
	 * 
	 * @param string $input
	 * @return string
	 */
	public static function slashes($input) {
		// check if we have magic quotes on
		return (!get_magic_quotes_gpc()) ? addslashes($input) : $input;
	}
	
	/**
	 * Use in CSS context.
	 * 
	 * @param string $input
	 * @return string
	 */
	public static function CSS($input) {
		return addcslashes($input, "\x00..\x2C./:;<=>?@[\\]^`{|}~");
	}
	
	/**
	 * Use in URL context.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function URL($input) {
		return filter_var($input, FILTER_SANITIZE_ENCODED);
	}
	
	/**
	 * Escape an email.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function email($input) {
		return filter_var($input, FILTER_SANITIZE_EMAIL);
	}
	
	/**
	 * Converts quotes to HTML entities.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function quotes($input) {
		// make sure we convert even escaped quotes
		return str_replace(array("'", "\'", '"', "\""), array("&#39;", "&#39;", "&#34;", "&#34;"), $input);
	}
	
	/**
	 * Converts PHP tags to HTML entities.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function PHP($input) {
		return str_replace(array('<?php', '?>', '<?PHP', '<?'),
				   array('&#60;&#63;php', '&#63;&#62;', '&#60;&#63;PHP', '&#60;&#63;'),
				   $input);
	}
	
	/**
	 * Strips nasties off a filename (based on CodeIgniter).
	 *
	 * @param string $input
	 * @param boolean $whitespace If we want to escape whitespace into underscores
	 * @return string
	 */
	public static function file($input, $whitespace=TRUE) {
		$bad = array("<!--", "-->", "'", "<", ">", '"', '&', '$', '=', ';', '?', '/', "%20", "%22", "%3c",
			     "%253c", "%3e", "%0e", "%28", "%29", "%2528", "%26", "%24", "%3f", "%3b", "%3d");
		$input = str_replace($bad, '', $input);
		
		// escape whitespace if enabled
		if ($whitespace) $input = self::whitespace($input);
		
		return $input;
	}
	
	/**
	 * Strips nasties off a directory name.
	 *
	 * @param string $input
	 * @param boolean $whitespace If we want to escape whitespace into underscores
	 * @return string
	 */
	public static function directory($input, $whitespace=TRUE) { return self::file($input, $whitespace); }
	
	/**
	 * Escape whitespace by converting into underscores (based on CodeIgniter).
	 *
	 * @param string $input
	 * @return string
	 */
	public static function whitespace($input) {
	        return preg_replace("/\s+/", "_", $input);
	}
	
    /**
	 * Generate a slug from a text (e.g., "Červený 'nejede'!" will turn into "cerveny-nejede").
	 *
	 * @param string $input
	 * @return string
	 */
	public static function slug($input) {
	    return preg_replace("/\s+/", "-",
            preg_replace("/[^a-zA-Z0-9 ]/", "",
                strtolower(
                    Fari_Decode::accents(($input)))));
	}
}