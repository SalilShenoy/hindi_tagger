<?php
namespace HW3_Composer;

use seekquarry\yioop\configs as C;
require_once "vendor/autoload.php";

class HindiTokenizer
{
   public static function reduceWhitespace($text) 
   {
      return preg_replace("/\s+/", " ", $text);
   }

   public static function removePunctuations($text) 
   {
	   return preg_replace('/(|,:)\p{P}/u', '', $text); 
   }

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
       $tokens = explode(' ', $text);

       $result = [];
       $tag_list = [];
       $tag = "";
       $i = 0;

       foreach ($tokens as $token) {
          //Tag the tokens as found in the Lexicon
          $current = ['token' => $token, 'tag' => 'UNKNOWN'];
          if (is_numeric($token)) {
	            $current['tag'] = "Q";
	        } elseif (!empty($dictionary[$token])) {
              $tag_list = $dictionary[$token];
              $current['tag'] = $tag_list[0];
          }

	        $result[$i] = $current;
          $i++;
	     }
       
       return $result;
   }

   public static function tagUnknownWords($partiallyTaggedText) 
   {
     $result = $partiallyTaggedText;
     $nouns = ['NN','NNP','NNS'];
     $verbs = ['VBZ','VBD','VBN'];
	   $length = count($result);
	   $previous = $result[0];
	   for ($i = 1; $i < $length; $i++)
	   {
	      $current = $result[$i];
        $current['token'] = trim($current['token']);
        $current['tag'] = trim($current['tag']);
        print("Line No.: " + $i);
        print("\t");
        print($current['token']);
        print("\t");
        print($current['tag']);
        print("\t");
        print($previous['token']);
        print("\t");
        print($previous['tag']);
        print("\n");
        if ($current['tag'] == "UNKNOWN" || $previous['tag'] == "UNKNOWN") {
              
              //RULE 1: If the previous word tagged is a Adjective / Pronoun / Postposition then
              //the current word is likely to be a noun
              if ($previous['tag'] == 'AJ'     ||
                  $previous['tag'] == 'PRO_NN' ||
                  $previous['tag'] == 'POST_POS') 
              {
                  $current['tag'] = 'NN';
                  $result[$i] = $current;
              }
              //RULE 2: If the current word is a verb then the previous word is likely to be a
              //noun
              elseif (in_array($current['tag'], $verbs)) {
                $previous['tag'] = 'NN';
                $result[$i] = $previous;
              }
              //PRONOUN IDENTIFICATION
              //RULE 1: If the previous word is unknown and cuurent word is a noun then the previous
              //word is most likely to be a pronoun
              elseif ($previous['tag'] == 'UNKNOWN' &&
                $current['tag'] == 'NN') {
                $previous['tag'] = 'PRO_NN';
                $result[$i] = $previous;
              }
              //VERB IDENTIFICATION
              //RULE: If the current word is tagged as Auxilary verb  and previous word is tagged as
              //Unknown then most likely that the previous word is a verb
              elseif ($current['tag'] == 'VAUX' &&
                $previous['tag'] == 'UNKNOWN') {
                $previous['tag'] = 'VB';
                $result[$i] = $previous;
              }
              //ADJECTIVE IDENTIFIATION
              //RULE: if the currennt word ends with 'तम' or 'इक' or 'िक' or 'तर' then the word is an
              //adjective
              elseif ( mb_substr($current['token'], -2, 2) == "इक" || 
                       mb_substr($current['token'], -2, 2) == "िक" ||
                       mb_substr($current['token'], -2, 2) == "तर"  ||
                       mb_substr($current['token'], -2, 2) == "तम") {
                $current['tag'] = 'AJ';
                $result[$i] = $current;
              }
              else 
              {
                if ($current['tag'] == "UNKNOWN") {
                  $current['tag'] = 'NN';
                  $result[$i] = $current;
                } else {
                  $previous['tag'] = 'NN';
                  $result[$i-1] = $previous;
                }
                
              }
          }

          $previous = $current;
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
          "WP" => "NN",
          "VB" => "VB", "VBD" => "VB", "VBN" => "VB", "VBP" => "VB",
          "VBZ" => "VB",
          "JJ" => "AJ", "JJR" => "AJ", "JJS" => "AJ",
          "RB" => "AV", "RBR" => "AV", "RBS" => "AV", "WRB" => "AV",
          "case" => "CONJ", 
          "direct_pn" => "NP", "oblique_sg_pn" => "NP", "pn" => "NP",
          "direct_DT" => "DT",
          "INT" => "INTJ"
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
$text = $hiToken->removePunctuations($text);
$text = $hiToken->reduceWhitespace($text);
$tagged_tokens = $hiToken->tagTokenizePartsofSpeech($text);
$tagged_tokens = $hiToken->tagUnknownWords($tagged_tokens);
$tagged_phrase = $hiToken->taggedPartOfSpeechTokensToString($tagged_tokens);

print($tagged_phrase);
?>


