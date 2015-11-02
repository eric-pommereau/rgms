<?php
// Tests d'installation PHP/APACHE pour RGMS
    
// Inclusions des librairies (sans afficher les warning)
require_once ("./libs/twitteroauth/twitteroauth.php");
require_once ("./libs/rgms/Timeline2xml.php");

echo html_header();
echo "<h2>Installation XXX - tests</h2>";

if(class_exists("TwitterOAuth")) {
    displayMessage("Classe d'authentification Twitter", TRUE);
}

if(class_exists("Converter")) {
    displayMessage("Classe de conversion XML", TRUE);
}

// Vérification du fichier de configuration

if(file_exists('./conf/conf.json')) {
    displayMessage("Le fichier conf/conf.json est présent", TRUE);
}else {
    displayMessage("Le fichier conf/conf.json est absent", FALSE);
    return 0;
}

$conf = json_decode(file_get_contents('./conf/conf.json'));

$key = isset($conf->twitter->api->consumer_key) ? $conf->twitter->api->consumer_key : "";
$secret = isset($conf->twitter->api->consumer_secret) ? $conf->twitter->api->consumer_secret : "";
$oauth_token = isset($conf->twitter->api->oauth_token) ? $conf->twitter->api->oauth_token : "";
$oauth_token_secret = isset($conf->twitter->api->oauth_token_secret) ? $conf->twitter->api->oauth_token_secret : "";

if($key != "" && $secret != "" && $oauth_token != "" && $oauth_token_secret != "") {
    displayMessage("Paramétrage correct pour twitter", TRUE);
}else {
    displayMessage("Paramétrage pour twitter incorrect", FALSE);
}

// Tester une requête twitter...
$aTestResponse = testTwitterRequest($conf);
displayMessage($aTestResponse['message'], $aTestResponse['status']);

// Tester l'enregistrement d'un fichier
$aTestResponse = testFileWriteErase();
displayMessage($aTestResponse['message'], $aTestResponse['status']);



echo html_footer();

// Afficher les message
function displayMessage($sMessage, $bStatus) {
    $className = $bStatus == true ? "ok" : "nok";
    $displayOkKo = $bStatus == true ? "OK" : "KO";
    printf('<p class="%s">-- %s <b>[%s]</b></p>', $className, $sMessage, $displayOkKo);
}

function html_header() {
    $html = <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <title>Tests RGMS</title>
        <style type="text/css">
            .ok {color:green;}
            .nok {color:red;}
        </style>
    </head>
    <body>    
EOF;
    return $html;
}

function html_footer() {
    $html = <<<EOF
    </body>    
</html>
EOF;

return $html; 
}

function testTwitterRequest($conf) {
    try {
        $url_base_time = 'https://api.twitter.com/1.1/search/tweets.json';
            
        $connection = new TwitterOAuth(
            $conf -> twitter -> api -> consumer_key, 
            $conf -> twitter -> api -> consumer_secret, 
            $conf -> twitter -> api -> oauth_token, 
            $conf -> twitter -> api -> oauth_token_secret
        );
        
        $aQuery = array(
            'q' => $conf->twitter->reqParams->q, 
            'geocode' => sprintf('%s,%s,%skm', 
                $conf->geo->lat, 
                $conf->geo->lon, 
                $conf->geo->radius), 
            'result_type' => $conf->twitter->reqParams->result_type, 
            'count' =>  $conf->twitter->reqParams->count,
            'exclude_replies' => true,
            'since_id' => 0
        );
    
        $sQueryParams = http_build_query($aQuery);
        
        $query = sprintf('%s?%s', $url_base_time, $sQueryParams);
        
        $content = $connection -> get($query); 
        
        $iCountResponse = count($content->statuses);
        
        return array("status" => TRUE, "message" =>sprintf("Test de recherche twitter <b>[%s]</b> réponses pour le critère <b>[%s]</b>", $iCountResponse, $conf->twitter->reqParams->q));
        
    }catch (Exception $ex) {
        return array("status" => false, "message" => $ex->getMessage());
    } 
}

function testFileWriteErase() {
    $fileName = "./datas/test.txt";
    $testCase = "Test écriture / supression du fichier";
    try {
        if($bytes = @file_put_contents($fileName, "test") == TRUE) {
            $bUnlink = @unlink($fileName);
            if($bUnlink==FALSE) {
                throw new Exception(sprintf($testCase . " --> echec de suppression du fichier %s", $fileName), 1);    
            }
            return array("status" => TRUE, "message" => $testCase);
        }else {
               throw new Exception(sprintf($testCase . " --> echec de création du fichier %s", $fileName), 1);
        }
    }catch (Exception $ex) {
        return array("status" => FALSE, "message" => $ex->getMessage());
    }
} 



