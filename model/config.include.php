<?php

$GLOBALS['config'] = array();

$GLOBALS['config']['general']['serverUrl'] = 'http://www.profilepuzzle.com/app';

$GLOBALS['config']['general']['appUrl'] = "http://apps.facebook.com/profilepuzzle";

$GLOBALS['config']['FacebookWrapper']['appId'] = "******************";

$GLOBALS['config']['FacebookWrapper']['postAuthUrl'] = $GLOBALS['config']['general']['appUrl']
                                                       .'/index.php';

$GLOBALS['config']['FacebookWrapper']['permissionsArray'] 
            = array( "read_stream",
                     "user_activities", "friends_activities",
                     "user_birthday", "friends_birthday",
                     "user_events", "friends_events",
                     "user_likes", "friends_likes",
                     "user_location", "friends_location",
                     "user_photos", "friends_photos",
                     "user_status", "friends_status",
                     "user_videos", "friends_videos");

$GLOBALS['config']['FacebookWrapper']['appSecret'] = "**********************";


/////////////////////////////////


$GLOBALS['config']['DBAccess']['user'] = '***************';
$GLOBALS['config']['DBAccess']['password'] = '*******************';
$GLOBALS['config']['DBAccess']['host'] = '***********';
$GLOBALS['config']['DBAccess']['databaseName'] = '*************';


?>
