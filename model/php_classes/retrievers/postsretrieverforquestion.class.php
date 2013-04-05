<?php

class PostsRetrieverForQuestion {
    
    private $facebookWrapper;
    private $dbAccess;
    
    private $currentUserFBID;
    private $userInfo;
    
    
    public function start($currentUserFBID, $friendID = 0) {
        
         try {
            
            $this->facebookWrapper = FacebookWrapper::getInstance();
            $this->dbAccess = DBAccess::getInstance(); 
             
            $this->currentUserFBID = $currentUserFBID;
            
            if ($friendID == 0)
                $authorID = $this->currentUserFBID;
            else
                $authorID = $friendID;
            
            // Are posts already in DB?
            $postsInDB = $this->dbAccess->sendPreparedProcedure('count_posts_in_db',
                                                                array('authorID' => $authorID,
                                                                      'userID' => $this->currentUserFBID));
            
            if (!isset($postsInDB[0]->numberOfPosts)) {
                
                // No post already in DB
                if ($friendID == 0 || $friendID == $this->currentUserFBID) {
                
                        // Retrieve current user posts
                        $this->userInfo = $this->getUserInfo($currentUserFBID);
                        $numberOfPosts = $this->retrieveCurrentUserPostsFromAPI();
                    } 
                    else {

                        $this->userInfo = $this->getUserInfo($friendID);
                        $numberOfPosts = $this->getFriendsPostsLikedOrCommentedByCurrentUser($this->userInfo['userFBID']);
                    }

                    return $numberOfPosts;
                    
            }
            else
                return $postsInDB[0]->numberOfPosts;
            
            
        }
        catch(Exception $e) {
            
            error_log('Post Retriever Error - '
                      . $e->getFile()
                      . ' @ line '
                      . $e->getLine()
                      .' :  '. $e->getMessage());
            
            throw new Exception;
            
        }
        
    }
    
    protected function retrieveCurrentUserPostsFromAPI() {
        
        $whichPosts = array('links' => 'link_id', 
                            'photos' => 'object_id',
                            'statuses' => 'status_id',
                            'photo_tags' => 'object_id');
        
        $FBQueries = array();
        $numberOfPosts = 0;
        
        foreach ($whichPosts as $typeOfPosts => $primaryKey) {
            
            $FBQueries[$typeOfPosts] = $this->facebookWrapper
                                       ->constructQuery('get_current_user_'.$typeOfPosts);
            
            $FBQueries[$typeOfPosts.'_likes'] = $this->facebookWrapper
                                                ->constructQuery('get_current_user_liked_posts',
                                                                 array('primaryKey' => $primaryKey,
                                                                       'posts' => $typeOfPosts));
            
            $FBQueries[$typeOfPosts.'_comments'] = $this->facebookWrapper
                                                   ->constructQuery('get_current_user_commented_posts',
                                                                    array('primaryKey' => $primaryKey,
                                                                          'posts' => $typeOfPosts));
            
        }
        
        $APIResults = $this->facebookWrapper->sendAPIMultiQuery($FBQueries);
        
        foreach($APIResults['data'] as $APIResult) {
            
            $areLikedPosts = strpos($APIResult['name'], '_likes');
            $areCommentedPosts = strpos($APIResult['name'], '_comments');
            
            if (strpos($APIResult['name'], 'links') !== false)
                    $postType = 'link';
            else if (strpos($APIResult['name'], 'statuses') !== false)
                    $postType = 'status';
            else if (strpos($APIResult['name'], 'photos') !== false)
                    $postType = 'photo';
            else if (strpos($APIResult['name'], 'photo_tags') !== false)
                    $postType = 'photo_tag';     
            
            
            if ($areLikedPosts || $areCommentedPosts) {
                
                foreach ($APIResult['fql_result_set'] as $post) {
                    
                    $postID = $this->convertScientificToRegularNumber($post['object_id']);
                    
                    $this->dbAccess->sendPreparedProcedure('insert_new_post',
                                                           array('authorID' => $this->userInfo['userFBID'],
                                                                 'userID' => $this->currentUserFBID,
                                                                 'postID' => $postID, 
                                                                 'postType' => $postType),
                                                           false);
                    
                    $numberOfPosts++;
                    
                }
                
            }
                
        }
        
        return $numberOfPosts;
        
    }
    
    private function getFriendsPostsLikedOrCommentedByCurrentUser($friendID) {
        
        $whichPosts = array('links', 'photos', 'statuses', 'photo_tags');
        $APIQueries = array();
        $numberOfPosts = 0;
        
        foreach ($whichPosts as $typeOfPosts) {
            
            $APIQueries[$typeOfPosts.'_likes'] = $this->facebookWrapper
                                                      ->constructQuery('get_friend_liked_'.$typeOfPosts,
                                                                       array('userID' => $this->currentUserFBID,
                                                                       'friendID' => $friendID));
            
            $APIQueries[$typeOfPosts.'_comments'] = $this->facebookWrapper
                                                             ->constructQuery('get_friend_commented_'.$typeOfPosts,
                                                                              array('userID' => $this->currentUserFBID,
                                                                              'friendID' => $friendID));
            
        }
            
        $APIResults = $this->facebookWrapper->sendAPIMultiQuery($APIQueries);
        
        if (count($APIResults['data']) > 0) {
            
            foreach ($APIResults['data'] as $APIResult) {
            
                
                if (strpos($APIResult['name'], 'links') !== false)
                    $postType = 'link';
                else if (strpos($APIResult['name'], 'statuses') !== false)
                        $postType = 'status';
                else if (strpos($APIResult['name'], 'photos') !== false)
                        $postType = 'photo';
                else if (strpos($APIResult['name'], 'photo_tags') !== false)
                        $postType = 'photo_tag';     

                
                foreach ($APIResult['fql_result_set'] as $post) {
                    
                    $postID = $this->convertScientificToRegularNumber($post['object_id']);
                    
                    $this->dbAccess->sendPreparedProcedure('insert_new_post',
                                                           array('authorID' => $this->userInfo['userFBID'],
                                                                 'userID' => $this->currentUserFBID,
                                                                 'postID' => $postID, 
                                                                 'postType' => $postType),
                                                           false);
               
                    $numberOfPosts++;
                    
                }    
            
            }
            
        }
        
        return $numberOfPosts;
        
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
        
        $userFBID = $results['data'][0]['uid'];
        
        return array('name' => $name, 'profilePicture' => $profilePicture, 'userFBID' => $userFBID);
       
    }
    
}

?>