<?php

include_once('model/config.include.php');

// App needs to authetificate to FB API throught URL mechanism
if (!isset($_GET['code'])) {
    
    setcookie('testCookie', 'true', time() + 1000, '/');
    
    $authentificationUrl = json_decode(file_get_contents($GLOBALS['config']['general']['serverUrl'].'/model/data_access.php?authentification_url'));
    
    // Redirection via JS (including browser window height and width)
    echo '<script>top.location.href="'. $authentificationUrl .'"</script>';
    
}
else {
    
    $code = $_GET['code'];
    $appUrl = $GLOBALS['config']['general']['appUrl'];
    
    // Including HTML base
?>
<!DOCTYPE html>
<html>
    
<head>

    <title>Profile Puzzle</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <!-- Importing jQuery tools -->
    <script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script src="http://code.jquery.com/ui/1.9.1/jquery-ui.min.js"></script>
    <script src="controller/jquery_plugins/jquery.tmpl.min.js"></script>
    <script src="controller/jquery_plugins/jquery.waitforimages.min.js"></script>
    <script src="controller/jquery_plugins/jquery.cookie.min.js"></script>
    <script src="controller/jquery_plugins/soundmanager2/soundmanager2.min.js"></script>
    
    <!-- Importing JS files to feed controller layer -->
    <script src="controller/application.prototype.js"></script>
    <script src="controller/pageloader.prototype.js"></script>
    <script src="controller/progress_bar.prototype.js"></script>
    <script src="controller/progress_bar_animation.prototype.js"></script>
    <script src="controller/userscore.prototype.js"></script>
    <script src="controller/gui.prototype.js"></script>
    <script src="controller/shuffle_animation.prototype.js"></script>
    <script src="controller/people_choice_animation.prototype.js"></script>
    <script src="controller/tutorial_animation.prototype.js"></script>
    <script src="controller/dataloader.prototype.js"></script>
    <script src="controller/soundplayer.prototype.js"></script>
    
    <!-- Importing CSS files -->
    <link rel="stylesheet" type="text/css" href="view/css/start_up.css" />
    <link rel="stylesheet" type="text/css" href="view/css/general.css" />
    <link rel="stylesheet" type="text/css" href="view/css/progress_bar.css" />
    <link rel="stylesheet" type="text/css" href="view/css/score_header.css" />
    <link rel="stylesheet" type="text/css" href="view/css/gameboard.css" />
    <link rel="stylesheet" type="text/css" href="view/css/game_mechanics.css" />
    <link rel="stylesheet" type="text/css" href="view/css/pop_up.css" />
    <link rel="stylesheet" type="text/css" href="view/css/level_pop_up.css" />
    <link rel="stylesheet" type="text/css" href="view/css/tutorial.css" />
    
    <script type="text/javascript">

        var app;
        
        $(document).ready(function () {
            
            try {
                
                // Cannot run if used under HTTPS protocol
                if (window.location.protocol == 'https:') {
                    
                    throw 'https protocol error';
                    
                }

                app = new Application();
                app.setAPICode("<?php echo $code; ?>");
                app.setAppUrl("<?php echo $appUrl; ?>");
                app.start();
                
            }
            catch (e) {
                
                top.location.href = "<?php echo $GLOBALS['config']['general']['appUrl']; ?>";
                
            }
                                 
       });

    </script>   

</head>

<body>
    
    <div id="startupLayer"></div>
    <div id="progressBarLayer"></div>
    <div id="popUpLayer"></div>
    <div id="questionLayer"></div>
    <div id="prerenderLayer"></div>
   
</body>

</html>
<?php

}

?>