<?php

set_time_limit(0);



////////////////////////////////////


$user = array(  'userFBID' => '1234',
                'name' => 'Jean-Baptiste de Clerfayt',
                'profilePicture' => 'http://profile.ak.fbcdn.net/hprofile-ak-ash3/70437_690169686_2403913_q.jpg',
                'score' => '3',
                'level' => '1'
             );

/////////////////

$question = array();

$likers = array();

$likers[] = array('name' => 'Laura Carpentier',
                  'profilePicture' => './view/images/profil1.jpg',
                   'userFBID' => '10');

$likers[] = array('name' => 'Céline Parizel',
                  'profilePicture' => './view/images/profil2.jpg',
                   'userFBID' => '11');

$likers[] = array('name' => 'Jean-Baptiste de Clerfayt',
                  'profilePicture' => './view/images/profil3.jpg',
                   'userFBID' => '12');

$likers[] = array('name' => 'Fabrice Mendelson',
                  'profilePicture' => './view/images/profil4.jpg',
                   'userFBID' => '13');

$likers[] = array('name' => 'Pauline Cornu',
                  'profilePicture' => './view/images/profil5.jpg',
                   'userFBID' => '14');



$comments = array();
$comments[] = array(    'whatToFind' => 'comment',
                        'name' => 'Céline Parizel',
                        'userFBID' => '5',
                        'profilePicture' => './view/images/profil6.jpg',
                        'content' => 'Congrats <img src="./view/images/emoticons/wink.png" class="emoticon" />',
                        'commentFBID' => 'com3'
                   );

$comments[] = array(    'whatToFind' => 'comment',
                        'name' => 'Fabrice Mendelson',
                        'userFBID' => '2',
                        'profilePicture' => './view/images/profil7.jpg',
                        'content' => '<img src="./view/images/emoticons/fonzie.png" class="emoticon" /> Wow !!!!',
                        'commentFBID' => 'com4'
                   );

$comments[] = array(    'whatToFind' => 'name',
                        'name' => 'Khoi Do',
                        'userFBID' => '7',
                        'profilePicture' => './view/images/profil1.jpg',
                        'content' => "Where was it?",
                        'commentFBID' => 'com5'
                   );

$comments[] = array(    'whatToFind' => 'name',
                        'name' => 'Michèle Catelan',
                        'userFBID' => '8',
                        'profilePicture' => './view/images/profil2.jpg',
                        'content' => "Je me nomme comme une région d'Espagne et j'habite en Suisse.",
                        'commentFBID' => 'com6'
                   );

$comments[] = array(    'whatToFind' => 'comment',
                        'name' => 'Isabelle Miyamoto',
                        'userFBID' => '9',
                        'profilePicture' => './view/images/profil3.jpg',
                        'content' => "Ceci n'est pas un profil Facebook",
                        'commentFBID' => 'com7'
                   );

$data = array(  'status' => 'intergalactic smiles :D — avec Jean-Baptiste de Clerfayt et Tom Pieters. ', // status, link, photo
                
                'photoUrl' => 'http://sphotos-h.ak.fbcdn.net/hphotos-ak-ash4/c133.0.403.403/p403x403/183239_4602291019029_1745485460_n.jpg'
             
            );

$question[]   =  array(       'date' => '28 November 2012',
                               'author' => array('name' => 'Jean-Baptiste de Clerfayt',
                                          'profilePicture' => 'http://profile.ak.fbcdn.net/hprofile-ak-ash3/70437_690169686_2403913_q.jpg'),
                        
                                         'authorContent' => array(   'type' => 'photo',
                                                                         'data' => $data
                                                                     ),

                                        'friendsContent' => array(  'likers' => $likers,
                                                                    'comments' => $comments
                                                                 )
                                        );

///////////////////////////////////


$likers = array();

$likers[] = array('name' => 'Charlotte Corday',
                  'profilePicture' => './view/images/femme.jpg',
                   'userFBID' => '2');

$likers[] = array('name' => 'Bartolom&eacute; de Las Casas',
                  'profilePicture' => './view/images/couple.jpg',
                  'userFBID' => '3');

$likers[] = array('name' => 'Jean-Baptiste de Clerfayt',
                  'profilePicture' => './view/images/jbdc_profile_picture.jpg',
                   'userFBID' => '1');

    
    
$comments = array();
$comments[] = array(    'whatToFind' => 'comment',
                        'name' => 'Charlotte Corday',
                        'userFBID' => '2',
                        'profilePicture' => './view/images/femme.jpg',
                        'content' => 'OMG!!! <img src="./view/images/emoticons/asian_smiley.png" class="emoticon" /> <img src="./view/images/emoticons/heart.png" class="emoticon" />',
                        'commentFBID' => 'com1'
                   );

$comments[] = array(    'whatToFind' => 'name',
                        'name' => 'Bartolom&eacute; de Las Casas',
                        'userFBID' => '3',
                        'profilePicture' => './view/images/couple.jpg',
                        'content' => "Amazing holidays together! ",
                        'commentFBID' => 'com2'
                   );

$comments[] = array(    'whatToFind' => 'comment',
                        'name' => 'Betsy Ross',
                        'userFBID' => '3',
                        'profilePicture' => './view/images/profil4.jpg',
                        'content' => " Awesome share! Thanks!",
                        'commentFBID' => 'com2'
                   );

$data = array(  'status' => "",
                'photoUrl' => './view/images/post.jpg',
                'photoHeight' => '163'
                
            );

$question[]   =  array( 'date' => 'May 19',
                        'author' => array('name' => 'Bartolom&eacute; de Las Casas',
                                          'profilePicture' => './view/images/couple.jpg'),
                                  'authorContent' => array(   'type' => 'photo',
                                                                        'data' => $data
                                                                    ),

                                 'friendsContent' => array(  'likers' => $likers,
                                                                    'comments' => $comments
                                                                 )
                                        );
                    

///////////////////////////////////




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
        $toSendResults = $question; // $dal->getNewQuestion();
        

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
