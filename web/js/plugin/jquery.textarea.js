define(function( require , exports , model ){
    "use strict"
    var $ = require('jquery');
    var selection = document.selection,
        // 获取当前光标位置,和选中文本{text:"",start:0,end:0}
        _getPos = function( textarea ){
            var text , start , end , value = textarea.value;
            // textarea.focus(); delete @ 2012-12-31 当输入框大于屏幕高度时，focus会导致在IE下会改变scrollTop的值
            if(selection){ // for IE
                textarea.focus();
                // fix IE , let getPos support input element;
                var range = selection.createRange(),
                    dup = range.duplicate();
                
                switch(textarea.tagName){
                    case 'TEXTAREA':
                        dup.moveToElementText(textarea);
                        dup.collapse(true); 
                        dup.setEndPoint("StartToStart",range);
                        start = dup.text.length;
                        dup.collapse(true);
                        dup.setEndPoint("StartToEnd", range);  
                        end = dup.text.length;
                        text = textarea.value.substr(start , end-start);
                        break;
                    case 'INPUT':
                        var tRange = textarea.createTextRange();
                        tRange.collapse(true);
                        tRange.setEndPoint("EndToStart",range);
                        start = tRange.text.length;
                        tRange.collapse(true);
                        tRange.setEndPoint("EndToEnd", range);  
                        end = tRange.text.length;
                        text = textarea.value.substr(start , end-start);

                        break;
                }
            } else {
                start = textarea.selectionStart;
                end = textarea.selectionEnd;
                text = (start !== end) ? value.substring(start, end) :'';
            }
            return {text:text,start:start,end:end};
        },
        // 设置光标位置
        _setPos = function(textarea , start , length){
            var value = textarea.value;
            start = start || value.length;
            length = length || 0;
            //textarea.focus();
            if(textarea.createTextRange){
                var textRange = textarea.createTextRange();
                //textRange.moveStart("character" , -value.length);
                //textRange.moveEnd("character" , -value.length);
                textRange.collapse(true);
                textRange.moveStart("character", start);
                textRange.moveEnd("character" , length);
                textRange.select();
            }else{
                try{// TODO firefox bug , textarea.setSelectionRange === undefined  && textarea.__proto__.setSelectionRange === function
                    textarea.focus();
                    textarea.setSelectionRange(start , start + length);
                }catch(e){}
            }
        },
        _insertText = function(textarea ,text , start , length ){
            if( start === undefined ){
                _range = _getPos(textarea);
                start = _range.start;
                length = _range.end - _range.start;
            }
            length = length || 0;
            if( selection ) {
                var sR;
                _setPos(textarea, start , length);
                sR = selection.createRange();
                sR.text = text;
                sR.setEndPoint('StartToEnd', sR);
                sR.select();
            } else {
                var oValue, nValue, nStart, nEnd, st;
                _setPos(textarea, start , length);
                oValue = textarea.value;
                nValue = oValue.substring(0, start) + text +
                    oValue.substring(start + length);
                nStart = nEnd = start + text.length;
                st = textarea.scrollTop;
                textarea.value = nValue;
                if(textarea.scrollTop != st) {
                    textarea.scrollTop = st;
                }
                textarea.setSelectionRange(nStart, nEnd);
            }
        },
        _moveTo = function(textarea , start){
            _setPos(textarea , start);
        },
        // 移动到开头
        _toHead = function(textarea){
            _moveTo(textarea , 0);
        },
        // 移动到结尾
        _toTail = function(textarea){
            _moveTo(textarea , textarea.value.length);
        },
        _autoHeight = function(textarea , min , max , cb){
            min = min || 1;
            max = max || 100;
            max = 100;
            var $textarea = $(textarea),// jQuery对象
                textarea = $textarea[0],// DOM对象
                rowHeight = parseInt($textarea.css('line-height')) || 22, // 行高
                minHeight = min * rowHeight,
                maxHeight = max * rowHeight,
                resizeTextarea = function(dom) {
                    var _rows = dom.rows, _height, _overflow,
                        scrollTop = $(window).scrollTop();
                    dom.style.height = 'auto';
                    dom.rows = min;
                    var _continue = false;
                    // ie6-8需要这个来获取正确的scrollHeight
                    dom.scrollHeight;
                    var _scrollHeight = dom.scrollHeight;
                    if (!_rows || _rows < min || !dom.value) { _rows = min; }
                    while( true ) {
                        _continue = false;
                        if (( _rows * rowHeight > _scrollHeight + rowHeight / 2 || _rows > max ) && _rows > min){ 
                            _continue = true;
                            _rows -= 1;
                        }
                        if (( _rows * rowHeight < _scrollHeight - rowHeight / 2 || _rows < min ) && _rows < max) {
                            _continue = true;
                            _rows += 1;
                        }
                        //dom.setAttribute('rows' , _rows);
                        if( !_continue ) break;
                    }
                    if (_rows >= min && _rows < max) {
                        _height = _rows * rowHeight + 'px';
                        _overflow = 'hidden';
                    } else {
                        _height = maxHeight + 'px';
                        _overflow = 'auto';
                    }
                    $(dom).css({ 'height' : _height, 'overflow-y' : _overflow }).attr('rows', _rows);
                    $(window).scrollTop(scrollTop);
                    cb && cb();
                };
            
            $textarea.css({ 
                'height' : !$textarea.val()? minHeight : textarea.scrollHeight,
                'line-height': rowHeight + 'px'
                // fixbug set 0 to rows getting an exception in firefox
            }).attr('rows', Math.max(Math.ceil(textarea.scrollHeight/rowHeight) , 1));
            
            // bind self defined event
            $textarea.bind('autoheight keyup' , function(){
                    resizeTextarea(this);
                })
                .attr('auto-height' , true)
                .trigger('autoheight');

        },

        /* 计算textarea字数
         * textarea  @param {Object} 需要计算字数的textarea
         * numDom    @param {} 显示计数的元素 
         * num       @param {Number} 规定的最长字数
         * callback  @param {Function} 回调函数
         * type      @param {} 是字还是字符
         * is_simple @param {Boolean} 是否只简单的反馈当前字数，默认为false
         */
        _countWords = function(textarea , numDom , num , callback , type , is_simple){
            type = !!type;
            function counts(){
                var value = textarea.value,
                    len = textarea.value.length,
                    inH;
                if(num - len >= 0 ){
                    inH = '还可以输入<em>' + (num - len) + '</em>字';
                    
                    if ( is_simple ) inH = '<em>' + len + '</em>';
                    
                    numDom.innerHTML = inH;
                }else{
                    inH = '已经超出<em style="color:red;">' + (len - num) + '</em>字';

                    if ( is_simple ) inH = '<em style="color:red;">' + len + '</em>';

                    numDom.innerHTML = inH;
                }
                callback && callback(num - len , len);
            }
            $(textarea).bind('countwords' , function(){
                counts();
            });
            if($.browser.msie && $.browser.version < 7){
                // set width fix for ie6
                var $textarea = $(textarea),
                    width = $textarea.width();
                if(width > 0)
                $textarea.css('width' , width);
            }

            $textarea.keyup(function(){
                $(this).trigger('countwords');
            });

            // run first time
            $(textarea).trigger('countwords');
        },
        _getPagePos = (function(){
            // 克隆元素样式并返回类
            var _cloneStyle = function(elem, cache) {
                if (!cache && elem['${cloneName}'])
                    return elem['${cloneName}'];
                var className, name, rstyle = /^(number|string)$/;
                var rname = /^(content|outline|outlineWidth)$/; //Opera: content; IE8:outline && outlineWidth
                var cssText = [], sStyle = elem.style;

                for (name in sStyle) {
                    if (!rname.test(name)) {
                        // fix firefox bug: if text input , the firefox focus the line-height 17px , get height style instead of line-height .what the fuck fuck fuck fuck fuck fuck ;
                        /*
                         1111111  11     11   1111111   11    11
                         11       11     11  11      1  11   11
                         1111111  11     11  11         11 11
                         11       11     11  11      1  11   11
                         11        1111111    1111111   11    11
                        */
                        var tempName = name;
                        if($.browser.mozilla && elem.tagName == "INPUT" && name == 'lineHeight'){
                            tempName = 'height';
                        }
                        var val = _getStyle(elem, tempName);
                        if (val !== '' && rstyle.test(typeof val)) { // Firefox 4
                            name = name.replace(/([A-Z])/g, "-$1").toLowerCase();
                            cssText.push(name);
                            cssText.push(':');
                            cssText.push(val);
                            cssText.push(';');
                        }
                    }
                }
                cssText = cssText.join('');
                elem['${cloneName}'] = className = 'clone' + (new Date).getTime();
                _addHeadStyle('.' + className + '{' + cssText + '}');
                return className;
            },

            // 向页头插入样式
            _addHeadStyle = function(content) {
                var style = _style[document];
                if (!style) {
                    style = _style[document] = document.createElement('style');
                    document.getElementsByTagName('head')[0].appendChild(style);
                }
                ;
                style.styleSheet && (style.styleSheet.cssText += content)
                        || style.appendChild(document.createTextNode(content));
            },
            _style = {},

            // 获取最终样式
            _getStyle = 'getComputedStyle' in window ? function(elem, name) {
                return getComputedStyle(elem, null)[name];
            } : function(elem, name) {
                return elem.currentStyle[name];
            },

            // 获取元素在页面中位置
            _offset = function(elem) {
                var box = elem.getBoundingClientRect(), doc = elem.ownerDocument, body = doc.body, docElem = doc.documentElement;
                var clientTop = docElem.clientTop || body.clientTop || 0, clientLeft = docElem.clientLeft
                        || body.clientLeft || 0;
                var top = box.top + (self.pageYOffset || docElem.scrollTop)
                        - clientTop, left = box.left
                        + (self.pageXOffset || docElem.scrollLeft) - clientLeft;
                return {
                    left : left,
                    top : top,
                    right : left + box.width,
                    bottom : top + box.height
                };
            };
            /**
             * 获取输入光标在页面中的坐标
             *
             * @param {HTMLElement} 输入框元素
             * @return {left: xxx, top: xxx, bottom: xxx} 返回left和top,bottom。[left, top]为光标顶部坐标，[left, bottom]为光标底部坐标。
             */
           return function(elem , index) {
                if (document.selection) { //IE Support
                    //elem.focus(); delete @ 2012-12-31 当输入框大于屏幕高度时，focus会导致在IE下会改变scrollTop的值
                    var currPos = _getPos(elem).start;
                    _setPos(elem , index);
                    var Sel = document.selection.createRange(),
                        _top = Sel.boundingTop + elem.scrollTop + document.documentElement.scrollTop;
                    _setPos(elem , currPos); // reset pos
                    return {
                        left : Sel.boundingLeft,
                        top : _top,
                        bottom : _top + Sel.boundingHeight
                    };
                }
                index = index===null? _getPos(elem).start : index;
                var cloneDiv = '{$clone_div}', cloneLeft = '{$cloneLeft}', cloneFocus = '{$cloneFocus}', cloneRight = '{$cloneRight}';
                var none = '<span style="white-space:pre-wrap;"> </span>';
                var div = elem[cloneDiv] || document.createElement('div'), focus = elem[cloneFocus]
                        || document.createElement('span');
                var text = elem[cloneLeft] || document.createElement('span');
                var offset = _offset(elem), focusOffset = {
                    left : 0,
                    top : 0
                };

                if (!elem[cloneDiv]) {
                    elem[cloneDiv] = div, elem[cloneFocus] = focus;
                    elem[cloneLeft] = text;
                    div.appendChild(text);
                    div.appendChild(focus);
                    document.body.appendChild(div);
                    focus.innerHTML = '@';
                    focus.style.cssText = 'display:inline-block;overflow:hidden;z-index:-100;word-wrap:break-word;';
                    div.className = _cloneStyle(elem);
                    div.style.cssText = 'visibility:hidden;display:inline-block;position:absolute;z-index:-100;word-wrap:break-word;overflow:hidden;';
                }
                ;
                div.style.left = offset.left + "px";
                div.style.top = offset.top + "px";
                var regObj = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '\n': '<br/>',
                    ' ': none
                }
                var strTmp = elem.value.substring(0, index).replace(/&/g, '&amp;').replace(new RegExp('[<>\\n\\s]' , 'g') , function($0){
                    return regObj[$0] || $0;
                });
                text.innerHTML = strTmp;

                focus.style.display = 'inline-block';
                try {
                    focusOffset = _offset(focus);
                } catch (e) {}
                ;
                focus.style.visibility = 'hidden';
                return {
                    left : focusOffset.left,
                    top : focusOffset.top - elem.scrollTop,
                    bottom : focusOffset.bottom - elem.scrollTop
                };
            };
        })();

    return {
        getPos: _getPos,
        setPos: _setPos,
        setText: _insertText,
        getPagePos: _getPagePos,
        moveTo: _moveTo,
        autoHeight: _autoHeight,
        countWords: _countWords,
        toHead: _toHead,
        toTail: _toTail
    }
});