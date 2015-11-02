<?php
/********************************************
 Fichier proxy PHP pour les appels twitter
 ********************************************/

require_once ("../libs/twitteroauth/twitteroauth.php");
require_once ("../libs/rgms/Timeline2xml.php");
$conf = json_decode(file_get_contents('../conf/conf.json'));

$time_start = microtime(true);

try {
    $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : $conf-> twitter -> reqParams -> keyWords; 
    $lon = isset($_REQUEST['lon']) ? $_REQUEST['lon'] : $conf -> geo -> startPlace -> lon; 
    $lat = isset($_REQUEST['lat']) ? $_REQUEST['lat'] : $conf -> geo -> startPlace -> lat;
    $radius = isset($_REQUEST['radius']) ? $_REQUEST['radius'] : $conf -> geo -> radius; 
    $result_type = isset($_REQUEST['result_type']) ? $_REQUEST['result_type'] : $conf-> twitter -> reqParams -> resultType;
    $count = isset($_REQUEST['count']) ? $_REQUEST['count'] : $conf-> twitter -> reqParams -> count; 
    $since_id = isset($_REQUEST['since_id']) ? $_REQUEST['since_id'] : 0;
    $max_id = isset($_REQUEST['max_id']) ? $_REQUEST['max_id'] : 0;
    $simul = isset($_REQUEST['simul']) ? $_REQUEST['simul'] : false;

    $record = isset($conf->record) ? $conf->record : false;
    
    if(isset($conf->record) && $conf->record == "true") $record = true;
    else $record = false;
        
    $connection = new TwitterOAuth(
        $conf -> twitter -> api -> consumer_key, 
        $conf -> twitter -> api -> consumer_secret, 
        $conf -> twitter -> api -> oauth_token, 
        $conf -> twitter -> api -> oauth_token_secret
    );

    $url_base_time = 'https://api.twitter.com/1.1/search/tweets.json';
    
    $aQuery = array(
        'q' => $q, 
        'geocode' => sprintf('%s,%s,%skm', 
            $lat, 
            $lon, 
            $radius), 
        'result_type' => $result_type, 
        'count' => $count,
        'exclude_replies' => true,
        'since_id' => $since_id
    );

    $sQueryParams = http_build_query($aQuery);

    $query = sprintf('%s?%s', $url_base_time, $sQueryParams);

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    
    // $content = unserialize(file_get_contents("../datas/response.2014-10-28.djihad.recent.ser"));

    // Simulation pour les tests et les tests offline
    // True évalué en chaîne de caractères (fait par JSON)
    if($simul == "true") {
        $filePath = "../datas/" . $simul_file_name;
        
        if(file_exists($filePath)) {
            $content = unserialize(file_get_contents($filePath));     
        }else {
            throw new Exception("Fichier de simulation absent : " . $filePath);
        } 
    }else {
        
        $content = $connection -> get($query);
         
        // Enregistrement de la requête pour éventuel rejeux
        if($record == "true") {
            $date_dir = date('Y-m-d');
            $dayPath = "../datas/" . $date_dir;
            if(! is_dir($dayPath)) {
                mkdir($dayPath);
            }
            // Nom du fichier xml à générer --
            $XmlfileName = sprintf($dayPath . '/resp.%s.%s.%s.xml',rand(1, 100),$q, $result_type);
            
            // Génération du fichier
            $xmlDocument = Converter::TimeLine2Xml($content);
            
            // Enregistrement
            $xmlDocument->save($XmlfileName);
        }
    }
    
    echo json_encode($content);

} catch(Exception $ex) {
    echo json_encode(array('error' => true, 'message' => $ex -> getMessage()));
}
