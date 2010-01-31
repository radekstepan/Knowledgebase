<?php if (!defined('FARI')) die();

/**
 * A calendar class returning formatted calendar months into the View:
 *
 *         <?php foreach($calendar as $monthName => $month): ?>
            <div class="calendar">
                <h2><?php echo $monthName; ?></h2>
                <table>
                    <tr><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th><th>S</th></tr>
                    <tr><?php foreach($month as $day): ?>
                        <?php echo (substr($day, 0, 1) == '*') ?
                        '<td id="'.$monthName.'-'.substr($day, 1).'">'.substr($day, 1).'</td></tr><tr>' :
                        '<td id="'.$monthName.'-'.$day.'">'.$day.'</td>'; ?>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
 *
 *
 * @author Radek Stepan <radek.stepan@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Fari
 */

class Fari_Calendar {

    /**
	 * Returns a class description.
	 *
	 * @return string
	 */
	public static function _desc() { return 'Calendar generator and formatter'; }

    /**
     * Main point of entry for text parsing.
     *
     * @param int $months The number of months to display
     * @param string $date "n-Y" formatted date when to start calendar
     * @return string HTML formatted calendar ready to echo in the View
     */
    public static function get($months, $date=NULL) {
        // determine today's date
        if (isset($date)) {
            // input date separated by '-'
            $date = explode('-', $date);
            // check and use passed month
            if (!empty($date[0]) && Fari_Filter::isInt($date[0], array(0, 12))) {
                $startMonth = $date[0];
            } else $startMonth = date('n');
            // check and use passed year
            if (!empty($date[1]) && Fari_Filter::isInt($date[1], array(1900, 2999))) {
                $startYear = $date[1];
            } else $startMonth = date('n');
        } else {
            $startMonth = date('n'); // 0 - 12
            $startYear = date('Y'); // 1984
        }

        $result = array();
        // check that the number of months to display is a positive int, default to 4
        $months = ($months > 0) ? $months : 4;
        // get us x months
        for ($i = 0; $i < $months; $i++) {
            // we are changing the year
            if ($startMonth + $i > 12) {
                $startMonth = 0; $startYear++;
            }
            $result = self::_getMonth($startMonth + $i, $startYear, $result);
        }
        return $result;
    }
    
    /**
     * Get formatted month.
     *
     * @param int $month Month
     * @param int $year Year
     * @return string Result to add to
     */
    private static function _getMonth($month, $year, $result) {
        // what is the 1st of the month?
        $monthFirst = date("N", mktime(0, 0, 0, $month, 1, $year)); // 1 - 7
        // number of days in the month
        $monthDays = date("t", mktime(0, 0, 0, $month, 1, $year)); // 28, 29, 30, 31?

        $array = array();
        // traverse the month
        for ($i = 1; $i <= $monthDays+1; $i++) {
            if ($i >= $monthFirst) {
                // a week separator
                $array[] = ($i % 7 == 0) ? '*'.($i-$monthFirst+1) : ($i-$monthFirst+1);
            } else $array[] = '';
        }
        // fill the rest of the table
        for ($i; $i <= 35; $i++) $array[] = '';
        $result[date('F', mktime(0, 0, 0, $month, 1, 1984))] = $array; // key is teh month name
        return $result;
    }
    
}