<?php if (!defined('FARI')) die();

/**
 * Mini Textile class converting Textile input to HTML output.
 *
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Textile {

    /**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Textile to HTML converter'; }

    /**
     * Main point of entry for text parsing.
     *
     * @param string $text Textile tagged input text
     * @return string HTML formatted text
     */
	public static function toHTML($text) {
		// return if input blank
		if (empty($text)) { return $text; }

		// prep text for some serious regex
		$text = self::_prep($text);

		// links
		$text = self::_links($text);

        // unordered list
        $text = self::_list($text);

		// font formating
		$text = self::_font($text);
		// special characters conversion
		$text = self::_punctuation($text);

		// blocks formating
		$text = self::_blocks($text);

		// give us back elmo! (newlines)
		$text = self::_newlines($text);

		return $text;
	}

    /**
     * Prepare input text by creating matching endlines etc.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _prep($text) {
		// DOS to Unix and Mac to Unix line endings
		$text = preg_replace('{\r\n?}', "\n", $text);

		// add a newline character to the end and return
		return $text."\n";
	}

    /**
     * Process unordered lists.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _list($text) {
        /*
         * <li> tags.
         *
         * (?:\n|\</li>)\*\s(.+)
         *
         * Match the regular expression below
         *    Match either the regular expression below...
         *       Match a line feed character «\n»
         *    Or match a regular expression number 2 below
         *       Match the characters «</li>» literally
         * Match the character «*» literally
         * Match a single character that is a whitespace character
         * Match the regular expression below and capture its match into backreference number 1
         *    Match any single character that is not a line break character «.+»
         *       Between one and unlimited times, as many times as possible, giving back as needed (greedy)
         *
         */
		$text = preg_replace('/(?:\\n|\<\/li>)\*\s(.+)/', "<li>$1</li>\n", $text);

         /*
         * Wrap in unordered list <ul> tags.
         *
         * <li>(.+)\n
         *
         * Match the characters «<li>» literally
         * Match the regular expression below and capture its match into backreference number 1
         *    Match any single character that is not a line break character
         *       Between one and unlimited times, as many times as possible, giving back as needed (greedy)
         * Match a line feed character «\n»
         *
         */
        $text = preg_replace('/<li>(.+)\\n/m', '<ul><li>$1</ul>', $text);

		return $text;
	}

    /**
     * Headings, paragraphs, blockquotes, bold, underline, italics.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _font($text) {
        /**
         * Headings.
         *
         * h([\0-9])\. (.+)\n
         *
         * Match the character “h” literally «h»
         * Match the regular expression below and capture its match into backreference number 1 «([\0-9])»
         *    Match a single character in the range between Octal index 0 in the character set (0 decimal or 00 hexadecimal) and “9” «[\0-9]»
         * Match the character “.” literally «\.»
         * Match the character “ ” literally « »
         * Match the regular expression below and capture its match into backreference number 2 «(.+)»
         *    Match any single character that is not a line break character «.+»
         *       Between one and unlimited times, as many times as possible, giving back as needed (greedy) «+»
         * Match a line feed character «\n»
         *
         */
		$text = preg_replace('/h([\0-9])\. (.+)\n/m','<elmo><h$1>$2</h$1><elmo>', $text);

		/**
		 * Blockquotes (bq. ).
		 *
		 * bq\. (.+)\n
		 *
		 * Match the characters “bq” literally «bq»
		 * Match the character “.” literally «\.»
		 * Match the character “ ” literally « »
		 * Match the regular expression below and capture its match into backreference number 1 «(.+)»
		 *    Match any single character that is not a line break character «.+»
		 *       Between one and unlimited times, as many times as possible, giving back as needed (greedy) «+»
		 * Match a line feed character «\n»
		 *
		 */
		$text = preg_replace('/bq\. (.+)\n/m','<elmo><blockquote>$1</blockquote><elmo>', $text);

		/**
         * Paragraph tags (p. ).
         *
		 * p\. (.+)\n
		 *
		 * Match the characters “p” literally «p»
		 * Match the character “.” literally «\.»
		 * Match the character “ ” literally « »
		 * Match the regular expression below and capture its match into backreference number 1 «(.+)»
		 *    Match any single character that is not a line break character «.+»
		 *       Between one and unlimited times, as many times as possible, giving back as needed (greedy) «+»
		 * Match a line feed character «\n»
		 *
         */
        $text = preg_replace('/p\. (.+)\n/m','<elmo><p>$1</p><elmo>', $text);

		// strong (bold)
		$text = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>$2', $text);

		// emphasis (italics)
        $text = preg_replace('/\_([^\_]+)\_/', '<em>$1</em>$2', $text);

		// insert (underline)
        $text = preg_replace('/\+([^\+]+)\+/', '<ins>$1</ins>$2', $text);

		return $text;
	}

    /**
     * Punctuation & special characters conversion.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _punctuation($text) {
		return str_replace(array(
					'--',	// dash
					'...',	// triple dots
					' x ',	// x math symbol
					'(tm)', // trademark
					'(TM)',
					'(r)',	// registered
					'(R)',
					'(c)',	// copyright
					'(C)',
				), array(
					'&#8212;',
					'&#8230;',
					' &#215; ',
					'&#8482;',
					'&#8482;',
					'&#174;',
					'&#174;',
					'&#169;',
					'&#169;',
				), $text);
	}

    /**
     * Hyperlinks & images.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _links($text) {
		/**
		 * Hyperlinks (matches quotes as ASCII character and as HTML entity).
		 *
	 	 * "([^\"]+)\":(ftp|http)://([^\s]+)
		 *
		 * Match the character “"” literally «"»
		 * Match either the regular expression below and capture its match into backreference number 1
		 *    Match any character that is not a """
		 *       Between one and unlimited times, as many times as possible, giving back as needed (greedy)
		 * Match the character """ literally
		 * Match the character ":" literally
		 * Match the regular expression below and capture its match into backreference number 2
		 *    Match either the regular expression below...
		 *       Match the characters "ftp"
		 *    Or match the regular expression number 2 below
		 *       Match the characters "http" literally
		 * Match the characters "://" literally
		 * Match the regular expression below and capture its match into backreference number 3
		 *    Match any character that is not a whitespace character
		 *       Between one and unlimited times, as many times as possible, giving back as needed (greedy)
		 *
		 */
		$regex = '/(?:\"|&#34;)([^\(?:\"|&#34;)]+)(?:\"|&#34;):(http|https|ftp):\/\/([^\\s]+)/';
		$text = preg_replace($regex, '<a href="$2://$3">$1</a>', $text);

		/**
		 * Image links.
		 *
		 * \!(https?|ftp|file):\/\/([-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])\!
		 *
		 * Match the character “!” literally «\!»
		 * Match the regular expression below and capture its match into backreference number 1 «(https?|ftp|file)»
		 *    Match either the regular expression below (attempting the next alternative only if this one fails) «https?»
		 *       Match the characters “http” literally «http»
		 *       Match the character “s” literally «s?»
		 *          Between zero and one times, as many times as possible, giving back as needed (greedy) «?»
		 *    Or match regular expression number 2 below (attempting the next alternative only if this one fails) «ftp»
		 *       Match the characters “ftp” literally «ftp»
		 *    Or match regular expression number 3 below (the entire group fails if this one fails to match) «file»
		 *       Match the characters “file” literally «file»
		 * Match the character “:” literally «:»
		 * Match the character “/” literally «\/»
		 * Match the character “/” literally «\/»
		 * Match the regular expression below and capture its match into backreference number 2 «([-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])»
		 *    Match a single character present in the list below «[-A-Z0-9+&@#\/%?=~_|!:,.;]*»
		 *       Between zero and unlimited times, as many times as possible, giving back as needed (greedy) «*»
		 *       The character “-” «-»
		 *       A character in the range between “A” and “Z” «A-Z»
		 *       A character in the range between “0” and “9” «0-9»
		 *       One of the characters “+&@#” «+&@#»
		 *       A / character «\/»
		 *       One of the characters “%?=~_|!:,.;” «%?=~_|!:,.;»
		 *    Match a single character present in the list below «[-A-Z0-9+&@#\/%=~_|]»
		 *       The character “-” «-»
		 *       A character in the range between “A” and “Z” «A-Z»
		 *       A character in the range between “0” and “9” «0-9»
		 *       One of the characters “+&@#” «+&@#»
		 *       A / character «\/»
		 *       One of the characters “%=~_|” «%=~_|»
		 * Match the character “!” literally «\!»
		 *
		 */
		$regex = '/\!(https?|ftp|file):\/\/([-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])\!/i';
		$text = preg_replace($regex, '<img src="$1://$2">', $text);

		return $text;
	}

    /**
     * Blocks & line breaks formatting.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _blocks($text) {
        // paragraphs
		$output = '';
		foreach (explode("\n\n", $text) as $paragraph) $output .= '<elmo><p>' . $paragraph . '</p><elmo>';
        $text = $output;

		/**
		 * Line break tags (<br>).
		 *
		 * (.+)\n
		 *
		 * Match the regular expression below and capture its match into backreference number 1 «(.+)»
		 *    Match any single character that is not a line break character «.+»
		 *       Between one and unlimited times, as many times as possible, giving back as needed (greedy) «+»
		 * Match a line feed character «\n»
		 *
		 */
		$text = preg_replace('/(.+)\n/', "$1<br /><elmo>", $text);

		return $text;
	}

    /**
     * Adds newline characters for nice formatting.
     *
     * @param string $text Textile tagged input text
     * @return string Processed text
     */
	private static function _newlines($text) {
        // give us back newlines for nice formatting
		return str_replace('<elmo>', "\n", $text);
	}

}