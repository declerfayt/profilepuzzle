<?php

class NewUserBuilder {
   
    private $userFBID;
    private $facebookWrapper;
    private $dbAccess;
     
    public function createNewUser($userFBID) { 
    
        $this->userFBID = $userFBID;
        
        $this->facebookWrapper = FacebookWrapper::getInstance();
        $this->dbAccess = DBAccess::getInstance();
        
        // Retrieving user general info
        $results = $this->facebookWrapper
                        ->sendAPISingleProcedure('get_all_user_info', 
                                                 array('userID' => $userFBID));
        
        $translatedResults = $this->translateAPIUserInfo($results);
        
        $this->dbAccess->sendPreparedProcedure('create_new_user',
                                               $translatedResults, false);
        
        $this->getUserFanPages();
        
    }
    
    
    private function translateAPIUserInfo($APIResults) {
        
        $APIResults = $APIResults['data'][0];
        
        $returnResults = array();
        
        $returnResults['id'] = $APIResults['uid'];
        $returnResults['language'] = substr($APIResults['locale'], 0, 2);
        
        $returnResults['birthday_date'] =   substr($APIResults['birthday_date'], 6, 4)
                                           . '-'
                                           . substr($APIResults['birthday_date'], 0, 2)
                                           . '-'
                                           . substr($APIResults['birthday_date'], 3, 2);
        
        $returnResults['first_name'] = $APIResults['first_name'];
        $returnResults['last_name'] = $APIResults['last_name'];
        $returnResults['city'] = $APIResults['current_location']['city'];
        $returnResults['country'] = $APIResults['current_location']['country'];
        $returnResults['profile_picture'] = $APIResults['pic'];
        
        if ($APIResults['sex'] == 'male')
            $returnResults['sex'] = true;
        else if ($APIResults['sex'] == 'female')
            $returnResults['sex'] = false;
        
        return $returnResults;
        
    }
    
    private function getUserFanPages() {
        
        $results = $this->facebookWrapper
                        ->sendAPISingleProcedure('get_current_user_fan_pages', 
                                                 array('userID' => $this->userFBID));
        
        foreach ($results['data'] as $result) {
            
            $pageID = $this->convertScientificToRegularNumber($result['page_id']);
            
            $this->dbAccess->sendPreparedProcedure('insert_user_fan_pages',
                                                   array('userID' => $this->userFBID,
                                                         'pageID' => $pageID), false);
            
        }
        
    }
    
    private function convertScientificToRegularNumber($number) {
        
        return number_format($number, 0, '', '');
        
    }
     
}
?>
