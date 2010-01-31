<?php if (!defined('FARI')) die();

/**
 * A Model class that, if extended by a child class will give that class the ability to call SELECT
 * statements on itself.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 
 * @package Fari
 */

class Fari_Model {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Select statements on the model\'s table'; }
	
	/**
	 * Get child class name that extends this parent class.
	 *
	 * @return string Child class name
	 */
	private static function _getChildClassName() {
		// we need to use PHP 5.3.0 at least
		try { if (version_compare(phpversion(), '5.2.9', '<') == TRUE) {
			// throw an exception
			throw new Fari_Exception('Fari MVC Framework requires PHP 5.3.0, you are using ' .
						 phpversion().'.');
			} else {
				return get_called_class();
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
	/**
	 * A select statement using Fari_Db::select() on itself.
	 * 
	 * @param string $columns Columns to return
	 * @param array $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Table
	 */
	public static function select($columns='*', $where=NULL, $id=NULL, $order=NULL, $limit=NULL) {
		try {
			// get table name
			$tableName = strtolower(self::_getChildClassName());
			return Fari_Db::select($tableName, $columns, $where, $order, $limit);
        } catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
}