<?php

class QuestionBuilder {
    
    private $facebookWrapper;
    private $dbAccess;
    private $currentUserFBID;
    private $userInfo;
    private $contructionTries;
    
    public function __construct() {
        
        $this->contructionTries = 0;
        
    }
    
    public function getNewQuestion($currentUserFBID) {
        
        try {
            
            $question = array();
        
            $this->facebookWrapper = FacebookWrapper::getInstance();
            $this->dbAccess = DBAccess::getInstance();
            
            $this->currentUserFBID = $currentUserFBID;
            $this->userInfo = $this->getUserInfo($this->currentUserFBID);

            for ($i = 0; $i < 2; $i++) {
             
                $postID = '';
                
                // 'Friends in common' question
                if (rand(0, 20) == 0) {
                  
                    $DBResult = $this->dbAccess->sendPreparedProcedure('select_random_friend',
                                                                        array('userID' => $this->currentUserFBID));
                    
                    if (isset($DBResult[0]->author_id)) {
                        
                        $friendID = $DBResult[0]->author_id;
                        $APIResults = $this->facebookWrapper->getMutualFriends($friendID);
                        
                        if (count($APIResults) > 0)
                            $question[$i] = $this->constructFriendsInCommonPostForQuestion($APIResults, $friendID);
                        else
                            throw new Exception;
                        
                    }
                    else
                        throw new Exception;
                    
                }
                else {
                
                    for ($test = 0; $test < 3; $test++) { 

                        $DBResult = $this->dbAccess->sendPreparedProcedure('pick_random_post',
                                                                            array('userID' => $this->currentUserFBID));

                        if (isset($DBResult[0]->post_id)) {

                            $postID = $DBResult[0]->post_id;
                            $postType = $DBResult[0]->post_type;
                            $authorID = $DBResult[0]->author_id;
                            break;

                        }

                        sleep(1);

                    }

                    if ($postID == '') {

                        return '0';
                    }

                    if ($authorID == $this->currentUserFBID)
                        $APIQueries = $this->constructPostRetrivalQueries($postID, $postType);
                    else
                        $APIQueries = $this->constructPostRetrivalQueries($postID, $postType, true);


                    $this->eraseUsedPost($postID);

                    $APIResults = $this->facebookWrapper->sendAPIMultiQuery($APIQueries);
                    $question[$i] = $this->constructPostForQuestion($APIResults);

                 }
                
            }                    

            return $this->translatePostDates($question);
            
        }
        catch(Exception $e) {
            
            error_log('Question Generator Error - '
                      . $e->getFile()
                      . ' @ line '
                      . $e->getLine()
                      .' :  '. $e->getMessage());
            
            if ($this->contructionTries < 5) {
                
                $this->contructionTries++;
                return $this->getNewQuestion($currentUserFBID);
                
            }
            else
                throw new Exception;
            
        }
        
    }
    
    private function translatePostDates($question) {
        
        // Posts must contain at least a date, otherwise we throw
        // an exeption to restart the question generation
        if (empty($question[0]['date']) || empty($question[1]['date']))
            throw new Exception;
        
        if ($question[1]['date'] < $question[0]['date'])
            $question = array_reverse($question);
        
        $question[0]['date'] = date('F j, Y', $question[0]['date']);
        $question[1]['date'] = date('F j, Y', $question[1]['date']);
        
        return $question;
        
    }
    
