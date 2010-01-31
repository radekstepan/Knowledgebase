<?php if (!defined('FARI')) die();

/**
 * Pagination of table results.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Paginator {
	
	/**
	 * What is the page requested?
	 * @var string
	 */
	private $pageRequested = 1;
	
	/**
	 * How many items to display per page?
	 * @var string
	 */
	private $itemsPerPage = 10;
	
	/**
	 * Range of pages to display in the paginator
	 */
	private $rangeToDisplay = 3;
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Pagination of db results'; }
	
	/**
	 * Setup the paginator.
	 *
	 * @param int $itemsPerPage How many items to display per page?
	 * @param int $rangeToDisplay Range of pages to display in the paginator
	 */
	public function __construct($itemsPerPage=10, $rangeToDisplay=3) {
		// set items to display per page
		$this->itemsPerPage = $itemsPerPage;
		// set the paginator range
		$this->rangeToDisplay = $rangeToDisplay;
	}
	
	/**
	 * A select query on a table of data.
	 *
	 * @param int pageRequested Number of page requested, will default to 1
	 * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param string/array $where WHERE $where = $id
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Array of 'paginator' and 'items' arrays
	 */
	public function select($pageRequested, $table, $columns='*', $where=NULL, $id=NULL, $order=NULL) {
		// get a table total of items
		$itemsTotal = $this->getItemsTotal($table, $where, $id);
		
		// if we don't have any items, return two empty arrays
		if ($itemsTotal == 0) return array('items' => array(), 'paginator' => array());
		
		// we must have some items then :)
		// set the requested page and return pages total
		$pagesTotal  = $this->setPageRequested($pageRequested, $itemsTotal);
		
		// build paginator around the page requested using range
		// will be the lowest among requested range and the total
		$maximumPage = min($this->pageRequested + $this->rangeToDisplay, $pagesTotal);
		// will be the highest value between the range requested and the first page
		$page = max(1, $this->pageRequested - $this->rangeToDisplay);
		
		// start a list
		$paginator = array();
		// traverse and build listing with 'classes'
		for ($page; $page<=$maximumPage; $page++) {
			// are we on current page?
			if ($this->pageRequested == $page) array_push($paginator, array('number' => $page,
											'class' => 'current'));
			else array_push($paginator, array('number' => $page,
							  'class' => 'page'));
		}
		
		// now get the actual paginated data
		// data offset
		$offset = ($this->pageRequested - 1) * $this->itemsPerPage;
		// SQL offset,limit
		$itemsLimit = $offset . ',' . $this->itemsPerPage;

                // db query
                $itemsArray = Fari_Db::select($table, $columns, $where, $order, $itemsLimit);
                // return arrays 'items' and 'paginator'
                return array('items' => $itemsArray, 'paginator' => $paginator);
	}

	/**
	 * Will set a valid page number requested for pagination and return number of pages in the query.
	 *
	 * @param int $requestedPage Page requested by user, can be invalid!
	 * @param int $itemsTotal Number of items in the query result
	 * @return int Pages total count
	 */
	private function setPageRequested($requestedPage, $itemsTotal) {
		// get the total number of pages we can display
		$pagesTotal = ceil($itemsTotal / $this->itemsPerPage);
		
		// set to first page if request invalid (not within the the min page, max page range)
		if (!Fari_Filter::isInt($requestedPage, array(1, $pagesTotal))) $requestedPage = 1;
		
		// set page requested
		$this->pageRequested = $requestedPage;
		
		return $pagesTotal;
	}
	
	/**
	 * Calculate the total number of items in a query.
	 *
	 * @param string $table Database table we work with
	 * @param string/array $where WHERE $where = $id
         * @return int Items total count
	 */
	private function getItemsTotal($table, $where=NULL) {
		// count total
		$array = Fari_Db::select($table, "COUNT(*) AS total", $where);
		
		// why this way? to reuse select() easily
		return $array[0]['total'];
	}
	
}