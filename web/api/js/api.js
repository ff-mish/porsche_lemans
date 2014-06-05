/*
 * api model
 */
define(function( require , exports , model ){
    var $ = require('jquery');
    var code = 'status';
    var msg = 'message';
    // 公开的API
    // 签名：ajax = function ( api, data, successHandler, errorHandler, completeHandler ) {}
    // 备注：POST操作默认要登录，GET操作默认不需要登录
    //       默认是POST操作

    // 配置
    var _api = {

    };

    // 内部API
    var _unloginErrorNum = -2000;
    var _needRefresh     = {};

    function _isFormatUrl ( url ){
        return !!/#\[.*\]/.test( url );
    }
    function _load ( api , data , success , error , complete ) {
        if ( typeof data == "function" ) {
            return arguments.callee(api, {} , data, success, error);
        }
        if( typeof data == "string" ){
            data = LP.query2json( data );
        }
        var ajaxConfig = _api[api];
        if ( !ajaxConfig ) { 
            //return console && console.error( api + ' api is not exists!' ); 
            ajaxConfig = {};
            ajaxConfig.u = api;
        }

        var method = ajaxConfig.method || "";
        if ( method == "")  {
            var res = /get/i.exec(api);
            method = (res && res.index == 0) ? "GET" : "POST";
        } else {
            method = method.toUpperCase();
        }

        error = error || ajaxConfig.error;

        data = LP.mix( ajaxConfig.data || {} , data );
        var doAjax = function () {
            $.ajax({
                  url      : _isFormatUrl( ajaxConfig.u ) ? LP.format( ajaxConfig.u , data ) : ajaxConfig.u
                , data     : data
                , type     : method
                , dataType : ajaxConfig.dataType || 'json'
                , cache    : ajaxConfig.cache || false
                , global   : ajaxConfig.alertOnError === false || ajaxConfig.global === false ? false : true
                , error    : error
                , complete : complete
                , timeout  : ajaxConfig.timeout
                , success: function(e) {
                    if ( e && typeof e == "string" ) {
                        success(e);
                    } else {
                        _callback( e , api , success , error,  ajaxConfig , doAjax );
                    }
                }
            });
        }

        if ( ajaxConfig.needLogin === true || (ajaxConfig.needLogin !== false && method !== "GET") ) {
            // 如果不为GET的话，则默认是要登录的
            _execAfterLogin( doAjax , api );
        } else {
            doAjax();
        }
    }

    // err
    // msg
    function _callback ( result, api, success, error, config , ajaxFn ) {
        if ( !result ) return;
        var alertOnError = config.alertOnError;

        var error_no = result[code];
        if ( error_no ) {
            if( alertOnError !== false ){
                // 如果是未登录错误，弹出登录框
                if( error_no == _unloginErrorNum ){
                    // TODO ..  show login tempalte
                    /*
                    require.async('login' , function( exports ){
                        exports.login( ajaxFn );
                    });
                    */
                    return;
                }
                console && console.log( result[msg] || _api[api].m + _e('出错啦，请稍候重试...')  );
                //LP.error( result[msg] || _api[api].m + _e('出错啦，请稍候重试...') );
            }
            error && error( result[msg] , result );
        } else if ( success ) {
            success( result );
        }

        // 用于判断页面是否需要刷新
        if( _needRefresh[api] ) {
            _needRefresh[api] = false;
            // remove url hash
            setTimeout(function() { location.href = location.href.replace(/#.*/,''); } , 1000);
        }
    }

    function _execAfterLogin ( cb , api ) {
        if( !LP.isLogin() ) {
            if ( _api[api].forceNoRefresh != true )
                _needRefresh[api] = true;
            // require login
            /*
            require.async('login' , function( exports ){
                exports.login( cb );
            });
            */
            require.async('util' , function( exports ){
                exports.trigger('login' , cb );
            });

        } else if (cb) {
            cb();
        }
    }

    $(document).ajaxError(function(evt, xhr, ajaxOptions, thrownError) {
        return;
        try{
            if ( xhr.status == 200 || thrownError.match(/^Invalid JSON/)) {
                LP.alert( _e(' (*´Д｀*) 系统出错了。请反馈给我们。'), 3000 );
            } else if ( thrownError !== "" ) {
                // 请求被Canceled的时候，thrownError为空【未验证】。这时候直接忽略。
                LP.alert( _e('发生了未知的网络错误，请稍后重试。') , 3000);
            }
        } catch(e) {};
    });

    // for model
    exports.ajax = _load;
    exports.get = function( api , data , success , error , complete ) {
        if( !_api[api] ){
            _api[api] = {u: api , method: 'GET'}
        }
        _load( api , data , success , error , complete );
    }
    exports.post = function( api , data , success , error , complete ) {
        if( !_api[api] ){
            _api[api] = {u: api , method: 'POST'}
        }
        _load( api , data , success , error , complete );
    }
});
