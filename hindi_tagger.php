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

       $result = [];
       $tag_list = [];
       $i = 0;

       foreach ($tokens as $token) {
 
          //Tag the tokens as found in the Lexicon
          $current = ['token' => $token, 'tag' => 'UNKNOWN'];
          if (!empty($dictionary[$token])) {
               $tag_list = $dictionary[$token];
               $current['tag'] = $tag_list[0];
           }

           //NOUN IDENTIFICATION
       
           //RULE 1: If the previous word tagged is a Adjective / Pronoun / Postposition then 
           //the current word is likely to be a noun
           if ($previous['tag'] == 'JJ'     || 
               $previous['tag'] == 'PRO_NN' ||
               $previous['tag'] == 'POST_POS') {
               $current['tag'] = 'NN';
           }

           //RULE 2: If the current word is a verb then the previous word is likely to be a 
           //noun
           if (in_array($current['tag'], $verbs)) {
               $previous['tag'] = 'NN';
               $result[$i-1] = $previous;
           }

           //RULE 3: If the current tag is a noun then next / previous is likely to be a noun

           //DEMONSTRATIVE IDENTIFICATION

           //RULE 1: If the current and previous words are tagged as pronouns then the previous
           //word is likley to be a demonstrative
           if ($current['tag'] == 'PRO_NN' && 
               $previous['tag'] == 'PRO_NN') {
               $previous['tag'] = 'DEM';
               $result[$i-1] = $previous;
           }

           //RULE 2: If current word is a noun and previous word is a pronoun then the current
           //word is liklely to be demonstrative
           if ($current['tag'] == 'NN' && 
               $previous['tag'] == 'PRO_NN') {
               $current['tag'] = 'DEM';
           }

           //PRONOUN IDENTIFICATION

           //RULE 1: If the previous word is unknown and cuurent word is a noun then the previous
           //word is most likely to be a pronoun
           if ($previous['tag'] == 'UNKNOWN' && 
               $current['tag'] == 'NN') {
               $previous['tag'] = 'PRO_NN';
               $result[$i-1] = $previous;
           }

           //NAME Identification

           //RULE: If we get two words which are untagged the most probably they form a name and 
           //will be tagged as noun
           if ($previous['tag'] == 'UNKNOWN' && 
               $current['tag'] == 'UNKNOWN') {
               $current['tag'] = 'NN';
               $previous['tag'] = 'NN';
               $result[$i-1] = $previous;
           }

           //ADJECTIVE IDENTIFCATION

           //RULE: If the word ends with <tar>, <tam>, <thik> then we tag it as a Adjective
       

           //VERB IDENTIFICATION

           //RULE: If the current word is tagged as Auxilary verb  and previous word is tagged as 
           //Unknown then most likely that the previous word is a verb
           if ($current['tag'] == 'VAUX' &&
               $previous['tag'] == 'UNKNOWN') {
               $previous['tag'] = 'VB';
               $result[$i-1] = $previous;
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
$text = "श्रीनगर में एक 200 साऱ पुरानी दरगाह में आग ऱगनेके बाद प्रदशशनकाररयों नेपुलऱस पर पथराव ककया है और इऱाके में तनाव है।"; 
$tagged_tokens = $hiToken->tagTokenizePartsofSpeech($text);
#print_r ($tagged_tokens);

$tagged_phrase = $hiToken->taggedPartOfSpeechTokensToString(
                           $tagged_tokens);
print ($tagged_phrase);

#श्रीनगर मेंएक 200 साऱ पुरानी दरगाह मेंआग ऱगनेके बाद प्रदशशनकाररयों नेपुलऱस पर पथराव ककया है और इऱाके मेंतनाव है।
?>


