<?php if (!defined('FARI')) die();

/**
 * Export of database tables.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Backup {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Export of database tables'; }
	
        /**
         * Builds and returns an XML version of a table.
         *
         * @param string/array $items Database table we work with or array of data already
	 * @param string $columns Columns to export
	 * @param array $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
         * @return string XML backup of the table, headers not set
         */
	public static function toXML($items, $columns='*', array $where=NULL, $order=NULL, $limit=NULL) {
                // dom string
 		$DOMDocument = new DOMDocument('1.0', 'UTF-8');
                
 		// get items from the database if we are not passing a formed array already
                if (!is_array($items)) $items = Fari_Db::select($items, $columns, $where, $order, $limit);
                
                // <table> root
 		$table = $DOMDocument->appendChild($DOMDocument->createElement('table'));
 		
		// traverse through all records
 		foreach ($items as $item) {
                        // get array keys of the item
                        // we could explode $columns as well if they are passed
			$keys = array_keys($item);
                        
			// <table><row> elemenent we will always have
	 		$row = $table->appendChild($DOMDocument->createElement('row'));
                        
                        // traverse through keys/columns
	 		foreach ($keys as $column) {
                                // <table><row><column> value, escaped
				$row->appendChild($DOMDocument->createElement($column, Fari_Escape::XML($item[$column])));
                        }
 		}
                
                // generate xml and return
 		$DOMDocument->formatOutput = TRUE;
 		return $DOMDocument->saveXML();
        }
        
}