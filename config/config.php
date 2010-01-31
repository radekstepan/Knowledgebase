<?php if (!defined('FARI')) die();

/**
 * A config for your application, set db and app settings here.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

/**
 * Application settings
 */

// app directory; this directory contains your models, views & controllers
if (!defined('APP_DIR')) define('APP_DIR', 'application');
// application version
if (!defined('APP_VERSION')) define('APP_VERSION', 'Knowledgebase 0.6.0');
// default Controller for the application (pages in a CMS)
if (!defined('DEFAULT_CONTROLLER')) define('DEFAULT_CONTROLLER', 'search');
// set to FALSE on live version of your application
if (!defined('REPORT_ERR')) define('REPORT_ERR', TRUE);

/**
 * Database settings (in use by Fari_Db helper class)
 */

// mysql, pgsql, sqlite
if (!defined('DB_DRIVER')) define('DB_DRIVER', 'sqlite');
// localhost, 127.0.0.1
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
// database name
if (!defined('DB_NAME')) define('DB_NAME', 'db/database.db');
// database username
if (!defined('DB_USER')) define('DB_USER', '');
// database password
if (!defined('DB_PASS')) define('DB_PASS', '');

/**
 * FTP
 */
// ftp server (can be ssl)
if (!defined('FTP_HOST')) define('FTP_HOST', '');
// ftp account username
if (!defined('FTP_USER')) define('FTP_USER', '');
// ftp account password
if (!defined('FTP_PASS')) define('FTP_PASS', '');

/**
 * Timezone
 */

// set a default timezone
date_default_timezone_set('UTC');
