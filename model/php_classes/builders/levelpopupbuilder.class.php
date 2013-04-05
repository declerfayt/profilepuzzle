<?php

class LevelPopupBuilder {
    
    private $currentUserFBID;
    private $userInfo;
    private $dbAccess;
    private $facebookWrapper;
    private $postsRetrieverForLevelPopup;
    private $fieldsToRetrieve;
    
    public function getLevelPopupInfo($level, $currentUserFBID) {
        
        $this->currentUserFBID = $currentUserFBID;
        $this->dbAccess = DBAccess::getInstance();
        $this->facebookWrapper = FacebookWrapper::getInstance();
        $this->userInfo = $this->getUserInfo($currentUserFBID);
        
        if ($level == 2)
            return $this->getLevelPopupInfoForLevel2($this->buildTempReturnArray());  
        else {
            
            $this->postsRetrieverForLevelPopup = new PostsRetrieverForLevelPopup();
            $this->setFieldsToRetrieve(); 
            return $this->getLevelPopupInfoForOtherLevels($this->buildTempReturnArray(), $level);
            
        }
        
    }
    
    private function setFieldsToRetrieve() {
        
        $this->fieldsToRetrieve = array();
        
        $this->fieldsToRetrieve[3] = array('popupTitle' => 'Your best photos',
                                           'postType' => 'photos');
        
        $this->fieldsToRetrieve[4] = array('popupTitle' => 'Your best shared links',
                                           'postType' => 'links');
        
        $this->fieldsToRetrieve[5] = array('popupTitle' => 'Your best statuses',
                                           'postType' => 'statuses');
        
        $this->fieldsToRetrieve[6] = array('popupTitle' => 'Your best of 2013',
                                           'year' => '2013');
        
        $this->fieldsToRetrieve[7] = array('popupTitle' => 'Your best of 2012',
                                           'year' => '2012');
        
        $this->fieldsToRetrieve[8] = array('popupTitle' => 'Your best of 2011',
                                           'year' => '2011');
        
        $this->fieldsToRetrieve[9] = array('popupTitle' => 'Your best of 2010',
                                           'year' => '2010');
        
        $this->fieldsToRetrieve[10] = array('popupTitle' => 'Your best of 2009',
                                           'year' => '2009');
        
        $this->fieldsToRetrieve[11] = array('popupTitle' => 'Your best of 2008',
                                           'year' => '2008');
        
        $this->fieldsToRetrieve[12] = array('popupTitle' => 'Your best of 2007',
                                           'year' => '2007');
        
        $this->fieldsToRetrieve[13] = array('popupTitle' => 'Your best of 2008',
                                           'year' => '2008');
        
        $this->fieldsToRetrieve[14] = array('popupTitle' => 'Your best of 2007',
                                           'year' => '2007');
        
        $this->fieldsToRetrieve[15] = array('popupTitle' => 'Your best of 2006',
                                           'year' => '2006');
        
        $this->fieldsToRetrieve[16] = array('popupTitle' => 'Your best of 2005',
                                           'year' => '2005');
        
        
    }
    
    private function buildTempReturnArray() {
        
        $tempReturnArray = array();
        $tempReturnArray['levelInfo'] = array();
        $tempReturnArray['data'] = array();
        
        return $tempReturnArray;
        
    }
    
    private function getUserInfo($userFBID) {
        
            $results = $this->facebookWrapper
                            ->sendAPISingleProcedure('get_simple_user_info', 
                                                     array('userID' => $userFBID));
            
            $name = $results['data'][0]['first_name']
                    .' '.$results['data'][0]['last_name'];
            
            $profilePicture = $results['data'][0]['pic_square'];
            
            return array('name' => $name,
                         'profilePicture' => $profilePicture);
            
    }
    
