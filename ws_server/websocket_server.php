<?php
	$dbhost = "127.0.0.1";	//数据库地址
	$dbname = "chat_1";		//数据库表名称
	$dbuser = "";			//数据库账号
	$dbpass = "";			//数据库密码
	$DB = new PDO("mysql:host=".$dbhost.";dbname=".$dbname,$dbuser,$dbpass);
	
	/**
		查询一条:
			$DB->query("SELECT * FROM  WHERE  ")->fetch();
		查询多条:
			$DB->query("SELECT * FROM  ")->fetchAll();
		改:
			$DB->exec("UPDATE `admin` SET  WHERE ");
		增:
			$DB->exec("INSERT INTO `users` (`user`) VALUES ('12345')");
		删:
			$DB->exec("DELETE FROM users WHERE  ");
	**/
	
	$server = new swoole_websocket_server("0.0.0.0", 345);
	$server->on('open', function (swoole_websocket_server $server, $request) {
		global $DB;
		// 用户链接websocket事件
		// 线程ID -> $request->fd
		// 链接时传的值 -> $request->get
		// 发送消息给用户 -> $server->push(id,data)
		$id = $request->fd;
		$session_key = $request->get['session_key'];
		
		//查用户信息
		$session = $DB->query("SELECT * FROM session WHERE session_key='".$session_key."' ")->fetch();
		//修改用户状态为已链接且绑定socket_id
		$DB->exec("UPDATE `session` SET socket_id='".$id."',online='1' WHERE session_key='".$session_key."' ");
		
		echo $session['name']." -> ".$id." <start>";
		
		
		//统计所有在线用户,包括刚链接的自己
		$num = $DB->query("SELECT count(*) FROM session WHERE socket_id>0 AND online='1'")->fetch()['count(*)'];
		//遍历所有在线用户信息
		$online = $DB->query("SELECT socket_id FROM session WHERE socket_id>0 AND online='1' ")->fetchAll();
		foreach($online as $val){
			if ($server->isEstablished($val['socket_id'])) {
				$server->push($val['socket_id'],json_encode([
					"type"=>"users_login",
					"name"=>$session['name'],
					"online_num"=>$num
				],JSON_UNESCAPED_UNICODE));
			}
		}
		
	});
	$server->on('message', function (swoole_websocket_server $server, $frame){
	   	global $DB;
	    //服务器收到用户信息后的动作
	    //线程ID -> $frame->fd
	    //接收的数据 -> $frame->data
	  	$id = $frame->fd;
		$data = $frame->data;
		
		if(mb_strlen($data,'utf-8')<1){
			$server->push($id,json_encode([
				"type"=>"send_err",
				"message"=>"请勿发送空消息~"
			],JSON_UNESCAPED_UNICODE));
		}else if(mb_strlen($data,'utf-8')>200){
			$server->push($id,json_encode([
				"type"=>"send_err",
				"message"=>"文字太多啦！目前仅支持200个字符"
			],JSON_UNESCAPED_UNICODE));
		}else{
			//转义所有html标签
			$data = htmlspecialchars($data);
			
			//查用户信息
			$session = $DB->query("SELECT id,name FROM session WHERE socket_id='".$id."' ")->fetch();
			
			$time = @time();
			
			$online = $DB->query("SELECT socket_id FROM session WHERE socket_id>0 AND online='1' ")->fetchAll();
			foreach($online as $val){
				if ($server->isEstablished($val['socket_id'])) {
					$server->push($val['socket_id'],json_encode([
						"type"=>"new_text",
						"name"=>$session['name'],
						"text"=>$data,
						"time"=>$time
					],JSON_UNESCAPED_UNICODE));
				}
			}
			
			//保存消息记录到mysql
			$DB->exec("INSERT INTO `chat_logs` (`session_id`,`time`,`msg`) VALUES ('".$session['id']."','".$time."','".$data."')");
			
		}
		
			
		
	});
	
	$server->on('close', function ($ser, $fd) {
		global $DB;
		global $server;
		//用户断开socket事件
		//线程ID -> $fd
		
		//修改用户信息为离线
		$DB->exec("UPDATE  `session` SET online='0',socket_id='0' WHERE socket_id='".$fd."'");
		
		//统计所有在线用户
		$num = $DB->query("SELECT count(*) FROM session WHERE socket_id>0 AND online='1'")->fetch()['count(*)'];
		//遍历所有在线用户信息
		$online = $DB->query("SELECT socket_id FROM session WHERE socket_id>0 AND online='1' ")->fetchAll();
		foreach($online as $val){
			if ($server->isEstablished($val['socket_id'])) {
				$server->push($val['socket_id'],json_encode([
					"type"=>"users_logout",
					"online_num"=>$num
				],JSON_UNESCAPED_UNICODE));
			}
		}
		
		
	    
	});
	
	
	$server->start();