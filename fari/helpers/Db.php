<?php if (!defined('FARI')) die();

/**
 * Database connection and CRUD functions.
 * Input is not filtered here! But at least parametres are binded.
 *
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Db {

	/**
	 * DB connection instance
	 * @var PDO
	 */
	private static $instance;

	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Database connection and queries'; }

	/**
	 * Singleton pattern preventing __construct and __clone.
	 */
	private function __construct() { }
	private final function __clone() { }

	/**
	 * Connect to the database or return connection instance.
	 *
	 * @return PDO Instance of PDO connection
	 */
	private static function _connect() {
		// do we have an instance already?
		if (!self::$instance instanceof PDO) {
                        try {
				// which driver are we using?
				switch (strtolower(DB_DRIVER)) {
					// MySQL
					case 'mysql':
						self::$instance = new PDO('mysql:host=' .
									  DB_HOST . ';dbname=' .
									  DB_NAME .
									  ';unix_socket=/var/run/mysqld/mysqld.sock',
									  DB_USER, DB_PASS);
						break;
					// PostgreSQL (untested)
					case 'pgsql':
						self::$instance = new PDO('pgsql:dbname=' . DB_NAME . ';host=' .
									  DB_HOST, DB_USER, DB_PASS);
						break;
					// SQLite 3 that can only be under BASEPATH
					case 'sqlite':
						self::$instance = new PDO('sqlite:' . BASEPATH . '/' . DB_NAME);
						break;
				}
				// error mode on, throw exceptions
				self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $exception) {
                                try {
                                        throw new Fari_Exception('Cannot connect to DB: ' .
								 $exception->getMessage() . '.');
                                } catch (Fari_Exception $exception) { $exception->fire(); }
			}
		}
                // queries executed counter
                $_SESSION['Fari\Benchmark\Queries']++;
                // return an instance
                return self::$instance;
	}

    /**
     * Select from a table and return an array.
     *
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Table
     */
    public static function select($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL, $toString=FALSE) {
        // connect to the database
        $dbHandler = self::_connect();
        // form sql statement
		try {
            $sql = 'SELECT ' . $columns . ' FROM ' . $table;
            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . self::_buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // add ordering and limit clauses
            if (isset($order)) $sql .= ' ORDER BY ' . $order;
			if (isset($limit)) $sql .= ' LIMIT ' . $limit;

			// prepare statement
            $statement = $dbHandler->prepare($sql);

			// bind id parametres
            if (is_array($where)) $statement = self::_bindParametres($where, $statement);

            if ($toString) self::_toString($statement->queryString, NULL, $where);
            else {
                // execute query
                $statement->execute();
                // return associative array
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot select from ' . $table . ': ' .
                    $exception->getMessage() . '.');
                } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Select from a table and return an array (echo query string).
     *
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return echo Query string to the view
         */
        public static function selectString($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL) {
                self::select($table, $columns, $where, $order, $limit, TRUE);
        }

    /**
     * Select a single row from a table and return as a one-dimensional array.
     *
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Table
     */
    public static function selectRow($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL, $toString=FALSE) {
        if ($toString) self::select($table, $columns, $where, $order, $limit, TRUE);
        else {
                $result = self::select($table, $columns, $where, $order, $limit);
                return $result[0];
        }
    }

    /**
     * Select a single row from a table and return as a one-dimensional array (echo query string).
     *
     * @param string $table Database table we work with
     * @param string $columns Columns to return
     * @param array/string $where Where clause in a form array('column' => 'value')
     * @param string $order Order by clause
     * @param string $limit Limit by clause
     * @return array Table
     */
    public static function selectRowString($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL) {
            self::selectRow($table, $columns, $where, $order, $limit, TRUE);
    }

	/**
     * Insert into a table.
     *
     * @param string $table Database table we work with
	 * @param array $values Values to insert in a form array('column' => 'value')
	 * @return void
     */
     public static function insert($table, array $values, $toString=FALSE) {
        // connect to the database
		$dbHandler = self::_connect();

		// can't reuse param binding easily as we have (columns) VALUES (values) and not column = value
		$columns = implode(', ', array_keys($values));
		$valuesQuoted = implode(', ', self::_addQuotes($values));

		// form sql statement
		try {
            $sql = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $valuesQuoted . ')';

            // prepare statement
			$statement = $dbHandler->prepare($sql);

                        if ($toString) self::_toString($statement->queryString, $values);
                        else $statement->execute(); // execute query
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot insert into ' . $table . ': ' .
                    $exception->getMessage() . '.');
                } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Insert into a table (echo query string).
     *
     * @param string $table Database table we work with
     * @param array $values Values to insert in a form array('column' => 'value')
     * @return void
     */
    public static function insertString($table, array $values) {
        self::insert($table, $values, TRUE);
    }

	/**
     * Update a table.
     *
     * @param string $table Database table we work with
	 * @param array $values Values to insert in a form array('column' => 'value')
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @return void
     */
     public static function update($table, array $values, $where=NULL, $toString=FALSE) {
        // connect to the database
		$dbHandler = self::_connect();

		// form sql statement
		try {
			// use set0, set1 for parameter preparation for binding
            $sql = 'UPDATE ' . $table . ' SET ' . self::_buildColumns(array_keys($values), 'set', ',');

            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . self::_buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // prepare statement
			$statement = $dbHandler->prepare($sql);

			// bind set and id parametres
			$statement = self::_bindParametres($values, $statement, 'set');
           if (is_array($where)) $statement = self::_bindParametres($where, $statement);

            if ($toString) self::_toString($statement->queryString, $values, $where);
            else $statement->execute(); // execute query
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot update ' . $table . ': ' . $exception->getMessage());
                } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Update a table (echo query string).
     *
     * @param string $table Database table we work with
     * @param array $values Values to insert in a form array('column' => 'value')
     * @param array $where Where clause in a form array('column' => 'value')
     * @return void
     */
    public static function updateString($table, array $values, array $where=NULL) {
            self::update($table, $values, $where, TRUE);
    }

    /**
     * Delete from a table.
     *
     * @param table name
     * @param string $table Database table we work with
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @return void
     */
    public static function delete($table, $where=NULL, $toString=FALSE) {
        // connect to the database
		$dbHandler = self::_connect();

        // form sql statement
		try {
            $sql = 'DELETE FROM ' . $table;

            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . self::_buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // prepare statement
			$statement = $dbHandler->prepare($sql);

			// bind id parametres
            if (is_array($where)) $statement = self::_bindParametres($where, $statement);

            if ($toString) self::_toString($statement->queryString, NULL, $where);
            else $statement->execute(); // execute query
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot delete from ' . $table . ': ' . $exception->getMessage());
                } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Delete from a table (echo query string).
     *
     * @param table name
     * @param string $table Database table we work with
     * @param array $where Where clause in a form array('column' => 'value')
     * @return void
     */
    public static function deleteString($table, array $where=NULL) {
            self::delete($table, $where, TRUE);
    }

    /**
     * Take a value from subarray and use it as a key (e.g.: use on 'settings' arrays)
     *
     * @param array $array Array with data
     * @param string $key Key to use
     * @return array Formatted array
     */
    public static function toKeyValues(array $array, $key) {
        // traverse the input
        foreach ($array as $arrayKey => $value) {
            // create a new array entry
            $array[$value[$key]] = $value;
            // unset the original key and redundant subarray key
            unset($array[$arrayKey]); unset($array[$value[$key]][$key]);
        }
        return $array;
    }

	/**
	 * Will add quotes to column values when inserting into a database.
	 *
	 * @param string/array $values Value string or array of values to 'quoteize'
	 * @return array Array with values in quotes
	 */
	private static function _addQuotes($values) {
		// in case we work with a string
		if (!is_array($values)) return "'$values'";
		// in case we work with an array
		foreach($values as &$value) {
			$value = "'$value'";
		}
		return $values;
	}

    /**
     * The columns builder. Will create numbered :id params that will can be binded.
     *
     * @param string/array $columns Column = param
     * @param string $id ID parameter that will be binded, e.g., id
     * @param string $separator Separator between columns, e.g., AND
     * @return string Query with prepped prams
     */
    private static function _buildColumns($columns, $id='id', $separator='AND') {
        $sql = '';
		// are we adding an array of values?
		if (is_array($columns)) {
            // start the WHERE clause
            $count = count($columns);
            // traverse the passed arguments
			for ($i=0; $i<$count; $i++) {
                $sql .= $columns[$i] . ' = :' . $id . $i; // add where id0, id1 etc. clauses
                if ($i < $count-1) $sql .= ' ' . $separator . ' '; // add AND if we are to add more stuff
            }
        } else $sql .= $columns . ' = :' . $id; // just one parameter

		return $sql;
    }

    /**
     * Bind prepped values in a statement of form :id0, id1 etc.
     *
     * @param string/array $values Values we want to bind instead of numeric :id
     * @param string $statement An SQL statement
     * @param string $id ID parameter that will be binded, e.g., id
     * @return string Statement with values binded
     */
    private static function _bindParametres($values=NULL, $statement, $id='id') {
        // return if nothing to bind
        if (!isset($values)) return $statement;
        try {
            // in case we pass array on...
            if (is_array($values)) {
                // initialize counter for id0 etc..
				$i = 0;
				// traverse values, keys are not numeric
                foreach ($values as $value) {
                    // set parameter data type integer or string
                    $paramType = (Fari_Filter::isInt($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;

					// bind parameter :id0, :id1 etc.
                    $statement->bindValue(':' . $id . $i, $value, $paramType);

					// increase counter
					$i++;
                }
            // just one value passed on
			} else {
                // set parameter data type integer or string
                $paramType = (Fari_Filter::isInt($values)) ? PDO::PARAM_INT : PDO::PARAM_STR;

                // bind parameter :id
                $statement->bindParam(':' . $id, $values, $paramType);
            }

            return $statement;
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot bind parametres.');
                } catch (Fari_Exception $exception) { $exception->fire(); }
        }
    }

    /**
     * Echo the SQL statement into the view
     *
     * @param string $statement SQL query string
     * @param array $values The values to insert, update
     * @param array/string $where The where clause
     * @return echo Query string into the view
     */
    private static function _toString($statement, array $values=NULL, $where=NULL) {
        // traverse the values and where clause arrays
        if (is_array($where)) {
            $binder = 'set'; foreach (array($values, $where) as $array) {
                if (isset($array)) {
                    // replace bound parametres with actual values
                    $i=0; foreach ($array as $value) {
                            // determine value type of string or integer
                            $value = (Fari_Filter::isInt($value)) ? "$value" : "'$value'";
                            // we have a variable binding key
                            $statement = preg_replace("/:$binder$i/", $value, $statement);
                            $i++;
                    }
                }
                // a switch to keep track of which array are we traversing
                $binder = 'id';
            }
        }

        // echo into the view
        die("<pre>$statement</pre>");
    }

}