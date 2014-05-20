/*
 * Select panel. Like most need keyboard event panel , just as auto complete panel or suggestion panel.
 * It support keyboard event ( up , down ) , and 
 *
 */
define(function( require , exports , model ){
    "use strict";
    var $ = require('jquery');
    var __query_timer = null,
        defaultConfig = {
            cached: true,
            enableEmpty: false,
            zIndex: 1000, 
            hoverClass: 'hover', // hover class
            availableCssPath: '', // 用于hover的css path
            wrapClass: '',
            autoSelect: true,
            loop : true,
            //maxHeight: 180,
            supportKeyEvent: true, // 是否支持键盘事件
            width: '',
            leftOff: 0, // 向左的偏移值
            topOff: 0, // 向上的偏移值
            getIndexData: null, // 获取某个index值的数据
            onSelect: null,
            onHover: null,
            hideWhenBlank: false, /*  当结果为空时 不显示wrap */
            container: null, // container of suggestion wrap
            loadingContent: '',
            getCacheKey: function(key){
                return key;
            },
            renderHead: function(data){
            },
            // how to render data , and return a html string
            renderData: function(data){
            },
            renderFoot: function(data){
            },
            // how to get data
            getData: function(cb){
                cb([]);
            }
        },
        BaseSelectPanel = function( handler , o ){
            var t = this;
            t.cache = {}; // 不能用在prototype中，所有的实例不能共享
            o = t.config = LP.mix( defaultConfig , o || {} );
            if(!o.width){
                o.width = $(handler).outerWidth();
            }
            t.$wrap = $(t.template).addClass('search-wrap ' + o.wrapClass)
                .css({'position': 'absolute' , 'z-index': o.zIndex , 'width': o.width-2})
                .appendTo( o.container || $(handler).parent() )
                .hide();

            // init event
            t.$wrap.delegate(o.availableCssPath , "mouseover" , function(){
                t.hover($(this));
            }).delegate(o.availableCssPath , "click" , function(){
                if ($(this).attr('target') === '_blank') return;
                o.onclick && o.onclick($(this));

                t.select($(this) , t.data[t.$wrap.find(o.availableCssPath).index($(this))]);
                return false;
            }).click(function(ev){ // click element clear the time out 
                //var tar = ev.target; // if there is a element , do not return false , if has action-type attribute do not return false;
                //if(tar.tagName != 'A' || (tar.getAttribute('href').indexOf('javascript') >= 0 && !tar.getAttribute('action-type')))
                //  return false;
            });
            
            // key down event
            t.$handler = $(handler);
            if(o.supportKeyEvent){
                t.$handler.keydown(function(ev){// textarea or inputt.$textarea
                    if(t.$wrap.is(':hidden')) return;
                    switch(ev.keyCode){
                        case 40: // down
                            t.next();
                            break;
                        case 38: // up
                            t.prev();
                            break;
                        case 13: //enter
                            t.select();
                            break;
                        case 9: // tab
                        case 27: //esc
                            t.hide(); // need return
                        default:
                            return;
                    }
                    return false;
                });
            }
            // because div do not trigger click event when you click at div scroll bar , so the best Solution is bind event to document.
            // when click other elements trigger hide event;
            $(document).click(function(ev){
                var tar = ev.target;
                if(tar != t.$handler.get(0) && !$.contains(t.$wrap.get(0) , tar)){
                    t.hide();
                }
            });
            
            // resize hide the panel
            // fuck mapabc , when search locations , it fire the event window resie in ie6 and ie7.
            if(!($.browser.msie && $.browser.version < 8)){
                $(window).resize(function(){
                    t.hide();
                });
            }
        };

    BaseSelectPanel.prototype = {
        addListener: function( event ,fn ){
            this.events = this.events || {};
            this.events[ event ] = this.events[ event ] || [] ;
            this.events[ event ].push( fn );
        },
        fireEvent: function(  ){
            var event = arguments[0];
            var args = Array.prototype.slice.call( arguments ).slice( 1 );
            var t = this;
            var events = this.events[ event ] || [];
            for( var i = 0 ; i < events.length ; i++  ){
                if( events[i].apply( t , args ) === false ){
                    return false;
                }
            }
        },
        template: ['<div>',
                    //'<div class="__auto_head"></div>',
                    //'<div class="__auto_body"></div>',
                    //'<div class="__auto_foot"></div>',
                    '</div>'].join(''),
       
        __getBody : function(){
            return this.$wrap.find('.__auto_body');
        },
        show : function( left , top , key ){
             // setPosition
            var t   = this
            , o     = t.config
            , callback = function( data ){
                if(o.cached)t.cache[cacheKey] = data;
                // 如果当前的key 不是输入框的最后的key，则需要隐藏
                if(t.key != key) return;
                // 如果没有结果，则需要隐藏
                if(o.hideWhenBlank && (!data || !data.length))  {
                    t.hide();
                    return;   
                }
                var html = o.renderData.call(t , data) ,
                    hHtml = o.renderHead ? o.renderHead.call(t , data) : '' , 
                    fHtml = o.renderFoot ? o.renderFoot.call(t , data) : '';
                t.data = data;
                if(!html || t.fireEvent("beforeShow" , t , html) === false){
                    t.$wrap.hide();
                    return;
                }
                // 如果在请求时， 用户已经取消请求，则请求成功回来后不render数据
                if(t.$wrap.is(':hidden')) return;
                hHtml = hHtml ? '<div class="__auto_head">' + hHtml + '</div>' : '';
                fHtml = fHtml ? '<div class="__auto_foot">' + fHtml + '</div>' : '';
                html = '<div class="__auto_body">' + html + '</div>';
                t.$wrap.css('height' , 'auto').html(hHtml + html + fHtml).show().css({opacity:1});
                t.$wrap.find(o.availableCssPath).addClass('search-item');
                // select first element
                if(o.autoSelect)
                    t.hover(t.$wrap.find(o.availableCssPath).eq(0));
                // set height of the $wrap
                var $body = t.__getBody();
                if(o.maxHeight && $body.height() > o.maxHeight){
                    $body.height(o.maxHeight).css({
                        'overflow-y': 'auto',
                        'overflow-x': 'hidden'
                    });
                }
                // fire event
                t.fireEvent("show" , t);
            };
            
            t.$hoverDom = null;
            
            if( !o.enableEmpty && !key ){
                t.$wrap.hide();
                return;
            }

            // set default top and left 
            t.key = key === undefined ? t.$handler.val() : key;
            var off = t.$handler.offset();
            var wpOff = t.$wrap.show().offsetParent().offset();

            top = (top || (off.top - wpOff.top + t.$handler.outerHeight())) + o.topOff;
            left = (left || (off.left - wpOff.left)) + o.leftOff;
            t.$wrap.html(o.loadingContent).css({
                position: 'absolute',
                top     : Math.ceil(top) ,
                left    : Math.ceil(left)
            }).show();
            // if no content, hide the panel
            if(!o.loadingContent)
                t.$wrap.css({opacity:0});
           
            var cacheKey = o.getCacheKey(key);
            if(o.cached && t.cache[cacheKey] !== undefined){
                callback(t.cache[cacheKey]);
            }else{
                clearTimeout(__query_timer);
                __query_timer = setTimeout(function(){
                    o.getData.call(t , function(data){
                        callback(data);
                    } , function(){
                        t.$wrap.html('<span style="padding-left:10px;color:#FFD991;">出错啦，请稍候重试...</span>').show();
                    });
                } , 150);
            }
        },
        // hide panel
        hide: function(){
            this.$wrap.hide();
            // fire event
            this.fireEvent("hide" , this);
        },
        select: function( $dom , data ){
            var t = this , o = t.config;
            t.$hoverDom = $dom || t.$hoverDom;
            t.hide();
            if(t.$hoverDom && t.$hoverDom.length){
                t.fireEvent("select" , t.$hoverDom);
                var index = t.$wrap.find(o.availableCssPath).index(t.$hoverDom);
                if(o.onSelect)o.onSelect.call(t , t.$hoverDom , data || (o.getIndexData ? o.getIndexData.call(t,index) : t.data[index]) || {});
            }
        },
        hover: function( $dom ){
            var t   = this
            , o     = t.config
            , hoverClass    = o.hoverClass
            , $lastHoverDom = t.$hoverDom;
            if( t.$hoverDom )
                t.$hoverDom.removeClass( hoverClass );
            t.$hoverDom = $dom;
            t.$hoverDom.addClass( hoverClass );
            // if curr hover dom is last hover dom , then return
            if( $lastHoverDom && t.$hoverDom.get(0) === $lastHoverDom.get(0) ) return;

            t.fireEvent( 'hover' , t.$hoverDom , $lastHoverDom );
            if( o.onHover ) o.onHover.call( t , t.$hoverDom , $lastHoverDom );
            // scroll into view
            if( t.$hoverDom.length ){
                var position    = t.$hoverDom.position()
                ,   donHeight   = t.$hoverDom.height()
                ,   $body       = t.__getBody()
                ,   scrollTop   = $body.scrollTop()
                ,   height      = t.$wrap.height();
                if ( position.top <= 0 ){
                    $body.scrollTop( scrollTop + position.top );
                } else if ( position.top + donHeight >= height ) {
                    $body.scrollTop( position.top - height + scrollTop + donHeight );
                }
            }
        },
        next: function(){
            var t   = this
            , o     = t.config
            , $list = t.$wrap.find( o.availableCssPath )
            , index = t.$hoverDom ? $list.index( t.$hoverDom ) : -1
            , $next = $list.eq( index + 1 ) ;
           
            if( $next.length ){
                t.hover( $next );    
            } else if( o.loop ){
                t.hover( $list.first() );    
            }
        },
        prev: function(){
            var t   = this
            , o     = t.config
            , $list = t.$wrap.find( o.availableCssPath )
            , index = $list.index( t.$hoverDom )
            , $prev = $list.eq( index - 1 ) ;

            if( index > 0 && $prev.length ) {
                t.hover( $prev );    
            } else if( o.loop ){
                t.hover( $list.last() );    
            }
        }
    }
    return BaseSelectPanel;
});