    private function constructPostForQuestion($APIResults) {
        
        $APIResultsForPhotos = $APIResults;
        $APIResultsForComments = $APIResults;
        
        $questionPost = $this->getEmptyContainerForPost();
        
        foreach ($APIResults['data'] as $APIResult) {
            
            switch($APIResult['name']) {
                
                case 'status': if (!empty($APIResult['fql_result_set'])) {
                        
                                    $authorID = $this->convertScientificToRegularNumber($APIResult['fql_result_set'][0]['uid']);
                    
                                    if ($authorID == $this->currentUserFBID) {
                                        
                                        $questionPost['author']['name'] = $this->userInfo['name'];
                                        $questionPost['author']['profilePicture'] = $this->userInfo['profilePicture'];
                          
                                    }
                                    else {
                                        
                                        $friendInfo = $this->getUserInfo($authorID, false);
                                        $questionPost['author']['name'] = $friendInfo['name'];
                                        $questionPost['author']['profilePicture'] = $friendInfo['profilePicture'];
                                        
                                    }
                    
                                    $questionPost['authorContent']['type'] = 'status';
                                    $questionPost['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['message']);
                                    $questionPost['date'] = $APIResult['fql_result_set'][0]['time'];

                               }
   
                               break;
                               
                case 'link': if (!empty($APIResult['fql_result_set'])) {
                        
                                    $authorID = $this->convertScientificToRegularNumber($APIResult['fql_result_set'][0]['owner']);
                    
                                    if ($authorID == $this->currentUserFBID) {
                                        
                                        $questionPost['author']['name'] = $this->userInfo['name'];
                                        $questionPost['author']['profilePicture'] = $this->userInfo['profilePicture'];
                          
                                    }
                                    else {
                                        
                                        $friendInfo = $this->getUserInfo($authorID, false);
                                        $questionPost['author']['name'] = $friendInfo['name'];
                                        $questionPost['author']['profilePicture'] = $friendInfo['profilePicture'];
                                        
                                    }
                                    
                                    $questionPost['authorContent']['type'] = 'link';
                                    $questionPost['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['owner_comment']);
                                    $questionPost['authorContent']['data']['url'] = $APIResult['fql_result_set'][0]['url'];
                                    $questionPost['authorContent']['data']['captionPicture'] = $APIResult['fql_result_set'][0]['picture'];
                                    $questionPost['authorContent']['data']['summaryTitle'] = $APIResult['fql_result_set'][0]['title'];
                                    $questionPost['authorContent']['data']['summaryDescription'] = $APIResult['fql_result_set'][0]['summary'];
                                    $questionPost['date'] = $APIResult['fql_result_set'][0]['created_time'];

                               }
   
                               break;
                
                case 'photo_info': if (!empty($APIResult['fql_result_set'])) {
                        
                                        $questionPost['authorContent']['type'] = 'photo';
                                        $questionPost['date'] = $APIResult['fql_result_set'][0]['created'];
                                        $questionPost['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['caption']);

                                        if ($this->convertScientificToRegularNumber($APIResult['fql_result_set'][0]['owner']) == $this->currentUserFBID) {

                                            $currentUserIsAuthor = true;
                                            $questionPost['author']['name'] = $this->userInfo['name'];
                                            $questionPost['author']['profilePicture'] = $this->userInfo['profilePicture'];

                                        }
                                        else
                                            $currentUserIsAuthor = false;

                                        foreach ($APIResultsForPhotos['data'] as $APIResultForPhoto) {

                                            if ($APIResultForPhoto['name'] == 'photo_author' && !$currentUserIsAuthor) {

                                                $questionPost['author']['name'] = $APIResultForPhoto['fql_result_set'][0]['first_name']
                                                                                  . ' ' . $APIResultForPhoto['fql_result_set'][0]['last_name'] ;

                                                $questionPost['author']['profilePicture'] = $APIResultForPhoto['fql_result_set'][0]['pic_square'];

                                            }
                                            else if ($APIResultForPhoto['name'] == 'photo_url') {

                                                $questionPost['authorContent']['data']['photoUrl'] = $APIResultForPhoto['fql_result_set'][0]['src'];

                                                $height = $APIResultForPhoto['fql_result_set'][0]['height'];
                                                $width = $APIResultForPhoto['fql_result_set'][0]['width'];

                                                // 244px = fixed width for photos in question template
                                                $questionPost['authorContent']['data']['photoHeight'] = round(244 * ($height/$width));
                                                
                                            }

                                        }
                                        
                                    }
                   break;
                   
                   case 'likers_for_friend_post':
                   case 'likers': if (!empty($APIResult['fql_result_set'])) {
                        
                                        for($i=0; $i<count($APIResult['fql_result_set']); $i++) {

                                            $questionPost['friendsContent']['likers'][$i]['name'] = $APIResult['fql_result_set'][$i]['first_name']
                                                                                            .' '. $APIResult['fql_result_set'][$i]['last_name'];

                                            $questionPost['friendsContent']['likers'][$i]['profilePicture'] = $APIResult['fql_result_set'][$i]['pic_square'];
                                            $questionPost['friendsContent']['likers'][$i]['userFBID'] = $this->convertScientificToRegularNumber($APIResult['fql_result_set'][$i]['uid']);

                                        }
                                  }
   
                   break;

                   case 'comments_for_friend_post':
                   case 'comments': if (!empty($APIResult['fql_result_set'])) {
                       
                                        $peopleList = array();
                                        
                                        foreach ($APIResultsForComments['data'] as $APIResultForComment) {

                                                if ($APIResultForComment['name'] == 'people_who_commented') {
                                                    
                                                    foreach ($APIResultForComment['fql_result_set'] as $peopleResult) {
                                                        
                                                       $peopleList[$peopleResult['uid']] = array();
                                                       $peopleList[$peopleResult['uid']]['name'] = $peopleResult['first_name'].' '.$peopleResult['last_name'];
                                                       $peopleList[$peopleResult['uid']]['profilePicture'] = $peopleResult['pic_square'];
                                                        
                                                    }

                                                }

                                        }
                        
                                        for($i=0; $i<count($APIResult['fql_result_set']); $i++) {

                                            $commentAuthorFBID = $APIResult['fql_result_set'][$i]['fromid'];
                                            
                                            $questionPost['friendsContent']['comments'][$i]['commentFBID'] = $APIResult['fql_result_set'][$i]['id'];
                                            $questionPost['friendsContent']['comments'][$i]['userFBID'] = $this->convertScientificToRegularNumber($commentAuthorFBID);
                                            $questionPost['friendsContent']['comments'][$i]['content'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][$i]['text']);
                                            $questionPost['friendsContent']['comments'][$i]['name'] = $peopleList[$commentAuthorFBID]['name'];
                                            $questionPost['friendsContent']['comments'][$i]['profilePicture'] = $peopleList[$commentAuthorFBID]['profilePicture'];
                                        
                                            if (rand(0, 1) == 0)
                                                $questionPost['friendsContent']['comments'][$i]['whatToFind'] = 'name';
                                            else
                                                $questionPost['friendsContent']['comments'][$i]['whatToFind'] = 'comment';
                                            
                                        }
                                        
                                    }
   
                   break;
                   
            }
        }
        
        return $questionPost;
        
    }
    
    private function convertScientificToRegularNumber($number) {
        
        return number_format($number, 0, '', '');
        
    }
    
    private function getUserInfo($userFBID) {
        
            $results = $this->facebookWrapper
                            ->sendAPISingleProcedure('get_simple_user_info', 
                                                     array('userID' => $userFBID));
            
            $name = $results['data'][0]['first_name']
                    .' '.$results['data'][0]['last_name'];
            
            $profilePicture = $results['data'][0]['pic_square'];
            
            return array('name' => $name, 'profilePicture' => $profilePicture, 'userFBID' => $userFBID);
            
    }
    
    private function constructPostRetrivalQueries($post, $postType, $isFriendPost = false) {
        
        $APIQueries = array();
        
        $searchItems = array();
        
        if ($postType == 'status' || $postType == 'link')
            $searchItems[] = $postType;
        
        else {
            
            $searchItems[] = 'photo_info';
            $searchItems[] = 'photo_url';
            
        }
            
        
        if ($isFriendPost) {
            
            $APIQueries['friendsIDs'] = $this->facebookWrapper
                                        ->constructQuery('get_friends_ids',
                                                         array('userID' => $this->currentUserFBID));
        
            $searchItems = array_merge($searchItems, 
                                       array('likers_for_friend_post', 'comments_for_friend_post'));

            
            
        }
        else {
            
           $searchItems = array_merge($searchItems, array('likers', 'comments'));
           
        }
        
        foreach ($searchItems as $searchItem) {
        
            $APIQueries[$searchItem] = $this->facebookWrapper
                                       ->constructQuery("get_$searchItem",
                                                        array('postID' => $post));
        
        }
        
        $APIQueries['people_who_commented'] = $this->facebookWrapper
                                              ->constructQuery('get_people_who_commented',
                                                               array('commentsQuery' => $searchItems[count($searchItems) - 1]));
    
        if ($postType == 'photo' || $postType == 'photo_tag') {
            
            $APIQueries['photo_author'] = $this->facebookWrapper
                                           ->constructQuery('get_photo_author',
                                                             array('photoQuery' => 'photo_info'));
        }
        
        
         
        return $APIQueries;
        
    }
    
    private function eraseUsedPost($postID) {
        
        $this->dbAccess->sendPreparedProcedure('erase_post',
                                               array('postID' => $postID,
                                                     'userID' => $this->currentUserFBID),
                                               false);
        
    }
    
    private function getEmptyContainerForPost() {
        
        $returnArray = array();
        $returnArray['author'] = array();
        $returnArray['authorContent'] = array();
        $returnArray['authorContent']['data'] = array();
        $returnArray['friendsContent'] = array();
        $returnArray['friendsContent']['likers'] = array();
        $returnArray['friendsContent']['comments'] = array();
        
        return $returnArray;
        
    }
    
    private function convertUrlAndEmoticons($message) {
        
        $urlRegexWithHttp = '((http:\/\/|https:\/\/)(www.)?(([a-zA-Z0-9-]){2,}\.){1,4}([a-zA-Z]){2,6}(\/([a-zA-Z-_\/\.0-9#:?=&;,%]*)?)?)';
        $urlReplacementWithHttp = '<a href="$0" class="userLink" target="_blank">$0</a>';
        $newMessage = preg_replace($urlRegexWithHttp, $urlReplacementWithHttp, $message);
        
        if ($newMessage == $message) {
           
            // No URL with http/https header found, let's search without
            // You use here an if to avoid URL with http to be converted twice here
            $urlRegexWithoutHttp = '((www.)(([a-zA-Z0-9-]){2,}\.){1,4}([a-zA-Z]){2,6}(\/([a-zA-Z-_\/\.0-9#:?=&;,%]*)?)?)';
            $urlReplacementWithoutHttp = '<a href="http://$0" class="userLink" target="_blank">$0</a>';
            $newMessage = preg_replace($urlRegexWithoutHttp, $urlReplacementWithoutHttp, $message);
            
        }
        
        $message = $newMessage;
        
        $emoticonsRegex = array();
        $emoticonsReplacement = array();
        
        $emoticonsRegex[0]= "/O\:-?\)+|0\:-?\)+/";
        $emoticonsReplacement[0] = '<img src="./view/images/emoticons/angel.png" class="emoticon" />';
        
        $emoticonsRegex[1] = '/\^\^|\^_\^/';
        $emoticonsReplacement[1] = '<img src="./view/images/emoticons/asian_smiley.png" class="emoticon" />';
        
        $emoticonsRegex[2] = '/(:|=)-?D+/';
        $emoticonsReplacement[2] = '<img src="./view/images/emoticons/lol.png" class="emoticon" />';
        
        $emoticonsRegex[3] = '/<3|<\/3|♥/';
        $emoticonsReplacement[3] = '<img src="./view/images/emoticons/heart.png" class="emoticon" />';
        
        $emoticonsRegex[4] = '/(:|=)-?\(+/';
        $emoticonsReplacement[4] = '<img src="./view/images/emoticons/unhappy.png" class="emoticon" />';
        
        $emoticonsRegex[5]= '/(:|=|;)-?(o|O|0)+|8-?(o|O)/';
        $emoticonsReplacement[5] = '<img src="./view/images/emoticons/oh.png" class="emoticon" />';
        
        $emoticonsRegex[6]= '/(:|=|;)-?\*+/';
        $emoticonsReplacement[6] = '<img src="./view/images/emoticons/kiss.png" class="emoticon" />';
        
        $emoticonsRegex[7]= '/(:|;)-?(p|P)+/';
        $emoticonsReplacement[7] = '<img src="./view/images/emoticons/tongue.png" class="emoticon" />';
        
        $emoticonsRegex[8]= '/(B|8)-?\)+/';
        $emoticonsReplacement[8] = '<img src="./view/images/emoticons/fonzie.png" class="emoticon" />';
        
        $emoticonsRegex[9]= '/-_-/';
        $emoticonsReplacement[9] = '<img src="./view/images/emoticons/chinese.png" class="emoticon" />';
        
        $emoticonsRegex[10]= '/(:|=|;)-?(\/+)$|(:|=|;)-?(\/+)\s/';
        $emoticonsReplacement[10] = '<img src="./view/images/emoticons/sorry.png" class="emoticon" /> ';
        
        $emoticonsRegex[11]= '/;-?\)+/';
        $emoticonsReplacement[11] = '<img src="./view/images/emoticons/wink.png" class="emoticon" />';
        
        $emoticonsRegex[12]= "/(:|=)'-?\(+/";
        $emoticonsReplacement[12] = '<img src="./view/images/emoticons/sad.png" class="emoticon" />';
        
        $emoticonsRegex[13]= "/3:-?\)+/";
        $emoticonsReplacement[13] = '<img src="./view/images/emoticons/devil.png" class="emoticon" />';
        
        $emoticonsRegex[14]= "/O\.o|o\.O/";
        $emoticonsReplacement[14] = '<img src="./view/images/emoticons/confused.png" class="emoticon" />';
        
        $emoticonsRegex[15] = '/>_</';
        $emoticonsReplacement[15] = '<img src="./view/images/emoticons/doh.png" class="emoticon" />';
        
        $emoticonsRegex[16] = '/(:|=)-?\)+/';
        $emoticonsReplacement[16] = '<img src="./view/images/emoticons/smiley.png" class="emoticon" />';
        
        $message = preg_replace($emoticonsRegex, $emoticonsReplacement, $message);
        
        return $message;
        
    }
    
    private function constructFriendsInCommonPostForQuestion($APIResults, $friendID) {
        
        $questionPost = $this->getEmptyContainerForPost();
        
        $friendInfo = $this->getUserInfo($friendID, false);
        $questionPost['author']['name'] = $friendInfo['name'];
        $questionPost['author']['profilePicture'] = $friendInfo['profilePicture'];
        
        $questionPost['authorContent']['type'] = 'friendsInCommon';
        $questionPost['authorContent']['data']['currentUserName'] = $this->userInfo['name'];
        $questionPost['date'] = time();
        
        
        for ($i = 0; $i < count($APIResults); $i++) {

            $questionPost['friendsContent']['likers'][$i]['name'] = $APIResults[$i]['name'];
            $questionPost['friendsContent']['likers'][$i]['profilePicture'] = $APIResults[$i]['picture']['data']['url'];
            $questionPost['friendsContent']['likers'][$i]['userFBID'] = $this->convertScientificToRegularNumber($APIResults[$i]['id']);

        }
        
        return $questionPost;
        
    }
    
}

?>