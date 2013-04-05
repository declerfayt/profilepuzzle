function ProgressBar(application) {
    
    var _this = this;
    this.application = application;
    this._htmlLayer = $('#progressBarLayer');
    
    this.show = function() {

        var innerHtmlContent = '<div id="progressBarImages">';
        innerHtmlContent += '<div class="progressBarCompleted"></div>';
        innerHtmlContent += '<div class="progressBarLoading"></div>';
        innerHtmlContent += '<div class="progressBarToLoad"></div></div>';

        _this._htmlLayer.html(innerHtmlContent);

        var progressBarAnimation = new ProgressBarAnimation();
        progressBarAnimation.start(12000);
        
        _this._htmlLayer.fadeIn('slow');

        
    }
    
    this.hide = function() {
        
        if (_this.application.pageLoader.numberOfQuestionsDisplayed == 0) {
            
            $('#ytplayer').animate({ opacity: 0.4 }, 1500, 'swing', function() {});
            
            var watchOnYoutubeDivContent = '<div id="watchOnYoutube"><a href="http://www.youtube.com/watch?v=' + _this.application.YoutubeIntroVideoCode + '" target="_blank" class="about">Watch on<br />YouTube</a></div>';
            
            $('#progressBarImagesForStartUpLayer').html(watchOnYoutubeDivContent);
            
            $('#displayFirstQuestionButton').animate({

                           opacity: 1

                     }, 1500, 'swing', function() {
                         
                         // $('#displayFirstQuestionButton').removeClass('disabled')
                     
                     })
                     
                     .click(function() {
                         
                         // Once app's first big loading is completed, we can prepare next level pop up
                         _this.application.dataLoader.bufferNextData('level_popup');
                         
                         $('#startupLayer').remove();
                         _this._htmlLayer.fadeOut('slow');
                         _this.application.pageLoader.givePageLife();
                         
                    });
            
        }
        else {
            
            _this._htmlLayer.fadeOut('slow');
            setTimeout(function() {$('#progressBarImages').html('');}, 1500);
            _this.application.pageLoader.givePageLife();
            
        }
        
        
        
    }
    
}

