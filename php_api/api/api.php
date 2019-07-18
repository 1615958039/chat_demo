<?php
	include("config.php");
	$_POST = json_decode(file_get_contents('php://input'),true);
	$type = $_GET['type'];
	
	if($type=="get_session"){
		//新建 SESSION 
		$name = $_POST['name'];
		preg_match_all('/[\x{4e00}-\x{9fa5}A-Za-z0-9_\?\(\)\[\]\=\.\,]/u',$name,$res);
		$text = '';
		foreach($res[0] as $val){
			$text = $text.$val;
		}
		$name = $text;
		
		if(mb_strlen($name,'utf-8')<2)code(["code"=>"0","message"=>"昵称需要设置两个字符以上哦"]);
		if(mb_strlen($name,'utf-8')>10)code(["code"=>"0","message"=>"昵称只能设置10个字符以内哦"]);
		
		if($sql("SELECT * FROM session WHERE name='".$name."' "))code(["code"=>"0","message"=>"该昵称已被使用"]);
		
		$session_key = substr(md5($time.$date.$name.rand(10000,999999)),8,16);
		
		$res = $sql("INSERT INTO `session` (`socket_id`,`session_key`,`name`) VALUES ('0','{$session_key}','{$name}')");
		
		if(!$res)code(["code"=>"0","message"=>"注册账号失败！请联系管理员"]);
		
		code(["code"=>"1","message"=>"注册session_key成功！","session_key"=>$session_key]);
	}
	
	else if($type=="get_logs"){
		
		$last_time = $_POST['last_time'];
		$session_key = $_POST['session_key'];
		//这里得写session_key的权限判断,这里省略
		
		$data = [];
		$res = $sql("SELECT * FROM chat_logs WHERE time<'".$last_time."' ORDER BY id DESC LIMIT 0,10","list");
		foreach($res as $val){
			array_unshift($data,[
				"type"=>"new_text",
				"name"=>$sql("SELECT name FROM session WHERE id='".$val['session_id']."' ")['name'],
				"text"=>$val['msg'],
				"time"=>$val['time']
			]);
		}
		
		code([
			"code"=>"1",
			"message"=>"success",
			"data"=>$data
		]);
	}
	
	else if($type=="get_name"){
		
		$session_key = $_POST['session_key'];
		$res = $sql("SELECT * FROM session WHERE session_key='".$session_key."' ");
		if(!$res)code(["code"=>"0","message"=>"登陆状态生效！请重新登陆"]);
		code(["code"=>"1","message"=>"success","name"=>$res['name']]);
		
	}
