LP.use(['jquery' , 'util' , 'validator' , 'api'] , function( $ , util , valid , api ){

	var $dateTip = $('#j-days-total');
	var validateDate = function( start , end ){
		if( start && end ){
			var now = new Date();
			if( start < ( now.getFullYear() + '' + ( now.getMonth() + 1 ) + '' + now.getDate() ) )
			if( start > end ){
				$dateTip
					.hide();
					//.html("开始时间不能大于结束时间");
				return "开始时间不能大于结束时间";
			} else {
				var stime = +new Date( start.substr(0 , 4) , start.substr(4 , 2) - 1 , start.substr(6 , 2) );
				var etime = +new Date( end.substr(0 , 4) , end.substr(4 , 2) - 1 , end.substr(6 , 2) );
				var days = ( ( etime - stime ) / 86400000  + 1);
				$dateTip.css('color' , '')
					.show()
					.html("共" + days + "天" );
				return true;
			}
		}
		$dateTip.show();
		return start ? "请选择结束时间" : "请选择开始时间";
	}
	$('input[name="time_start"],input[name="time_end"]').change(function(){
		valid.triggerValidator( 'time' );
	});

	// init country search input
	util.searchLoc($('#county-search') , function( data ){
        $('#county-search').val( data.name ).data( 'name' , data.name );
        $('input[name="country"]').val( data.id );
        valid.triggerValidator( 'country' );
    }, 'country');
	

	var val = valid.formValidator()
		.add( 
			valid.validator('time')
				.setTipDom('#J_date-tip')
				.addCallBack(function(){
                	var start = $('input[name="time_start"]').val().replace( /[-/]/g , "" );
					var end = $('input[name="time_end"]').val().replace( /[-/]/g , "" );
					var r = validateDate( start , end );
					if( typeof r == "string" )
						return r;
					return true;
                })
			)
		.add(
			valid.validator('first-name')
				.setRequired( _e('没有填入姓信息') )
			)
		.add(
			valid.validator('last-name')
				.setRequired( _e('没有填入名信息') )
			)
		.add(
			valid.validator('sex')
				.setRequired( _e('请选择称呼') )
				.setTipDom( '#J_sex-tip')
			)
		.add(
			valid.validator('country')
				.setRequired( _e('请从搜索下拉中选择国家') )
				.setTipDom( '#J_country-tip' )
		 	)
		.add(
			valid.validator('address')
				.setRequired( _e("请填写出发地") )
				.setTipDom( '#J_address-tip' )
			)
		.add(
			valid.validator('profily')
				.setRequired( _e('没有同意协议') )
				.setTipDom('#J_profily-tip')
			)
		.add(
			valid.validator('email')
				.setRequired( _e('电子邮箱必填') )
				.setRegexp( "email" ,"邮箱地址格式不对")
				.setTipDom('#J_email-tip')
			)
		.add(
			valid.validator('tel')
				.setRequired( _e('手机必填') )
				.setRegexp( "telephone" , "手机格式不对" )
				.setTipDom('#J_tel-tip')
			)
		.add( 
			valid.validator('bdesc')
				.setRequired( _e('行程描述必填') )
			);
	var isLoading = false;
	$('.order-form').submit(function(){
		if( isLoading ) return false;
		val.valid(function(){
			var data = LP.query2json( $('.order-form').serialize() );
			data.name = data['last-name'] + ' ' + data['first-name'];

			var start = data.time_start.split("-");
			data.time_start = (+new Date( start[0] , start[1] - 1,start[2] )) / 1000;

			var end = data.time_end.split("-");
			data.time_end = (+new Date( end[0] , end[1] - 1, end[2] )) / 1000;

			isLoading = true;
			api.post( "/order/submit" , data , function( r ){
				window.location.href = "/order/?id=" + r.data.id;
			}  , null , function(){
				isLoading = false;
			});
		});
		return false;
	});
});	