<?php
	error_reporting(0);
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: *");
	header("Content-Type: text/html;charset=utf-8");
	date_default_timezone_set("Asia/Shanghai");
	
	/* 数据库 -> IP，账号，密码，表名 */
	$loca = '127.0.0.1';
	$user = '';
	$pass = '';
	$name = 'chat_1';
	
	 
	/* 时间日期 */ 	$date = date("Y-m-d H:i:s"); 
	/* 时间戳 */		$time = time();
	
	
	$dbconnect  =  mysqli_connect($loca,$user,$pass,$name);
	if(!$dbconnect)die('服务器链接失败~');
	mysqli_set_charset($dbconnect,'UTF8');
	$sql = function($sqlstatement,$type='')use($dbconnect){
		if(strpos($sqlstatement,"count(*)")>0){
			$result = mysqli_query($dbconnect, $sqlstatement);
			$row = mysqli_fetch_assoc($result);
			return $row['count(*)'];
		}else if(strpos($sqlstatement,"select")===0 || strpos($sqlstatement,"SELECT")===0){
			$result = mysqli_query($dbconnect, $sqlstatement);
			if($type=="list"){
				$i=0;$arr=array();
				while($row = mysqli_fetch_assoc($result))$arr[$i++] = $row;
				return $arr;
			}
			return mysqli_fetch_assoc($result);
		}else if(strpos($sqlstatement,"UPDATE")===0 || strpos($sqlstatement,"update")===0 ||
				 strpos($sqlstatement,"INSERT")===0 || strpos($sqlstatement,"insert")===0 ||
				 strpos($sqlstatement,"DELETE")===0 || strpos($sqlstatement,"delete")===0){
			if($type=="get_last_id" && (strpos($sqlstatement,"INSERT")===0 || strpos($sqlstatement,"insert")===0)){
				$result = mysqli_query($dbconnect, $sqlstatement);
				if($result){
					return mysqli_insert_id($dbconnect);
				}else{
					return false;
				}
			}else{
				return mysqli_query($dbconnect, $sqlstatement);
			}
		}else{
			$result = mysqli_query($dbconnect, $sqlstatement);
			return mysqli_fetch_assoc($result);
		}
	};
	
	function ip(){
	    $realip = '';
	    if (isset($_SERVER)) {
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
	            foreach ($arr as $ip) {
	                $ip = trim($ip);
	                if ($ip != 'unknown') {
	                    $realip = $ip;
	                    break;
	                }
	            }
	        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $realip = $_SERVER['HTTP_CLIENT_IP'];
	        } else {
	            if (isset($_SERVER['REMOTE_ADDR'])) {
	                $realip = $_SERVER['REMOTE_ADDR'];
	            } else {
	                $realip = '0.0.0.0';
	            }
	        }
	    }
	    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	    return $realip;
	}
	
	
	
	// json返回数据
	function code($arr){
		die(json_encode($arr,JSON_UNESCAPED_UNICODE));
	}
	
	