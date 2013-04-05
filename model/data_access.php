<?php

set_time_limit(0);

// Managing sessions cookie
if (!isset($_COOKIE['currentSesssionID'])) {
    
    session_start();
    setCookie('currentSesssionID', session_id());

}
else {
    
    session_id($_COOKIE['currentSesssionID']);
    session_start();
    
}

include_once 'classes_autoloader.include.php';
include_once 'config.include.php';

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json; charset=utf-8');

$dal = DAL::getInstance();

// Router for received HTTP request

try {

    if (isset($_GET['authentification_url']))
        $toSendResults = $dal->getAuthentificationUrl();

    else if (isset($_GET['score']) && empty($_GET['score']) && isset($_GET['api_code'])) {

        $dal->setAPICode($_GET['api_code']);
        $dal->setCurrentUserFBID();
        $toSendResults = $dal->getUserScoreAndLevel();

    }

    else if (isset($_GET['posts_retriever']))
        $toSendResults = $dal->retrievePosts($_GET['posts_retriever']);
    
    else if (isset($_GET['question']))
        $toSendResults = $dal->getNewQuestion();
        

    else if (isset($_GET['score']) && $_GET['score'] != ''
             && isset($_GET['level']) && !empty($_GET['level']))
        $toSendResults = $dal->updateUserScoreAndLevel($_GET['score'], $_GET['level']);
    
    else if (isset($_GET['level_popup']))
        $toSendResults = $dal->getLevelPopupInfo($_GET['level_popup']);
    
    else {

        // Sending a HTTP 500 error
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        $toSendResults = array('Error' => 'Wrong HTTP request');

    }

}
catch(Exception $e) {
    
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    error_log('Data_Access Error - '. $e->getFile()
               . ' @ line '. $e->getLine().' :  '. $e->getMessage());
    $toSendResults = array('Error' => 'An error has occured');
    
}

// Sending JSON results
echo json_encode($toSendResults);

?>
