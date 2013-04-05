<?php

class DAL extends CookieSerializableSingleton {
    
    protected $currentUserFBID;
    
    public function initCacheArray() {
        
        return array('currentUserFBID');
        
    }
       
    public function getAuthentificationUrl() {
        
        $facebookWrapper = FacebookWrapper::getInstance();
        return $facebookWrapper->getAuthentificationUrl();
        
    }
    
    public function setAPICode($APICode) {
        
        if (empty($this->accessToken)) {
            
            $facebookWrapper = FacebookWrapper::getInstance();
            $this->accessToken = $facebookWrapper->setAPICode($APICode);
            
        }
        
    }
    
    public function setCurrentUserFBID() {
        
        if (empty($this->currentUserFBID)) {
            
            $this->currentUserFBID = $this->getCurrentUserIDFromFacebookAPI();
            
            if (!$this->isUserAlreadyInDB($this->currentUserFBID)) {
            
                $newUserBuilder = new NewUserBuilder();
                $newUserBuilder->createNewUser($this->currentUserFBID);

            }
        
        }
        
    }
    
    public function getNewQuestion() {
        
        $questionBuilder = new QuestionBuilder();
        return $questionBuilder->getNewQuestion($this->currentUserFBID);
        
    }
    
    private function getCurrentUserIDFromFacebookAPI() {
        
        $facebookWrapper = FacebookWrapper::getInstance();
        $results = $facebookWrapper->sendAPISingleProcedure('get_current_user_id');
        return number_format($results['data'][0]['uid'], 0, '', '');
        
    }
    
    private function isUserAlreadyInDB($userFBID) {
        
        $dbAccess = DBAccess::getInstance();
        
        $results = $dbAccess->sendPreparedProcedure('find_user_id', 
                                                    array('userID' => $userFBID));
        
        if (isset($results[0]->id))
            return true;
        else
            return false;
        
    }
    
    public function getUserScoreAndLevel() {
        
        $dbAccess = DBAccess::getInstance();
        
        $results = $dbAccess->sendPreparedProcedure('get_user_score_and_level', 
                                                    array('userID' => $this->currentUserFBID));
        
        return array('score' => $results[0]->score,
                     'level' => $results[0]->level);
        
    }
    
    public function updateUserScoreAndLevel($score, $level) {
        
        // Prevent user SQL injection
        $score = intval($score);
        $level = intval($level);
        
        $dbAccess = DBAccess::getInstance();
        
        $results = $dbAccess->sendPreparedProcedure('update_user_score_and_level', 
                                                     array('userID' => $this->currentUserFBID,
                                                     'score' => $score,
                                                     'level' => $level));
        
        return array('DB Access Reply' => 'Score and level updated in DB');
        
    }
    
    public function getLevelPopupInfo($level) {
        
        $levelPopupBuilder = new levelPopupBuilder();
        return $levelPopupBuilder->getLevelPopupInfo($level, $this->currentUserFBID);
        
    }
    
    public function retrievePosts($userID) {
        
        $postRetrieverForQuestion = new PostsRetrieverForQuestion();
        
        if (empty($userID))
            return $postRetrieverForQuestion->start($this->currentUserFBID);
        else
            return $postRetrieverForQuestion->start($this->currentUserFBID, intval($userID));
            
    }
    
}

?>