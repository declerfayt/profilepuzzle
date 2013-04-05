function PeopleChoiceAnimation() { 

    var _this = this;
    this._firstStepOfAnimation = 800;
    this._secondStepOfAnimation = 2500;
    this._itemMargin = 20;
    
    this.start = function() {

        $('.draggableItem').each(function() {

            $(this).animate({

                opacity: 0.4

            }, _this._firstStepOfAnimation, 'swing', function() {

                 return;

            });
            
        });

        var topPosition;
        var leftPosition;

        $('#peopleChoice_0, #peopleChoice_1, #peopleChoice_2').each(function() {

            topPosition = $(window).height() - parseInt($(this).css('bottom')) - 33;
            leftPosition = $(window).width() - parseInt($(this).css('right')) - 33;

            $(this).css('z-index', '5')
                   .css('top', topPosition + 'px')
                   .css('left', leftPosition + 'px')
                   .animate({

                        top: _this._randomHeight($(this)),
                        left: _this._randomWidth($(this)),
                        opacity: 0

                   }, _this._secondStepOfAnimation, 'swing', function() {

                             return;

                    })
        
        });
        

        $('#peopleChoice_0 img, #peopleChoice_1 img, #peopleChoice_2 img').each(function() {

            $(this).animate({

                  opacity: 1

            }, _this._firstStepOfAnimation, 'swing', function() {

                return;

            })
            
        });    
        
    }
    
    this._randomHeight = function(jQueryElement) {
        
        return Math.floor(   Math.random() 
                           * ((  $(window).height()
                              - jQueryElement.height()
                              - _this._itemMargin) / 2));
                      

    }
    
    this._randomWidth = function(jQueryElement) {
        
         return Math.floor(   Math.random() 
                           * ((  $(window).width()
                              - jQueryElement.width()
                              - _this._itemMargin) / 4));

    }
    
}