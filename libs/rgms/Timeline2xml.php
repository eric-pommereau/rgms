<?php
/**
 * @return DOMDocument 
 */
class Converter {
    static function TimeLine2Xml($twitterOauthResponse) {
        $tweets = $twitterOauthResponse->statuses;
        $search = $twitterOauthResponse->search_metadata;
        
        $xml = new DOMDocument("1.0");
        $rootElem = $xml -> createElement("twitterResp");
        $xml->appendChild($rootElem);
        
        $statusesElement = $xml -> createElement("statuses");
        $searchElem = $xml -> createElement("search_metadata");
        
        for($i=0;$i<count($tweets);$i++) {
            $tweetElement = $xml -> createElement("tweet");
            
            // id_str, l'identifiant du tweet
            $elem = $xml -> createElement("id_str");
            $textNode = $xml -> createTextNode($tweets[$i]->id_str);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // text, le contenu du tweet
            $elem = $xml -> createElement("text");
            $textNode = $xml -> createTextNode($tweets[$i]->text);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // user->name : nom réel du compte
            $elem = $xml -> createElement("user_name");
            $textNode = $xml -> createTextNode($tweets[$i]->user->name);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // user->screen_name : nom affiché (peut bouger)
            $elem = $xml -> createElement("screen_name");
            $textNode = $xml -> createTextNode($tweets[$i]->user->screen_name);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // user->location (lieu affiché par l'utilisateur)
            $elem = $xml -> createElement("location");
            $location = isset($tweets[$i]->user->location) ? $tweets[$i]->user->location : null;  
            $textNode = $xml -> createTextNode($location);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // user->place : lieu du tweet
            $elem = $xml -> createElement("place");
            $place = isset($tweets[$i]->user->place) ? $tweets[$i]->user->place : null;  
            $textNode = $xml -> createTextNode($location);
            $elem -> appendChild($textNode);
            $tweetElement -> appendChild($elem);
            
            // geo.x, geo.y : lieu du tweet
            $elem = $xml -> createElement("geo");
            $geo = isset($tweets[$i]->geo) ? $tweets[$i]->geo : null;  
            
            if($geo->type == "Point") {
                $elemY = $xml -> createElement("y");
                $elemY -> appendChild($xml -> createTextNode($geo->coordinates[0]));
                $elemX = $xml -> createElement("x");
                $elemX -> appendChild($xml -> createTextNode($geo->coordinates[1]));
                $elem -> appendChild($elemX);
                $elem -> appendChild($elemY);
            }
            
            $tweetElement -> appendChild($elem);
                        
            $statusesElement->appendChild($tweetElement); 
        }
        
        // $titleText = $xml -> createTextNode('"PHP Undercover"');
        
        $elem = $xml -> createElement("completed_in");
        $textNode = $xml -> createTextNode($twitterOauthResponse->search_metadata->completed_in);
        $elem -> appendChild($textNode);
        $searchElem->appendChild($elem);
        
        $elem = $xml -> createElement("refresh_url");
        $textNode = $xml -> createTextNode($twitterOauthResponse->search_metadata->refresh_url);
        $elem -> appendChild($textNode);
        $searchElem->appendChild($elem);
        
        $rootElem -> appendChild($searchElem);        
        $rootElem -> appendChild($statusesElement);

        $xml -> formatOutput = true;
        
        // echo "<xmp>". $xml->saveXML() ."</xmp>";
        
        return $xml;
 
    }

    static function getSampleXML() {
        $xml = new DOMDocument("1.0");

        $root = $xml -> createElement("data");
        $xml -> appendChild($root);

        $id = $xml -> createElement("id");
        $idText = $xml -> createTextNode('1');
        $id -> appendChild($idText);

        $title = $xml -> createElement("title");
        $titleText = $xml -> createTextNode('"PHP Undercover"');
        $title -> appendChild($titleText);

        $book = $xml -> createElement("book");
        $book -> appendChild($id);
        $book -> appendChild($title);

        $root -> appendChild($book);

        $xml -> formatOutput = true;
        echo "<xmp>" . $xml -> saveXML() . "</xmp>";

        $xml -> save("mybooks.xml") or die("Error");

    }

}
/*
         * [search_metadata] => stdClass Object
            (
                [completed_in] => 0.059
                [max_id] => 5.3432959783247E+17
                [max_id_str] => 534329597832474624
                [query] => kalash
                [refresh_url] => ?since_id=534329597832474624&q=kalash&geocode=48.932424%2C2.449951%2C5km&result_type=recent&include_entities=1
                [count] => 100
                [since_id] => 0
                [since_id_str] => 0
            )
         */