function GUI(application) { 

    var _this = this;
    
    this.application = application;
    
    this._correctAnswer;
    this._userAnswer;
    this._draggedJQueryElement;
    this._droppedJQueryElement;
    this._numberOfActions;
    
    this.shuffleDraggableItems = function() {
        
         var shuffleAnimation = new ShuffleAnimation();
         shuffleAnimation.start();
         
    }
    
    this.displayTutorialAnimation = function() {
        
        var tutorialAnimation = new TutorialAnimation(_this.application);
        return tutorialAnimation.start();
        
    }
    
    this.startDragAndDropMechanics = function() {
        
        $('.personPossibleAnswer, .commentPossibleAnswer').droppable({
            
            disabled: true, 
            hoverClass: 'highlightPossibleAnswer',
            drop: function() {
                
                _this._correctAnswer = $(this).attr('correctAnswer');
                _this._droppedJQueryElement = $(this);
                
            }
        
        });
        
        $('.draggableItem').draggable({
            
            start: function() {
                
                $('.noPossibleAnswer, .draggableItem').not(this)
                   .fadeTo('slow', 0.4, function() {});
                
                if($(this).children('div').hasClass('commentProposal')) {
                    
                    $('.personPossibleAnswer')
                      .fadeTo('slow', 0.4, function() {});
                    
                    $('.commentPossibleAnswer').droppable({
                        disabled: false});
                }
                else {
                    
                    $('.commentPossibleAnswer')
                       .fadeTo('slow', 0.4, function() {});
                
                    $('.personPossibleAnswer').droppable({
                        disabled: false});
                        
                }
            },
            
            stop: function() {
        
                if($(this).children('div').hasClass('commentProposal')) {
                    
                    $('.commentPossibleAnswer')
                       .droppable('option', 'disabled', true);
                
                }
                else {
                    
                    $('.personPossibleAnswer')
                       .droppable('option', 'disabled', true);
                       
                };
                
                $(  '.noPossibleAnswer, .draggableItem,'
                  + '.commentPossibleAnswer, .personPossibleAnswer')
                  .fadeTo('slow', 1, function() {});
                  
                // Does user has proposed an answer?
                if (    _this._correctAnswer 
                     && _this._droppedJQueryElement) {
                    
                    _this._userAnswer = $(this).attr('userAnswer');
                    _this._draggedJQueryElement = $(this);
                    
                    // Send user answer and correct answer to app
                    _this.application
                      .setUserProposalAndAnswer(_this._userAnswer,
                                                _this._correctAnswer,
                                                _this._droppedJQueryElement,
                                                _this._draggedJQueryElement);
                    
                }
                
                // Reset user drag & drop info
                _this._userAnswer = null;
                _this._correctAnswer = null;
                _this._droppedJQueryElement = null;
                _this._draggedJQueryElement = null;
                 
            }
        });
        
    }
    
    this.eraseBadAnswer = function(droppedJQueryElement, draggedJQueryElement) {
        
        if (droppedJQueryElement.hasClass('commentPossibleAnswer')) {
            
            droppedJQueryElement.addClass('noPossibleAnswer')
                                .removeClass('commentPossibleAnswer');
        }
        else if (droppedJQueryElement.hasClass('personPossibleAnswer')) {
            
            droppedJQueryElement.addClass('noPossibleAnswer')
                                .removeClass('personPossibleAnswer');
        }
        
        draggedJQueryElement.remove();
        
    }
    
    this.integrateCorrectUserAnswer = function(droppedJQueryElement, 
                                               draggedJQueryElement,
                                               placeOfUserAnswer) {
        
        if (droppedJQueryElement.hasClass('likersContainer')) {
            
            droppedJQueryElement
                .children().eq(placeOfUserAnswer + 1)
                .html(_this
                      ._getPersonNameFromDraggedElement(draggedJQueryElement))
                .removeClass('emptyDots');
            
            // Stop highlighting when this likers container is full
            var currentLikers = droppedJQueryElement.attr('currentLikers');
            droppedJQueryElement.attr('currentLikers', ++currentLikers);
            
            if (currentLikers == droppedJQueryElement.attr('totalLikers')) {
                
                droppedJQueryElement.addClass('noPossibleAnswer')
                                    .removeClass('personPossibleAnswer');
            }
                
                
        }
        else if (droppedJQueryElement
                 .hasClass('commentPossibleAnswer')) {
                   
                     droppedJQueryElement.find('.personComment')
                       .html(_this
                             ._getCommentFromDraggedElement(draggedJQueryElement))
                       .removeClass('emptyDots');
                       
                       droppedJQueryElement.addClass('noPossibleAnswer')
                                           .removeClass('commentPossibleAnswer');
                       
        }
        else if (droppedJQueryElement
                 .hasClass('personPossibleAnswer')) {
        
                    droppedJQueryElement.find('.personName')
                       .html(_this
                             ._getPersonNameFromDraggedElement(draggedJQueryElement))
                       .removeClass('emptyDots');
                    
                    var personPicture = draggedJQueryElement
                                         .find('.profilePicture')
                                         .attr('src')
                    
                    droppedJQueryElement.find('.profilePicture')
                       .attr('src', personPicture);
                    
                    droppedJQueryElement.addClass('noPossibleAnswer')
                                        .removeClass('personPossibleAnswer');
                    
        }
        
        draggedJQueryElement.remove();
        
    }
    
    this._getCommentFromDraggedElement = function(draggedJQueryElement) {
        
        return draggedJQueryElement.find('.personComment')
                                   .html().trim();
    
    }
    
    this._getPersonNameFromDraggedElement = function(draggedJQueryElement) {
        
        return draggedJQueryElement.find('.personName')
                                   .html().trim();
    
    }
    
    this.isGameboardComplete = function() {
        
        var gameboardComplete = true;
        
        $('.commentPossibleAnswer, .personPossibleAnswer').each(function(){
        
            if (! $(this).hasClass('noPossibleAnswer'))
                gameboardComplete = false;
        
        });
    
        return gameboardComplete;
        
    }
    
    this.displayAppLoadingPage = function() {
        
        var innerHtmlContent = '<div id="videoTutorial">';
        innerHtmlContent += '<iframe id="ytplayer" type="text/html" width="600" height="336" src="http://www.youtube.com/embed/' + _this.application.YoutubeIntroVideoCode + '?autoplay=1&wmode=opaque&autohide=1&loop=1&modestbranding=1&rel=0&theme=light" frameborder="0"/>';
        innerHtmlContent += '</div>';
        innerHtmlContent += '<div id="progressBarImagesForStartUpLayer">';
        innerHtmlContent += '<div class="progressBarCompleted"></div>';
        innerHtmlContent += '<div class="progressBarLoading"></div>';
        innerHtmlContent += '<div class="progressBarToLoad"></div></div>';
        innerHtmlContent += '<div id="displayFirstQuestionButton"></div>';
        
        // wait 1500 miliseconds before activate progress bar
        // (Hack to conter Facebook weird delayed iframe display)
        setTimeout(function() {
            
             $('#startupLayer')
                .html(innerHtmlContent)
                .css('opacity', '0')
                .ready(function() {
                    
                    setTimeout(function() {
                        
                        $('#startupLayer').animate({ opacity: 1 }, 
                             2500, 'swing', 
                             function() {
                        
                                 $('#progressBarLayer').css('background-color', 'transparent');
                                          
                             });
                        
                    }, 1500);
                                      
                })
             
        }, 1500);
        
    }
    
    this._activateNextQuestionButton = function() {
        
        // run animation
        $('#nextQuestionButton').hide();
        
        var topPosition = $(window).height() - parseInt($('#nextQuestionButtonAnimation').css('bottom')) - 23;
        var leftPosition = $(window).width() - parseInt($('#nextQuestionButtonAnimation').css('right')) - 30;

        $('#nextQuestionButtonAnimation').css('top', topPosition + 'px')
                                         .css('left', leftPosition + 'px')
                   
        $('#nextQuestionButtonAnimation').animate({

                  top: topPosition - 57,
                  left: leftPosition - 76,
                  opacity: 0.7

            }, 750, 'swing', function() {

               $('#nextQuestionButtonAnimation').animate({

                  top: topPosition,
                  left: leftPosition,
                  opacity: 1

                }, 750, 'swing', function() {

                    // End of the animation
                    return;

                });

        });
            
        $('#nextQuestionButtonAnimation img').animate({

                  width: 106,
                  height: 80

            }, 750, 'swing', function() {

                $('#nextQuestionButtonAnimation img').animate({

                  width: 30,
                  height: 23

                }, 750, 'swing', function() {

                        $('#nextQuestionButtonAnimation').hide();
                        
                        $('#nextQuestionButton').show();

                        $('#nextQuestionButton')
                            .removeClass('disabled')
                            .click(function() {

                                _this.application.pageLoader.loadTemplate('question');

                            })
                            .mouseover(function() {

                                $('#gameboard, .draggableItem')
                                         .fadeTo('slow', 0.4, function() {});

                            })
                            .mouseout(function() {

                                $('#gameboard, .draggableItem')
                                         .fadeTo('slow', 1, function() {});

                            });

                });

        });
            
    }
    
    this.ejectBadAnswer = function(draggedJQueryElement) {
        
        draggedJQueryElement.animate({

                  top: _this._randomHeight(draggedJQueryElement),
                  left: _this._randomWidthOutsideGameboard(draggedJQueryElement),
                  opacity: 1

            }, 750, 'swing', function() {

                // End of the animation
                return;

            });
    }
    
    this._randomHeight = function(jQueryElement) {
        
        return Math.floor(   Math.random() 
                           * (  $(window).height()
                              - jQueryElement.height()
                              - 20));

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
                                 - 20
                                 - marginForPersonNameApparition));

    }
    
    this.showScore = function(score, upOrDown, animation) {
        
        if (upOrDown == 'up' && animation == 'blink') {
            
            _this._blinkHtmlElement('#scoreCell_' + score, 'empty', 'activated');
        }
        else if (upOrDown == 'up' && animation == 'regular') {
            
            $('#scoreCell_' + score).addClass('activated').removeClass('empty');
            
        }
        else if (upOrDown == 'down' && animation == 'blink') {
            
            _this._blinkHtmlElement('#scoreCell_' + score, 'activated', 'empty');
            
        }
        
    }
    
    this._blinkHtmlElement = function(element, startClass, endClass) {
        
            var flashes = 7;

            var show = function(element, startClass, endClass) {

                if (flashes > 0) {

                       window.setTimeout(

                             function() {
                                 $(element).addClass(endClass)
                                           .removeClass(startClass);
                                 hide(element, startClass, endClass);
                             }, 300);

                       flashes--;
                }
            }

            var hide = function(element, startClass, endClass) {

                if (flashes > 0) {

                       window.setTimeout(

                             function() {
                                 $(element).addClass(startClass)
                                           .removeClass(endClass);
                                 show(element, startClass, endClass);
                             }, 300);

                       flashes--;
                }
            }

            show(element, startClass, endClass);
            
    }
    
    this.iterateActionsCount = function() {
        
        _this._numberOfActions++;
        
        if (_this._numberOfActions == 3)
            _this._activateNextQuestionButton();
            
    }
    
    this.initActionCount = function() {
        
        _this._numberOfActions = 0;
        
    }
    
    this.popUpFadeInAnimation = function() {
        
        $('#popUpLayer').fadeIn('1000');
        
    }
    
    this.integrateGuiFunctionForPopUpTemplate = function(templateName) {
        
            $(".okButton").click(function() {_this._closePopup(templateName);});
           
    }
    
    this._closePopup = function(templateName) {
        
            $('.okButton').css('background-image', 'none')
                          .css('background-color', '#1A356E');

            $('#popUpLayer').fadeOut('1000');
            
            if (templateName == 'level_popup') {

               var newHtmlContent = '<tr>';
               
               for (var i = 1; i <= parseInt(_this.application.userScore.level) * 5; i++) {
                   
                   newHtmlContent += '<td id="scoreCell_' + i + '" class="scoreCell empty"></td>';
                   
               }
               
               newHtmlContent += '</tr>';

		 $('#left').html('<span style="font-weight: bold;">Score:</span><br />(Level ' + _this.application.userScore.level + ')');
               
               $('#scoreTable').fadeOut('1000')
                               .html(newHtmlContent)
                               .fadeIn('1000');
                              
            }
            
    }
    
    this.displayNewPeopleChoice = function(pictureIndex, pictureUrl) {
        
        $('#peopleChoice_' + pictureIndex).find('img')
            .hide()
            .attr('src', pictureUrl)
            .fadeIn('slow');
            
    }
    
    this.displayPreviousPeopleChoices = function() {
        
        for (pictureIndex = 0; pictureIndex < 3; pictureIndex++) {
            
            $('#peopleChoice_' + pictureIndex).find('img')
                .attr('src', _this.application.peopleChoiceFriendsPictures[pictureIndex]);
                
        }
        
    }
    
    this.displayPeopleChoiceAnimation = function() {
        
        var peopleChoiceAnimation = new PeopleChoiceAnimation();
        peopleChoiceAnimation.start();
        
    }
    
    this.displayGameboardBackground = function() {
        
        $('body').css('background-image', 'url("./view/images/timeline.png")');
        
    }
    
    this.displayShareTeaserVideoPopup = function() {
        
        _this.application.pageLoader.loadTemplate('share_teaser');
        
    }
    
}