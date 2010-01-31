<?php if (!defined('FARI')) die();

class Knowledge {

    private static $stopwords = array('I', 'a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for',
            'how', 'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when',
            'where', 'who', 'will', 'with', 'und', 'the', 'www', 'from');

    public static function stems($text) {
        // split sentence into words
        $words = preg_split('/[^a-zA-Z\'"-]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        // stemmer plugin
        require_once BASEPATH . '/application/plugins/class.stemmer.inc';
        $stemmer = new Stemmer();

        $result = '';

        foreach ($words as $word) {
            // if is at least three characters and not in the list of stopwords...
            if ((strlen($word) > 2) && !in_array($word, self::$stopwords)) {
                // stem & attach to result
                $result .= $stemmer->stem(strtolower($word)) . ' ';
            }
        }
        // trailing space
        $result = substr($result, 0, -1);

        return $result;
    }

}
