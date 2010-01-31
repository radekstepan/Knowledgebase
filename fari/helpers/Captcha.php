<?php if (!defined('FARI')) die();

/**
 * A graphical mathematial captcha creation and checking.
 * 
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Captcha {
	
	/**
	 * Session storage
	 * @var const
	 */
	const SESSION_STORAGE = 'Fari\Captcha\\';
	
	/**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Image maptcha for forms'; }
	
	/**
	 * Create a maptcha and return it as an image. Save correct answer in the session.
	 *
	 * @param string $name Name of the captcha answer in the session
	 * @return image/png
	 */
	public static function create($name='Default') {
		// first number
		$firstNumber = mt_rand(1, 9);
		// second number
		$secondNumber = mt_rand(1, 9);
		// save correct result to a session (and encrypt)
		$_SESSION[self::SESSION_STORAGE . $name] = sha1($firstNumber + $secondNumber);
		
		// return the question as an image
		return self::_textToImage($firstNumber . ' + ' . $secondNumber . ' =');
	}
	
	/**
	 * Check that a captcha answer is valid.
	 *
	 * @param string $unsafeAnswer Unsafe answer from the user to check
	 * @param string $name Name of the captcha answer in the session
	 * @return boolean TRUE if answer is correct, FALSE otherwise
	 */
	public static function isValid($unsafeAnswer, $name='Default') {
		// escape unsafe token input
		$unsafeAnswer = Fari_Escape::text($unsafeAnswer);
		
		// check if token is valid
		return (sha1($unsafeAnswer) == $_SESSION[self::SESSION_STORAGE . $name]) ? TRUE : FALSE;
	}
	
	/**
	 * Convert text to an image and add noise.
	 *
	 * @param string $question Text we want to display
	 * @param int $height Height of the resulting image
	 * @param int $width Width of the resulting image
	 * @return image/png
	 */
	private static function _textToImage($question, $height=15, $width=60) {
		try {
			// create a new image of size 60x15 and test if we have GD library in the process
			@$image = imagecreate($width, $height);
			if (!isset($image)) throw new Fari_Exception('Can\'t create an image.');
		} catch (Fari_Exception $exception) { $exception->fire(); }
			
		// white background
		$colorBackground = imagecolorallocate($image, 255, 255, 255);
		// black text
		$colorText = imagecolorallocate($image, 0, 0, 0);
		// color for noise (light gray)
		$colorNoise = imagecolorallocate($image, 190, 190, 190);
		
		for ($i=0; $i<3; $i++) {
			// add noise first formed of question text
			imagestring($image, 0, $i * ($width / 3), $i * ($height / 3), $question, $colorNoise);
		}
		// write question text (size, left, top)
	      	imagestring($image, 4, 0, 0, $question, $colorText);
		
		// set image header
		if (!headers_sent()) @header('Content-type: image/png');
		
		// make PNG img
		imagepng($image);
		imagecolordeallocate($image, $colorText);
		imagecolordeallocate($image, $colorBackground);
		
		// output the resulting image
		imagedestroy($image);
	}
	
}