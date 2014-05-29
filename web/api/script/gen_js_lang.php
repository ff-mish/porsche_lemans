<?php

// get file
define( 'API_DIR' , __DIR__ . '/../' );

$file = API_DIR . 'js/lp.base.js';

// get content
$con = file_get_contents( $file );
preg_match_all( '/_e\(([\'"])([^)]+)\1\)/' , $con , $match );

// get lang file
$php_lang = array( 'en_us' , 'zh_cn' );

foreach ($php_lang as $lang ) {
	$arr = require $lang . '.php';

	foreach ($match[2] as $str) {
		if( !isset( $arr[ $str ] ) ){
			$arr[ $str ] = "";
		}
	}

	// gen js lang
	file_put_contents( API_DIR . 'js/lang/' . $lang . '.js' , 'var langs = ' . json_encode( $arr , true ) . ';' );
	file_put_contents( $lang . '.php' , "<?php \n return " . var_export( $arr , true ) . ';' );
}



// foreach ($variable as $key => $value) {
// 	# code...
// }

// $langFile = array( 'en_us.js' , 'zh_cn.js' );
// $r = var_export( array("a"=>1) , true );

//var_dump( $match );