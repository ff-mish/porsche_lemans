seajs.config({
  // 配置 shim 信息，这样我们就可以通过 require("jquery") 来获取 jQuery
  //plugins: ['shim']

  shim: {
    // for jquery
    jquery: {
      src: "../jquery/jquery-1.8.3.min.js"
      , exports: "jQuery"
    }
    ,raphaeljs: {
      src: "../raphael/raphaeljs.min.js"
      , deps :[ "../raphael/eve.js"]
      , exports: "Raphael"
    }
    ,handlebars: {
      src: "../handlebars/handlebars-v1.1.2.js"
      , exports: "Handlebars"
    }
    ,easing:{
      src: "../plugin/jquery.easing.1.3.js"
      , deps: ['jquery']
    }
  	,transit:{
  	  src: "../plugin/jquery.transit.min.js"
  	  , deps: ['jquery']
  	}
    ,isotope:{
      src: "../plugin/jquery.isotope.min.js"
      , deps: ['jquery']
    }
    ,jscrollpane:{    
      src: "../plugin/jquery.jscrollpane.js"
      , deps: ['jquery']
    }
    ,mousewheel:{
      src: "../plugin/jquery.mousewheel.js"
      , deps: ['jquery']
    }
    ,uiwidget: {
      src: "../plugin/jquery.ui.widget.js"
      , deps: ['jquery']
    }
    ,uicustom: {
        src: "../plugin/jquery-ui-1.10.3.custom.js"
        , deps: ['jquery']
    }
    ,fileupload:{
      src: "../plugin/jquery.fileupload.js"
      , deps: ['jquery','uiwidget']
    }
    ,form:{
      src: "../plugin/jquery.form.js"
      , deps: ['jquery']
    }
    ,validate:{
      src: "../plugin/jquery.validate.js"
      , deps: ['jquery']
    }
    ,"video-js":{
      src: "../video-js/video.js"
      , deps: ['../video-js/video-js.css']
    }
  }
  , alias: {
    api: '../api.js?_201402#'
    , panel: "../panel/panel"
    , suggestion: '../plugin/jquery.suggestion.js'
    , textareaUtil: '../plugin/jquery.textarea.js'
    , autoComplete: '../autocomplete/autoComplete'
    , validator: '../validator/validator'
  }
});