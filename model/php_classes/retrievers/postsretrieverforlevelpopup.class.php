<?php

class PostsRetrieverForLevelPopup {
    
    private $facebookWrapper;
    private $dbAccess;
    
    private $currentUserFBID;
    private $userInfo;
    
    private $beginOfYear;
    private $endOfYear;
    private $postType;
    
    
    public function __construct() {
        
        $this->postType = array('links' => 'link_id', 
                                'photos' => 'object_id',
                                'statuses' => 'status_id',
                                'photo_tags' => 'object_id');
        
        $this->beginOfYear = NULL;
        $this->endOfYear = NULL;
        
    }
    
    public function setYear($year) {
        
        date_default_timezone_set('UTC');
        $this->beginOfYear = mktime(0, 0, 0, 1, 1, $year);
        $this->endOfYear = mktime(0, 0, 0, 12, 31, $year);
        
    }
    
    public function setPostType($postType) {
        
        if (isset($this->postType[$postType])) {
            
            $primaryKey = $this->postType[$postType];
            $this->postType = array($postType => $primaryKey);
            
        }
        
    }
    
    
    public function start($currentUserFBID, $level) {
        
         try {
            
            $this->facebookWrapper = FacebookWrapper::getInstance();
            $this->dbAccess = DBAccess::getInstance(); 
             
            $this->currentUserFBID = $currentUserFBID;
            $this->userInfo = $this->getUserInfo($currentUserFBID);
            return $this->retrieveCurrentUserPostsFromAPI($level);
            
            
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
    
    protected function retrieveCurrentUserPostsFromAPI($level) {
        
        $whichPosts = $this->postType;
        
        $FBQueries = array();
        
        foreach ($whichPosts as $typeOfPosts => $primaryKey) {
            
            if ($this->beginOfYear == NULL && $this->endOfYear == NULL) {
                
                $FBQueries[$typeOfPosts] = $this->facebookWrapper
                                       ->constructQuery('get_current_user_'.$typeOfPosts);
            }
            else {
                
                $FBQueries[$typeOfPosts] = $this->facebookWrapper
                                       ->constructQuery('get_yearly_current_user_'.$typeOfPosts,
                                                        array('beginOfYear' => $this->beginOfYear,
                                                              'endOfYear' => $this->endOfYear));
                
            }
            
            
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
        $posts = array();
        
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
                    
                    if (!isset($posts[$postID])) {
                        
                        $posts[$postID] = array('likes' => 0, 'comments' => 0);
                    }
                    
                    if ($areLikedPosts)
                        $posts[$postID]['likes'] += 1;
                    else if ($areCommentedPosts)
                        $posts[$postID]['comments'] += 1;
                    
                    $posts[$postID]['type'] = $postType;
                }
                
            }
                
        }
        
        foreach ($posts as $postID => $postValues) {
            
            $this->dbAccess->sendPreparedProcedure('insert_new_level_item',
                                                    array('userID' => $this->currentUserFBID,
                                                          'postID' => $postID,
                                                          'level' => $level,
                                                          'likes' => $postValues['likes'],
                                                          'comments' => $postValues['comments'],
                                                          'postType' => $postValues['type']),
                                                           false);
            
        }
        
        return count($posts);
        
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