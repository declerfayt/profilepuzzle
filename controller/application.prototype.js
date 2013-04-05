function Application() {
    
    var _this = this;
    
    this.userScore = new UserScore(_this);
    this.pageLoader = new PageLoader(_this);
    this.gui = new GUI(_this);
    this.dataLoader = new DataLoader(_this);
    this.soundPlayer = new SoundPlayer(this);
    this._APICode;
    this._appUrl;
    this.peopleAlreadyChosen = false;
    this.peopleChoiceFriendsPictures = ['./view/images/void.png', './view/images/void.png', './view/images/void.png'];
    this.YoutubeIntroVideoCode = '-MNv-nE6ggY';
    this.cookiesEnabled;
    
    this.start = function() {
        
        _this._areCookiesEnabled();
        _this.gui.displayAppLoadingPage();
        
        var progressBarAnimation = new ProgressBarAnimation();
        progressBarAnimation.start(30000);
        
        _this.soundPlayer.setup();
        
        _this.userScore.getFromModel(_this._APICode);
        
    }
    
    this.setAPICode = function(APICode) {
        
        _this._APICode = APICode;
    
    }
    
    this.setAppUrl = function(appUrl) {
        
        _this._appUrl = appUrl;
        
    }
    
    this.getAppUrl = function() {
        
        return _this._appUrl;
        
    }
    
    this.getCode = function() {
        
        return _this._code;
        
    }
    
    this.setUserProposalAndAnswer = function(userAnswer, correctAnswer,
                                            droppedJQueryElement,
                                            draggedJQueryElement) {
        
        
        // People choice selector
        if (userAnswer.indexOf('_') == -1 && _this.peopleAlreadyChosen == false) {
            
            // We're sure a friend proposal has been selected, not a comment proposal
            var chosenFriendPictureUrl = draggedJQueryElement.find('img').attr('src');
            var indexOfFriendPicture = (_this.pageLoader.numberOfQuestionsDisplayed - 1) % 3;
            _this.peopleChoiceFriendsPictures[(_this.pageLoader.numberOfQuestionsDisplayed - 1) % 3] = chosenFriendPictureUrl;
            _this.peopleAlreadyChosen = true;
            _this.dataLoader.retrievePostsFromFriend(parseInt(userAnswer));
            _this.gui.displayNewPeopleChoice(indexOfFriendPicture, chosenFriendPictureUrl);
            
        }
        
        
        
        if (droppedJQueryElement.hasClass('likersContainer')) {
        
            // We split the correct answer for the likers list case (
            // the correct answer is a comma-separated set of FB user IDs,
            // ie. '14564623,32808023,79798732,323280832')
            var correctAnswers = correctAnswer.split(',');
            var containsCorrectAnswer = false;
            var placeOfUserAnswer;
            
            for (i=0; i<correctAnswers.length; i++) {
                
                if (userAnswer == correctAnswers[i]) {
                    
                    containsCorrectAnswer = true;
                    placeOfUserAnswer = i;
                }
            }
            
            if (containsCorrectAnswer) 
                _this._userWins(droppedJQueryElement, 
                                draggedJQueryElement,
                                placeOfUserAnswer);
            
            else _this._userLoses(droppedJQueryElement, draggedJQueryElement);
            
            
        }
        else if (userAnswer != correctAnswer) 
            _this._userLoses(droppedJQueryElement, draggedJQueryElement);
        
        else
            _this._userWins(droppedJQueryElement, 
                            draggedJQueryElement, null);
        
    }
    
    this._userLoses = function(droppedJQueryElement, draggedJQueryElement) {
        
        _this.gui.iterateActionsCount();
        
        _this.gui.ejectBadAnswer(draggedJQueryElement);
        
        for (var i=0; i<3; i++) {
         
            if (_this.userScore.score - 1 >= 0) {
            
                _this.gui.showScore(_this.userScore.score, 'down', 'blink');
                --_this.userScore.score;
                
            }
            
        }
        
    }
    
    this._userWins = function(droppedJQueryElement, 
                              draggedJQueryElement,
                              placeOfUserAnswer) {
        
        _this.gui.iterateActionsCount();
        
        _this.gui.integrateCorrectUserAnswer(droppedJQueryElement, 
                                              draggedJQueryElement,
                                              placeOfUserAnswer);
        
        _this.userScore.score++;
        
        if (_this.userScore.score == _this.userScore.level * 5) {
            
            _this.gui.showScore(_this.userScore.score, 'up', 'regular');
            _this.userScore.score = 0;
            _this.userScore.level++;
            _this.pageLoader.loadTemplate('level_popup');
            
        }
        else {
            
            _this.gui.showScore(_this.userScore.score, 'up', 'blink');
            
        }
            
            
            
        if (_this.gui.isGameboardComplete()) {
            
            _this.gui.showScore(_this.userScore.score, 'up', 'regular');
            _this.pageLoader.loadTemplate('question');
            
        }
            
        
        
    }
    
    this._areCookiesEnabled = function() {
        
         if ($.cookie('testCookie') === undefined) {
             
             _this.cookiesEnabled = false;
             
         }
         else {
             
             $.removeCookie('testCookie');
             _this.cookiesEnabled = true;
             
         }
         
    }
    
}