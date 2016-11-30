<?php
 namespace HW3_Composer;

 use seekquarry\yioop\configs as C;

 require_once "vendor/autoload.php";
 
/*
 Parts of speech from the lexicon 
 1. noun
 2. pronoun
 3. ex_noun
 4. verb
 5. adjective
 6. adverb
*/

 class HindiToknize
 {
    public static function tagTokenizePartsofSpeech($text)
    {
        static $dictionary = [];
        if (empty($dictionary)) {
            $fh = 
            gzopen('vendor/seekquarry/yioop/src/locale/hi/resources/lexicon.txt.gz', 'r'); 
            while ($line = gzgets($fh)) {
                $line = gzgets($fh);
                $line = ltrim($line, '/');
                $tags = explode(',', $line);
                $dictionary[array_shift($tags)] = $tags;
            }
            gzclose($fh);
        }

        #print_r ($dictionary);
        $nouns = ['NN'];
        $tokens = explode(' ', $text);

        print_r ($tokens);
        
        $result = [];
        $tag_list = [];
        $i = 0;

        foreach ($tokens as $token) {
            $current = ['token' => $token, 'tag' => 'NN'];
            if (!empty($dictionary[$token])) {
                $tag_list = $dictionary[$token];
                $current['tag'] = $tag_list[0];
            }  

            $result[$i] = $current;
            $i++;
            $previous = $current;
            $previous_token = $token;
        }
        return $result;
    }

    public static function taggedPartOfSpeechTokensToString($tagged_tokens, 
                                                            $with_tokens = true)
    {
        $tagged_phrase = [];
        $with_tokens = $with_tokens;

        foreach ($tagged_tokens as $t) {
            $tag = trim($t['tag']);
            $tag = (isset($simplified_parts_of_speech[$tag])) ?
                $simplified_parts_of_speech[$tag] : $tag;
            $token = ($with_tokens) ? $t['token'] . "~" : "";
            $tagged_phrase .= $token . $tag .  " ";
        }

        return $tagged_phrase;
    }
}

$hiToken = new HindiToknize;
$text = "मैं ठीक हूँ";
$tagged_tokens = $hiToken->tagTokenizePartsofSpeech($text);
print_r ($tagged_tokens);
$tagged_phrase = $hiToken->taggedPartOfSpeechTokensToString(
            $tagged_tokens);
print_r ($tagged_phrase);
 ?>
