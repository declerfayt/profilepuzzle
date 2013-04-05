function PageLoader(application) {
    
    var _this = this;
    
    this.application = application;
    this.progressBar = new ProgressBar(application);
    
    this._templateAndLayerMapping = { 'question' : '#questionLayer', 
                                      'level_popup' : '#popUpLayer',
                                      'about_this' : '#popUpLayer',
                                      'low_activity' : '#popUpLayer',
                                      'cookies_disabled' : '#popUpLayer',
                                      'share_teaser' : '#popUpLayer'};
    
    this._templateName;
    this._templateContent;
    this._templateData;
    
    this._cachedItems = {question: {status: 'empty', value: ''}};
    
    this.numberOfQuestionsDisplayed = 0;
    
    this.loadTemplate = function(template) {
    
             var timeToWait
    
             if (    _this.application.pageLoader.numberOfQuestionsDisplayed % 3 == 0
                  &&  template != 'level_popup' && template != 'cookies_disabled' ) {
                 
                 _this.application.gui.displayPeopleChoiceAnimation();
                 timeToWait = 2000;
                 
             }
             else {
                 
                 timeToWait = 0;
                 
             }
             
             setTimeout(function() {
                  
                    // Show progress bar
                    _this.progressBar.show();
                    _this._templateName = template;

                    var templateFile = 'view/html_templates/' 
                                        + template + '.template.html';
                  
                    $.get(templateFile, function(data) {

                        _this._templateContent = data;
                        _this.getDataFromModel();

                    });
                        
             }, timeToWait);
            
    }
    
    this.getDataFromModel = function() {
        
        _this.application.dataLoader.getFromModel(_this._templateName);
        
    }
    
    this.includeDataToTemplate = function(data) {
        
        _this._templateData = data;
        
        // No item to show. Low activity on the user profile
        if (parseInt(data.dynamicTemplateData) == 0 && _this._templateName == 'question') {
            
            _this.loadTemplate('low_activity');
            
        }
        
        $.tmpl(_this._templateContent, _this._templateData)
            .appendTo($('#prerenderLayer'));
         
        $('#prerenderLayer')
            .waitForImages(function() {
            
                $(_this._templateAndLayerMapping[_this._templateName])
                    .html('')
                    .html($('#prerenderLayer').html());
                
                _this.progressBar.hide();
            
                $('#prerenderLayer').html('');
                
        });
    
    }
    
    this.givePageLife = function() {
        
        if (_this._templateName == 'question') {
        
            _this.numberOfQuestionsDisplayed++;
            _this.application.peopleAlreadyChosen = false;
            _this.application.gui.displayGameboardBackground();
        
            if (_this.numberOfQuestionsDisplayed % 3 == 1 && _this.numberOfQuestionsDisplayed != 1) {
                
                _this.application.peopleChoiceFriendsPictures = new Array();
                _this.application.peopleChoiceFriendsPictures = ['./view/images/void.png', './view/images/void.png', './view/images/void.png'];
                
            }
            
            _this.application.gui.displayPreviousPeopleChoices();
        
            _this.application.gui.initActionCount();
            
            
            if (    _this.numberOfQuestionsDisplayed == 1 
                 && _this.application.userScore.score == 0
                 && _this.application.userScore.level == 1 ) {

                _this.application.gui.displayTutorialAnimation();
                
            }
            else {
                
                // Once the template is loaded, we can start the animation
                _this.application.gui.shuffleDraggableItems();
            
                 // Load game mechanics
                _this.application.gui.startDragAndDropMechanics(_this);
            }
            
            // And prepare next question/level popup
            _this.application.dataLoader.bufferNextData(_this._templateName);
            
         
       }
       else if (_this._templateName == 'level_popup') {
           
           _this.application.gui.popUpFadeInAnimation();
           _this.application.gui.integrateGuiFunctionForPopUpTemplate(_this._templateName);
           _this.application.dataLoader.bufferNextData(_this._templateName);
           _this.application.soundPlayer.playSound('levelUp');
           
       }
       else {
           
           _this.application.gui.popUpFadeInAnimation();
           _this.application.gui.integrateGuiFunctionForPopUpTemplate(_this._templateName);
           
       }
       
    }
        
}

// Question template needs this JS function
function templateImageManagement(image) {
                                
    if (image.attr('src').trim() == '' || image.height() < 2) {
            image.parents('tr').hide();
    }

    if (image.width() > 223) {

        // Manager Timeline Photos dimensions
        var newHeight = Math.round(220 * (image.height() / image.width() ));
        image.width(220);
        image.height(newHeight);

    }

}