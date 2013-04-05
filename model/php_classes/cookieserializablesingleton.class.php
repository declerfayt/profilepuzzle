<?php

abstract class CookieSerializableSingleton {
    
    private static $instances = array();
    protected $cachedFields;
    
    // Avoid ressource conflits when two different instances are used
    // in parrallel processing
    protected $originalValuesForCachedFields;
    
    protected function __construct() {
        
        $this->cachedFields = $this->initCacheArray();
        $this->restoreInstanceFieldsFromCache();
                
    }
    
    public static final function getInstance() {
        
        $class = get_called_class();
        
        if (!isset(self::$instances[$class]))
            self::$instances[$class] = new $class();
        
        return self::$instances[$class];
        
    }
    
    abstract function initCacheArray();
    
    protected function restoreInstanceFieldsFromCache() {
        
        foreach ($this->cachedFields as $fieldToCache) {

            if (isset($_SESSION[$fieldToCache]) && !empty($_SESSION[$fieldToCache])) {
                    
                      $this->{$fieldToCache} = unserialize($_SESSION[$fieldToCache]);
                      $this->originalValuesForCachedFields[$fieldToCache] = $this->{$fieldToCache};
               
            }
                     
        }
                    
    }

    public function __destruct() {
        
        foreach ($this->cachedFields as $fieldToCache) {
            
            if (    !isset($this->originalValuesForCachedFields[$fieldToCache])
                 || $this->{$fieldToCache} != $this->originalValuesForCachedFields[$fieldToCache]) {
            
                    $_SESSION[$fieldToCache] = serialize($this->{$fieldToCache});
            
                
                }
                
            }
        
   }
   
}

?>