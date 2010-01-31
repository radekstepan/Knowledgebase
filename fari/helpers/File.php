<?php if (!defined('FARI')) die();

/**
 * Files listing and uploading.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_File {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Files listing and uploading'; }
	
	/**
	 * File upload.
	 *
	 * @param string $directoryPath Where to upload to
	 * @param string $inputUserFile The <input> element name passed from a form
	 * @return array Status and a message
	 */
        public static function upload($directoryPath, $inputUserFile='userfile') {
                // only allow uploads in 'our' directory
		$directoryPath = BASEPATH . self::addTrailingSlash($directoryPath);
                
                // check that a file was selected
		if (!isset($_FILES[$inputUserFile])) {
			return array(
                                'status' => 'fail',
                                'message' => 'No file selected.'
                        );
		}
                
                // check that path is valid
                if (!is_dir($directoryPath)
		    || !is_writable($directoryPath)) {
			return array(
                                'status' => 'fail',
                                'message' => 'The upload path is not writable.'
                        );
                }
                
                // was the file uploaded?
		if (!is_uploaded_file($_FILES[$inputUserFile]['tmp_name'])) {
			// determine the cause of upload error
                        return self::_findUploadError($inputUserFile);
		}
                
                // convert (escape) new filename
                $fileName = Fari_Escape::file($_FILES[$inputUserFile]['name'], TRUE);
                
                // move to destination directory
                if (!@move_uploaded_file($_FILES[$inputUserFile]['tmp_name'], $directoryPath . $fileName)) {
                        return array(
                                'status' => 'fail',
                                'message' => 'File upload error.'
                        );
		}
                
                return array(
                        'status' => 'success',
                        'message' => 'File \'' . $fileName . '\' uploaded succesfully.'
                );
        }
        
	/**
	 * Make a directory on the server.
	 *
	 * @param string $directoryPath Path where to create the directory
	 * @param string $directoryName Name of the directory to create
	 * @param int $permissions Permissions to apply to the directory
	 * @return array Status and a message
	 */
	public static function mkdir($directoryPath, $directoryName, $permissions=0755) {
                // only allow uploads in 'our' directory
		$directoryPath = BASEPATH . self::addTrailingSlash($directoryPath);
		
		// check that path is valid
                if (!is_dir($directoryPath)
		    || !is_writable($directoryPath)) {
			return array(
                                'status' => 'fail',
                                'message' => 'The path is not writable.'
                        );
                }
		
		// escape dirname
                $directoryName = Fari_Escape::directory($directoryName, TRUE);
		
		// does directory already exist?
		if (is_dir($directoryPath . $directoryName)) {
			return array(
                                'status' => 'fail',
                                'message' => 'Directory \'' . $directoryName . '\' already exists.'
                        );
		}
		
		// make a directory
		if (!@mkdir($directoryPath . $directoryName, $permissions, TRUE)) {
			return array(
                                'status' => 'fail',
                                'message' => 'Failed to create a folder.'
                        );
		}
		
		return array(
                        'status' => 'success',
                        'message' => 'Directory \'' . $directoryName . '\' created succesfully.'
                );
	}
	
	/**
	 * Deletes a file/directory.
	 *
	 * @param string $directoryPath Path to the file/directory
	 * @param string $name Name of the directory/file to delete
	 * @param boolean $basepath Used in recursion, don't modify!
	 * @return array Status and a message
	 */
	public static function delete($directoryPath, $name, $basepath=FALSE) {
		// add a slash if is missing
		$directoryPath = self::addTrailingSlash($directoryPath);
		// only allow delete in 'our' directory
		if (!$basepath) $directoryPath = BASEPATH . $directoryPath;
		
		// is it a file?
		if (is_file($directoryPath . $name)) {
			// try to delete
			if (!@unlink($directoryPath . $name)) {
				return array(
					'status' => 'fail',
					'message' => 'Failed to delete a file.'
				);
			} else return array(
					'status' => 'success',
					'message' => 'File \'' . $name . '\' deleted succesfully.'
				);
		// is it a directory?
		} elseif (is_dir($directoryPath . $name)) {
			// traverse items in the folder
			foreach (self::listing($directoryPath . $name, TRUE, TRUE) as $item) {
				// recursive delete
				self::delete($directoryPath . $name, $item['name'], TRUE);
			}
			// actual delete of the folder
			if (!@rmdir($directoryPath . $name)) {
				return array(
					'status' => 'fail',
					'message' => 'Failed to delete a folder.'
				);
			} else return array(
					'status' => 'success',
					'message' => 'Directory \'' . $name . '\' deleted succesfully.'
				);
		} else return array(
				    'status' => 'fail',
				    'message' => 'Object \'' . $name . '\' doesn\'t exist.'
				);
	}
	
	/**
	 * Adds a trailing slash if not present so that directory listings etc work well (at all).
	 *
	 * @param string $directoryPath Path
	 * @return string Directory path with a trailing slash
	 */
	public static function addTrailingSlash($directoryPath) {
		// add a trailing slash if one is not present
		if (substr($directoryPath, -1, 1) != '/') $directoryPath .= '/';
		return $directoryPath;
	}
	
        /**
         * List and return an array of a directory on the server.
         *
	 * @param string $directoryPath Path to the directory
	 * @param boolean $recursive Set to TRUE if you want subsubdirectories and subsubfiles as well
	 * @param boolean $basepath Used in recursion, don't modify!
	 * @return array Directory listing with stats
         */
        public static function listing($directoryPath, $recursive=FALSE, $basepath=FALSE) {
		// add a slash if is missing
		$directoryPath = self::addTrailingSlash($directoryPath);
		// only allow delete in 'our' directory
		if (!$basepath) $directoryPath = BASEPATH . $directoryPath;
                
		$listing = array();
                try {
                        // check that we can list the directory
                        if (!is_dir($directoryPath)) throw new Fari_Exception('Couldn\'t list the directory \'' .
									      $directoryPath . '.');
                        
                        // create new DirectoryIterator object
                        $iterator = new DirectoryIterator($directoryPath);
                        foreach ($iterator as $item) {
                                // if item is not a dot directory or starting with a dot...
                                if (!$item->isDot()
				   && (substr((string)$item, 0, 1) != '.')) {
                                        // add parameter to array stating if item is a directory or a file etc.
                                        $item = self::_getItemStats($item);
                                        // add traverse sub level if recursive and directory
                                        if ($recursive && $item['type'] == 'dir') {
                                                array_push($item, self::listing(self::addTrailingSlash($directoryPath) .
										$item['name'], TRUE, TRUE));
                                        }
                                        // add item with params to listing
                                        array_push($listing, $item);
                                }
                        }
			
                        return $listing;
                } catch (Fari_Exception $exception) { $exception->fire(); }
        }
        
        /**
         * Will return a MIME type for a given file.
         *
         * @param string $filePath Path to a file we want to determine mime type for
         * @return string MIME type
         */
        public static function getMime($filePath) {
                if (is_readable($filePath)) {
                        if (extension_loaded('fileinfo')) {
                                $mimeType = finfo_file(finfo_open(FILEINFO_MIME), $filePath);
                        } elseif (function_exists('mime_content_type')
				  && mime_content_type($filePath)) {
                                $mimeType = mime_content_type($filePath);
                        }
                        return $mimeType;
                }
	}
        
        /**
         * Determine the cause of upload error (based on CodeIgniter).
         *
	 * @param string $inputUserFile The <input> element name passed from a form
	 * @return array Directory listing with stats
         */
        private static function _findUploadError($inputUserFile) {
                $errorCode = (!isset($_FILES[$inputUserFile]['error'])) ? 4 : $_FILES[$inputUserFile]['error'];
		switch($errorCode) {
			case 1:
				return array(
					     'status' => 'fail',
                                             'message' => 'File exceeds the size limit.'
					     );
				break;
			case 6:
				return array(
					     'status' => 'fail',
                                             'message' => 'No temporary directory.'
					     );
				break;
			case 7:
				return array(
					     'status' => 'fail',
                                             'message' => 'Cannot write file.'
					     );
				break;
			default:
				return array(
					     'status' => 'fail',
                                             'message' => 'Error uploading the file.'
					     );
				break;
			}
        }
        
	/**
	 * Determine file/directory statistics for input item.
	 *
	 * @param DirectoryIterator $item Item passed from a DirectoryIterator
	 * @return array Listing with stats
	 */
        private static function _getItemStats(DirectoryIterator $item) {
                $list = array();
                
                // add filename
                $list['name'] = (string)$item;
                
                // add directory/file type
                $list['type'] = $item->getType();
                
                // add modification time
                $list['modified'] = date('Y-m-d H:i:s', $item->getMTime());
                
                // get permissions
                $list['permissions'] = $item->getPerms();
                
                // is writable?
                $list['writable'] = ($item->isWritable()) ? 1 : 0;
                
                // add path
                $list['path'] = $item->getPathName();
                $list['real_path'] = $item->getRealPath();
                
                // add size
                $list['size'] = Fari_Format::bytes($item->getSize());
                
                return $list;
        }
	
}