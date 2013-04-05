function UserScore(application) {
    
    var _this = this;
    
    this.application = application;
    
    this.score;
    this.level;
    
    this.getFromModel = function(APICode) {
        
        _this.activateAutoModelUpdate();
        
        $.ajax({   type: "GET",
		   url: "model/data_access.php",
		   dataType: "json",
                   data:   "score&api_code=" + APICode,
                   success: function(data){
                       
                       // The score is automaticaly stored into DB when app crashes,
                       // but it needs 10 seconds to record it on the server. Thus we
                       // use 20-second-life-time cookie to record last score.
                       
                       if (   $.cookie('appCrashProof_score') === undefined
                           || $.cookie('appCrashProof_level') === undefined) {
                
                                // No app crash-proof score recorded
                                _this.score = parseInt(data.score);
                                _this.level = parseInt(data.level);
                       }
                       else {
                           
                                _this.score = parseInt($.cookie('appCrashProof_score'));
                                _this.level = parseInt($.cookie('appCrashProof_level'));
                                $.removeCookie('appCrashProof_score');
                                $.removeCookie('appCrashProof_level');
                           
                       }
                       
                       _this.application.dataLoader.retrievePostsFromCurrentUser();
                       
                   },
                   error: function () {
                         
                       top.location.href = _this.application.getAppUrl();
                         
                    }
                   
               });
   
    }
    
    this.activateAutoModelUpdate = function() {
        
        // We send the score and level to DB every time user close its brower
        // window (or reload the page, change URL, ...) and we also update DB
        // every 5 minutes (300000 miliseconds)
        window.onbeforeunload = function() {
        
            // Crash-proof score persistance cookie management
            $.cookie('appCrashProof_score', _this.score);
            $.cookie('appCrashProof_level', _this.level);
            
            _this._updateModel();
        
        };
        
        setInterval(function() {_this._updateModel();}, 300000);
        
    }
    
    this._updateModel = function() {
        
        var dataToUpdate = 'score=' + _this.score + '&level=' + _this.level;
        
        $.ajax({   type: "GET",
		   url: "model/data_access.php",
		   dataType: "json",
                   data:   dataToUpdate, 
                   success: function(){}
                   
               });
            
    }
    
}    