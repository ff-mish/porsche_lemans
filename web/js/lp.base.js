/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing'] , function( $ , api ){
    'use strict'
    
    function retweetMonitoring() {
      var self = $(this);
      var uuid = self.parents(".tweet-signle-item").attr("data-uuid");
      var msg = self.parents(".tweet-signle-item").find(".profile-msg").text();
      LP.panel({
        type: "panel",
        "content": "<textarea name='msg'>"+msg+"</textarea><input name='uuid' type='hidden' value='"+uuid+"'/>",
        "title": "",
        mask: true,
        style: "default",
        className: "retweet popup",
        destroy: true,
        submitButton: true,
        cancelButton: true,
        submitText: _e("Repost"),
        cancelText: _e("Cancel"),
        closeAble: false,
        onShow: function () {
          //TODO::
        },
        onSubmit: function () {
          var textarea = this.$panel.find('textarea');
          var uuid  = this.$panel.find("input[name='uuid']");
          api.post("/api/twitte/post", {"msg": textarea.val(), "uuid": uuid.val()}, function (e) {
            
          });
        },
        onCancel: function () {
          
        },
        width: $(window).width() * 0.6,
      });
    }
    
    function commentMonitoring() {
      var self = $(this);
      var uuid = self.parents(".tweet-signle-item").attr("data-uuid");
      var screen_name = self.parents(".tweet-signle-item").find(".title").text();
      LP.panel({
        type: "panel",
        "content": "<textarea name='msg'>"+screen_name+" " + window.topic +"</textarea>",
        "title": "",
        mask: true,
        style: "default",
        className: "retweet popup",
        destroy: true,
        submitButton: true,
        cancelButton: true,
        submitText: _e("Repost"),
        cancelText: _e("Cancel"),
        closeAble: false,
        onShow: function () {
          //TODO::
        },
        onSubmit: function () {
          var textarea = this.$panel.find('textarea');
          var uuid  = this.$panel.find("input[name='uuid']");
          api.post("/api/twitte/post", {"msg": textarea.val(), "uuid": uuid.val()}, function (e) {
            
          });
        },
        onCancel: function () {
          
        },
        width: $(window).width() * 0.6,
      });
    }

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = function( $dom , current , total ,  startAngle ){
        if( !$dom.length ) return;
        var percent = Math.min( current / total , 1 );
        startAngle = startAngle / 180 * Math.PI || 0;
        LP.use('raphaeljs' , function( Raphael ){
            // Creates canvas 320 × 200 at 10, 50
            var width = $dom.width();
            var height = $dom.height();
            var r = 35 , stockWidth = 10 , stockColor = "#ff0707";

            var start = [ width / 2 + Math.cos( startAngle )  * r , height / 2 + Math.sin( startAngle ) * r ];

            var paper = Raphael( $dom.get(0) , width , height );
            var redPath = paper.path( "" )
                    .attr("stroke" , stockColor )
                    .attr("stroke-width" ,stockWidth );
            var blackPath = paper.path( "" )
                    .attr("stroke", "#000")
                    .attr("stroke-width" , stockWidth);
            var text = paper.text( width / 2 , height / 2 , "0%" ) 
                .attr({stroke: "#fff"});

            var now = new Date();
            var duration = 1000;
            var ani = function(){
                var p = Math.min( 1 ,  ( new Date() - now ) / duration );
                var end = [ width / 2 + Math.cos( startAngle + percent * p * 2 * Math.PI ) * r , height / 2 + Math.sin( startAngle + percent * p * 2 * Math.PI ) * r  ]
                var path = [
                    'M' , start[0] , ' ' , start[1] ,
                    'A' , r , ' ' , r , ' 0 ' , percent * p > 0.5 ? '1' : '0' , ' 1 ' ,  end[0] , ' ' , end[1]
                    ].join("");
                var otherPath = [
                    'M' , start[0] , ' ' , start[1] ,
                    'A' , r , ' ' , r , ' 0 ' , percent * p > 0.5 ? '0' : '1' , ' 0 ' ,  end[0] , ' ' , end[1]
                ].join("");

                if( percent * p < 1 ){
                    redPath.attr( 'path' , path );
                    blackPath.attr( 'path' , otherPath );
                }
                if( percent * p == 1 ){
                    paper.circle( width / 2 , height / 2, r )
                        .attr("stroke" , stockColor )
                        .attr("stroke-width" ,stockWidth );
                    redPath.remove();
                    blackPath.remove();
                }

                // render numbers
                text.attr('text' , ~~( p * 100 * percent * 100 ) / 100 + '%')

                if( p != 1 ){
                    setTimeout(ani , 60/1000);
                }
            }
            ani();
        });
    }

    // left { max: xx , tip : '' , text: yyy }
    var dragCoordinate = function( $dom , left , right , top , bottom ){
        var width = $dom.width();
        var height = $dom.height();
        var ch = ~~( height / 2 );
        var cw = ~~( width / 2 );
        var sw = 40; // step width
        var sh = 7; // step height

        var xstart = [ 100 , ch ] , xend = [ width - 100 , ch ] , xwidth = xend[ 0 ] - xstart[ 0 ]
            , ystart = [ cw , 100 ] , yend = [ cw , height - 100 ] , yheight = yend[ 1 ] - ystart[ 1 ];


        var left = [ 120 , xstart[1] ] , right = [ 340 , xstart[1] ] , top = [ ystart[0] , 100 ] , bottom = [ ystart[0] , 300 ];

        var pathAttr = {
            'stroke' : '#999',
            'opacity' : 0.7,
            'stroke-width' : 0.4
        }
        var textAttr = {
            'stroke' : '#999',
            'opacity' : 0.7
        }
        LP.use('raphaeljs' , function( Raphael ){
            // draw x 
            var paper = Raphael( $dom.get(0) , width , height );
            var xpath = [
                'M' , xstart.join(" ") , 'L' , xend.join(" ") ,
                // 'M0 ' , ch , 'L' , width , ' ' , ch ,
                'M' , xstart[ 0 ] , ' ' , xstart[1] - ~~(sh/2) , 'l0 ' , sh ];
            for( var i = 1 ; i * sw < xwidth ; i++ ){
                xpath.push( 'm' + sw + ' 0l0 ' + ( i % 2 == 1 ? '-' : '' ) + sh );
            }
            paper.path( xpath.join("") )
                .attr(pathAttr);

            paper.text( xstart[0] - 30 , xstart[1] , 'Impact' )
                .attr( textAttr );
            paper.text( xend[0] + 30 , xend[1] , 'Quality' )
                .attr( textAttr );

            // drag y
            var ypath = [
                'M' , ystart.join(" ") , 'L' , yend.join(" ") ,
                //'M' , cw , ' 0L' , cw , ' ' , height ,
                'M' , ystart[ 0 ] - ~~(sh/2) , ' 0l' , sh , ' 0' ];
            for( var i = 1 ; i * sw < yheight ; i++ ){
                ypath.push( 'm0 ' + sw + 'l' + ( i % 2 == 1 ? '-' : '' ) + sh + ' 0' );
            }
            paper.path( ypath.join("") ).attr(pathAttr);

            paper.text( ystart[0] , ystart[1] - 10 , 'Speed t/h' )
                .attr( textAttr );
            paper.text( yend[0] , yend[1] + 10  , 'Assiduite' )
                .attr( textAttr );


            var rpath = [];
            $.each( [left,top , right , bottom] , function( i , dot){
                rpath.push( ( i == 0 ? 'M' : 'L' ) + dot[0] + ' ' + dot[1] );
            } );
            rpath.push('Z');
            console.log( rpath );
            // drag red path
            paper.path( rpath.join("") ).attr( {
                "stroke": '#f00',
                "stroke-width": 3
            } );
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
                api.get('/api/user/Friendssuggestion' , {q: key} , function( e ){
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


    var renderVideo  = (function(){
        var tpl = '<video id="#[id]" style="width: 100%;height: 100%;" class="video-js vjs-default-skin"\
                preload="auto"\
                  poster="#[poster]">\
                 <source src="#[videoFile].mp4" type="video/mp4" />\
                 <source src="#[videoFile].webm" type="video/webm" />\
                 <source src="#[videoFile].ogv" type="video/ogg" />\
            </video>';
        var vid = 0;
        return function( $wrap , videoFile , poster , cfg , cb ){
            var id = 'my_video_' + ( vid++ );
            var defaultConfig = { "controls": false, "autoplay": true, "preload": "auto","loop": true, "children": {"loadingSpinner": false}};
            $wrap.append( LP.format( tpl , {id: id , poster: poster , videoFile: videoFile } ) );
            LP.use('video-js' , function(){
                videojs.options.flash.swf = "./js/video-js.swf";
                cfg = LP.mix(defaultConfig , cfg);
                var ratio = cfg.ratio || ( 516 / 893 );
                var myVideo = videojs( id , cfg , function(){
                    var v = this;
                    $(window).resize(function(){
                        var w = $wrap.width();
                        var h = $wrap.height();
                        var vh = 0 ;
                        var vw = 0 ;
                        if( h / w > ratio ){
                            vh = h;
                            vw = h / ratio;
                        } else {
                            vh = w * ratio;
                            vw = w;
                        }
                        v.dimensions( vw , vh );

                        $('#' + v.Q).css({
                            "margin-top": ( h - vh ) / 2,
                            "margin-left": ( w - vw ) / 2
                        });
                    }).trigger('resize');
                    cb && cb.call( this );
                } );
            });
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
        var ratio = 516 / 893;

        renderVideo( $('<div></div>').css({
            "position": "fixed",
            "z-index": "-1",
            "top": "0",
            "left": "0",
            "height": "100%",
            "width": "100%",
            "overflow": "hidden"
        }).appendTo( document.body ) , "/videos/small" , "/images/bg7.jpg" ,  {} );
        // // init video
        // var ratio = 516 / 893;
        // LP.use('video-js' , function(){
        //     videojs.options.flash.swf = "./js/video-js.swf";
        //     var myVideo = videojs("bg_video_1", { "controls": false, "autoplay": true, "preload": "auto","loop": true, "children": {"loadingSpinner": false} } , function(){
        //       // Player (this) is initialized and ready.
        //       console.log( this);
        //     });

        //     $(window).resize(function(){
        //         var w = $(window).width();
        //         var h = $(window).height();
        //         var vh = 0 ;
        //         var vw = 0 ;
        //         if( h / w > ratio ){
        //             vh = h;
        //             vw = h / ratio;
        //         } else {
        //             vh = w * ratio;
        //             vw = w;
        //         }

        //         console.log( myVideo );
        //         myVideo.dimensions( vw , vh );

        //         $('#bg_video_1').css({
        //             "margin-top": ( h - vh ) / 2,
        //             "margin-left": ( w - vw ) / 2
        //         })
        //     }).trigger('resize');

        // });
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
            //content: "<p>email:<input name=\"email\" value=\"\" /></p><p><textarea cols='40' rows='5'></textarea></p>",
            content: '<div class="member-list"></div>',
            title: "Invite User",
            className: "add-team-panel",
            submitButton: true,
            onload: function() {
                //initSuggestion( this.$panel.find('textarea') );
                
            },
            onSubmit: function(){
                // var $input = this.$panel.find('input[name="email"]');
                // var email = $input.val();
                // if( !email.match( /^[a-zA-Z_0-9][a-zA-Z\-_.0-9]@([a-zA-Z\-_0-9]+\.)+[a-zA-Z]+$/) ){
                //     $input.css('border-color' , 'red');
                // } else {
                //     $input.css('border-color' , '');
                // }
                // var msg = this.$panel.find('textarea').val();
                // api.post( '/api/user/invite' , {msg: msg,email:email} , function(){
                //     LP.right('success');
                // });
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
                api.post( '/api/twitte/post' , {msg: msg, "from": "web"} , function(){
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
                api.post( '/api/twitte/post' , {msg: msg} , function(){
                    LP.right('success');
                } );
            }
        });
    });

    LP.action('fuel-load' , function( data ){
        $(this).attr('disabled' , 'disabled');
        var page = parseInt( $(this).data( 'page' ) || 0 ) + 1;
        var type = $(this).data( 'type' ) || '';

        $(this).data( 'page' , page );

        api.get('/api/media/list' , { page:page , type: type } , function( e ){
            // DEBUG.. render fuel item
            $.each( e.data || [] , function( i , data ){
              if (data["type"] == "video") {
                data["video"] = 1;
              }
                LP.compile('fuel-tpl' , data , function( html ){
                    $('.fuellist').append( html );
                    if (i >= e.data.length - 1) {
                      callback();
                    }
                });
                
            } );
            
            function callback(){
              $(this).removeAttr('disabled');

              LP.use('isotope' , function(){
                 // first init isotope , render no animate effect
                 $('.fuellist')
                     .isotope({
                         resizable: false
                     });
              });
            }
        });
    });


    LP.action('legal-mentions' , function( data ){
        $('#legal-mentions').fadeIn();
    });

    LP.action('skip-intro' , function(data){
        $(this).parent().fadeOut();
    });

    LP.action('leaveteam' , function( e ){
        var self = $(this);
        api.get("/api/user/leaveteam", function ( e ) {
           //TODO:: 动画效果
           self.fadeOut("fast");
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


        // init nav list effect
        $('.nav p').css({opacity:0,marginLeft:-20}).each(function( i ){
            $(this).delay( i * 200 )
                .animate({
                    opacity: 1,
                    marginLeft: 0
                } , 500);
        });

        $('.navicon').click(function(){
            LP.triggerAction('post_weibo');
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
                api.get('/api/question/random' , '' , function( e ){
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
                            } , 1000 * 30);
                        },
                        onClose: function(){
                            clearInterval( timer );
                        },
                        onSubmit: function(){
                            var t = this;
                            api.post("/api/question/answer" , { answer: t.$panel.find('input[name="answer"]:checked').val() , qaid: data.qaid} , function(){
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
                var ratio = 516 / 893;
                // show the big video
                renderVideo( $('#home_video') , "/videos/small" , "/images/bg7.jpg" ,  {ratio: ratio} , function(){
                    $('#' + this.Q).css('z-index' , -1);
                } );
                // get parameter d
                var urlObj = LP.parseUrl();
                if( urlObj.params.d ){
                    api.post( "/api/web/decryptionURL" , {d: urlObj.params.d} );
                }
                
                break;
            case "teambuild":
                bigVideoInit();
                api.get("/api/user" , function( e ){
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
                    api.post( "/api/user/BuildTeam" ,  $(this).serialize() , function( e ){
                        $(".teambuild_from").hide();
                        $('#teambuild_info').fadeIn();
                    });
                    return false;
                });
                break;

            case "countdown":
                // TODO...
                var start = window.start_time || '2014-09-13 02:43:07';
                var now = window.time_now || '2014-05-15 00:05:24';
               
                var dura = ~~( ( start - now ) / 1000 );
                var d = ~~( dura/86400 );
                var h = ~~( ( dura - d * 86400 ) / 3600 );
                var m = ~~( ( dura - d * 86400 - h * 3600 ) / 60 );
                var s = dura - d * 86400 - h * 3600 - m * 60;
                countDownMgr.init( $(".conut_downitem" ) , [ 99 , 23 , 59 , 59 ] , [ d , h , m , s ] );
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
                
              case "stand":
                $(".stand .teambuild_member").live({
                    'mouseenter':function(){
                        $(this).addClass("hover");
                    },
                    'mouseleave':function(){
                        $(this).removeClass("hover");
                    }
                });

                // init member speed
                rotateAnimate( $('.member_speed').eq(0) , 200 , 360 , 45 );
                rotateAnimate( $('.member_speed').eq(1) , 240 , 360 , 45 );
                rotateAnimate( $('.member_speed').eq(2) , 300 , 360 , 45 );

                dragCoordinate( $('.stand_chart') );
                break;
                
            case "monitoring":
              var tweetGroup = $(".tweet-con .tweet-list");
              api.get("/api/twitte", function (e) {
                function callbackRender(index, group) {
                  for (var i = 0; i < group.length; i++) {
                    var tweet = {
                      media: group[i]["media"],
                      date: group[i]["date"],
                      name: group[i]["user"]["name"],
                      from: group[i]["from"],
                      content: group[i]["content"],
                      uuid: group[i]["uuid"]
                    };
                    LP.compile("tweet-item-tpl", tweet, function(html) {
                      $($(".item", tweetGroup).get(index)).children(".tweet-items").append(html);
                    });
                  }
                  if (group.length <= 0) {
                    $($(".item", tweetGroup).get(index)).children(".tweet-items").append("<li class='tweet-signle-item clearfix'>empty</li>");
                  }
                }
                
                var group1 = e["data"]["web"];
                callbackRender(0, group1);
                
                var group2 = e["data"]["user"];
                callbackRender(1, group2);
                
                var group3 = e["data"]["team"];
                callbackRender(2, group3);
                
                var group4 = e["data"]["topic"];
                callbackRender(3, group4);
                
                $(".btns .retweet").live("click", retweetMonitoring);
                $(".btns .comment").live("click", commentMonitoring);
              });
              
              
              break;
        }
    });
});


