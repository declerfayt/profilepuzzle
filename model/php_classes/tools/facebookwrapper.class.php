<?php

class FacebookWrapper extends CookieSerializableSingleton {
        
        private $appId;
        private $appSecret;
        private $postAuthUrl;
	private $permissionsArray;
	
        protected $accessToken;
        
	protected function __construct() {
        
                parent::__construct();
                
                $this->appId = $GLOBALS['config'][get_class()]['appId'];
		$this->appSecret = $GLOBALS['config'][get_class()]['appSecret'];
		$this->postAuthUrl = $GLOBALS['config'][get_class()]['postAuthUrl']; 
		$this->permissionsArray = $GLOBALS['config'][get_class()]['permissionsArray'];
                
        }
        
        public function initCacheArray() {
        
            return array('accessToken');
        
        }
        
        public function getAuthentificationUrl() {
		
		$dialogUrl = 'https://www.facebook.com/dialog/'
                             .'oauth?client_id='
			     . $this->appId 
                             . '&redirect_uri='
                             . urlencode($this->postAuthUrl)
			     . '&scope=' . implode(',', $this->permissionsArray);
		
                return $dialogUrl;
  
	}
        
        public function setAPICode($APICode) {
            
            return $this->setAccessTokenWithAPICode($APICode);
           
        }
        
        private function setAccessTokenWithAPICode($APICode) {
		
                if (empty($this->accessToken)) {
                    
                    try {
	
                            $tokenUrl = 'https://graph.facebook.com/oauth/'
                                            .'access_token?client_id='
                                            . $this->appId . '&redirect_uri=' 
                                            . urlencode($this->postAuthUrl)
                                            . '&client_secret=' . $this->appSecret
                                            . '&code=' . $APICode;

                            $this->accessToken = file_get_contents($tokenUrl);

                            return $this->accessToken;
        
                    }
                    catch(Exception $e) {
                        
                        // Sending a 500 error on HTTP response
                        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                        echo json_encode(array('Error' => 'API code already used. Refresh app with code param in the URL to get a new one.'));
                        exit;
                        
                    }
                    
		}
                
	}
        
        public function sendAPISingleProcedure($procedureName,
                                               $paramsArray = array()) {
		
                $query = $this->constructQuery($procedureName, $paramsArray);
                
                $fqlQueryUrl = 'https://graph.facebook.com/'
				. '/fql?q='.urlencode($query)
				. '&' . $this->accessToken;
		
                $fqlQueryResult = file_get_contents($fqlQueryUrl);
		
		return json_decode($fqlQueryResult, true);
		
	}
	
        public function constructQuery($procedureName, $paramsArray = array()) {
            
            $query = file_get_contents("db_procedures/fb_api/$procedureName.fql");
            
            foreach ($paramsArray as $key => $value) {
                
                $query = str_replace('{{'.$key.'}}', $value, $query);
             
            }
            
            return $query;
            
        }
        
	public function sendAPIMultiQuery($multiQueriesArray) {
		
                $fqlMultiQueryUrl = 'https://graph.facebook.com/fql?q={';
		
		foreach ($multiQueriesArray as $key => $value) {
			
			// Add a comma between quieries, plus erase useless
                        // characters (blank space, tabulations,...)
                        $fqlMultiQueryUrl .= '"'.urlencode(
                                                preg_replace('#(\s{2,})|\t#', 
                                                             ' ', $key))
                                             .'":"'.urlencode(
                                               preg_replace('#(\s{2,})|\t#',
                                                            ' ', $value)).'",';
						
		}
		
		// Erase last comma
		$fqlMultiQueryUrl = substr($fqlMultiQueryUrl, 0,
                                           strlen($fqlMultiQueryUrl)-1);
		
		$fqlMultiQueryUrl .= '}&'.$this->accessToken;
		
                $fqlMultiQueryResult = file_get_contents($fqlMultiQueryUrl);
		
		return json_decode($fqlMultiQueryResult, true);
                
	}
        
        public function getMutualFriends($friendID) {
            
                $fqlQueryUrl = 'https://graph.facebook.com/me/mutualfriends/'
                               .urlencode($friendID)
                               .'?fields=picture,name,id&'
                               .$this->accessToken;
		
                $fqlQueryResult = file_get_contents($fqlQueryUrl);
		
		$returnArray = json_decode($fqlQueryResult, true);
                $returnArray = $returnArray['data'];
                shuffle($returnArray);
                
                if (count($returnArray) > 5)
                    return array_slice($returnArray, 0, 5);
                else
                    return $returnArray;
 
        }
        
}

?>