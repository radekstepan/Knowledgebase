<?php if (!defined('FARI')) die();

/**
 * Setup error reporting and register a shutdown function passign errors to the Fari_Diagnostics display.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

// set error reporting
error_reporting(E_ALL);
// hide displaying of errors, we will register a shutdown function...
ini_set('display_errors', 0);

/**
 * Fire up a diagnostics display or production server message if an error is raised.
 *
 * @return void
 */
function shutdown() {
        if ($error = error_get_last()) {
		extract($error);
		switch($type) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				// show diagnostics display
				if (REPORT_ERROR) Fari_Diagnostics::display('PHP Error', $file, $line, $message.'.');
				// show message on a production server
				else Fari_Diagnostics::productionMessage($message);
				break;
		}
        }
}
// register our 'error handler'
register_shutdown_function('shutdown');


/**
 * Exceptions handler for the application. Will call Fari_Diagnostics for display.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Exception extends Exception {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Custom Exceptions thrower'; }
	
	/**
	 * Fire up a display with error message, sourcecode and some trace.
	 *
	 * @return void
	 */
	public function fire() {
		// get the specifics of the error
		// where was the error thrown
		$file = $this->getFile();
		// line with the error
		$line = $this->getLine();
		// message we are outputing
		$message = $this->getMessage();
		// trace of error
		$trace = $this->getTrace();
		
		Fari_Diagnostics::display('Fari Exception', $file, $line, $message, $trace);
	}
	
}


