function ShuffleAnimation() { 

    var _this = this;
    this._timeOfAnimation = 1000;
    this._itemMargin = 20;
    
    this.start = function() {

        // Start animation on each elements
        $('.draggableItem').each(function() {

            // Random start position from gameboard
            $(this).css('top', 
                        _this._randomHeight($(this)))
                   .css('left',
                         _this._randomWidthInsideGameboard($(this)))
                   .css('opacity', 0);

            // Reaching random end position outside gameboard w/ fade-in FX
            $(this).animate({

                  top: _this._randomHeight($(this)),
                  left: _this._randomWidthOutsideGameboard($(this)),
                  opacity: 1

            }, _this._timeOfAnimation, 'swing', function() {

                // End of the animation
                return;

            });


        });
        
    }
    
    this._randomHeight = function(jQueryElement) {
        
        return Math.floor(   Math.random() 
                           * (  $(window).height()
                              - jQueryElement.height()
                              - _this._itemMargin));

    }
    
    this._randomWidthInsideGameboard = function(jQueryElement) {
        
        return Math.floor(   Math.random() 
                           * (  $('#gameboard').width()
                              - jQueryElement.width() ));

    }
    
    this._randomWidthOutsideGameboard = function(jQueryElement) {
        
        // When friend's picture sets near the left border of the screen,
        // their names badly pop outside the screen, thus we include a margin
        // to avoid this.
        if (jQueryElement.children().hasClass('personProposal')) {
            
            var marginForPersonNameApparition = 100;
        }
        else var marginForPersonNameApparition = 0;
        
        
        return   $('#gameboard').width()
               + Math.floor(   Math.random() 
                             * (   $(window).width()
                                 - $('#gameboard').width()
                                 - jQueryElement.width()
                                 - _this._itemMargin
                                 - marginForPersonNameApparition));

    }
    
}