    private function getLevelPopupInfoForLevel2($tempReturnArray) {
        
        date_default_timezone_set('UTC');
        $now = mktime();
        
        $tempReturnArray['levelInfo']['popupTitle'] = 'Your best birthdays';
        
        $APIQueries = array();

        $results = $this->dbAccess->sendPreparedProcedure('get_user_birthday_date', 
                                                          array('userID' => $this->currentUserFBID));
        
        $birthdayDate = $results[0]->birthday_date;
        $day = substr($birthdayDate, -2);
        $month = substr($birthdayDate, -5, 2);
        
        for ($year = 2015; $year >= 2005; $year--) {
            
            $currentYearBirthday = mktime(0, 0, 0, $month, $day, $year);
            
            if ($currentYearBirthday < $now) {
                
                $startBirthdayTime = mktime(0, 0, 0, $month, $day - 2, $year);
                $endBirthdayTime = mktime(0, 0, 0, $month, $day + 2, $year);
                
                $APIQueries[$year] = $this->facebookWrapper->constructQuery('get_user_birthday_whishers',
                                                              array('startBirthdayTime' => $startBirthdayTime,
                                                                    'endBirthdayTime' => $endBirthdayTime));
                
            }
            
        }
        
        switch ($month) {
            
            case 01: $month = 'January'; break;
            case 02: $month = 'February'; break;
            case 03: $month = 'March'; break;
            case 04: $month = 'April'; break;
            case 05: $month = 'May'; break;
            case 06: $month = 'June'; break;
            case 07: $month = 'July'; break;
            case 08: $month = 'August'; break;
            case 09: $month = 'September'; break;
            case 10: $month = 'October'; break;
            case 11: $month = 'November'; break;
            case 12: $month = 'December'; break;
            
        }
        
        $APIResults = $this->facebookWrapper->sendAPIMultiQuery($APIQueries);
        $tempReturnArrayLayerItemPosition = 0;
        
        foreach($APIResults['data'] as $APIResult) {
            
            if (!empty($APIResult['fql_result_set'])) {
                
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition] = array();
                
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['author'] = array();
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['author']['name'] = $this->userInfo['name'];
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['author']['profilePicture'] = $this->userInfo['profilePicture'];
                
                
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['authorContent'] = array();
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['authorContent']['type'] = 'birthday';
                
                $happyBirthdaySentence = "$month $day, ".$APIResult['name'];
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['authorContent']['data'] = array();
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['authorContent']['data']['status'] = $happyBirthdaySentence;
                
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent'] = array();
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent']['likers'] = array();
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent']['comments'] = array();
                
                foreach ($APIResult['fql_result_set'] as $whisher)
                    $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent']['likers'][]['name'] = $whisher['first_name'].' '.$whisher['last_name'];
                
                
                $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['date'] = $APIResult['name'];
                
                $tempReturnArrayLayerItemPosition++;
                
            }
            
        }
        
        // Now we sort the return array
        $sortingArray = array();
        
        foreach ($tempReturnArray['data'] as $birthdayYear) 
            $sortingArray[$birthdayYear['date']] = count($birthdayYear['friendsContent']['likers']);
        
        arsort($sortingArray);
        $secondSortingArray = array();
        
        foreach ($sortingArray as $sortYear => $value)
            $secondSortingArray[] = $sortYear;
        
        $finalReturnArray = array();
        $finalReturnArray['levelInfo'] = $tempReturnArray['levelInfo'];
        $finalReturnArray['data'] = array();
        
        for ($i = 0; $i < count($secondSortingArray); $i++) {
            
            foreach ($tempReturnArray['data'] as $item) {
            
                if ($secondSortingArray[$i] == $item['date']) {
                    
                    $finalReturnArray['data'][$i] = $item;
                    $finalReturnArray['data'][$i]['date'] = "$month $day, ".$item['date'];
                    break;
             
                }
                
           }
           
        }
                    
