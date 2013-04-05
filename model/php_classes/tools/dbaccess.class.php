<?php

class DBAccess {
    
    private $connexion;
    private static $instance;

    private function __construct() {

        try {
                
                $this->connexion = new PDO('mysql:host='.$GLOBALS['config']['DBAccess']['host']
                                     .';dbname='.$GLOBALS['config']['DBAccess']['databaseName'],
                                     $GLOBALS['config']['DBAccess']['user'],
                                     $GLOBALS['config']['DBAccess']['password']);

                $this->connexion->setAttribute(PDO::ATTR_ERRMODE, 
                                               PDO::ERRMODE_EXCEPTION);

                $this->connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, 
                                               PDO::FETCH_OBJ);

		  $this->connexion->exec("SET CHARACTER SET utf8");

                return true;
                
        }
        catch(Exception $e) {
            
            error_log('PDO Error - '
                      . $e->getFile()
                      . ' @ line '
                      . $e->getLine()
                      .' :  '. $e->getMessage());
            return false;
        }
   }
   
   public static function getInstance() {

       if(is_null(self::$instance)) {
           
            self::$instance = new DBAccess();
        
        }
        return self::$instance; 
        
   }

   public function sendPreparedProcedure($preparedProcedureName,
                                         $procedureParamsArray = array(),
                                         $doesReturnResults = true) {

         try {

                 $preparedProcedure = file_get_contents($GLOBALS['config']['general']['serverUrl']."/model/db_procedures/sql/$preparedProcedureName.sql");
                 
                 // error_log('!!! MySQL Procedure !!! '.$preparedProcedure);
                 
                 $statement = $this->connexion->prepare($preparedProcedure);

                 $statement = $this->InjectionFreeParamsBiding($statement, $procedureParamsArray);
                
                 $statement->execute();

                 if ($doesReturnResults)
                     $results = $statement->fetchAll();
                 
                 $statement->closeCursor();
                 unset($statement);

                 if ($doesReturnResults)
                    return $results;
                 else
                    return true;

        }
        catch(Exception $e) {

               return false;
                 
        }
    }
   
    public function sendCacheProcedure($userID, $action, // get, set, insert
                                       $key, $value = '', 
                                       $doesReturnResults = true) {

         try {

                 $query = file_get_contents('db_procedures/sql/'.$action.'_from_cache.sql');
             
                 $query = str_replace('{{userID}}', mysql_real_escape_string($userID), $query);
                 $query = str_replace('{{key}}', mysql_real_escape_string($key), $query);
                 
                 if ($key != 'question')
                    $query = str_replace('{{value}}', mysql_real_escape_string($value), $query);
                 else {
                    
                    $value = str_replace('"', '\"', $value);
                    $query = str_replace('{{value}}', $value, $query);
                    
                 }
                 
                 $statement = $this->connexion->prepare($query);
                 $statement->execute();

                 if ($doesReturnResults)
                     $results = $statement->fetchAll();
                 
                 $statement->closeCursor();
                 unset($statement);

                 if ($doesReturnResults && $key == 'question')
                     $results = str_replace('\"', '"', $results);
                 
                 if ($doesReturnResults)
                    return $results;
                 else
                    return true;

        }
        catch(Exception $e) {

                 return false;
                 
        }
        
   }
        
   private function InjectionFreeParamsBiding($PDOStatement, $paramsArray) {
        
        foreach ($paramsArray as $key => $value) {

            $PDOStatement->bindParam($key, mysql_real_escape_string($value));

        }
        
        return $PDOStatement;
        
    }
    
    public function sendMultiQueries($queriesArray) {
        
        foreach ($queriesArray as $query) {
            
            // error_log('!!! MySQL Query !!! '.$query);
            
            try {
            
                 $statement = $this->connexion->prepare($query);
                 $statement->execute();
                 $statement->closeCursor();
                 unset($statement);

            }
            catch(Exception $e) {

                 error_log('PDO Error - '
                           . $e->getFile() 
                           . ' @ line '. $e->getLine() 
                           .' :  '. $e->getMessage());
                 
            }
            
        }
        
    }
    
}

?>