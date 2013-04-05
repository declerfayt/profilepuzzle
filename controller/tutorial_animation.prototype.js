function TutorialAnimation(application) { 

    var _this = this;
    _this.application = application;
    
    this.animationElement;
    this.answerElement;
    this.bodyElement;
    
    this._timeOfAnimation = 1000;
    this._itemMargin = 20;
    
    this.start = function() {

        _this.animationElement = $('.draggableItem > .personProposal').first().parent();
        
        if ($.browser.safari || $.browser.chrome) { 
            
            _this.bodyElement = $('body');
            
        } else { 
            
            _this.bodyElement = $('html');
        
        } 
        
        if (_this.animationElement.size() == 0) {
            
            // No item to display tutorial
            _this.application.pageLoader.numberOfQuestionsDisplayed = 0;
            _this.application.pageLoader.loadTemplate('question');
        }
        
        $('.draggableItem').hide();
        
        _this.animationElement
                .show()
                .css('top', _this._randomHeight(_this.animationElement))
                .css('left', _this._randomWidthInsideGameboard(_this.animationElement))
                .css('opacity', 0)
                .animate({

                    top: _this._randomHeight( _this.animationElement),
                    left: _this._randomWidthOutsideGameboard( _this.animationElement),
                    opacity: 1

                }, 1500, 'swing', function() {

                    
                    $('#questionLayer').append('<div id="tutorial"><div id="mousePicture" class="clickOff"></div><div id="mouseCursor" class="regular"></div></div>');
                    
                    var topPosition = parseInt(_this.animationElement.css('top')) - 5;
                    var leftPosition = parseInt(_this.animationElement.css('left')) - 80
                    
                    $('#tutorial').animate({

                        top: topPosition + 'px',
                        left: leftPosition + 'px'

                    }, 3000, 'swing', function() {
                        
                        _this.animationElement.trigger('mouseover')
                                              .css('background', 'url("./view/images/white_transp_background.png") repeat transparent');
                        
                        $('#mouseCursor').removeClass('regular')
                                          .addClass('move');
                        
                        setTimeout(function() {
                            
                            $('#mousePicture').removeClass('clickOff')
                                              .addClass('clickOn');
                            
                            setTimeout(function() {
                            
                                _this._dragItemAnimation();
                            
                            }, 1000);
                            
                        }, 1000);

                    });
                    

                });
        
    }
    
    this._dragItemAnimation = function() {
        
                $('.noPossibleAnswer, .commentPossibleAnswer')
                    .fadeTo('slow', 0.4, function() {});
                
                $('.personPossibleAnswer').each(function(){
            
                    if ($(this).attr('correctAnswer').indexOf(_this.animationElement.attr('useranswer')) != -1) {
                        
                        _this.answerElement = $(this);
                        return false;
                        
                    }
                
                });
                
                $('.post').css('margin-bottom', '1000px');
                
                var position = _this.answerElement.findPosition();
                
                _this.bodyElement.animate({
                   
                      scrollTop: parseInt(position.top - 200) + 'px' 
                   
                }, 1000, 'swing', function() {
                    
                     _this.answerElement.addClass('highlightPossibleAnswer');
                    
                     $('#tutorial').animate({

                            top: parseInt(200 - 20) + 'px',
                            left: parseInt(position.left) + 'px'

                        }, 3000, 'swing', function() {});
                     
                     
                     _this.animationElement.animate({

                            top: parseInt(200 - 20) + 'px',
                            left: parseInt(position.left + 80) + 'px'

                        }, 3000, 'swing', function() {
                    
                              setTimeout(function() {

                                    $('.noPossibleAnswer, .commentPossibleAnswer')
                                        .fadeTo('slow', 1, function() {});

                                    $('.personPossibleAnswer')
                                        .removeClass('highlightPossibleAnswer');
                                        
                                    $('#mouseCursor').removeClass('move')
                                          .addClass('regular');
                        
                                    $('#mousePicture').removeClass('clickOn')
                                          .addClass('clickOff');    
                                        
                                    _this.application.setUserProposalAndAnswer(_this.animationElement.attr('useranswer'), 
                                                                               _this.animationElement.attr('useranswer'),
                                                                               _this.answerElement, _this.animationElement);
                                     
                                    $('#tutorial').fadeOut('3000');
                                        
                                    setTimeout(function() {
                                        
                                         _this.bodyElement.animate({
                   
                                            scrollTop: 0
                   
                                        }, 1000, 'swing', function() {
 
                                            $('.post').css('margin-bottom', '20px');
                                            $('.draggableItem').show();
                                            _this.application.gui.shuffleDraggableItems();
                                            _this.application.gui.startDragAndDropMechanics(_this.application.pageLoader);
                                            
                                        
                                        });
                                    
                                    }, 3000); 

                                }, 1500);
                    
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