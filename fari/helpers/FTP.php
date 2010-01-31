<?php if (!defined('FARI')) die();

/**
 * FTP connection and related queries.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_FTP {
	
	/**
	 * FTP connection instance
	 * @var Object
	 */
	private static $instance;
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'FTP connection and queries'; }
        
        /**
         * Connect to an FTP server.
         *
         * @return Object FTP connection
         */
        private static function _connect() {
                // do we have an instance already?
                if (!isset(self::$instance)) {
                        // give us an ftp connection
			// try ssl first
                        @$connection = ftp_ssl_connect(FTP_HOST);
                        if (!$connection) {
				// go old school
				@$connection = ftp_connect(FTP_HOST);
				// fail if we don't have a connection
				if (!$connection) return array(
							 'status' => 'fail',
							 'message' => 'Couldn\'t connect to the FTP server.'
							 );
			}
			
                        // login to the server
                        @$login = ftp_login($connection, FTP_USER, FTP_PASS);
                        // check that login went fine
                        if (!$login) return array(
						  'status' => 'fail',
						  'message' => 'Incorrect FTP account credentials.'
						  );
                        // change connection to a passive mode (connection initiated by client and not server)
                        ftp_pasv($connection, TRUE);
                        
			// set ftp connection
                        self::$instance = $connection;
                }
		return self::$instance;
        }
        
        /**
         * Upload a file via FTP.
         *
         * @param string $sourceFile Source file to upload
         * @param destination file
         * @return array Array with status, message
         */
        public static function upload($sourceFile, $destinationDirectory) {
                // connect to ftp
                $connection = self::_connect();
                
                // upload file
                @$upload = ftp_put($connection, $destinationDirectory, $sourceFile, self::_getTransferMode($sourceFile));
                if (!$upload) return array(
					   'status' => 'fail',
                                           'message' => 'Failed to upload a file via FTP.'
                                        );
                
                // close ftp connection
                ftp_close($connection);
		
		// success message
		return array(
			     'status' => 'success',
                             'message' => 'File \'' . $sourceFile . '\' uploaded succesfully.'
		);
        }
        
        /**
         * Change file permissions on the server.
         *
         * 	 0 / owner / owner's user group / everybody else
         *       1 = execute permissions
         *       2 = write permissions
         *       4 = read permissions
         *
         * @param string $file File to change permissions on
         * @param int $permissions Permissions to set (e.g., 0775, 0644 etc.)
         * @return array Array with status, message
         */
        public static function chmod($file, $permissions) {
                // connect to ftp
                $connection = self::_connect();
                
                // change permissions
                @$chmod = ftp_chmod($connection, $permissions, $file);
                if (!$chmod) return array(
					  'status' => 'fail',
                                          'message' => 'Failed to change file permissions.'
                                        );
                
		// close ftp connection
                ftp_close($connection);
		
		// success message
		return array(
			     'status' => 'success',
                             'message' => 'Permissions changed succesfully.'
		);
        }
        
        /**
         * Delete a file on the server.
         *
         * @param string $file File to delete
         * @return array Array with status, message
         */
        public static function delete($file) {
                // connect to ftp
                $connection = self::_connect();
                
                // delete file
                @$delete = ftp_delete($connection, $file);
                if (!$delete) return array(
					   'status' => 'fail',
                                           'message' => 'Couldn\'t delete the file.'
                                        );
		
                // close ftp connection
                ftp_close($connection);
		
		// success message
		return array(
			     'status' => 'success',
                             'message' => 'File \'' . $file . '\' deleted succesfully.'
		);
        }
        
        /**
         * Get and return a file from an FTP server.
         * @example Fari_Ftp::get('radekstepan.com/index.php', 'tmp/cache/index.php');
         *
         * @param string $getFile File to get
         * @param string $targetFile Target file to get to
         * @return string File
         */
        public static function get($getFile, $targetFile) {
                // connect to ftp
                $connection = self::_connect();
                
                // change to a directory if we need to
                $routeParametres = explode('/', $getFile);
		if (is_array($routeParametres)) {
                        // get the last element... the file (and pop it as is not a directory)
                        $getFile = array_pop($routeParametres);
                        
                        // change directory(-ies)
                        @$chdir = ftp_chdir($connection, implode('/', $routeParametres));
                        if (!$chdir) return array(
						  'status' => 'fail',
						  'message' => 'Couldn\'t change to the directory.'
                                        );
                }
                
                // get file
                @$get = ftp_get($connection, $targetFile, $getFile, self::_getTransferMode($getFile));
                if (!$get) return array(
					'status' => 'fail',
                                        'message' => 'Couldn\'t get the file.'
					);
                
		// close ftp connection
                ftp_close($connection);
		
		// return file
		return $get;
        }
        
        /**
         * Make a directory on an FTP server.
         *
         * @param string $directoryName Name of the directory we'd like to create, dir path accepted
         * @return array Array with status, message
         */
        public static function mkdir($directoryName) {
                // connect to ftp
                $connection = self::_connect();
                
		// change to a directory if we need to
                $routeParametres = explode('/', $directoryName);
		if (is_array($routeParametres)) {
                        // get the last element... the file (and pop it as is not a directory)
                        $directoryName = array_pop($routeParametres);
                        
                        // change directory(-ies)
                        @$chdir = ftp_chdir($connection, implode('/', $routeParametres));
                        if (!$chdir) return array(
						  'status' => 'fail',
						  'message' => 'Couldn\'t change to the directory.'
                                        );
                }
		
                // make directory
                @$mkdir = ftp_mkdir($connection, $directoryName);
                if (!$mkdir) return array(
					  'status' => 'fail',
                                          'message' => 'Couldn\'t create the directory.'
                                        );
		
                // close ftp connection
                ftp_close($connection);
		
		// success message
                return array(
			     'status' => 'success',
                             'message' => 'Directory \'' . $directoryName . '\'.'
			     );		
        }
        
        /**
         * List and return an array of a directory on the server.
         *
         * @param string $directory Directory to list
         * @param boolean $rawMode Optional to get more detailed dir listing
         * @return array Array with directory listing
         */
        public static function listing($directory, $rawMode=FALSE) {
                // connect to ftp
                $connection = self::_connect();
                
                // list directory directory
                // use raw mode, give more detailed listing
                @$list = ($rawMode) ? ftp_rawlist($connection, $directory) : ftp_nlist($connection, $directory);
                if (!is_array($list)) return array(
						   'status' => 'fail',
						   'message' => 'Couldn\'t list the directory.'
                                        );
                
		// close ftp connection
                ftp_close($connection);
		
		return $list;
        }
        
        /**
         * Get mode required for FTP transfers based on file extension.
         *
         * @param string $file File to figure out the mode for
         * @return const Transfer mode
         */
        private static function _getTransferMode($file) {
                // pop (gotta love the word) the extension
                $extension = end(explode('.', $file));
                // check for these
                switch ($extension) {
                        case '':
                        case 'am':
                        case 'asp':
                        case 'bat':
                        case 'c':
                        case 'cfm':
                        case 'cgi':
                        case 'conf':
                        case 'cpp':
                        case 'css':
                        case 'dhtml':
                        case 'diz':
                        case 'h':
                        case 'hpp':
                        case 'htm':
                        case 'html':
                        case 'in':
                        case 'inc':
                        case 'js':
                        case 'm4':
                        case 'mak':
                        case 'nfs':
                        case 'nsi':
                        case 'pas':
                        case 'patch':
                        case 'php':
                        case 'php3':
                        case 'php4':
                        case 'php5':
                        case 'phtml':
                        case 'pl':
                        case 'po':
                        case 'py':
                        case 'qmail':
                        case 'sh':
                        case 'shtml':
                        case 'sql':
                        case 'tcl':
                        case 'tpl':
                        case 'txt':
                        case 'vbs':
                        case 'xml':
                        case 'xrc':
                                return FTP_ASCII;
                }
                return FTP_BINARY;
        }
	
}