/**
 * Diagnostics, working as a wrapper to provide a nice display for Errors and Exceptions.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Diagnostics {
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Provides a graphical wrapper for Errors and Exceptions'; }
	
	/**
	 * Display Error or an Exception.
	 *
	 * @param string $type PHP Error or Fari Exception
	 * @param string $file File where the error was thrown
	 * @param string $line Line on which the error was thrown
	 * @param string $message Message of the error
	 * @return void
	 */
	public static function display($type, $file, $line, $message, $trace=NULL) {
		// clean output
		ob_end_clean();
		
		// are we on a production server?
		if (!REPORT_ERR) self::productionMessage($message);
		
		// set color of the highlighting
		ini_set('highlight.string',	'#080');
		ini_set('highlight.comment',	'#999; font-style: italic');
		ini_set('highlight.default',	'#33393c');
		ini_set('highlight.html',	'#06b');
		ini_set('highlight.keyword',	'#d24; font-weight: bold');
		
		// 'build' the header
		self::_showHeader();
		
		// output the message to the user
		echo '<div id="message"><h1>' . $type . '</h1><br />' . $message . '</div>';
		
		// output information about the file
		echo '<div id="file">File: <b>' . $file . '</b> Line: <b>' . $line . '</b></div>';
		
		// show the source
		self::_showErrorSource($file, $line);
		
		// show trace if present
		if (!empty($trace)) self::_showErrorTrace($trace);
		
		// show declared classes
		self::_showDeclaredClasses();
		
		// output information about the application and framework version
		echo '<div id="file"><b>' . FARI . '</b> running <b>' . APP_VERSION . '</b></div>';
		
		// close the whole page properly
		echo '</body></html>';
		
		// end the misery...
		die();
	}
	
	private static function _showHeader() {
	?>
		<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<title>Fari Diagnostics</title>
			<style type="text/css">
				body{background:#fff;color:#33393c;font:12px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;margin:0px;}
				#message,.error{background-color:#FF0000;color:#fff;font-weight:bold;padding:1px 0px 1px 0px;font-size:100%;margin:0px;}
				#message{padding:5px 30px 10px 30px;}
					h1{margin-bottom:0px;font-weight:normal;font-size:175%;}
				#file{background-color:#F5F5F5;margin:10px 30px 0px 30px;padding:5px;
				border:1px solid #999999;}
				.code{background-color:#FFFFCC;padding:5px;border:1px solid #FFCC00;margin:10px 30px 10px 30px;}
					i{color:#999999;}
					.num{color:#9E9E7E;font-style:normal;font-weight:normal;}
					span.err{color:#FFF;}
				a{color:#4197E3;}
				table{font:16px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;font-size:100%;}
					td{padding-right:20px;}
			</style>
			<script type="text/javascript">
			<!--
				function toggle(id) {
				var e = document.getElementById(id);
				if(e.style.display == 'block')
					e.style.display = 'none';
				else
					e.style.display = 'block';
				}
			//-->
			</script>
		</head>
		<body>
	<?php
	}
	
	/**
	 * Output a syntax highlighted sourcecode.
	 * 
	 * @param string $errorFile Is the file where exception was thrown
	 * @param string $errorLine Line we want to highlight as troublesome
	 * @param string $displayRange How many source code lines before and after error line to show
	 * @param string $divId So that we can show/hide some source and then call it via js
	 * @return void
	 */
	private static function _showErrorSource($errorFile, $errorLine, $displayRange=6, $divId=0) {
		// get the source code into a string
		$sourceCode = highlight_file($errorFile, TRUE);
		// split into an array so that we can extract lines
		$sourceCode = explode('<br />', $sourceCode);
		
		// where (which line) to start showing source code? (start at line 1 ;)
		$beginLine = max(1, $errorLine - $displayRange);
		// where to stop?
		$endLine = min($errorLine + $displayRange, count($sourceCode));
		
		// open div with the error message
		if ($divId != '0') echo '<div id="' . $divId . '" class="code" style="display:none;">';
		else echo '<div id="' . $divId . '" class="code">';
		
		// highlighting might have started before we 'cut' it
		// set pointer to the beginning of our output
		$pointer = $beginLine;
		// while we haven't reached the start of file...
		while ($pointer-- > 0) {
			/**
			 * Match unlimited times any character that is not a \n
			 * Capture into backreference </span> OR <span *>.
			 */
			if (preg_match('%.*(</?span[^\>]*>)%', $sourceCode[$pointer], $match)) {
				// echo the highlighting tag if we've started it and not closed it
				if ($match[1] !== '</span>') echo $match[1];
				break;
			}
		}
		
		// paint the code
		// set pointer to the beginning of our output
		$pointer = $beginLine-1;
		// while we haven't reached the end of output...
		while (++$pointer <= $endLine) {
			// take our line from the source
			$line = $sourceCode[$pointer-1];
			// highlight our error line
			if ($pointer == $errorLine) {
				// strip formatting
				$line = strip_tags($line);
				// add tags
				echo '<h1 class="error"><span class="num err">' . $pointer . ':</span> &nbsp;&nbsp;&nbsp; ' . $line . '</h1>';
			// and output sourcecode line with delimiter
			} else echo '<span class="num">' . $pointer . ':</span> &nbsp;&nbsp;&nbsp; ' . $line . "<br />\n";
		}
		
		// close div
		echo '</div>';
	}
	
	/**
	 * Build a trace display with sourcecodes and all.
	 *
	 * @param array $errorTrace Contains an array with the trace as thrown
	 * @return void
	 */
	private static function _showErrorTrace(array $errorTrace) {
		// header
		echo '<div id="file"><b>Trace:</b>';
		// start the counter, we are humans so from 1
		$counter = 1;
		// traverse the array
		foreach ($errorTrace as $key => $row) {
			extract($row);
			echo '<br />';
			if (isset($file)) echo '<b>' . $counter . '.</b>&nbsp;&nbsp;' . $file;
			if (isset($line)) echo '&nbsp;&nbsp;(' . $line . ')';
			if (isset($function)) echo '&nbsp;&nbsp;' . $function . '()&nbsp;&nbsp;';
			
			// link to a javascript function that shows/hides the code listing
			echo '<a href="" onclick="toggle(\'' . $counter . '\');return false;" >source</a>';
			// add sourcecode listing
			self::_showErrorSource($file, $line, 6, $counter);
			$counter++; // add to the counter
		}
		echo "\n</div>"; // close her up
	}
	
	/**
	 * Shows declared classes and their descriptions.
	 * 
	 * @return void
	 */
	private static function _showDeclaredClasses() {
		// get declared classes in the order they were declared
		$declaredClasses = get_declared_classes();
		// show only application related classes, Fari_Exception is implemented if we can see this :)
		// a pointer to start from
		$pointer = array_search('Fari_Exception', $declaredClasses) - 1;
		
		// header
		echo '<div id="file"><b>Declared Classes:</b><table>';
		// go through the array...
		$classCount = count($declaredClasses);
		while ($pointer++ < $classCount) {
			// get class name
			$class = @$declaredClasses[$pointer];
			// output it
			echo "\n<tr><td>" . $class . '</td><td><i>';
			
			// get description if is implemented
                        @eval('if (method_exists($class, "_desc")) echo $class::_desc();');
                        // if (method_exists($class, '_desc')) echo $class::_desc(); // use from PHP 5.3.0
			
			// close description
			echo '</i></td</tr>';
		}
		// close her up
		echo '</table></div>';
	}
	
	/**
	 * Is called when we are on a production server and don't want to show the source code.
	 *
	 * @param string $errorMessage Message Thrown
	 * @return void
	 */
	public static function productionMessage($errorMessage) {
		// 'build' the header and show an apologetic message
		self::_showHeader();
	?>
		<div class="code" style="width:600px;margin:0 auto;margin-top:100px;">
		
		<!-- error message in English -->
		<h1>We are sorry...</h1><p><b><?php echo $errorMessage ;?></b>.<br />You would help us if you told
		us what were you doing at the time the error happened, we can then locate and fix the problem as
		soon as possible. Again, we are sorry for the inconvenience.</p>
		
		<!-- error message in Czech -->
		<h1>Omlouváme se...</h1><p><?php echo $errorMessage ;?></b>.<br />Pomohli byste nám kdybyste nám dali
		vědět co jste dělali když se tato chyba stala. Můžeme tak najít a opravit tuto chybu co nejrychleji
		. Znovu bychom se Vám rádi omluvili za tuto nepříjemnost.</p>
		
		</div></body></html>
	<?php
		// end the misery
		die();
	}

}