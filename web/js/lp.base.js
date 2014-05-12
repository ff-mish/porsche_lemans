/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = function( $dom , current , total , easing , startAngle ){
        var percent = current / total;
        startAngle = startAngle / 180 * Math.PI || Math.PI*3/2;
        LP.use('raphaeljs' , function( Raphael ){
            // Creates canvas 320 × 200 at 10, 50
            var width = $dom.width();
            var height = $dom.height();
            var r = 35 , stockWidth = 10 , stockColor = "#ff0707";

            var start = [ width / 2 + Math.cos( startAngle )  * r , height / 2 + Math.sin( startAngle ) * r ];
            var end = [ width / 2 + Math.cos( startAngle + percent * 2 * Math.PI ) * r , height / 2 + Math.sin( startAngle + percent * 2 * Math.PI ) * r  ]
            console.log( start , end );
            var path = [
                'M' , start[0] , ' ' , start[1] ,
                'A' , r , ' ' , r , ' 0 ' , percent > 0.5 ? '1' : '0' , ' 1 ' ,  end[0] , ' ' , end[1]
                ].join("");
            var otherPath = [
                'M' , start[0] , ' ' , start[1] ,
                'A' , r , ' ' , r , ' 0 ' , percent > 0.5 ? '0' : '1' , ' 0 ' ,  end[0] , ' ' , end[1]
            ].join("");

            var paper = Raphael( $dom.get(2) , width , height , function(){
                // Creates circle at x = 50, y = 40, with radius 10
                var circle = this.circle( width / 2 , height / 2, 35 );
                // Sets the fill attribute of the circle to red (#f00)
                // circle.attr("stroke", "#000")
                //     .attr("stroke-width" , 10);
                this.path( otherPath )
                    .attr("stroke", "#000")
                    .attr("stroke-width" , 10);
                this.path( path )
                    .attr("stroke" , stockColor )
                    .attr("stroke-width" ,stockWidth );
            });
        });
    }

    var initSuggestion = (function(){
        var tUtil = null;
        var BaseSelectPanel = null;
        var atSugConfig = {
            zIndex: 9999,
            width: 200,
            wrapClass: 'suggestWrap',
            // maxHeight: 200,
            availableCssPath: 'p', // 用于hover的css path
            loadingContent: '<h4>想用@提到谁？</h4><div class="suggest-list" node-type="suggestion-list">',
            renderData: function(data){
                var key = this.key , aHtml = ['<h4>想用@提到谁？</h4><div class="suggest-list" node-type="suggestion-list">'];
                $.each(data , function(i,item){
                    aHtml.push(['<p data-insert="' , item.nickname , '">', item.nickname.replace(key , '<b>' + key + '</b>') + '<br/></p>'].join(''));
                });
                aHtml.push('</div>');
                return aHtml.join('');
            },
            // how to get data
            getData: function(cb){
                var key = this.key ;
                api.get('./api/user/Friendssuggestion' , {q: key} , function( e ){
                    cb( e.data );
                });
            }
        }
        var inputSuggestion = function( $textarea , cfg ){
            var regx = cfg.regx,
                tag = cfg.tag,
                lastIndex = 0,
                currIndex = 0,
                lastText = '',
                suggestion = null,
                _timeout = null,
                showSuggestion = function( ev ){
                    if( suggestion && suggestion.$wrap.is(':visible')){
                        switch(ev.keyCode){
                            case 40: // down
                            case 38: // up
                            case 13: //enter
                                return;
                        }
                    }
                    
                    var textarea = this,
                        value = textarea.value,
                        range = tUtil.getPos(textarea),
                        text = value.substring(0 , range.start);
                    
                    currIndex = range.start;
                    lastIndex = text.lastIndexOf(tag);
                    lastText = text.substring(lastIndex);
                    if(!regx.test(lastText)){
                        suggestion && suggestion.hide();
                        return;
                    }
                    if(!suggestion){
                        suggestion = new BaseSelectPanel(textarea , cfg.selectConfig);
                        suggestion.addListener("select" , function($dom){
                            var name = $dom.attr('data-insert');
                            if(!name){
                                tUtil.setText(textarea , '\n' , currIndex);
                            }else{
                                cfg.afterSelect && cfg.afterSelect(textarea , name , lastIndex , lastText.length);
                                //tUtil.setText(textarea , name , lastIndex , lastText.length);
                            }
                            $textarea.trigger('countwords');
                        });
                        suggestion.addListener("beforeShow" , function(t , data){
                            if(cfg.beforeShow){
                                return !!cfg.beforeShow( t , data );
                            }
                            return true;
                            //return !!$(data).find('li').length;
                        });
                    }
                    
                    // show suggestion
                    var pos = tUtil.getPagePos(textarea ,lastIndex);
                    var toff = $(textarea).offset()
                    suggestion.show( pos.left - toff.left , pos.bottom + 3 - toff.top + 30 , lastText.substring(1));
                },
                eventFn = function(ev){
                   if(ev.keyCode == 27){
                       return false;;
                   }
                   // 延迟处理
                   clearTimeout(_timeout);
                   var textarea = this;
                   _timeout = setTimeout(function(){
                       showSuggestion.call(textarea , ev);
                   },100);
                };
            // key up event
            $textarea.keyup(eventFn);
            // mouse down event
            $textarea.mouseup (eventFn);
            return suggestion;
        }


        return function( $textarea ){
            LP.use(['textareaUtil','suggestion'] , function( textUtil , sug ){
                tUtil = textUtil ;
                BaseSelectPanel = sug;
                inputSuggestion( $textarea , {
                    regx : /^@([^\s,)(\]\[\{\}\\\|=\+\/\-~`!#\$%\^&\*\.:;"'\?><]){1,15}$/,
                    tag  : '@',
                    selectConfig : atSugConfig,
                    afterSelect : function(textarea , value , lastIndex , len){
                        tUtil.setText(textarea , value+" " , lastIndex + 1 , len - 1);
                    }
                });
            } )
        }
    })();


    // page actions here
    // ======================================================================
    LP.action("login" ,function(){
        api.ajax( "login" , $(this).closest('form').serialize() , function( ){

        } );
        return false;
    });
    

    LP.action("member_invent" , function(){
        LP.panel({
            content: "<textarea cols='40' rows='5'></textarea>",
            title: "邀请用户",
            submitButton: true,
            onload: function(){
                initSuggestion( this.$panel.find('textarea') );
            },
            onSubmit: function(){
                var msg = this.$panel.find('textarea').val();
                api.post( './api/user/invite' , {msg: msg} , function(){
                    LP.right('success');
                });
            }
        });
    });


    LP.action("post_weibo" , function(){
        LP.panel({
            content: "<textarea cols='40' rows='5'></textarea>",
            title: "发weibo",
            submitButton: true,
            onload: function(){
                initSuggestion( this.$panel.find('textarea') );
            },
            onSubmit: function(){
                var msg = this.$panel.find('textarea').val();
                api.post( './api/twitte/post' , {msg: msg} , function(){
                    LP.right('success');
                } );
            }
        });
    });




    // page init here
    // =======================================================================
    $(function(){
        //fuel
        $('.fuelitem').live({
            'mouseenter':function(){
                $(this).children('.fuelshade').show()
                $(this).children('.fuelbtnbox').show()
            },
            'mouseleave':function(){
                $(this).children('.fuelshade').hide()
                $(this).children('.fuelbtnbox').hide()
            }
        });
        // login
        $('.login_tips').live('click',function(){
            $(this).hide()
            $(this).prev('input').focus()
        });
        $('.loginipt input').live('blur',function(){
            if($(this).val() == ''){
                $(this).next('.login_tips').show()
            }                
        });


        if( $('.member_speed').length ){
            rotateAnimate( $('.member_speed') , 100 , 360 );
        }


        // init first page template
        switch( CONFIG.page ){
            case "home":
                api.get("./api/weibo/loginurl" , function( e ){
                    LP.compile( "init-tpl" , {weibo_url: e.data.url , twitter_url: e.data.twitter_url} , function( html ){
                        $(".home_share").append( html );
                    } )
                });

                // get parameter d
                var urlObj = LP.parseUrl();
                if( urlObj.params.d ){
                    api.post( "./api/web/decryptionURL" , {d: urlObj.params.d} );
                }


                // fix Q & A
                !!(function(){
                    var now  = new Date();

                    var cookieTimes = [];
                    var qaCookie = LP.getCookie( "__QA__") ;
                    if( qaCookie ){
                        cookieTimes = qaCookie.split(",");
                    }

                    // deal current hour
                    var atimes = 0;
                    for( var _i = cookieTimes.length - 1 ; _i >= 0 ; _i-- ){
                        if( now - cookieTimes[ _i ] < 60 * 60 * 1000
                            && new Date( parseInt(cookieTimes[ _i ]) ).getHours() == now.getHours() ){
                            atimes++;
                        }
                        break;
                    }

                    
                    var minutes = 60 - now.getMinutes();
                    var maxtimes = 3;

                    var times = minutes > 40 ? 3 : minutes > 24 ? 2 : minutes > 10 ? 1 : 0;
                    times = Math.min( times , maxtimes - atimes );
                    var sep = 10; // minutes
                    var eachRuntime = ( minutes - times * sep ) / times ;
                    var lastTime = 0;
                    var getNextTime = function( ){
                        if( qtimes < times ){
                            return sep / 2 + qtimes * ( sep + eachRuntime ) + Math.random() * eachRuntime ;
                        } else {
                            return minutes + 5 + 10 * Math.random() + ( qtimes - times ) * 25 ;
                        }
                    }
                    var showQa = function(){
                        cookieTimes.push( + new Date() );
                        LP.setCookie( "__QA__" , cookieTimes.join(",") , 86400 * 30 );

                        qtimes++;
                        var timer = null;
                        api.get('./api/question/random' , '' , function( e ){
                            var data = e.data;
                            var content = "<dl><dt>";
                            content += data.question + "</dt>";
                            $.each( [1,2,3,4] , function( i ){
                                content += '<dd><label><input type="radio" name="answer" value="' + ( i + 1 ) + '" />' + data['answer' + ( i + 1 ) ] + '</label></dd>';
                            } );
                            content += "</dl>";


                            LP.panel({
                                title: 'this is QA<span></span>',
                                content: content,
                                width: 300,
                                height: 100,
                                submitButton: true,
                                onload: function(){
                                    var times = 10;
                                    var t = this;
                                    timer = setInterval(function(){
                                        t.$panel.find('.hd span').html( "( closed after " + times-- + " seconds )" );
                                        if( times <= 0 ){
                                            t.close();
                                        }
                                    } , 1000);
                                },
                                onClose: function(){
                                    clearInterval( timer );
                                },
                                onSubmit: function(){
                                    var t = this;
                                    api.post("./api/question/answer" , { answer: t.$panel.find('input[name="answer"]:checked').val() , qaid: data.qaid} , function(){
                                        t.close();
                                    });
                                }
                            });
                        });
                        setTimeout( showQa , ( getNextTime() - lastTime ) * 60 * 1000 );
                    }
                    var qtimes = 0;
                    setTimeout( showQa , ( getNextTime() - lastTime ) * 60 * 1000 );
                })();
                break;
            case "teambuild":
                api.get("./api/user" , function( e ){
                    if( !e.data.user ) return;
                    // if current user is invited
                    if( e.data.team ){ // show team info
                        $.each( e.data.team.members || [] , function( i , member ){
                            LP.compile( 'teambuild-member-tpl' , member , function( html ){
                                $(html).insertBefore( $('#teambuild_info .member_add') );
                            } );
                        } );

                        $('#teambuild_info').fadeIn()
                            .find( ".member_add" )
                            [ parseInt(e.data.user.invited_by) ? 'hide' : 'show' ]();
                    } else {
                        $(".teambuild_from").fadeIn();
                    }
                } );

                // bind event 
                $(".teambuild_from").submit(function(){
                    api.post( "./api/user/BuildTeam" ,  $(this).serialize() , function( e ){
                        $(".teambuild_from").hide();
                        $('#teambuild_info').fadeIn();
                    });
                    return false;
                });
                break;
        }
    });
});


