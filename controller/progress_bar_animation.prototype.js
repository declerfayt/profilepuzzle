function ProgressBarAnimation() { 

    var _this = this;
    this._frames = 12;
    
    this.start = function(duration) {
        
        _this._animationLoop(duration, 0);
        
    }
    
    this._animationLoop = function(duration, i) {
        
         if (i <= _this._frames) {
             
            $('.progressBarCompleted')
             .css('width', i * 12);
             
            $('.progressBarLoading')
             .css('left', i * 12);
             
            $('.progressBarToLoad')
             .css('left', 36 + (i * 12))
             .css('width', 140 - (i * 12));
          
            setTimeout(function() {
              
              _this._animationLoop(duration, ++i);
              
            }, Math.round(duration/_this._frames));
            
         }
         
    }
}    