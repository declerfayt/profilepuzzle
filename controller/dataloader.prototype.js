function DataLoader(application) {
    
    var _this = this;
    this.application = application;
    
    this._cachedItems = {question: {status: 'empty', value: ''},
                         level_popup: {status: 'empty', value: ''}};
                     
    this._waitUntilCacheIsFinished = ['question', 'level_popup'];
    
    this.retrievePostsFromCurrentUser = function() {
        
         $.ajax({  type: "GET",
		   url: "model/data_access.php",
		   templateName: "json",
                   data:   "posts_retriever=current_user",
                   success: function(){}
                   
               });
         
         // Cookies must be enabled to run app
         if (_this.application.cookiesEnabled === false) {
            
               _this.application.pageLoader.loadTemplate('cookies_disabled');
                
         }
         else {
             
             _this.application.pageLoader.loadTemplate('question');
             
         }
             
    }
    
    this.retrievePostsFromFriend = function(friendID) {
        
         $.ajax({  type: "GET",
		   url: "model/data_access.php",
		   templateName: "json",
                   data:   "posts_retriever=" + friendID,
                   success: function(){}
                   
               });
         
    }
    
    this.getFromModel = function(templateName) {
        
            if (templateName == 'question' || templateName == 'level_popup') {
        
                // Looks if a question/level popup is in cache
                if (_this._cachedItems[templateName]['status'] == 'full' && _this._cachedItems[templateName]['value'] != '') {

                    var userData = {'score' :  _this.application.userScore.score,
                                    'level' :  _this.application.userScore.level};

                     var generalData = $.extend({}, {'user' : userData},
                                                    {'dynamicTemplateData' : _this._cachedItems[templateName]['value']});

                    _this.application.pageLoader.includeDataToTemplate(generalData);

                }
                else if (_this._cachedItems[templateName]['status'] == 'progress') {

                    _this._waitUntilCacheIsFinished[templateName] = setInterval(function() {

                        if (_this._cachedItems[templateName]['status'] == 'full' && _this._cachedItems[templateName]['value'] != '') {

                            clearInterval(_this._waitUntilCacheIsFinished[templateName]);

                            var userData = {'score' :  _this.application.userScore.score,
                                            'level' :  _this.application.userScore.level};

                            var generalData = $.extend({}, {'user' : userData},
                                                           {'dynamicTemplateData' : _this._cachedItems[templateName]['value']});

                            _this.application.pageLoader.includeDataToTemplate(generalData);

                        }

                    }, 1000);

                }
                else {

                    // Get template info from model layer

                    var askedAttribute = '';

                    if (templateName == 'level_popup') {

                        askedAttribute = '=' + parseInt(_this.application.userScore.level);

                    }

                    $.ajax({    type: "GET",
                                url: "model/data_access.php",
                                templateName: "json",
                                data:   templateName + askedAttribute, 
                                success: function(templateData){

                                               if (   !_this._areDataCorrect(templateData) 
                                                   && templateName == 'question'
                                                   // No item to show. Low activity on the user profile
                                                   && (parseInt(templateData) != 0)) {

                                                   // If server sent results that doesn't correspond
                                                   // to JSON standart (i.e. HTML error message), we
                                                   // ask for a new question (pay attention to infinite loop)
                                                   _this._cachedItems[templateName]['status'] = 'empty';
                                                   _this._cachedItems[templateName]['value'] = ''
                                                   _this.application.pageLoader.getDataFromModel();
                                                   
                                               }
                                               
                                               var userData = {'score' :  _this.application.userScore.score,
                                                               'level' :  _this.application.userScore.level};

                                               var generalData = $.extend({},
                                                                    {'user' : userData},
                                                                    {'dynamicTemplateData' : templateData});

                                               _this.application.pageLoader.includeDataToTemplate(generalData);

                                        },
                                error: function() {

                                    _this._cachedItems[templateName]['status'] = 'empty';
                                    _this._cachedItems[templateName]['value'] = ''
                                    _this.application.pageLoader.getDataFromModel();

                                }

                           });

                }
       
          }
          else {
              
              // Pages with no data imported from model (about this/error/monetisation popup)
              var data = {};
              
              setTimeout(function(){
                  
                  _this.application.pageLoader.includeDataToTemplate(data);
              
              }, 1000) 
              
          }
        
    }
    
    this.bufferNextData = function(templateName) {
        
            // Once a question/level popup is loaded, we process the cache for
            // the next one without waiting for reply.
            
            var askedAttribute = '';
                
            if (templateName == 'level_popup') {

                askedAttribute = '=' + (parseInt(_this.application.userScore.level) + 1);

            }
            
            $.ajax({    type: "GET",
                        url: "model/data_access.php",
                        dataType: "json",
                        data:   templateName + askedAttribute, 
                        success: function(templateData){

                                        _this._cachedItems[templateName]['status'] = 'full';
                                        _this._cachedItems[templateName]['value'] = templateData;
                                        
                                },
                        error: function() {

                            _this._cachedItems[templateName]['status'] = 'empty';
                            _this._cachedItems[templateName]['value'] = '';

                        }

             });
            
             // Setting question cache as in progress just after ajax call
             _this._cachedItems[templateName]['status'] = 'progress';
             _this._cachedItems[templateName]['value'] = '';
        
        
    }
    
    
    this._areDataCorrect = function(data) {
        
        try { 
            
            var firstPostAuthor = data[0]['author'];
            var secondPostAuthor = data[1]['author'];
        
        }
        catch (e) { 
            
            return false; 
        
        }
        
        return true;

    }

}