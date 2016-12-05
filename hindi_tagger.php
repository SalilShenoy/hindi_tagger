<?php
namespace HW3_Composer;

use seekquarry\yioop\configs as C;

require_once "vendor/autoload.php";
 

class HindiToknize
{
   public static function tagTokenizePartsofSpeech($text)
   {
       static $dictionary = [];
       if (empty($dictionary)) {
           $fh = 
           gzopen('lexicon.txt.gz', 'r'); 
           while ($line = gzgets($fh)) {
               $line = gzgets($fh);
               $line = trim($line, ' ');
               $tags = explode(',', $line);
               $dictionary[array_shift($tags)] = $tags;
           }
           gzclose($fh);
       }
       preg_match_all("/[\w\d]+/", $text, $matches);
       $nouns = ['NN'];
       $tokens = explode(' ', $text);

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
$text = $argv[1]; 
$tagged_tokens = $hiToken->tagTokenizePartsofSpeech($text);
$tagged_phrase = $hiToken->taggedPartOfSpeechTokensToString(
                           $tagged_tokens);
print_r ($tagged_phrase);
?>
