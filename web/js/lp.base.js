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


    // count down function 
    var countDownMgr = (function(  ){
        var colClass = "countdown-col";
        var groupClass = "countdown-group";
        // 8
        // 9
        // 0
        var initCol = function ( $dom , max , origin ){
            var htmls = [];
            var height = $dom.height();

            //save data to dom
            $dom.data( 'max' , max )
                .data( 'num' , origin )
                .data( 'height' , height )
                .addClass( groupClass );


            // build html
            $.each( (max + '').split("") ,  function( i , num ){
                var max = i == 0 ? num : 9;
                var inner = [];
                inner.unshift( '<div>0</div>') ;
                inner.unshift( '<div> ' + max + ' </div>') ;

                var tmp = max;
                while( tmp-- ){
                    inner.unshift( '<div> ' + tmp + ' </div>') ;
                }

                htmls.push( '<div class="' + colClass + '" data-max="' + max + '" >' + inner.join("") + '</div>' );
            });

            $dom.html( htmls.join("") );

            // set origin
            var originArr = (origin + "").split("");
            var $cols = $dom.children().css({
                float: "left"
            });

            if( originArr.length < $cols.length ){
                for( var i = 0 ;  i < $cols.length - originArr.length ; i ++ ){
                    originArr.unshift( 0 );
                }
            }

            for( var i = originArr.length - 1 ; i >= 0 ; i -- ){
                var $col = $cols.eq( $cols.length - ( originArr.length - i ) );
                var ch = (originArr[ i ] == 0 ? - ( ~~$col.data('max') + 1 ) : - originArr[ i ]) * height;
                $col.css( "margin-top" , ch )
                    .data('num' , originArr[ i ]);
            }
        }
        var reduce = function ( $dom ){
            var height = $dom.data( 'height');
            var num = $dom.data('num');
            var next = num - 1 < 0 ? $dom.data('max') : num - 1;
            var nextArr = (next + "").split("");
            var $cols = $dom.children();
            if( nextArr.length < $cols.length ){
                for( var i = 0 ;  i < $cols.length - nextArr.length ; i ++ ){
                    nextArr.unshift( 0 );
                }
            }

            for( var i = nextArr.length - 1 ; i >= 0 ; i -- ){
                !!(function( i ){
                    var ch = - nextArr[ i ] * height;
                    var $col = $cols.eq( $cols.length - ( nextArr.length - i ) );
                    if ( $col.data( 'num' ) != nextArr[ i ] ){

                        // change to it's last num
                        $col.css('margin-top' , ch - height ).animate({
                                "margin-top" : ch
                            } , 500 , '' , function(){
                                if( ch == 0 ){
                                    $(this).css('margin-top' , - (~~$(this).data('max') + 1) * height);
                                }
                                $(this).data('num' , nextArr[ i ]);
                                $dom.data('num' , next);
                            });
                    }
                })( i );
            }

            // reduce prev one
            if( num - 1 < 0 ){
                reduce( $dom.prevAll( "." + groupClass ).first() );
            }
        }

        return {
            init: function( $doms , maxs , origins ){
                //$doms = $doms.eq(0);
                $doms.each( function( i ){
                    initCol( $(this) , maxs[i] , origins[i] );
                } );

                // start animate
                setInterval(function(){
                    reduce( $doms.last() );
                } , 1000);
            }
        }
    })();


    var bigVideoInit = function(){
        // init video
        var ratio = 516 / 893;
        LP.use('video-js' , function(){
            videojs.options.flash.swf = "./js/video-js.swf";
            var myVideo = videojs("bg_video_1", { "controls": false, "autoplay": true, "preload": "auto","loop": true, "children": {"loadingSpinner": false} } , function(){
              // Player (this) is initialized and ready.
            });

            $(window).resize(function(){
                var w = $(window).width();
                var h = $(window).height();
                var vh = 0 ;
                var vw = 0 ;
                if( h / w > ratio ){
                    vh = h;
                    vw = h / ratio;
                } else {
                    vh = w * ratio;
                    vw = w;
                }
                myVideo.dimensions( vw , vh );

                $('#bg_video_1').css({
                    "margin-top": ( h - vh ) / 2,
                    "margin-left": ( w - vw ) / 2
                })
            }).trigger('resize');

        });
    }


    // page actions here
    // ======================================================================
    LP.action("login" ,function(){
        api.ajax( "login" , $(this).closest('form').serialize() , function( ){

        } );
        return false;
    });
    

    LP.action("member_invent" , function(){
        LP.panel({
            content: "<p>email:<input name=\"email\" value=\"\" /></p><p><textarea cols='40' rows='5'></textarea></p>",
            title: "Invite User",
            className: "add-team-panel",
            submitButton: true,
            onload: function(){
                initSuggestion( this.$panel.find('textarea') );
            },
            onSubmit: function(){
                var $input = this.$panel.find('input[name="email"]');
                var email = $input.val();
                if( !email.match( /^[a-zA-Z_0-9][a-zA-Z\-_.0-9]@([a-zA-Z\-_0-9]+\.)+[a-zA-Z]+$/) ){
                    $input.css('border-color' , 'red');
                } else {
                    $input.css('border-color' , '');
                }
                var msg = this.$panel.find('textarea').val();
                api.post( './api/user/invite' , {msg: msg,email:email} , function(){
                    LP.right('success');
                });
            }
        });
    });


    LP.action("post_weibo" , function(){
        LP.panel({
            content: "<textarea cols='40' rows='5'></textarea>",
            title: "",
            submitButton: true,
            className: "post-weibo-panel",
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




    LP.action('preview' , function( ev ){
        // show big pic or big video

        var $img = $(this)
            .closest('.fuelitem')
            .children('img');
        var imgH = $img.height();
        var imgW = $img.width();;
        LP.panel({
            content: "<img src=\"" + $img.attr('src') + "\"/>",
            title: "share the content",
            submitButton: true,
            onShow: function(){
                this.$panel.find('.lpn_panel')
                    .css({
                        'margin-top': '-50%',
                        'opacity' : 0
                    })
                    .animate({
                        marginTop: 0,
                        opacity: 1
                    } , 500 , 'easeOutQuart');

                var panel = this;
                $(window).on('resize.fixfuel' , function(){
                    var maxH = $(window).height() - 20;
                    var maxW = $(window).width() - 20;
                    var tarW = imgW , tarH = imgH;
                    if( maxH / maxW > imgH / imgW && maxW < imgW ){
                        tarH = imgH / imgW * maxW;
                        tarW = maxW;
                    } else if( maxH / maxW < imgH / imgW && maxH < imgH ){
                        tarW = imgW / imgH * maxH;
                        tarH = maxH;
                    }

                    // resize the panel
                    panel.resize( tarW , tarH );
                });
            },
            onBeforeClose: function(){
                var $panel = this.$panel;
                this.$panel.find('.lpn_panel')
                    .animate({
                        marginTop: '50%',
                        opacity: 0
                    } , 500 , 'easeInQuart' , function(){
                        $panel.fadeOut( function(){
                            $panel.remove();
                        } )
                            
                    });
                // unbind event
                $(window).off('resize.fixfuel');
                return false;
            },
            onSubmit: function(){
            }
        });
    });

    LP.action('repost' , function( ev ){
        LP.panel({
            content: "<textarea cols='40' rows='5'></textarea>",
            title: "share the content",
            submitButton: true,
            className: "post-weibo-panel1",
            onload: function(){
            },
            onSubmit: function(){
                var msg = this.$panel.find('textarea').val();
                api.post( './api/twitte/post' , {msg: msg} , function(){
                    LP.right('success');
                } );
            }
        });
    });

    LP.action('fuel-load' , function( ev ){
        $(this).attr('disabled' , 'disabled');
        var page = parseInt( $(this).data( 'page' ) || 0 ) + 1;
        var type = $(this).data( 'type' ) || 'pic';

        $(this).data( 'page' , page );

        api.get('./api/media/list' , { page:page , type: type } , function( e ){
            // DEBUG.. render fuel item
            e = {"data" :[{},{},{},{}]};
            $.each( e.data || [] , function( i , data ){
                LP.compile('fuel-tpl' , data , function( html ){
                    $('.fuellist').append( html );
                });
            } );

            $(this).removeAttr('disabled');

            LP.use('isotope' , function(){
                // first init isotope , render no animate effect
                $('.fuellist')
                    .isotope({
                        resizable: false
                    });
            });
        });
    });




    // page init here
    // =======================================================================
    $(function(){
        // init all pages fadein element 
        $('[data-fadein]').hide().each( function( i ){
            $(this).delay( i * 300 ).fadeIn( 800 );
        } );

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


        // init memeber speed effect
        if( $('.member_speed').length ){
            rotateAnimate( $('.member_speed') , 100 , 360 );
        }

        // init nav list effect
        $('.nav p').css({opacity:0,marginLeft:-20}).each(function( i ){
            $(this).delay( i * 200 )
                .animate({
                    opacity: 1,
                    marginLeft: 0
                } , 500);
        });


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


        // init first page template
        switch( $(document.body).data('page') ){
            case "index":
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
                
                break;
            case "teambuild":
                bigVideoInit();
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

            case "countdown":

                // get server time
                api.get('./api/web/time' , function( e ){
                    // DEBUG::
                    e = {"status":0,"message":"success","data":{"time_now":"2014-05-15 00:05:24","time_start":"2014-09-13 02:43:07"}};
                    var now = new Date( e.data.time_now );
                    var start = new Date( e.data.time_start );
                    var dura = ~~( ( start - now ) / 1000 );
                    var d = ~~( dura/86400 );
                    var h = ~~( ( dura - d * 86400 ) / 3600 );
                    var m = ~~( ( dura - d * 86400 - h * 3600 ) / 60 );
                    var s = dura - d * 86400 - h * 3600 - m * 60;
                    countDownMgr.init( $(".conut_downitem" ) , [ 99 , 23 , 59 , 59 ] , [ d , h , m , s ] );
                });
                bigVideoInit();
                
                break;

            case "fuel":
                //fuel
                $('.fuelitem').live({
                    'mouseenter':function(){
                        $(this).children('.fuelshade').stop().fadeIn()
                        $(this).children('.fuelbtnbox').stop().fadeIn()
                    },
                    'mouseleave':function(){
                        $(this).children('.fuelshade').stop().fadeOut()
                        $(this).children('.fuelbtnbox').stop().fadeOut()
                    }
                });

                // page loaded
                LP.triggerAction('fuel-load');
                break;
        }
    });
});


