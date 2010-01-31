<?php if (!defined('FARI')) die();

/**
 * Creates a sitemap.
 *
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Sitemap {

	/**
	 * A default link priority
	 * @var const
	 */
	const LINK_PRIORITY = 0.9;

	/**
	 * Slug, or a column with a name of a page
	 * @var string
	 */
	private $linkSlug = '';

	/**
	 * Column with last modification date of a page
	 * @var string
	 */
	private $lastModificationDate = '';

	/**
	 * Column with a page priority setting
	 * @var string
	 */
	private $pagePriority = '';

	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Creates a sitemap'; }

	/**
	 * Setup the sitemap. Parametres represent table columns to be used.
	 *
	 * @param string $linkSlug Slug, or a column with a name of a page
	 * @param string $lastModificationDate Optional column with last modification date of a page
	 * @param string $pagePriority Optional column with a page priority setting
	 */
	public function __construct($linkSlug='slug', $lastModificationDate=NULL, $pagePriority=NULL) {
		$this->linkSlug = $linkSlug;
		$this->lastModificationDate = $lastModificationDate;
		$this->pagePriority = $pagePriority;
	}

        /**
         * Builds and returns an XML sitemap.
         * @uses date in standard db form, W3C Datetime (YYYY-MM-DD)
         *
         * @param string/array $items Database table we work with or array of data already
         * @param string $linksURL URL to append slug links to (e.g., http://.$_SERVER['HTTP_HOST'].WWW_DIR.Controller)
         * @return XML sitemap
         */
	public function create($items, $linksURL=NULL) {
                // try determining this server's address if URL is not provided
		if (!isset($linksURL)) $linksURL = 'http://' . $_SERVER['SERVER_NAME'] . WWW_DIR;
		// add a trailing slash to URL
		$linksURL = Fari_File::addTrailingSlash($linksURL);

		// start dom string
 		$DOMDocument = new DOMDocument('1.0', 'UTF-8');
 		// <urlset> root
 		$rootNode = $DOMDocument->appendChild(
						$DOMDocument->createElementNS(
							'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset'));

 		// get items from the database if we are not passing a formed array already
                if (!is_array($items)) {
			// last modification date and page priority won't be provided
			if (!isset($this->lastModificationDate)
			    && !isset($this->pagePriority)) {
				$columns = $this->linkSlug;
			// last modification date won't be provided
			} elseif (!isset($this->lastModificationDate)) {
				$columns = $this->linkSlug . ', ' . $this->pagePriority;
			// page priority won't be provided
			} elseif (!isset($this->pagePriority)) {
				$columns = $this->linkSlug . ', ' . $this->lastModificationDate;
			// we will be provided with all params
			} else $columns = $this->linkSlug . ',' . $this->lastModificationDate . ',' . $this->pagePriority;

			// the actual call to the db
			$items = Fari_Db::select($items, $columns);
 		}

		// set default element text, page priority
		$pagePriorityText = self::LINK_PRIORITY;
		// set default element text, generate last modification date as now
		$lastModificationText = date('Y-m-d');

		// traverse through all records
 		foreach ($items as $item) {
			// <urlset><url>
	 		$URLNode = $rootNode->appendChild($DOMDocument->createElement('url'));

				// <urlset><url><loc> link address
				$URLNode->appendChild($DOMDocument->createElement('loc', $linksURL
											    . $item[$this->linkSlug]));

				// <urlset><url><lastmod> last modification date of the page
				if (isset($this->lastModificationDate)) {
						$lastModificationText = $item[$this->lastModificationDate];
                        // convert UNIX timestamp to well formed date if present
                        if (strlen($lastModificationText) == 10 && $lastModificationText > 1000000000) {
                            $lastModificationText = date('Y-m-d', $lastModificationText);
                        }
					}
				$URLNode->appendChild($DOMDocument->createElement('lastmod', $lastModificationText));

				// <urlset><url><priority> page priority
				if (isset($this->pagePriority)) $pagePriorityText = $item[$this->pagePriority];
				$URLNode->appendChild($DOMDocument->createElement('priority', $pagePriorityText));
 		}

                // generate XML and return
 		$DOMDocument->formatOutput = TRUE;
 		return $DOMDocument->saveXML();
        }

}