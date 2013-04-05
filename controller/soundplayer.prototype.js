function SoundPlayer(application) {
    
    var _this = this;
    
    this.application = application;
    this._isPlayerReady = false;
    
    this.setup = function() {
        
        soundManager.setup({
            
            url: 'controller/jquery_plugins/soundmanager2/soundmanager2.swf',
            flashVersion: 8,
            onready: function() {
    
                soundManager.createSound({
                    
                    id: 'levelUp',
                    url: 'view/sounds/level_up.mp3',
                    autoLoad: true,
                    autoPlay: false,
                    onload: function() {},
                    volume: 50
                    
               });
    
    
                _this._isPlayerReady = true;
                
            }
            
        });
        
    }
    
    this.playSound = function(sound) {
        
        if (_this._isPlayerReady == true) {
            
            soundManager.play(sound);
            
        }
        
    }
    
}   