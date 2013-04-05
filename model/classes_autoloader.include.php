<?php

function classesAutoloader($class, $classesFolder = 'php_classes') {
    
    $allContentOfFolder = scandir($classesFolder.'/');
    $filesList = array(); 

    foreach ($allContentOfFolder as $contentOfFolder) {
        
        if ($contentOfFolder == '.')
            continue;
        
        else if ($contentOfFolder == '..')
            continue;
        
        else if (is_dir($classesFolder.'/'.$contentOfFolder))
            classesAutoloader($class, $classesFolder.'/'.$contentOfFolder);
        
        else if (    is_file($classesFolder.'/'.$contentOfFolder) 
                 &&  $contentOfFolder == strtolower($class).'.class.php') {
            
            include_once $classesFolder.'/'.strtolower($class).'.class.php';
            
        }
            
    }
    
    
}

spl_autoload_register('classesAutoloader');

?>