<?php
include "settings.php";

$service_callback = ($_REQUEST["callback"]) ? $_REQUEST["callback"] : false;

function db(){
	return new mysqli(constant('db::host'),constant('db::user'),constant('db::pwd'),constant('db::db'));
}
function toKey( $num ) {
  return simple_encrypt( $num );
}
function toId( $num ) {
  return simple_decrypt( $num );
}
function san($s){
	return preg_replace("/[^A-Za-z0-9+\/=_-$]/", '', $s);
}
function simple_encrypt($text){
    return trim(
    	base64_encode(
		    	mcrypt_encrypt(
		    		MCRYPT_RIJNDAEL_256, 
		    		substr(constant('crypto::shaker'), 0, 32), 
		    		$text, MCRYPT_MODE_ECB, 
		    		mcrypt_create_iv(
		    			mcrypt_get_iv_size(
		    				MCRYPT_RIJNDAEL_256, 
		    				MCRYPT_MODE_ECB
		    			), 
		    			MCRYPT_RAND
		    		)
		    	)
		)
    ;
}
function simple_decrypt($text){
    return trim(
    	mcrypt_decrypt(
    		MCRYPT_RIJNDAEL_256, 
    		substr(constant('crypto::shaker'), 0, 32), 
    		base64_decode($text), 
    		MCRYPT_MODE_ECB, 
    		mcrypt_create_iv(
    			mcrypt_get_iv_size(
    				MCRYPT_RIJNDAEL_256, 
    				MCRYPT_MODE_ECB
    			), 
    			MCRYPT_RAND
    		)
    	)
    );
}
function jsonp( $data = array(), $callback = false ) {
	$json = json_encode($data);
	header("Content-type: application/" . ($callback == false ? "json" : "jsonp"));
	return $callback ? "$callback(" . $json . ");" : $json;
}
function default_error(){
	header("HTTP/1.0 500 Wrong Parameters");
	echo jsonp( array( 
		'type' => 'error', 
		'info' => 'wrong parameters'
	), $GLOBALS['service_callback'] );
	die();
}
function load($key, $appid){
	$mysqli = db();
	$id = toId( san($key), constant('crypto::shaker') );
	$app = san( $appid );
	if($result = $mysqli->query("SELECT content, created FROM " . constant('db::table') . "  WHERE `id` = $id AND `key` like '$app';")) {
		if( mysqli_num_rows ( $result ) > 0 ){
			header("HTTP/1.0 200 OK");
			while ($row = $result->fetch_assoc()) {
				echo jsonp( array( 
					'type' => 'success', 
					'info' => 'loaded', 
					'id' => $key, 
					'data' => base64_decode( $row["content"] ), 
					'created' => $row["created"] 
				), $GLOBALS['service_callback'] );
			}
		} else {
			header("HTTP/1.0 500 No Entries Found");
			echo jsonp( array( 
				'type' => 'error', 
				'info' => 'no entries found', 
				'id' => $key, 
				'key' => $app 
			), $GLOBALS['service_callback'] );
		}
		$result->free();
	} else {
		header("HTTP/1.0 500 Read Error");
		echo jsonp( array( 
			'type' => 'error', 
			'info' => 'read error', 
			'id' => $key, 
			'error' => $mysqli->error 
		), $GLOBALS['service_callback'] );
	}
	$mysqli->close();
}
function list_key($appid){
	$mysqli = db();
	$app = san( $appid );
	if($result = $mysqli->query("SELECT id, content, created FROM " . constant('db::table') . " WHERE `key` like '$app';")) {
		if( mysqli_num_rows ( $result ) > 0 ){
			$set = array();
			header("HTTP/1.0 200 OK");
			while ($row = $result->fetch_assoc()) {
				$set[] = array( 
					'id' => toKey( $row['id'], constant('crypto::shaker') ), 
					'content' => base64_decode( $row["content"] ), 
					'created' => $row["created"] 
				);
			}
			echo jsonp( array( 
				'type' => 'success', 
				'info' => 'loaded', 
				'key' => $app, 
				'data' => $set 
			), $GLOBALS['service_callback'] );
		} else {
			header("HTTP/1.0 500 No Entries Found");
			echo jsonp( array( 
				'type' => 'error', 
				'info' => 'no entries found', 
				'key' => $app 
			), $GLOBALS['service_callback'] );
		}
		$result->free();
	} else {
		header("HTTP/1.0 500 Read Error");
		echo jsonp( array( 
			'type' => 'error', 
			'info' => 'read error', 
			'id' => $key, 
			'error' => $mysqli->error 
		), $GLOBALS['service_callback'] );
	}
	$mysqli->close();
}
function update($content, $appid, $key){
	$mysqli = db();
	$id = toId( san($key), constant('crypto::shaker') );
	$app = san( $appid );
	if( 
		$mysqli->query("UPDATE " . constant('db::table') . " SET `content`='" . 
		base64_encode( $content ) . "' WHERE `id` = $id AND `key` like '$app';") 
	) {
		header("HTTP/1.0 200 OK");
		echo jsonp( array( 
			'type' => 'success', 
			'info' => 'updated', 
			'id' => $key  
		), $GLOBALS['service_callback'] );
	} else {
		header("HTTP/1.0 500 Error While Updating");
		echo jsonp( array( 
			'type' => 'error', 
			'info' => 'error while updating', 
			'id' => $key , 
			'error' => $mysqli->error 
		), $GLOBALS['service_callback'] );
	}
	$mysqli->close();
}
function insert($content, $appid){
	$mysqli = db();
	$app = san( $appid );
	if( 
		$mysqli->query("INSERT " . constant('db::table') . 
		" (`content`, `key`) VALUES ('" . base64_encode($content) ."','$app')") 
	){
		header("HTTP/1.0 200 OK");
		echo jsonp( array( 
			'type' => 'success', 
			'info' => 'inserted', 
			'id' => toKey($mysqli->insert_id, constant('crypto::shaker')) 
		), $GLOBALS['service_callback'] );
	} else {
		header("HTTP/1.0 500 Error While Inserting");
		echo jsonp( array( 
			'type' => 'error', 
			'info' => 'error while inserting', 
			'id' => false
		), $GLOBALS['service_callback'] );
	}
	$mysqli->close();	
}
// Calls
switch (true) {
	//Update an existing post
	case ( isset( $_REQUEST["data"] ) && isset( $_REQUEST["key"] ) && isset( $_REQUEST["id"] ) ):
		update( $_REQUEST["data"], $_REQUEST["key"], $_REQUEST["id"] );
		break;

	//Insert a new post
	case ( isset( $_REQUEST["data"] ) && isset( $_REQUEST["key"] ) ):
		insert( $_REQUEST["data"], $_REQUEST["key"] );
		break;

	//Load a post
	case ( isset( $_REQUEST["id"] ) && isset( $_REQUEST["key"] ) ):
		load( $_REQUEST["id"], $_REQUEST["key"] );
		break;

	//List all a posts from a key (!security problem!)
	case ( isset( $_REQUEST["key"] ) && isset( $_REQUEST["a"] ) && $_REQUEST["a"] == "list" && isset( $_REQUEST["auth"] ) ):
		(san($_REQUEST["auth"]) == simple_encrypt( $_REQUEST["key"] )) ? 
			list_key( $_REQUEST["key"] ) : 
			default_error();
		break;

	/**
	* TESTS
	**/
	//Resolve an ID from encrypted string
	case ( isset( $_GET["i"] ) && isset( $_REQUEST["auth"] ) ):
		header("HTTP/1.0 200 ID Test");

		echo jsonp( (san($_REQUEST["auth"]) == simple_encrypt( constant('crypto::debug') )) ?
			array( 
				'type' => 'test', 
				'info' => 'ID Test', 
				'key' => simple_encrypt( $_GET["i"] ) 
			) : 
			array( 
				'type' => 'error', 
				'info' => 'auth fail'
			)
			, $GLOBALS['service_callback'] 
		);	

		break;

	//Resolve a KEY from cleartext string
	case ( isset( $_GET["k"] ) && isset( $_REQUEST["auth"] ) ):
		header("HTTP/1.0 200 Key Test");

		echo jsonp( (san($_REQUEST["auth"]) == simple_encrypt( constant('crypto::debug') )) ?
			array( 
				'type' => 'test', 
				'info' => 'Key Test : ' . strlen($num), 
				'id' => simple_decrypt( $_GET["k"] ) 
			) : 
			array( 
				'type' => 'error', 
				'info' => 'auth fail'
			)
			, $GLOBALS['service_callback'] 
		);

		break;

	//Show basic usage examples if no arguments are supplied
	case ( 
		!isset( $_REQUEST["key"] ) && 
		!isset( $_REQUEST["a"] ) && 
		!isset( $_REQUEST["auth"] ) && 
		!isset( $_REQUEST["i"] ) && 
		!isset( $_REQUEST["id"] ) && 
		!isset( $_REQUEST["key"] ) 
	);
		include "info.html";
		break;

	//Default error
	default:
		default_error();
}