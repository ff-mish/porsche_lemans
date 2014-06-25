$(function ($) {
    if (!Detector.webgl) {
        $('body').addClass('no-webgl');
        setTimeout(function(){
            $('#my_video_0').parent().addClass('video_wrap').css({'height':'85%','top':'60px'});
            $(window).trigger('resize');
        },500);
        //Detector.addGetWebGLMessage();
    } else {
        var COLORS = ['red', '#3366cc','#666666']; //线颜色：0=weibo 1=twitter 2=其他
        var THREEColors=[new THREE.Color(COLORS[0]), new THREE.Color(COLORS[1]), new THREE.Color(COLORS[2])];

        var years=[];
        var container = document.getElementById('container');
        var globe = new DAT.Globe(container, {imgDir:'image/', pointSize:1.5, colorFn: function (label) {
            return THREEColors[label];
        }});

        var settime = function (globe, t) {
            return function () {
                new TWEEN.Tween(globe).to({time: t / years.length}, 500).easing(TWEEN.Easing.Cubic.EaseOut).onUpdate(function(){
                    //$("#hourBox").css({left:100*(data.length-1-this.time*years.length)});
                }).start();
                renderTo( t / years.length + 1 / years.length );

                // var y = document.getElementById('hour' + years[t]);
                // if (y.getAttribute('class') === 'hour active') {
                //     return;
                // }
                // var yy = document.getElementsByClassName('hour');
                // for (var i = 0; i < yy.length; i++) {
                //     yy[i].setAttribute('class', 'hour');
                // }
                // y.setAttribute('class', 'hour active');
            };
        };
        var timer = null;
        var currpos = 0;
        var isPlay = false;;
        var togglePlay = function(){
            if( isPlay ){
                parse();
            }
            else {
                play();
                isPlay = true;
                $('#playBtn').html('Pause');
            }
        }
        var parse = function(){
            isPlay = false;
            clearTimeout( timer );
            $('#playBtn').html('Play');
        }
        var play = function(){
            currpos += 1/24/60;
            if( currpos >= 1 ){
                currpos = 0;
                renderTo(0);
            } else {
                globe.rotate(1);
                renderTo( currpos );
            }
            timer = setTimeout( play , 1000 / 60 );
        }

        var renderTo = function( percent ){
            currpos = percent;
            $('#hourProcess').css('width' , currpos * 100 + '%');

            new TWEEN.Tween(globe).to({time: currpos - 1/24},20).start();
            // render hour
            $('.hour').eq( ~~( currpos * 24 ) ).addClass('active')
                .siblings()
                .removeClass('active');
            // render seconds
            var h = $('.hour').eq( ~~( currpos * 24 ) )
                .html()
                .replace('h' , '');
            var m = ~~ ( ( currpos * 24 - ~~( currpos * 24 ) ) * 60 );
            m = m < 10 ? '0' + m : m;
            $('#playTime').html( h + ':' + m );
        }

        TWEEN.start();

        $.ajax({ url: '/globe/data/background.json', type: "GET", async: true, cache: false, dataType: "json", success: function (background) {
            globe.addBackground(background);
            $.ajax({ url: '/globe/data/24h.json', type: "GET", async: true, cache: false, dataType: "json", success: function (ret) {
                if (ret && ret.status == 0) {
                    data = ret.data;

                    var hour = 0;
                    hour = ( 15 + hour ) % 24;

                    $('<span>', {id: 'hour' + hour, class: 'hour', text: hour + 'h'})
                        .appendTo($('div#hourBox')).click(settime(globe, -1));
                        //.mouseover(settime(globe, i));
                    //globe.addData([0], {format: 'legend', name: hour, animated: true});


                    for (var i = 0; i < data.length; i++) {
                        var hour = parseInt( data[i][0] );
                        hour = ( 15 + hour ) % 24;

                        hour == hour == 0 ? 24 : hour;
                        if( hour < 10 ){hour = '0' + hour}

                        years.push(hour);
                        $('<span>', {id: 'hour' + hour, class: 'hour', text: hour + 'h'})
                            .appendTo($('div#hourBox')).click(settime(globe, i));
                            //.mouseover(settime(globe, i));
                        globe.addData(data[i][1], {format: 'legend', name: hour, animated: true});
                    }
                    globe.createPoints();
                    settime(globe, -1)();
                    globe.animate();

                    //var w=$( "#hourBox" ).width()*2;
                    var w=(100*(data.length-1+0.5)+100)*2;
                    // $( "#hourContainer" ).width(w).css('margin-left',-w/2+'px');
                    $('#hourBar').fadeIn();
                    $( "#hourBox").show();//.appendTo($( "#hourContainer" )).css({left:100*(data.length-1)});
                    // $( "#hourContainer" ).append('<div class="cover-left"></div><div class="cover-right"></div>');
                    $('#playBtn').click(togglePlay)
                        .trigger('click');
                    document.body.style.backgroundImage = 'none'; // remove loading
                }
            }});
        }});
    }
});
