<?php if (!defined('FARI')) die();

class Search {

    // the weights applied
    private static $title = 1.5;
    private static $tags = 1.5;
    private static $source = 1.2;
    private static $category = 1.2;
    private static $type = 1;
    private static $text = 0.7;
    private static $comments = 0.25;
    // multiplier applied to tuple matches
    private static $tuple = 1.5;

    // fibonacci sequence to weigh down consecutive words used in a search
    private static $fib = array(1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597, 2584);

    public static function query($query) {
        // explode the query by space forming an array of searched for words
        $query = explode(' ', strtolower($query));
        // form an SQL LIKE
        $like = '';
        foreach ($query as $word) {
            $like .= "stems LIKE '%$word%' OR titleStems LIKE '%$word%' OR tags LIKE '%$word%' OR source LIKE '%$word%'
                OR category LIKE '%$word%' OR type LIKE '%$word%' OR comments LIKE '%$word%' OR text LIKE '%$word%'
                OR ";
        }
        $like = substr($like, 0, -4); // leave out the trailing ' OR '

        // fetch the text
        $result = Fari_Db::select('kb', '*', "($like)");

        return self::relevance($query, $result);
    }

    public static function relevance(array $query, array $result) {
        $scores = array(); // an array having id's as keys and relevance scores as values
        $new = array();
        $length = count($query); // length of the query

        // foreach found text...
        foreach ($result as $row) {
            // init the relevance
            $relevance = 0;
            // traverse the array so we can create tuples of 2 elements
            $seq = 0; for ($z = 0; $z <= ($length - 2); $z++) {
                // create the string we want to match for
                $string = $query[$z] . ' ' . $query[$z + 1];
                // find the matches for this string
                $count = substr_count($row['titleStems'], $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$title / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['type']), $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$type / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['source']), $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$source / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['category']), $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$category / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['tags']), $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$tags / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count($row['stems'], $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$text / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['comments']), $string);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$comments / self::$fib[$seq]) / self::$fib[$i];
            }
            // multiply the relevance gained in tuple phase
            $relevance = $relevance * 2;

            // find single keyword matches
            $seq = 1; foreach ($query as $word) { // foreach query word...
                $count = substr_count($row['titleStems'], $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$title / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['type']), $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$type / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['source']), $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$source / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['category']), $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$category / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['tags']), $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$tags / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count($row['stems'], $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$text / self::$fib[$seq]) / self::$fib[$i];
                $count = substr_count(strtolower($row['comments']), $word);
                for ($i = 0; $i < $count; $i++) $relevance += (self::$comments / self::$fib[$seq]) / self::$fib[$i];
                $seq++;
            }

            $scores[$row['id']] = $relevance; // add to scores array

            // add to result array based on id key
            $new[$row['id']] = $row;
        }

        // sort scores by value keeping keys
        arsort(&$scores);

        // build the result array with 5 cats of relevance scores
        foreach ($scores as $id => &$relevance) {
            $temp = self::score($score = $relevance);
            $relevance = $new[$id];
            $relevance['relevance'] = $temp; $relevance['score'] = number_format($score, 2);
        }

        return $scores;
    }

    private static function score($relevance) {
        // relevance score (5 categories)
        $divider = (self::$title + self::$type + self::$source + self::$category + self::$tags + self::$text +
            + self::$comments) / 2;
        switch ($relevance) {
            case (0): return 0;
            case ($relevance < $divider): return 1;
            case ($relevance < (2 * $divider)): return 2;
            case ($relevance < (3 * $divider)): return 3;
            case ($relevance < (4 * $divider)): return 4;
            default: return 5;
        }
    }

}