        return $finalReturnArray;
        
    }
    
    private function getLevelPopupInfoForOtherLevels($tempReturnArray, $level) {
        
        if (isset($this->fieldsToRetrieve[$level]['postType']))
            $this->postsRetrieverForLevelPopup->setPostType($this->fieldsToRetrieve[$level]['postType']);
        
        if (isset($this->fieldsToRetrieve[$level]['year']))
            $this->postsRetrieverForLevelPopup->setYear($this->fieldsToRetrieve[$level]['year']);
        
        $numberOfPosts = $this->postsRetrieverForLevelPopup->start($this->currentUserFBID, $level);
        
        if ($numberOfPosts > 0) {
            
            $returnResults = $this->computeResults($tempReturnArray, $level);
            
            $this->dbAccess->sendPreparedProcedure('truncate_level',
                                                   array('userID' => $this->currentUserFBID,
                                                         'level' => $level),
                                                   false);
            
            return $returnResults;
            
        }
        else {
            
            // No post to send
            $tempReturnArray['levelInfo']['popupTitle'] = $this->fieldsToRetrieve[$level]['popupTitle'];
            return $tempReturnArray;
            
        }
        
    }
    
    
    private function computeResults($tempReturnArray, $level) {
        
        $DBResults = $this->dbAccess->sendPreparedProcedure('get_top_20_for_level_popup', 
                                                            array('userID' => $this->currentUserFBID,
                                                                  'level' => $level));
        
        $tempReturnArray['levelInfo']['popupTitle'] = $this->fieldsToRetrieve[$level]['popupTitle'];
        $tempReturnArrayLayerItemPosition = 0;
        $computedPosts = 0;
        
        foreach ($DBResults as $DBResult) {
            
             if ($computedPosts >= 10)
                 break;
            
            
             $APIQueries = $this->constructPostRetrivalQueries($DBResult->post_id, $DBResult->post_type);
             $APIResults = $this->facebookWrapper->sendAPIMultiQuery($APIQueries);
             
             try {
             
                 $post = $this->getPost($APIResults);
                 
                 // Checking if computed post is well filled
                 if (    !isset($post['author']['name']) || empty($post['author']['name'])
                      || !isset($post['author']['profilePicture']) || empty($post['author']['profilePicture'])
                      || !isset($post['date']) || empty($post['date']) || empty($post['authorContent']['data'])) {
                     
                        continue;
                        
                      }
                 else
                     $computedPosts++;
               
             } 
             catch (Exception $e) {
                 
                 continue;
             }
             
                 
             $tempReturnArray['data'][$tempReturnArrayLayerItemPosition] = $post;
             
             if ($DBResult->post_type == 'status' || $DBResult->post_type == 'link')
                 $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['url'] = 'http://www.facebook.com/'.$this->currentUserFBID.'/posts/'.$DBResult->post_id;
             else if ($DBResult->post_type == 'photo' || $DBResult->post_type == 'photo_tag')
                 $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['url'] = 'http://www.facebook.com/photo.php?fbid='.$DBResult->post_id;

             $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent'] = array();
             $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent']['likers'] = $DBResult->likes;
             $tempReturnArray['data'][$tempReturnArrayLayerItemPosition]['friendsContent']['comments'] = $DBResult->comments;

             $tempReturnArrayLayerItemPosition++;
            
        }
        
        return $tempReturnArray;
        
    }
    
    private function constructPostRetrivalQueries($post, $postType) {
        
        $APIQueries = array();
        
        $searchItems = array();
        
        if ($postType == 'status' || $postType == 'link')
            $searchItems[] = $postType;
        
        else {
            
            $searchItems[] = 'photo_info';
            $searchItems[] = 'photo_url';
            
        }
            
        foreach ($searchItems as $searchItem) {
        
            $APIQueries[$searchItem] = $this->facebookWrapper
                                       ->constructQuery("get_$searchItem",
                                                        array('postID' => $post));
        
        }
        
        if ($postType == 'photo' || $postType == 'photo_tag') {
            
            $APIQueries['photo_author'] = $this->facebookWrapper
                                           ->constructQuery('get_photo_author',
                                                             array('photoQuery' => 'photo_info'));
        }
         
        return $APIQueries;
        
    }
    
    private function translatePostDate($date) {
        
        return date('F j, Y', $date);
        
    }
    
    private function getPost($APIResults) {
        
        $APIResultsForPhotos = $APIResults;
        
        $post = array();
        $post['author'] = array();
        $post['authorContent'] = array();
        $post['authorContent']['data'] = array();
        
        foreach ($APIResults['data'] as $APIResult) {
            
            switch($APIResult['name']) {
                
                case 'status': if (!empty($APIResult['fql_result_set'])) {
                        
                                    $post['author']['name'] = $this->userInfo['name'];
                                    $post['author']['profilePicture'] = $this->userInfo['profilePicture'];
                                    $post['authorContent']['type'] = 'status';
                                    $post['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['message']);
                                    $post['date'] = $this->translatePostDate($APIResult['fql_result_set'][0]['time']);
     
                               }
   
                               break;
                               
                case 'link': if (!empty($APIResult['fql_result_set'])) {
                        
                                    $post['author']['name'] = $this->userInfo['name'];
                                    $post['author']['profilePicture'] = $this->userInfo['profilePicture'];
                                    $post['authorContent']['type'] = 'link';
                                    $post['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['owner_comment']);
                                    $post['authorContent']['data']['url'] = $APIResult['fql_result_set'][0]['url'];
                                    $post['authorContent']['data']['captionPicture'] = $APIResult['fql_result_set'][0]['picture'];
                                    $post['authorContent']['data']['summaryTitle'] = $APIResult['fql_result_set'][0]['title'];
                                    $post['authorContent']['data']['summaryDescription'] = $APIResult['fql_result_set'][0]['summary'];
                                    $post['date'] = $this->translatePostDate($APIResult['fql_result_set'][0]['created_time']);

                               }
   
                               break;
                
                case 'photo_info': if (!empty($APIResult['fql_result_set'])) {
                        
                                        $post['authorContent']['type'] = 'photo';
                                        $post['date'] = $this->translatePostDate($APIResult['fql_result_set'][0]['created']);
                                        $post['authorContent']['data']['status'] = $this->convertUrlAndEmoticons($APIResult['fql_result_set'][0]['caption']);

                                        if ($this->convertScientificToRegularNumber($APIResult['fql_result_set'][0]['owner']) == $this->currentUserFBID) {

                                            $currentUserIsAuthor = true;
                                            $post['author']['name'] = $this->userInfo['name'];
                                            $post['author']['profilePicture'] = $this->userInfo['profilePicture'];

                                        }
                                        else
                                            $currentUserIsAuthor = false;

                                        foreach ($APIResultsForPhotos['data'] as $APIResultForPhoto) {

                                            if ($APIResultForPhoto['name'] == 'photo_author' && !$currentUserIsAuthor) {

                                               throw new Exception;

                                            }
                                            else if ($APIResultForPhoto['name'] == 'photo_url') {

                                                $post['authorContent']['data']['photoUrl'] = $APIResultForPhoto['fql_result_set'][0]['src'];

                                                $height = $APIResultForPhoto['fql_result_set'][0]['height'];
                                                $width = $APIResultForPhoto['fql_result_set'][0]['width'];

                                                // 238px = fixed width for photos in question template
                                                $post['authorContent']['data']['photoHeight'] = round(238 * ($height/$width));
                                                
                                            }

                                        }
                                        
                                    }
                   break;
                   
                   
            }
        }
        
        return $post;
        
    }
    
    private function convertScientificToRegularNumber($number) {
        
        return number_format($number, 0, '', '');
        
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
    
}

?>
