/*
 * page base action
 */
LP.use(['jquery', 'api', 'easing', 'queryloader'] , function( $ , api ){
    'use strict'

    if( $.browser.msie && $.browser.version <= 8 ){
        $(document.body).addClass('ie8');
    }
    var COLOR = window.from == 'weibo' || !window.from ? '#ff0000' : '#065be0';

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
            if( this.disabled ) return;
            this.disabled = true;

          var textarea = this.$panel.find('textarea');
          var uuid  = this.$panel.find("input[name='uuid']");
          api.post("/api/twitte/post", {"msg": textarea.val(), "uuid": uuid.val()}, function (e) {
                this.disabled = false;
          });
        },
        onCancel: function () {
          
        },
        width: $(window).width() * 0.6
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
            if( this.disabled ) return;
            this.disabled = true;
          var textarea = this.$panel.find('textarea');
          var uuid  = this.$panel.find("input[name='uuid']");
          api.post("/api/twitte/post", {"msg": textarea.val(), "uuid": uuid.val()}, function (e) {
            this.disabled = false;
          });
        },
        onCancel: function () {
          
        },
        width: $(window).width() * 0.6
      });
    }

    // widgets and common functions here
    // ======================================================================
    var rotateAnimate = function( $dom , current , total ,  startAngle ){
        current = current || 0;
        var percent = Math.min( current / total , 1 ) ;
        startAngle = startAngle / 180 * Math.PI || 0;
        LP.use('raphaeljs' , function( Raphael ){
            // Creates canvas 320 × 200 at 10, 50
            var width = $dom.width();
            var height = $dom.height();
            var r = 35 , stockWidth = 10 , stockColor = COLOR;

            var start = [ width / 2 + Math.cos( startAngle )  * r , height / 2 + Math.sin( startAngle ) * r ];

            var paper = Raphael( $dom.get(0) , width , height );

            var circleBg = paper.circle( width / 2 , height / 2, r )
                    .attr("stroke" , '#000' )
                    .attr("stroke-width" , 0 )
                    .animate({'stroke-width': stockWidth} , 700);

            var redPath = paper.path( "" )
                    .attr("stroke" , stockColor )
                    .attr("stroke-width" ,stockWidth );
            // var blackPath = paper.path( "" )
            //         .attr("stroke", "#000")
            //         .attr("stroke-width" , stockWidth);
            var text = paper.text( width / 2 , height / 2 , "0 T/H" )
                .attr({fill: "#fff",'font-size':'13px'});


            var now ;
            var duration = 700;
            var ani = function(){
                var p = Math.min( 1 ,  ( new Date() - now ) / duration );
                var end = [ width / 2 + Math.cos( startAngle + percent * p * 2 * Math.PI ) * r , height / 2 + Math.sin( startAngle + percent * p * 2 * Math.PI ) * r  ]
                var path = [
                    'M' , start[0] , ' ' , start[1] ,
                    'A' , r , ' ' , r , ' 0 ' , percent * p > 0.5 ? '1' : '0' , ' 1 ' ,  end[0] , ' ' , end[1]
                    ].join("");
                // var otherPath = [
                //     'M' , start[0] , ' ' , start[1] ,
                //     'A' , r , ' ' , r , ' 0 ' , percent * p > 0.5 ? '0' : '1' , ' 0 ' ,  end[0] , ' ' , end[1]
                // ].join("");

                if( percent * p < 1 ){
                    redPath.attr( 'path' , path );
                    //blackPath.attr( 'path' , otherPath );
                }
                if( percent * p == 1 ){
                    paper.circle( width / 2 , height / 2, r )
                        .attr("stroke" , stockColor )
                        .attr("stroke-width" ,stockWidth );
                    redPath.remove();
                    blackPath.remove();
                }

                // render numbers
                text.attr('text' , ~~( p * 100 * percent * 100 ) / 100 + ' T/H' );

                if( p != 1 ){
                    setTimeout(ani , 60/1000);
                }
            }
            if( percent ){
                setTimeout( function(){
                    now = new Date();
                    ani();
                } , 700 );
            }
            //if( !percent ){
                // redPath.remove();
                // blackPath.remove();
                // paper.circle( width / 2 , height / 2, r )
                //     .attr("stroke" , '#000' )
                //     .attr("stroke-width" ,stockWidth );
            // } else {
            //     ani();
            // }
        });
    }

    var questionTimerInitTimer = null;
    var questionTimerInit = function( $dom  , duration , cb ){
        var width = $dom.width();
        var r = width / 2;
        duration = duration || 30000;
        LP.use('raphaeljs' , function( Raphael ){
            var paper = Raphael( $dom.get(0) , width , width );
            var path = paper.path("").attr('fill' , '#f00');
            // draw a circle
            var circle = paper.circle( ~~r , ~~r , ~~r ).attr({
                'fill': COLOR,
                'stroke-width': 0
            });


            var rotateBlack = paper.rect(0 , 0 , width / 2 , width).attr({
                'fill': '#000',
                'stroke-width': 0
            });
            var topCircle = paper.path(['M' , ~~r , ' 0l0 ' , width , 'A' , ~~r  , ' ', ~~r , ' 0 0 1 ' , ~~r , ' 0'].join('') )
                .attr({
                    'fill': COLOR,
                    'stroke-width': 0,
                    zIndex: 2
                });
            var topBlack = paper.rect(width / 2 , 0 , width / 2 , width).attr({
                'fill': '#000',
                zIndex: 1
            }).hide();

            var now = new Date;
            var lastper = 0;
            var drawCircle = function(  ){
                var d = new Date - now;
                var per = d / duration;
                if( per >= 1 ){
                    //path.remove();
                    cb && cb();
                    return;
                }
                if( per > 0.5 ){
                    topBlack.show();
                    topCircle.hide();
                }
                rotateBlack.rotate( ( per - lastper ) * 360 , r , r  );
                // var p = [
                //     'M' , ~~(width/2) , ' 0l0 ' , ~~(width/2) ,
                //     'l' , ~~(Math.sin(per * Math.PI * 2) * r) , ' ' , -~~(Math.cos(per * Math.PI * 2) * r),
                //     'A' , ~~r , ' ' , ~~r , ' 0 ' , per > 0.5 ? 0 : 1 , ' 1 ' ,  ~~(width/2) , ' ' , 0 
                // ].join("");

                // console.log( p );
                // path.attr('path' , p);
                
                questionTimerInitTimer = setTimeout( drawCircle , 1000 / 60 );
                lastper = per;
            }
            questionTimerInitTimer = setTimeout( drawCircle , 1000 / 60 );
        });
    }
    // left { max: xx , tip : '' , text: yyy }
    var coordinate = (function(){
        var object = {};
        function init( $dom , cb ){
            
            //var left = [ 120 , xstart[1] ] , right = [ 340 , xstart[1] ] , top = [ ystart[0] , 100 ] , bottom = [ ystart[0] , 300 ];

            var pathAttr = {
                'stroke' : '#d1d1d1',
                'opacity' : 0.7,
                'stroke-width' : 2
            }
            var textAttr = {
                'fill' : '#fff',
                'font-size' : '14px',
                'opacity' : 1
            }

            LP.use('raphaeljs' , function( Raphael ){

                // draw x 
                var paper = Raphael( $dom.get(0) , $dom.width() , $dom.height() );

                var drawCoordinate = function( ){
                    var width = $dom.width();
                    var height = $dom.height();
                    var ch = ~~( height / 2 );
                    var cw = ~~( width / 2 );
                    
                    //var left = 0.5 , right = 0.7 , top = 0.3 , bottom = 0.9;

                    var xstart = [ 70 , ch ] , xend = [ width - 70 , ch ] , xwidth = xend[ 0 ] - xstart[ 0 ]
                        , ystart = [ cw , 70 ] , yend = [ cw , height - 70 ] , yheight = yend[ 1 ] - ystart[ 1 ];


                    var sw = ( xend[0] - xstart[0] ) / 10 ; // step width
                    var sh = 7; // step height

                    var center = [ xwidth / 2 + xstart[0] , yheight / 2 + ystart[1] ];

                    paper.path( ['M' , xstart.join(" ") , 'L' , xend.join(" ")].join("") ).attr(pathAttr);
                    var xpath = [
                        //'M' , xstart.join(" ") , 'L' , xend.join(" ") ,
                        // 'M0 ' , ch , 'L' , width , ' ' , ch ,
                        'M' , xstart[ 0 ] , ' ' , xstart[1] - ~~(sh/2) , 'l0 ' , sh ];
                    for( var i = 1 ; i * sw <= xwidth ; i++ ){
                        xpath.push( 'm' + sw + ' 0l0 ' + ( i % 2 == 1 ? '-' : '' ) + sh );
                    }
                    paper.path( xpath.join("") )
                        .attr(pathAttr)
                        .attr({'stroke-width': 1});

                    paper.text( xstart[0] - 40 , xstart[1] , _e('Impact') )
                        .attr( textAttr );
                    paper.text( xend[0] + 40 , xend[1] , _e('Quality') )
                        .attr( textAttr );

                    // draw y
                    paper.path( ['M' , ystart.join(" ") , 'L' , yend.join(" ")].join("") ).attr(pathAttr);
                    var ypath = [
                        //'M' , ystart.join(" ") , 'L' , yend.join(" ") ,
                        //'M' , cw , ' 0L' , cw , ' ' , height ,
                        'M' , ystart[ 0 ] - ~~(sh/2) , ' ' , ystart[1] , 'l' , sh , ' 0' ];
                    for( var i = 1 ; i * sw <= yheight ; i++ ){
                        ypath.push( 'm0 ' + sw + 'l' + ( i % 2 == 1 ? '-' : '' ) + sh + ' 0' );
                    }
                    paper.path( ypath.join("") ).attr(pathAttr).attr({'stroke-width': 1});;

                    paper.text( ystart[0] , ystart[1] - 20 , _e('Speed') )
                        .attr( textAttr );
                    paper.text( yend[0] , yend[1] + 20  , _e('Assiduity') )
                        .attr( textAttr );


                    object.center = center;
                    object.xwidth = xwidth;
                    object.xstart = xstart;
                    object.ystart = ystart;
                    object.yheight = yheight;



                    object.path = paper.path( "" ).attr( {
                        "stroke": COLOR,
                        "stroke-width": 3
                    } );
                }

                $(window).resize(function(){
                    paper.clear();
                    paper.setSize( $dom.width() , $dom.height() );
                    drawCoordinate();
                    object.path.attr('path' , '');
                    runAnimate.call( '' , target[0],target[1],target[2],target[3] , true );
                });
                drawCoordinate();
                cb && cb();
            });
        }

        function easeOutElastic( x, t, b, c, d ) {
            var s=1.70158;var p=0;var a=c;
            if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
            if (a < Math.abs(c)) { a=c; var s=p/4; }
            else var s = p/(2*Math.PI) * Math.asin (c/a);
            return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
        }

        var target = [];

        function runAnimate( left , right , top , bottom , noAnimate ){
            // left = 0.5;
            // right = 0.7;
            // top = 0.9;
            // bottom = 0.3;
            target = [left , right , top , bottom];
            

            var center = object.center;
            var xwidth = object.xwidth;
            var xstart = object.xstart;
            var ystart = object.ystart;
            var yheight = object.yheight;

            var lastLeft = object.lastLeft || 0;
            var lastTop = object.lastTop || 0;
            var lastRight = object.lastRight || 0;
            var lastBottom = object.lastBottom || 0;

            var duration = 1000;
            var now = new Date;
            var renderPath = function( left , right , top , bottom ){
                left = [ center[0] - xwidth / 2 * left , xstart[1] ];
                right = [ center[0] + xwidth / 2 * right , xstart[1] ];
                top = [ ystart[0] , center[1] - yheight / 2 * top ];
                bottom = [ ystart[0] , center[1] + yheight / 2 * bottom ];

                var rpath = [];
                $.each([ left, top , right , bottom] , function( i , dot){
                    rpath.push( ( i == 0 ? 'M' : 'L' ) + dot[0] + ' ' + dot[1] );
                });
                rpath.push('Z');

                object.path.attr( 'path' , rpath.join("") );
            }

            if( noAnimate ){
                renderPath( left , right , top , bottom  );
                object.lastLeft = left;
                object.lastRight = right;
                object.lastTop = top;
                object.lastBottom = bottom;
                return;
            }
            var ani = function(){
                var dur = new Date - now;
                var per = dur / duration;
                if( per > 1 ){
                    per = 1;
                }
                var l = easeOutElastic('' , dur , lastLeft , left - lastLeft , duration ) ;// per * ( left - lastLeft );
                var r = easeOutElastic('' , dur , lastRight , right - lastRight , duration ) ;// per * ( right - lastRight );
                var t = easeOutElastic('' , dur , lastTop , top - lastTop , duration ) ;//per * ( top - lastTop );
                var b = easeOutElastic('' , dur , lastBottom , bottom - lastBottom , duration ) ;//per * ( bottom - lastBottom );
                renderPath( l || 0 , r || 0 , t || 0 , b || 0 );
                // renderPath( l + lastLeft , r + lastRight , t + lastTop , b + lastBottom );
                if( per < 1 ) {
                    setTimeout( ani , 1000 / 60 );
                } else {
                    object.lastLeft = left;
                    object.lastRight = right;
                    object.lastTop = top;
                    object.lastBottom = bottom;
                }
            }
            ani();
        }
        return {
            init: init , 
            run: runAnimate ,
            isEmpty: function(){
                return !target[0] && !target[1] && !target[2] && !target[3];
            }
        }
    })();

    var animateTure =  (function(){
        $('.tutr-step').find('.step-btn')
            .click(function(){
                animateTure.showStep( $(this).data('step') );
                return false;
            });

        var renderTure = function( top , left , width , height , isAni ){
            var $step = $('.tutr-step').fadeIn();
            
            var winHeight = $(document).height();
            var winWidth = $(document).width();

            $step.find('.tutr-step-top')
                .animate({
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: top
                } , 500);
            $step.find('.tutr-step-left')
                .animate({
                    top: top,
                    left: 0,
                    width: left,
                    height: height
                } , 500);
            $step.find('.tutr-step-right')
                .animate({
                    top: top,
                    left: left + width,
                    width: winWidth - left - width,
                    height: height
                } , 500);
            $step.find('.tutr-step-bottom')
                .animate({
                    top: top + height,
                    left: 0,
                    width: '100%',
                    height: winHeight - top - height
                } , 500);
        }

        var isCoordinateEmpty = false;
        var isNoAchivmentsbox = false;
        return {
            showStep: function( num ){
                switch( num ){
                    case 1:
                        var off = $('.stand_tit').offset();
                        renderTure( off.top - 10 , off.left - 20 , 380 , 60 );
                        $('.tutr-step').find('.tutr-step-tip1')
                            .delay( 700 )
                            .css({left: off.left + 380 , top: off.top - 10 })
                            .fadeIn();
                        break;
                    case 2:
                        var off = $('.stand_tit').offset();
                        $('.tutr-step-tip1').fadeOut();
                        renderTure( off.top , off.left - 20 , $('.stand_tit').width() + 40 , $('.stand_tit').height() + $('.teambuild_members').height() );
                        $('.tutr-step').find('.tutr-step-tip2')
                            .delay( 700 )
                            .css({left: off.left + $('.stand_tit').width() + 60 , top: off.top })
                            .fadeIn();
                        break;
                    case 3:
                        var off = $('.stand_chart').offset();
                        $('.tutr-step-tip2').fadeOut();
                        renderTure( off.top , off.left - 20 , $('.stand_chart').width() + 20 , $('.stand_chart').height() );
                        $('.tutr-step').find('.tutr-step-tip3')
                            .delay( 700 )
                            .css({left: off.left  - 600 , top: off.top })
                            .fadeIn();

                        // if there is no data , render the demo data;
                        isCoordinateEmpty = coordinate.isEmpty();
                        if( isCoordinateEmpty ){
                            coordinate.run( Math.random(),Math.random(),Math.random(),Math.random() );
                        }
                        break;
                    case 4:

                        if( isCoordinateEmpty ){
                            coordinate.run( 0,0,0,0 );
                        }
                        // add demo 
                        isNoAchivmentsbox = !$('.stand_achivments .stand_achivmentsbox').children().length;
                        if( isNoAchivmentsbox ){
                            $('.stand_achivments .stand_achivmentsbox').html('<p></p><p></p><p></p>');
                        }
                        var off = $('.stand_achivments').offset();
                        $('.tutr-step-tip3').fadeOut();
                        renderTure( off.top , off.left - 20 , $('.stand_achivments').width() , $('.stand_achivments').height() + $('.stand_tweet').height() + 80 );
                        $('.tutr-step').find('.tutr-step-tip4')
                            .delay( 700 )
                            .css({left: off.left - 20 , top: off.top - $('.tutr-step').find('.tutr-step-tip4').height() - 80 , width: $('.stand_achivments').width() - 80 })
                            .fadeIn();
                        break;
                    case 5:
                        if( isNoAchivmentsbox ){
                            $('.stand_achivments .stand_achivmentsbox').html('');
                        }
                        $('.tutr-step-tip4').fadeOut();
                        LP.panel({
                            title: '',
                            content: '<div class="popup_box popup_dialog popup_email" >\
                                    <span class="con-step">5/5</span>\
                                    <h4>' + _e('Receive a reminder for the d-day') + '</h4>\
                                    <input class="popup_dialog_msg" placeholder="' + _e('Enter your email address') + '" />\
                                    <p class="error-tip"></p>\
                                    <div class="popup_dialog_btns">\
                                        <a href="javascript:void(0);">' + _e('Finish tutorial') + '</a>\
                                    </div>\
                                    <div class="popup_dialog_status">\
                                        <span>' + _e('Success!') + '</span>\
                                    </div>\
                                </div>',
                            onShow: function(){
                                var panel = this;
                                var $input = this.$panel.find('input').focus(function(){
                                    $tip.html('');
                                });

                                // init place holder
                                var $input = panel.$panel.find('input');
                                if( $input.get(0).placeholder === undefined ){
                                    $input.val( $input.attr('placeholder') )
                                        .focus(function(){
                                            if( this.value == $input.attr('placeholder') ){
                                                this.value = '';
                                            }
                                        })
                                        .blur(function(){
                                            if( !this.value ){
                                                this.value = $input.attr('placeholder');
                                            }
                                        });
                                }
                                // init tutor place holder

                                var $tip = this.$panel.find('.error-tip');
                                panel.$panel.find('.popup_dialog_btns a').click(function(){

                                    var email = $input.val();
                                    if( email &&
                                        (!email.match(/^[a-zA-Z_0-9].*[a-zA-Z]$/) ||
                                        !email.match(/[a-zA-Z_0-9]@[a-zA-Z_0-9]/) ||
                                        !email.match(/^[a-zA-Z_0-9.]+@[a-zA-Z_.0-9]+$/)) ){
                                        $tip.html( _e('wrong email') );
                                    }

                                    if($(this).hasClass('disabled')) {
                                        return;
                                    }
                                    if( email ){
                                        var $btn = $(this).addClass('disabled');
                                        $(this).next().fadeIn();
                                        api.post( '/api/user/logmail' , {email: email} , function(){
                                            var height = panel.$panel.find('.popup_dialog').height();
                                            panel.$panel.find('.popup_dialog').height(height);
                                            panel.$panel.find('.popup_dialog_btns').fadeOut();
                                            panel.$panel.find('.popup_dialog_status').delay(500).fadeIn(function(){
                                                setTimeout(function(){
                                                    panel.close();
                                                }, 500);
                                            });
                                        } , function(){
                                            $btn.removeClass('disabled');
                                        } );
                                    } else {
                                        panel.close();
                                    }

                                    api.get('/api/user/readtoturial');
                                });
                            }
                        });
                        
                    default:
                        $('.tutr-step').fadeOut();

                }
            }
        }
    })();


    // var initSuggestion = (function(){
    //     var tUtil = null;
    //     var BaseSelectPanel = null;
    //     var atSugConfig = {
    //         zIndex: 9999,
    //         width: 200,
    //         wrapClass: 'suggestWrap',
    //         // maxHeight: 200,
    //         availableCssPath: 'p', // 用于hover的css path
    //         //loadingContent: '<h4>想用@提到谁？</h4><div class="suggest-list" node-type="suggestion-list">',
    //         renderData: function(data){
    //             var key = this.key , aHtml = ['<h4>想用@提到谁？</h4><div class="suggest-list" node-type="suggestion-list">'];
    //             $.each(data , function(i,item){
    //                 aHtml.push(['<p data-insert="' , item.nickname , '">', item.nickname.replace(key , '<b>' + key + '</b>') + '<br/></p>'].join(''));
    //             });
    //             aHtml.push('</div>');
    //             return aHtml.join('');
    //         },
    //         // how to get data
    //         getData: function(cb){
    //             var key = this.key ;
    //             api.get('/api/user/Friendssuggestion' , {q: key} , function( e ){
    //                 cb( e.data );
    //             });
    //         }
    //     }
    //     var inputSuggestion = function( $textarea , cfg ){
    //         var regx = cfg.regx,
    //             tag = cfg.tag,
    //             lastIndex = 0,
    //             currIndex = 0,
    //             lastText = '',
    //             suggestion = null,
    //             _timeout = null,
    //             showSuggestion = function( ev ){
    //                 if( suggestion && suggestion.$wrap.is(':visible')){
    //                     switch(ev.keyCode){
    //                         case 40: // down
    //                         case 38: // up
    //                         case 13: //enter
    //                             return;
    //                     }
    //                 }
                    
    //                 var textarea = this,
    //                     value = textarea.value,
    //                     range = tUtil.getPos(textarea),
    //                     text = value.substring(0 , range.start);
                    
    //                 currIndex = range.start;
    //                 lastIndex = text.lastIndexOf(tag);
    //                 lastText = text.substring(lastIndex);
    //                 if(!regx.test(lastText)){
    //                     suggestion && suggestion.hide();
    //                     return;
    //                 }
    //                 if(!suggestion){
    //                     suggestion = new BaseSelectPanel(textarea , cfg.selectConfig);
    //                     suggestion.addListener("select" , function($dom){
    //                         var name = $dom.attr('data-insert');
    //                         if(!name){
    //                             tUtil.setText(textarea , '\n' , currIndex);
    //                         }else{
    //                             cfg.afterSelect && cfg.afterSelect(textarea , name , lastIndex , lastText.length);
    //                             //tUtil.setText(textarea , name , lastIndex , lastText.length);
    //                         }
    //                         $textarea.trigger('countwords');
    //                     });
    //                     suggestion.addListener("beforeShow" , function(t , data){
    //                         if(cfg.beforeShow){
    //                             return !!cfg.beforeShow( t , data );
    //                         }
    //                         return true;
    //                         //return !!$(data).find('li').length;
    //                     });
    //                 }
                    
    //                 // show suggestion
    //                 var pos = tUtil.getPagePos(textarea ,lastIndex);
    //                 var toff = $(textarea).offset()
    //                 suggestion.show( pos.left - toff.left , pos.bottom + 3 - toff.top + 30 , lastText.substring(1));
    //             },
    //             eventFn = function(ev){
    //                if(ev.keyCode == 27){
    //                    return false;;
    //                }
    //                // 延迟处理
    //                clearTimeout(_timeout);
    //                var textarea = this;
    //                _timeout = setTimeout(function(){
    //                    showSuggestion.call(textarea , ev);
    //                },100);
    //             };
    //         // key up event
    //         $textarea.keyup(eventFn);
    //         // mouse down event
    //         $textarea.mouseup (eventFn);
    //         return suggestion;
    //     }


    //     return function( $textarea ){
    //         LP.use(['textareaUtil','suggestion'] , function( textUtil , sug ){
    //             tUtil = textUtil ;
    //             BaseSelectPanel = sug;
    //             inputSuggestion( $textarea , {
    //                 regx : /^@([^\s,)(\]\[\{\}\\\|=\+\/\-~`!#\$%\^&\*\.:;"'\?><]){1,15}$/,
    //                 tag  : '@',
    //                 selectConfig : atSugConfig,
    //                 afterSelect : function(textarea , value , lastIndex , len){
    //                     tUtil.setText(textarea , value+" " , lastIndex + 1 , len - 1);
    //                 }
    //             });
    //         } )
    //     }
    // })();


    var globalVideos = [];
    var globalVideoInterval = [];

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
            var resize = cfg.resize === undefined ? true : cfg.resize;

            var defaultConfig = { "controls": false, "autoplay": true, "preload": "auto","loop": true, "children": {"loadingSpinner": false}};
            $wrap.append( LP.format( tpl , {id: id , poster: poster , videoFile: videoFile } ) );
            LP.use('video-js' , function(){
                videojs.options.flash.swf = "/js/video-js/video-js.swf";
                cfg = LP.mix(defaultConfig , cfg);
                var ratio = cfg.ratio || ( 516 / 893 );
                var myVideo = videojs( id , cfg , function(){
                    var v = this;
                    if( resize ){
                        $(window).resize(function(){
                            if( v.isRemoved  ) return;
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

                        if( $.browser.msie && $.browser.version <= 8 ){
                            setTimeout(function(){
                                $(window).trigger('resize');
                            } , 100);
                        }
                    }
                    cb && cb.call( this );
                } );
                var index = globalVideos.length;
                globalVideos[index] = 0;
//                globalVideoInterval[index] = setInterval( function(){
//                    globalVideos[index] = myVideo.bufferedPercent();
//                } , 100 );


                myVideo.muted( true );
                $wrap.data('video' , myVideo);
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
                $col.children().first().css( "margin-top" , ch )
                    .parent().data('num' , originArr[ i ]);
            }
        }
        var reduce = function ( $dom ){
            var height = $dom.height();
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
                        $col.children().first().css('margin-top' , ch - height ).animate({
                                "margin-top" : ch
                            } , 500 , '' , function(){
                                if( ch == 0 ){
                                    $(this).css('margin-top' , - (~~$(this).parent().data('max') + 1) * height);
                                }
                                $(this).parent().data('num' , nextArr[ i ]);
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

        var interval = null;
        return {
            init: function( $doms , maxs , origins ){
                clearInterval( interval );
                $(window).unbind('resize.countdown').bind('resize.countdown' , function(){
                    $doms.each( function( i ){
                        initCol( $(this) , maxs[i] , $(this).data('num') );
                    } );
                });

                //$doms = $doms.eq(0);
                $doms.each( function( i ){
                    initCol( $(this) , maxs[i] , origins[i] );
                } );

                // start animate
                interval = setInterval(function(){
                    reduce( $doms.last() );
                } , 1000);
            },

            initCountDown: function(){
                 api.get('/api/web/time' , function( e ){
                    var times = e.data.time_start.split(/[- :]/);
                    var start = new Date(times[0] , times[1] - 1 , times[2], times[3], times[4], times[5]);
                    times = e.data.time_now.split(/[- :]/);
                    var now = new Date(times[0] , times[1] - 1 , times[2], times[3], times[4], times[5]);

                    var dura = ~~( ( start - now ) / 1000 );
                    var d = ~~( dura/86400 );
                    var h = ~~( ( dura - d * 86400 ) / 3600 );
                    var m = ~~( ( dura - d * 86400 - h * 3600 ) / 60 );
                    var s = dura - d * 86400 - h * 3600 - m * 60;

                    countDownMgr.init( $(".conut_downitem" ) , [ 99 , 23 , 59 , 59 ] , [ d , h , m , s ] );
                });

                setTimeout(function(){
                    countDownMgr.initCountDown();
                } , 30 * 1000);
            }
        }
    })();


    var bigVideoInit = function(){
        var ratio = 516 / 893;
		var videoname = $('body').data('page');
        renderVideo( $('<div></div>').css({
            "position": "fixed",
            "z-index": "-1",
            "top": "0",
            "left": "0",
            "height": "100%",
            "width": "100%",
            "overflow": "hidden"
        }).appendTo( $('.page').css('background' , 'none') ) , "/videos/"+videoname , "" ,  {muted:1} );
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

    LP.action('logout' , function(){
        api.ajax('/api/user/logout', function(){
            window.location.href = '/';
        });
        return false;
    })

    LP.action("member_invent" , function(){
        LP.panel({
            content: '<div class="popup_invite">\
                    <div class="popup_close"></div>\
                    <div class="popup_invite_friend_list"></div>\
                    <div class="loading-wrap"><div class="loading"></div></div>\
                    <div class="cs-clear"></div>\
                    <div class="popup_invite_btns" style="position:relative;">\
                        <p class="popup_error">&nbsp;</p>\
                        <a href="javascript:void(0);" class="disabled">' + _e('Ok') + '</a>\
                    </div>\
                </div>',
            title: '',
            width: 760,
            height: 408,
            onShow: function(){
                // LP.use('jscrollpane' , function(){
                //     $('.popup_invite_friend_list').jScrollPane();    
                // });
            },
            onload: function() {
                var panel = this;
                var uTpl = '<div class="friend_item" data-uuid="#[uuid]">\
                        <div class="avatar"><img src="#[avatar]"></div>\
                        <div class="name">@#[name]</div>\
                        <div class="btns">\
                            <div class="selected" data-name="#[name]" style="display:none;"></div>\
                            <div class="send" >' + _e('Send Invitation') + '</div>\
                        </div>\
                    </div>';



                var loadFriends = function( page ){
                    if( next_cursor == -1 ) return;
                    isLoading = true;
                    panel.$panel.find('.loading-wrap').show();
                    // load user list from sina weibo or twitter
                    api.get("/api/user/friends" , next_cursor == -2 ? '' : { next_cursor: next_cursor } , function( e ){
                        next_cursor = e.ext.next_cursor;
                        panel.$panel.find('.loading-wrap').hide();

                        var $list = panel.$panel.find('.popup_invite_friend_list');
                        $.each( e.data , function( i , user ){
                            var $friend = $(LP.format( uTpl , {avatar: user.avatar_large , name: user.screen_name , uuid:user.uuid} ))
                                .css({top:-30 , opacity: 0 , 'position': 'relative'});
                            setTimeout(function(){
                               $friend.appendTo( $list )
                                    .animate({
                                        top: 0,
                                        opacity: 1
                                    } , 100);
                           } , i * 100 );
                        } );

                        setTimeout( function(){isLoading = false;} , 100 * e.data.length );
                    } , null, function(){
                        
                    });
                }
                
                

                var hasMore = true;
                var isLoading = false;
                var next_cursor = -2;
                loadFriends( );

                panel.$panel.find('.popup_invite_friend_list').delegate(".send" , 'click' , function(){
                    if( $(this).closest('.popup_invite_friend_list').find(".selected:visible").length
                        >= $('.teambuild_member .member_add').length ){
                        panel.$panel.find('.popup_error').html(_e(' You can\'t invite too many people '));
                        setTimeout(function(){panel.$panel.find('.popup_error').html('')} , 5000);
                        return false;
                    }
                    panel.$panel.find('.popup_error').html('');
                    $(this).hide().prev().show();

                    // btn status
                    panel.$panel.find('.popup_invite_btns a')
                        .removeClass('disabled');
                })
                .delegate(".selected" , "click" , function(){
                    $(this).hide().next().show();
                    panel.$panel.find('.popup_error').html('');
                    if( !panel.$panel.find('.popup_invite_friend_list .selected:visible').length ){
                        // btn status
                        panel.$panel.find('.popup_invite_btns a')
                            .addClass('disabled');
                    }
                })
                .bind('scroll' , function(){
                    if( !hasMore || isLoading ) return;

                    var scrollTop = $(this).scrollTop();
                    var height = $(this).height();
                    if( this.scrollHeight - scrollTop - height < 100 )
                        loadFriends( next_cursor );
                });

                panel.$panel.find('.popup_close')
                    .click( function(){
                        panel.close();
                    });

                // set invite
                panel.$panel.find('.popup_invite_btns a')
                    .click(function(){
                        if( $(this).hasClass('disabled') ) return false;
                        var $btn = $(this).addClass('disabled');

                        panel.$panel.find('.loading-wrap').show();
                        // get user list
                        var users = [];
                        var us = [];
                        $('.popup_invite_friend_list .selected:visible').each(function(){
                            users.push( '@' + $(this).data('name') );
                            var $p = $(this).closest('.friend_item');
                            us.push( {
                                'avatar' : $p.find('.avatar img').attr('src'),
                                'name' : $p.find('.name').html().replace('@' , ''),
                                'uuid' : $p.data('uuid')
                            } );
                        });
                        api.post( '/api/user/invite' , {msg: users.join("")} , function(){
                            $.each( us , function( i , u ){
                                // add user to panel
                                $(LP.format('<div class="member_item ">\
                                        <img src="#[avatar]" />\
                                        <p class="member_name"><span class="member_name_span">@#[name]<br/></span><span class="cancel-invit" style="display:none;cursor:pointer;" data-d="uuid=#[uuid]" data-a="cancel-invit">' + _e('Cancel Invit') + '</span></p>\
                                    </div>' , u ))
                                    .insertBefore( $('.teambuild_member .member_add').eq(0) )
                                    .parent()
                                    .addClass('stand_inviting')
                                    .find('.member_add')
                                    .remove();
                            } );
                            

                            panel.close();
                        } , null ,  function(){
                            $btn.removeClass('disabled');
                            panel.$panel.find('.loading').hide();

                        } );
                    });
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
            content: '<div class="popup_dialog popup_post">\
            <div class="popup_dialog_msg">\
                <textarea style="overflow:auto;">' + _e('') + '</textarea>\
            </div>\
            <div class="popup_dialog_btns">\
                <a href="javascript:void(0);" class="p-cancel">' + _e('Cancel') + '</a>\
                <a href="javascript:void(0);" class="p-confirm">' + _e('Confirm') + '</a>\
                <span class="loading"></span>\
            </div>\
            <div class="popup_dialog_status">\
                <span>' + _e('Success!') + '</span>\
            </div>',
            title: "",
            width: 784,
            height: 352,
            onShow: function(){
                var panel = this;
                this.$panel.find('.p-cancel')
                    .click(function(){
                        panel.close();
                    });
                this.$panel.find('.p-confirm')
                    .click(function(){
						if($(this).hasClass('disable')) {
							return;
						}
                        var msg = panel.$panel.find('textarea').val();
						$(this).addClass('disable');
						$(this).next().fadeIn();
                        api.post( '/api/twitte/post' , {msg: msg, "from": "web"} , function(){
							var height = panel.$panel.find('.popup_dialog').height();
							panel.$panel.find('.popup_dialog').height(height);
							panel.$panel.find('.popup_dialog_btns').fadeOut();
							panel.$panel.find('.popup_dialog_status').delay(500).fadeIn(function(){
                            	setTimeout(function(){
									panel.close();
								}, 500);
							});
                        } );
                    });
            }
            // ,
            // onSubmit: function(){
            //     var msg = this.$panel.find('textarea').val();
            //     api.post( '/api/twitte/post' , {msg: msg, "from": "web"} , function(){
            //         LP.right('success');
            //     } );
            // }
        });

        return false;
    });
    
    LP.action('start-tutr' , function(){
        if( $(document.body).data('page') != 'stand' ){
            // set cookit 
            LP.setCookie('_t_' , 1);
        } else {
            animateTure.showStep( 1 );
            return false;
        }
    });

    LP.action('preview' , function( data ){

        var media = $(this).closest('.fuelitem').data('media');
        // show big pic or big video
        var tpls = {
            'video': '<div class="popup_fuel">\
                    <div class="popup_close"></div>\
                    <div class="popup_fuel_video">\
                        <h4>#[title]</h4>\
                        <div class="popup_image_wrap"><img src="#[imgsrc]"/></div>\
                    </div>\
                    <div class="popup_fuel_btns">\
                        <a class="repost" data-img="#[imgsrc]" data-d="mid=#[mid]" data-a="repost" href="#">' + _e('Repost') + '</a>\
                    </div>\
                </div>',
            'image': '<div class="popup_fuel" >\
                <div class="popup_close"></div>\
                <div class="popup_fuel_photo_left">\
                    <div class="popup_image_wrap"><img src="#[imgsrc]"/></div>\
                </div>\
                <div class="popup_fuel_photo_right">\
                    <h4>#[title]</h4>\
                    <div class="popup_fuel_photo_description">\
                        #[description]\
                    </div>\
                    <div class="popup_fuel_btns">\
                        <a class="repost" data-img="#[imgsrc]" data-d="mid=#[mid]" data-a="repost" href="#">' + _e('Repost') + '</a>\
                    </div>\
                </div>\
                <div class="cs-clear"></div>\
            </div>'
        }

        var $img = $(this)
            .closest('.fuelitem')
            .children('img');
        // var imgH = $img.height();
        // var imgW = $img.width();
        var video = $(this).closest('.fuelitem').data('video');

        // init panel width and height
        // var $img = $('<img/>')

        var content = LP.format(video ? tpls['video'] : tpls['image'] , {imgsrc: $img.attr('src') , mid: data.mid , title: media.title , description: media.description});
        LP.panel({
            content: content,
            //title: "share the content",
            title: '',
            height: 'auto',
            width: 'auto',
            onShow: function(){
                this.$panel.find('.lpn_panel')
                    .css({
                        'margin-top': '-50%',
                        'opacity' : 0
                    })
                    .animate({
                        marginTop: 0,
                        opacity: 1
                    } , 500 , 'easeOutQuart' , function(){
                        var imgH = $('.popup_image_wrap img').height();
                        var imgW = $('.popup_image_wrap img').width();
                        if( video ){ // play the video
                            $('.popup_image_wrap img').hide();
                            renderVideo( $('.popup_image_wrap').css({width: imgW , height: imgH + 30}) , video.replace(/\.\w+$/ , '') , $img.attr('src') ,  {
                                controls: true,
                                resize: false
                            } );
                        }

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
                            //panel.resize( tarW , tarH );
                        });
                    } );

                var panel = this;

                this.$panel.find('.popup_close')
                    .click(function(){
                        panel.close();
                    });
            },
            onBeforeClose: function(){
                var $panel = this.$panel;
                // unbind event
                $(window).off('resize.fixfuel');
                return false;
            },
            onSubmit: function(){
            }
        });
    });

    LP.action('repost' , function( data ){
        var tpl = '<div class="popup_dialog popup_post popup_post_with_photo">\
                    <div class="popup_dialog_msg">\
                        <div class="popup_post_photo"><img src="#[imgsrc]" /></div>\
                        <textarea>' + _e('They’re watching you! A NEW psychological thriller from @kevwilliamson starring @DylanMcDermott &amp; @MaggieQ Wed 10/9c pic.twitter.com/o5v4b7M2is') + '</textarea>\
                    </div>\
                    <div class="popup_dialog_btns">\
                        <a href="javascript:void(0);">' + _e('Cancel') + '</a>\
                        <a href="javascript:void(0);">' + _e('Confirm') + '</a>\
                    </div>\
                </div>';

        LP.panel({
            content: LP.format( tpl , {imgsrc: $(this).data('img')}),
            title: "",
            onload: function(){
                var panel = this;
                panel.$panel.find('.popup_dialog_btns a')
                    .eq(0)
                    .click(function(){
                        panel.close();
                    })
                    .end()
                    .eq(1)
                    .click(function(){
                        var msg = panel.$panel.find('textarea').val();
                        api.post( '/api/media/share' , {share_text: msg , media_id: data.mid} , function(){
                            LP.right('success');
                        } );
                    });
            }
        });
    });

    var fuelPage = 0;
    LP.action('fuel-load' , function( data ){
        $(this).attr('disabled' , 'disabled');
        var page = ++fuelPage;

        $('.fuel .loading').show();
        $(this).data( 'page' , fuelPage );

        api.get('/api/media/list' , { page:page } , function( e ){
            $('.fuel .loading').hide();
            //  render fuel item
            $.each( e.data || [] , function( i , data ){
              if (data["type"] == "video") {
                data["video"] = 1;
              }
                LP.compile('fuel-tpl' , data , function( html ){
                    $( html ).appendTo( $('.fuellist') ).data( 'media' , data );
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
        return false;
    });


    LP.action('legal-mentions' , function( data ){
        // fix scroll
        // $('#legal-notice').fadeIn( function(){
        //     // if( !this.getAttribute('init') ){
        //     //     this.setAttribute('init' , 1);
        //     //     LP.use('jscrollpane' , function(){
        //     //         $('.legal-con').jScrollPane();    
        //     //     });
        //     // }
        // } );
		renderVideo( $('<div></div>').css({
			"position": "absolute",
			"z-index": "-1",
			"top": "0",
			"left": "0",
			"height": "100%",
			"width": "100%",
			"overflow": "hidden"
		}).addClass('videobg').appendTo( $('#legal-notice') ) , "/videos/index" , "" ,  {muted:1} , function(){

        });
		setTimeout(function(){
			$('#legal-notice').fadeIn();
			$(window).trigger('resize');
		} , 200);
    });

	LP.action('winners-prizes' , function( data ){
		
		renderVideo( $('<div></div>').css({
			"position": "fixed",
			"z-index": "-1",
			"top": "0",
			"left": "0",
			"height": "100%",
			"width": "100%",
			"overflow": "hidden"
		}).addClass('videobg').appendTo( $('#winners-prizes').css('background' , 'none') ) , "/videos/winner" , "" ,  {muted:1} , function(){

        } );
		setTimeout(function(){
			$('#winners-prizes').fadeIn();
			$(window).trigger('resize');
		} , 200);
	});

    LP.action('skip-intro' , function(data){
        $('#home_video').fadeOut(function(){
           var video = $(this).find('.video-js')
                .parent()
                .data('video');
            video.dispose();
            video.isRemoved = true;

           $(this).remove();
        } );
    });

    LP.action('leaveteam' , function( e ){
        var self = $(this);
        var tpl = '<div class="popup_box popup_dialog">\
                <div class="popup_dialog_msg">#[content]</div>\
                <div class="popup_dialog_btns">\
                    <a href="javascript:void(0);">' + _e('Cancel') + '</a>\
                    <a href="javascript:void(0);">' + _e('Confirm') + '</a>\
                </div>\
            </div>';
        LP.panel({
            title: '',
            content: LP.format( tpl , {content: _e('do you want to leave the team?')} ),
            onShow: function(){
                var panel = this;
                this.$panel.find('.popup_dialog_btns a')
                    .eq(0)
                    .click(function(){
                        panel.close();
                    })
                    .end()
                    .eq(1)
                    .click(function(){
                        api.get("/api/user/leaveteam", function ( e ) {
                           //TODO:: 动画效果
                           panel.close("fast");
							window.location.reload();
                        });
                    });
            }
        });
    });
    
    LP.action("invite_box", function(params) {
      LP.panel({
        type: "panel",
        "content": '<div class="popup_box popup_dialog"><div class="popup_dialog_msg">Do you want to join '+params["team_name"]+' ?</div><div class="popup_dialog_btns"><a href="javascript:void(0);" class="cancel">Cancel</a><a href="javascript:void(0);" class="confirm">Confirm</a></div></div>',
        "title": "",
        mask: true,
        destroy: true,
        submitButton: false,
        cancelButton: false,
        closeAble: false,
        onShow: function () {
          var panel = this;
          this.$panel.find(".cancel").click(function () {
            api.post("/api/user/jointeam", {"owner": 0}, function(e) {
              if (e["status"] == 0) {
                panel.close();
                window.location.reload();
              }
            });
            panel.close();
          });
          this.$panel.find(".confirm").click(function () {
            api.post("/api/user/jointeam", {"team_id": params["team_id"]}, function(e) {
              if (e["status"] == 0) {
                panel.close();
                window.location.reload();
              }
            });
          });
        },
        onSubmit: function () {
          
        },
        onCancel: function () {

        },
        width: $(window).width() * 0.6,
      });
    });
    
    
    // TODO ...
    LP.action('cancel-invit' , function( data ){
        var $dom = $(this).closest('.teambuild_member ');
        api.post('/api/user/cancelinvite' , {uuid: data.uuid} , function(){
            $dom.children().fadeOut( function(){
                $dom.html( '<a href="javascript:;" data-a="member_invent" class="member_add cs-clear">+</a>' )
                    .removeClass('stand_inviting');
            } );
        });
    });


    // page init here
    // =======================================================================
	var isComplete = false;
	var initComplete = function(){
		if(isComplete) return;
        isComplete = true;
		$('.loading-wrap').fadeOut();

		/* for animation */
		var isUglyIe = $.browser.msie && $.browser.version <= 8;
		if(isUglyIe && $('#scheme').length > 0)
			return;
		var ANIMATE_NAME = "data-animate";
		$('[' + ANIMATE_NAME + ']')
			.each(function(){
				var $dom = $(this);
				var tar = $dom.data('animate');
				var browser = $dom.data('browser');
				var style = $dom.data('style');
				var time = parseInt( $dom.data('time') );
				var delay = $dom.data('delay') || 0;
				var easing = $dom.data('easing');
				var begin = $dom.data('begin');
				tar = tar.split(';');
				var tarCss = {} , tmp;
				if(browser == 'uglyie' && isUglyIe) {
					return;
				}
				for (var i = tar.length - 1; i >= 0; i--) {
					tmp = tar[i].split(':');
					if( tmp.length == 2 )
						tarCss[ tmp[0] ] = $.trim(tmp[1]);
				}
				if( isUglyIe && tarCss.opacity !== undefined ){
					delete tarCss.opacity;
				}


				style = style.split(';');
				var styleCss = {} , tmp;
				for (var i = style.length - 1; i >= 0; i--) {
					tmp = style[i].split(':');
					if( tmp.length == 2 )
						styleCss[ tmp[0] ] = $.trim(tmp[1]);
				}
				if( isUglyIe && styleCss.opacity !== undefined ){
					delete styleCss.opacity;
				}
				$dom.css(styleCss).delay( delay )
					.animate( tarCss , time , easing );
				if( begin ){
					setTimeout(function(){
						animation_begins[begin].call( $dom );
					} , delay);
				}
			});
	}

    // page load event
    $(document.body).queryLoader2({
        onLoading : function( percentage ){
            var per = parseInt(percentage);
            $('.loading-percentage').html(per+'%');
            $('.loading-bar').css({'width':per+'%'});
            if(per == 100) {
				initComplete();
//                var timer = setInterval(function(){
//                    if( globalVideos.length == 0 ) return ;
//                    var total = 0;
//                    $.each( globalVideos , function( i , buff){
//                        total += buff;
//                    } ) ;
//
//                    if( total >= globalVideos.length / 10){
//                        initComplete();
//                        $.each( globalVideoInterval , function( i , intval ){
//                            clearInterval( intval );
//                        } );
//                        clearInterval( timer );
//                    }
//                } , 100);
            }
        },
        onComplete : function(){
			initComplete();

            // load all the video
//            var timer = setInterval(function(){
//                if( globalVideos.length == 0 ) return ;
//                var total = 0;
//                $.each( globalVideos , function( i , buff){
//                    total += buff;
//                } ) ;
//                if( total >= globalVideos.length / 10){
//                    initComplete();
//                    $.each( globalVideoInterval , function( i , intval ){
//                        clearInterval( intval );
//                    } );
//                    clearInterval( timer );
//                }
//            } , 100);
        }
    });

    $(function(){

        // init all pages fadein element 
        $('[data-fadein]').hide().each( function( i ){
            $(this).delay( i * 300 ).fadeIn( 800 );
        } );

        // lang swither
        $("a").click(function () {
            var self = $(this);
            if (self.attr("data-lang")) {
                LP.setCookie("lang", self.attr("data-lang"), 3600 * 24, "/");
                window.location.reload();
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


        // init nav list effect
        $('.nav p').css({opacity:0,marginLeft:-20}).each(function( i ){
            $(this).delay( i * 200 )
                .animate({
                    opacity: 1,
                    marginLeft: 0
                } , 500);
        });

        // init share btn
        $('#share').hover(function(){
            $('.share-btns').stop( true , true ).fadeIn().dequeue().animate({
                width: 240
            } , 300 );
        } , function(){
            $('.share-btns').stop(true , true).delay(200).fadeOut().dequeue().animate({
                width: 0
            } , 500 );
        });

		// init post twitter button
		$('.post_link').hover(function(){
			$(this).find('span').fadeIn();
		}, function(){
			$(this).find('span').fadeOut();
		});

        // init #legal-notice
        $('#legal-notice .popup_close').click(function(){
            $('#legal-notice').fadeOut(function(){
				$('#legal-notice .videobg').remove();
			});
        })

		// init #legal-notice
		$('#winners-prizes .popup_close').click(function(){
			$('#winners-prizes').fadeOut(function(){
				$('#winners-prizes .videobg').remove();
			});
		});


        // fix Q & A
        !!(function(){
            // ban qa
            return false;
            var now  = new Date();

            var cookieTimes = [];
            var qaCookie = LP.getCookie( "__QA__") ;
            if( qaCookie ){
                cookieTimes = qaCookie.split(",");
                cookieTimes = cookieTimes.slice(cookieTimes.length - 4);
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
                LP.setCookie( "__QA__" , cookieTimes.join(",") , 86400 * 30 , '/');

                qtimes++;
                var timer = null;
                api.get('/api/question/random' , '' , function( e ){

                    var data = e.data || {};
                    var content = '<div class="popup_dialog"><div class="popup_timer"></div><div class="popup_dun">1 <span>' + _e('Assiduity') + '</span></div><div class="popup_dialog_msg">';
                    content += data.question + '</div><div class="popup_dialog_options">';
                    $.each( [1,2,3,4] , function( i ){
                        content += '<label data-value="' + ( i + 1 ) + '">' + data['answer' + ( i + 1 ) ] + '</label>'
                    } );
                    content += "</div></div>";

                    LP.panel({
                        title: '',
                        content: content,
                        width: 784,
                        height: 296,
                        onload: function(){
                            var times = 10;
                            var t = this;

                            // init select event
                            this.$panel.find('.popup_dialog_options label')
                                .click(function(){
                                    $(this).addClass('active')
                                        .unbind('click')
                                        .siblings()
                                        .removeClass('active')
                                        .unbind('click');

                                    clearTimeout( questionTimerInitTimer );

                                    api.post("/api/question/answer" , { answer: t.$panel.find('.popup_dialog_options label.active').data('value') , qaid: data.qaid} , function(){
                                        t.close();
                                    });
                                });

                            // init timer
                            questionTimerInit( this.$panel.find('.popup_timer') , 30000 , function(){
                                // TODO..
                                api.post("/api/question/answer" , { answer: '' , qaid: data.qaid} , function(){
                                    t.close();
                                });
                            } );
                        }
                    });
                });
                setTimeout( showQa , ( getNextTime() - lastTime ) * 60 * 1000 );
            }
            var qtimes = 0;
            setTimeout( showQa , ( getNextTime() - lastTime ) * 60 * 1000 );
        })();

        // if( $(document.body).data('page') )
        bigVideoInit();


        var needTriggerTutr = false;
        // init first page template
        switch( $(document.body).data('page') ){
            case "index":
                var ratio = 516 / 893;
                // show the big video
                renderVideo( $('#home_video') , "/videos/small" , "" ,  {ratio: ratio} , function(){
                    $('#' + this.Q).css('z-index' , 0);
                } );
                // get parameter d
                var urlObj = LP.parseUrl();
                if( urlObj.params.d ){
                    api.post( "/api/web/decryptionURL" , {d: urlObj.params.d} );
                }

                countDownMgr.initCountDown();
                api.get('/api/web/time' , function( e ){
                     var start = new Date(e.data.time_start );
                     var now = new Date(e.data.time_now );
                    
                     var dura = ~~( ( start - now ) / 1000 );
                     var d = ~~( dura/86400 );
                     var h = ~~( ( dura - d * 86400 ) / 3600 );
                     var m = ~~( ( dura - d * 86400 - h * 3600 ) / 60 );
                     var s = dura - d * 86400 - h * 3600 - m * 60;

                     countDownMgr.init( $(".conut_downitem" ) , [ 99 , 23 , 59 , 59 ] , [ d , h , m , s ] );
                });

                break;
            case "teambuild":
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
                // api.get('/api/web/time' , function( e ){
                //     var start = new Date(e.data.time_start );
                //     var now = new Date(e.data.time_now );
                   

                //     var dura = ~~( ( start - now ) / 1000 );
                //     var d = ~~( dura/86400 );
                //     var h = ~~( ( dura - d * 86400 ) / 3600 );
                //     var m = ~~( ( dura - d * 86400 - h * 3600 ) / 60 );
                //     var s = dura - d * 86400 - h * 3600 - m * 60;

                //     countDownMgr.init( $(".conut_downitem" ) , [ 99 , 23 , 59 , 59 ] , [ d , h , m , s ] );
                // });
                countDownMgr.initCountDown();
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
                // show invited panel
                var dataCon = $("#data-stand");
                var isInvited = !!parseInt(dataCon.attr("data-is_invited"));
                if ( isInvited ) {
                   LP.triggerAction('invite_box', {"team_name": dataCon.attr("data-team_name"), "team_id": dataCon.attr("data-team_id")});
                }



                // init hover event
                $('.stand_chart_speed,.stand_chart_quality,.stand_chart_assiduite,.stand_chart_impact')
                    .hover(function(){
                        // TODO ...
                        $(this).find('.stand_chart_tip').fadeIn();
                    } , function(){
                        // TODO ...
                        $(this).find('.stand_chart_tip').fadeOut();
                    });

                $('.stand_tit .team_name').hover(function(){
                        $(this).next().fadeIn();
                    } , function(){
                        $(this).next().fadeOut();
                    });


                $(".stand .teambuild_member").live({
                    'mouseenter':function(){
                        $(this).addClass("hover");
                    },
                    'mouseleave':function(){
                        $(this).removeClass("hover");
                    }
                });

                // init team name
                var lastTname = null;
                var hideTimer = null;
                $('.team_name').blur(function(){
                    $(this).removeClass('focus');
                    var txt = $(this).text();
                    if( lastTname === txt ) return;
                    // match
                    
                    lastTname = txt;
                    api.post("/api/user/updateteam" , {name: txt} );
                }).keydown(function( ev ){ 
                    if( ev.shiftKey && ( ev.which == 57
                        || ev.which == 48 || ev.which == 49 || ev.which == 50 )
                        ) return false;
                    switch( ev.which ){
                        case 221:
                        case 219:
                            return false;
                        case 13:
                            $(this).trigger('blur');
                            return false;
                            break;
                    }
                    var txt = $(this).text();
                    var tmp = txt.replace( /[\u4e00-\u9fa5]/g , '00' );
                    if( tmp.length >= 12 && ev.which != 8 ){
                        $('.team_name_error_tip').fadeIn();
                        clearTimeout( hideTimer );
                        hideTimer = setTimeout(function(){
                            $('.team_name_error_tip').fadeOut();
                        } , 3000);
                        return false;
                    }
                    $('.team_name_error_tip').fadeOut();
                })
                .focus(function(){
                    $(this).addClass('focus');
                });

               countDownMgr.initCountDown();


                api.get('/api/user' , function( e ){
                    var data = e.data;
                    var crtuser = data["user"];

                    // auto render tuto
                    needTriggerTutr = LP.getCookie('_t_') || !crtuser.read_tutorial;
                    if( needTriggerTutr && !isInvited ){
                        LP.triggerAction( 'start-tutr' );
                        LP.removeCookie('_t_');
                    }

                    
                    var team = data.team;
                    
                    // TODO:: 如果发现team 是空， 则需要返回到team building 页面
                    
//                    var team = data.team || {
//                        score: {average: 100,impact:0.5 , quality:0.8 ,speed:0.3 , assiduite:0.2},
//                        name:'xxxx',
//                        users:[{
//                            "uid":"101648",
//                            "name":"\u8299\u7f8e\u513f",
//                            "from":"weibo",
//                            "cdate":"2014-05-16 10:39:25",
//                            "udate":"2014-05-16 10:39:25",
//                            "uuid":"5072167230",
//                            "lat":null,
//                            "lng":null,
//                            "speed": 0.9,
//                            "impact": 3452,
//                            "invited_by":"0","profile_msg":"","avatar":"http:\/\/tp3.sinaimg.cn\/5072167230\/180\/40049599975\/0","status":"1","friends":"81","location":"","score":null
//                    }]};

                    // 
                    $('.team_name').html( team.name );
                    $('#team-score').html( 'P' + (data.team_position < 10 && data.team_position > 0 ? '0' + data.team_position : data.team_position ) + ' / ' + (data.team_total < 10 && data.team_total > 0 ? '0' + data.team_total : data.team_total ) );

                    // render users
                    var utpl_crtuser = '<div class="teambuild_member stand_useritem cs-clear">\
                        <div class="member_item ">\
                            <img src="#[avatar]" />\
                            <p class="member_name"><span class="member_name_span">@#[name]<br/></span><span class="member-leave" data-a="leaveteam">' + _e('Leave Team') + '</span></p>\
                        </div>\
                        <div class="member_speed"></div>\
                        <div class="memeber_space" data-num="#[num]" data-unit="#[unit]">0</div></div>';
                    var utpl_teammem = '<div class="teambuild_member stand_useritem cs-clear">\
                        <div class="member_item ">\
                            <img src="#[avatar]" />\
                            <p class="member_name">@#[name]<br/></p>\
                        </div>\
                        <div class="member_speed"></div>\
                        <div class="memeber_space" data-num="#[num]" data-unit="#[unit]">0</div></div>';
                    var utpl_inviting = '<div class="teambuild_member stand_useritem cs-clear stand_inviting">\
                        <div class="member_item ">\
                            <img src="#[avatar]" />\
                            <p class="member_name"><span class="member_name_span">@#[name]<br/></span>#[opt]</p>\
                        </div></div>';
                    var html = [];
                    var speeds = [];
                    var spaces = [1000000 , 1000 , 100 , 1];
                    var spacesUnit = ['M' , 'K' , 'H' , ''];

                    var duration = 600;
                    var now = (+new Date()) + 1000;
                    var animateTo = function(  $dom , num , unit ){
                        var dur = new Date() - now;
                        var per = dur / duration;
                        if( per > 1 ){
                            per = 1;
                        }
                        if(( num + '' ).indexOf('.') < 0 )
                            var fixNum = 0;
                        else 
                            var fixNum = 1;
                        $dom.html( parseFloat((num * per).toFixed(fixNum)) + unit );
                        if( per < 1 ){
                            setTimeout( function(){
                                animateTo( $dom , num , unit );
                            } , 1000 / 60);
                        }
                    } 

                    // render invited user
                    $.each( team.users || [] , function( i , user ){
                        var space = '';
                        var unit = '';

                        if( user.friends > 100 ){
                            $.each( spaces , function( k , sp ){
                                space =  Math.round((user.friends / sp)*10) / 10;
                                if( space >= 1 ){
                                    unit = spacesUnit[k];
                                    return false;
                                }
                                unit = spacesUnit[k];
                            } );
                        } else {
                            space = user.friends;
                        }

                        html.push( LP.format( user.uid == crtuser["uid"] ? utpl_crtuser : utpl_teammem ,{
                            avatar:     user.avatar,
                            name:       user.name,
                            num:        space,
                            unit:       unit,
                            space:      space + unit}));
                        speeds.push( user.score ? user.score.speed : 0 );
                    } );
                    // render inviting user
                    $.each( data.inviting || [] , function( i , user ){

                        html.push( LP.format( utpl_inviting ,{
                            avatar:     user.avatar,
                            name:       user.screen_name,
                            opt:    user.invitor == crtuser["uid"] ? '<span class="cancel-invit" style="display:none;cursor:pointer;" data-d="uuid=' + user.uuid + '" data-a="cancel-invit">' + _e('Cancel Invit') + '</span>' : ''
                        } ) );
                    } );

                    // render plus 
                    for (var i = (data.inviting || []).length + (team.users || []).length; i < 3; i++) {
                        html.push( '<div class="teambuild_member stand_useritem cs-clear">\
                                <a href="javascript:;" data-a="member_invent" class="member_add cs-clear">+</a>\
                            </div>' );
                    }
                    $('.teambuild_members').html( html.join("") )
                        .find('.memeber_space')
                        .each( function(){
                            var $this = $(this);
                            setTimeout(function(){
                                animateTo( $this , $this.data('num') , $this.data('unit') );
                            } , 1000);
                            
                        } );
					// init member effect
					$('.teambuild_member:not(.stand_inviting)').css({opacity:0}).each(function( i ){
						$(this).delay( i * 200 )
							.animate({
								opacity: 1
							} , 500);
					});
                    $.each( speeds , function( i , speed ){
                        rotateAnimate( $('.member_speed').eq(i) , speed , 1 , 45 );
                    } );

                    // render achive
                    var ahtml = [];
                    for( var i = 0 ; i < data.team_star ; i++ ){
                        ahtml.push('<p></p>');
                    }

                    $('.stand_achivmentsbox').html( ahtml.join("") );
                    $('.stand_achivments').fadeIn();
                    //data.last_post || 
                    var posts =  data.last_post || [];
                    // render post
                    var aHtml = [];
                    $.each( posts , function( i , post ){
                        aHtml.push("<div class=\"stand_achivmentsbox\">" + post["content"] + "</div>");
                    } );

                    var postWidth = $('.stand_posts').width() / 2 + 21;
                    $('.stand_posts_inner').append( aHtml.join("") ).css('width' , posts.length * postWidth)
                        .data('index' , 0 );
                    $('.stand_tweet').fadeIn();
                    
                    // redner next page
                    $('.stand_add').click(function(){
						console.log(postWidth);
                        if($(this).hasClass('disabled') ) return;
                        if( Math.abs(parseInt( $('.stand_posts_inner').css('marginLeft') )) + $('.stand_posts').width()
                        >= $('.stand_posts_inner').width()) return;

                        $(this).addClass('disabled');
                        $('.stand_posts_inner').animate({
                            marginLeft: '-=' + ( postWidth * 2 )
                        } , 500 , '' , function(){
                            if( Math.abs(parseInt( $('.stand_posts_inner').css('marginLeft') )) + $('.stand_posts').width()
                                >= $('.stand_posts_inner').width()){
                                $('.stand_add').adddlass('disabled');
                            }
                            $('.stand_del').removeClass('disabled');
                        });
                    });

                    $('.stand_del').click(function(){
                        if($(this).hasClass('disabled') ) return;
                        if( Math.abs(parseInt( $('.stand_posts_inner').css('marginLeft') )) == 0 ) return;

                        $(this).addClass('disabled');
                        $('.stand_posts_inner').animate({
                            marginLeft: '+=' + ( postWidth * 2 )
                        } , 500 , '' , function(){
                            if( Math.abs(parseInt( $('.stand_posts_inner').css('marginLeft') )) == 0 ){
                                $('.stand_del').addClass('disabled');
                            }
                            $('.stand_add').removeClass('disabled');
                        });
                    });

                    // hover to show the leave team
                    $('.teambuild_members').delegate( '.member_item' , 'mouseenter' , function(){
                        if(!$(this).find('.member-leave,.cancel-invit').length) return;
                        $(this).find('.member-leave,.cancel-invit').stop( true , true ).fadeIn()
                            .end()
                            .find('.member_name_span')
                            .hide();
                    })
                    .delegate('.member_item' , 'mouseleave' , function(){
                        $(this).find('.member-leave,.cancel-invit').hide()
                            .end()
                            .find('.member_name_span')
                            .stop( true , true )
                            .fadeIn();
                    })

                    // render stand_chart
                    var score = team.score || {};
                    $('.stand_chart_score').html( (score.average || 0) + 'km/h' );
                    coordinate.init( $('.stand_chart') , function(){
                        coordinate.run( score.impact || 0 , score.quality || 0 , score.speed || 0 , score.assiduity || 0 );
                    } );
                        
                });

                break;
                
            case "monitoring":
              var tweetGroup = $(".tweet-con .tweet-list");
              var callbackRender = function(index, group) {
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
                      $(".monitor_item").eq(index).children(".monitor_list").append(html);
                    });
                  }
                  if (group.length <= 0) {
                    $(".monitor_item").eq(index).children(".monitor_list").append("<li class='tweet-signle-item clearfix'>empty</li>");
                  }
                }
              api.get("/api/twitte", function (e) {
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

            case "race":
                if(document.createElement("canvas").getContext){
                    LP.use('../race/race');

                    // get server time 
                    var getServerTime = function(){
                        api.get('/api/web/time' , function( e ){
                            clearInterval( interval );
                            var now = +new Date( e.data.time_now ) / 1000;
                            var start = +new Date( e.data.time_start ) / 1000;
                            
                            interval = setInterval( function(){
                                now += 1;
                                var duration = now - start;
                                var hour = ~~(duration / 3600);
                                var minute = ~~( ( duration - hour * 3600 ) / 60 );
                                var seconds = duration - hour * 3600 - minute * 60;

                                $('.race_time').html( ( hour > 9 ? hour : '0' + hour ) + ':' + 
                                 ( minute > 9 ? minute : '0' + minute ) + ':' + 
                                 ( seconds > 9 ? seconds : '0' + seconds ) );
                            } , 1000 );
                        });


                        api.get('/api/user' , function( e ){
                            var speed = e.data.team.score ? e.data.team.score.average : 0;
                            $('.race_speed').html( speed + 'Kp/h' );
                        });
                    }
                    var interval;
                    setInterval(getServerTime , 30 * 1000);
                    getServerTime();
                } else {
                    // render flash
                    $('.race_nav,.nav').hide();
                    $('#container').html(
                        '<object id="flash" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="100%" height="100%">\
                            <param name="movie" value="/js/raceflash/track.swf"/>\
                            <param name="quality" value="high"/>\
                            <param name="wmode" value="transparent"/>\
                            <param name="flashVars" value="xml=/js/raceflash/xml/track.xml"/>\
                            <embed name="flash" src="/js/raceflash/track.swf" quality="high" wmode="transparent" flashVars="xml=/js/raceflash/xml/track.xml" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="100%" height="100%" allowScriptAccess="always"></embed>\
                        </object>'
                        );
                     
                }
                
                break;
        }
    });
});


