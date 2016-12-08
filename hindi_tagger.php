<?php
namespace HW3_Composer;

use seekquarry\yioop\configs as C;

require_once "vendor/autoload.php";
 

$tags = ['NN', 'VB', 'CONJ', 'POST_POS', 'JJ', 'RB', 'OTHER','Q'];

class HindiTokenizer
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
       $nouns = ['NN','NNP','NNS'];
       $verbs = ['VBZ','VBD','VBN'];
       $tokens = explode(' ', $text);
       $tokens = array_reverse($tokens);

       $result = [];
       $tag_list = [];
       $i = 0;

       foreach ($tokens as $token) {
 
           $current = ['token' => $token, 'tag' => 'POST_POS'];
           if (!empty($dictionary[$token])) {
               $tag_list = $dictionary[$token];
               $current['tag'] = $tag_list[0];
           }

           if ($previous['tag'] == 'NN' && in_array($cuurent['tag'], $nouns)) {
              $current['tag'] = 'JJ';
           }

           if ($previous['tag'] == 'JJ' && in_array($current['tag'],$nouns) == false) {
              $current['tag'] = 'RB';
           }

           if ($previos['tag'] == 'RB' && in_array($current['tag'],'RB') == false) {
              $current['tag'] = 'RB';
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

      $simplified_parts_of_speech = [
          "NN" => "NN", "NNS" => "NN", "NNP" => "NN", "NNPS" => "NN",
          "PRP" => "NN", 'PRP$' => "NN", "WP" => "NN",
          "VB" => "VB", "VBD" => "VB", "VBN" => "VB", "VBP" => "VB",
          "VBZ" => "VB",
          "JJ" => "AJ", "JJR" => "AJ", "JJS" => "AJ",
          "RB" => "AV", "RBR" => "AV", "RBS" => "AV", "WRB" => "AV"
       ];

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

$hiToken = new HindiTokenizer;
$text = $argv[1]; 
$tagged_tokens = $hiToken->tagTokenizePartsofSpeech($text);
print_r ($tagged_tokens);
#$tagged_phrase = $hiToken->taggedPartOfSpeechTokensToString(
#                           $tagged_tokens);
#print_r ($tagged_phrase);
?>
