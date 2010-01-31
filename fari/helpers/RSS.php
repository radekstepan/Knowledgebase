<?php if (!defined('FARI')) die();

/**
 * Creates an RSS feed.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_RSS {
	
	/**
	 * Article title table column
	 * @var string
	 */
	private $articleTitle = '';

	/**
	 * Article link table column
	 * @var string
	 */
	private $articleLink = '';
	
	/**
	 * Article short description table column
	 * @var string
	 */
	private $articleDescription = '';
	
	/**
	 * Article publication date
	 * @var string
	 */
	private $articleDate = '';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Creates an RSS feed'; }
	
	/**
	 * Setup the RSS Feed. Parametres represent table columns to be used.
	 *
	 * @param string $articleTitle Article title table column
	 * @param string $articleLink Article link table column
	 * @param string $articleDescription Article short description table column
	 * @param string $articleDate Article publication date
	 */
	public function __construct($articleTitle, $articleLink, $articleDescription, $articleDate) {
		$this->articleTitle = $articleTitle;
		$this->articleLink = $articleLink;
		$this->articleDescription = $articleDescription;
		$this->articleDate = $articleDate;
	}
	
        /**
         * Builds and returns an RSS feed (check data on db insert!).
         *
         * @param string $feedTitle Title of the feed
         * @param string $feedURL Link to the feed
         * @param string $feedDescription Description of this feed
         * @param string $items Database table
         * @param boolean $isDateInRSS Set to TRUE if dates in tn the $items table are already in RSS format
         * @return string RSS Feed
         */
	public function create($feedTitle, $feedURL, $feedDescription, $items, $isDateInRSS=FALSE) {
		// escape input
		$feedTitle = Fari_Escape::XML($feedTitle);
		$feedURL = Fari_Escape::XML($feedURL);
		$feedDescription = Fari_Escape::XML($feedDescription);
		
		// set publishing date in RSS format
		$feedPublished = date(DATE_RSS);
		
		// start dom string
 		$DOMDocument = new DOMDocument('1.0', 'UTF-8');
		
		// form columns, we will use the info when traversing articles (and on the line below)
		$columns = $this->articleTitle . ', ' . $this->articleLink . ', ' . $this->articleDescription .
			', ' . $this->articleDate;
		
		// get items from the database if we are not passing a formed array already
                if (!is_array($items)) $items = Fari_Db::select($items, $columns);
		
		// <rss>
		$rootNode = $DOMDocument->createElement('rss');
		// use RSS version 2.0 attribute
		$rootNode->setAttribute('version', '2.0');
		$DOMDocument->appendChild($rootNode);
		
		// <rss><channel>
		$channel = $rootNode->appendChild($DOMDocument->createElement('channel'));
		
		// create the header
		// <rss><channel><title>
		$channel->appendChild($DOMDocument->createElement('title', $feedTitle));
		
		// <rss><channel><link>
		$channel->appendChild($DOMDocument->createElement('link', $feedURL));
		
		// <rss><channel><description>
		$channel->appendChild($DOMDocument->createElement('description', $feedDescription));
		
		// <rss><channel><pubDate>
		$channel->appendChild($DOMDocument->createElement('pubDate', $feedPublished));
		
		// column to RSS form 'conversion', elements have to follow that order...
		$articleColumns = explode(', ', $columns);
		$RSSColumns = array('title', 'link', 'description', 'pubDate');
		
		// traverse items now
		foreach ($items as $article) {
			// <rss><channel><item>
			$articleNode = $channel->appendChild($DOMDocument->createElement('item'));
			
			// traverse the items array consisting of 4 elements
			for ($i=0; $i<4; $i++) {;
				// <rss><channel><item><$column>
				
				// <$column> value, escaped
				$columnText = Fari_Escape::XML($article[$articleColumns[$i]]);
				// do we need to fix RSS pubDate?
				if ($RSSColumns[$i] == 'pubDate'
				    && !$isDateInRSS) $columnText = Fari_Format::date($columnText, 'RSS');
					
				$articleNode->appendChild($DOMDocument->createElement($RSSColumns[$i], $columnText));
                        }
                }
		
                // generate XML and return
 		$DOMDocument->formatOutput = TRUE;
 		return $DOMDocument->saveXML();
        }
	
}