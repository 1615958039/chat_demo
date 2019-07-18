<template>
	<div>

		<div class="head">
			<el-row class="heads">

				<el-col :span="3" class="heads-t">
					<span @click="refresh_s" class="el-icon-refresh-right"></span>
				</el-col>

				<el-col :span="18" class="heads-title">
					<span class="heads-title-text">
						聊天室<br>
						<span class="heads-title-span">{{online_num}}人在线</span>
					</span>
				</el-col>

				<el-col :span="3" class="heads-t">
					<span @click="demo" class="el-icon-s-custom"></span>
				</el-col>


			</el-row>
		</div>


		<div class="slimScrollDiv" id="logs">
			<!-- 聊天记录丢这里 -->
			<div v-bind:scroll="demo" class="content">

        <div  v-for="item in logs" v-bind:key="item.index">

          <!-- 这里是用户进入聊天室 -->
          <div v-show="item.type=='users_login'" class="cen-tag">
				<el-tag type="info" size="mini">
				<font color='#409EFF'><b>{{item.name}}</b></font>&nbsp;加入群聊
            </el-tag>
          </div>

          <!-- 这里是加载新消息按钮 -->
          <div v-show="item.type=='get_news'" class="cen-tag">
            <el-tag type="warning" size="mini" style="margin-bottom: 10px;">
              <font @click="get_news" color="">获取历史消息</font>
            </el-tag>
          </div>

          <!-- 用户自己发消息 -->
          <div v-show="(item.type=='new_text' && item.name==name)" class="right">
            <div class="author-name">
              {{item.name}} &nbsp;<small class="chat-date">{{item.time | date}}</small>
            </div>
            <div class="chat-message">{{item.text}}</div>
          </div>

          <!-- 其他人说话 -->
          <div v-show="(item.type=='new_text' && item.name!=name)" class="left">
            <div class="author-name">
              {{item.name}} &nbsp;<small class="chat-date">{{item.time | date}}</small>
            </div>
            <div class="chat-message active">{{item.text}}</div>
          </div>



        </div>
			</div>
		</div>

		<div class="foot" id="foot">

      <div v-show="dips == true" class="dips">
        <el-tag @click="dips_c" type="" effect="dark" size="small " class="dips-range">{{dips_num}}</el-tag>
      </div>

			<el-row class="inputs">

				<el-col :span="19" class="inputs-ipt">
					<el-input maxlength="200" minlength="1" v-model="inputs" @keyup.enter.native="send" ></el-input>
				</el-col>
				<el-col :span="5" class="inputs-bt">
					<el-button type="primary" :disabled="is_send" @click="send">发送</el-button>
				</el-col>

			</el-row>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'index',
		data () {
			return {

        // 配置区 --- start ---
        websocket_server: "ws://47.102.204.145:345",  //websocket的服务器地址
        session_api: "http://47.102.204.145:81",  //php接口服务器地址
        // 配置区 --- end ---

				socket: {},//WebSocket对象
				inputs: '',//聊天输入框的内容
				logs: [],//当前聊天数据json
				is_send: false,	//发送按钮是否禁止点击
				fullscreenLoading: false, //加载框是否启动
				online_num: 0,//当前在线用户
				name: "",//用户昵称
				session_key: "",//用户登陆状态标识
				user_socket_id: 0,//用户的socketID
				is_refresh: false,//是否为刷新状态
        dips: false,  //新消息提示框
        dips_num: 1,  //新消息数量

			}
		},
		filters: {
			date(val){	//时间戳转日期 -> H:m
				let date = new Date(val*1000);
				function tk(val){
					if(val<10){
						return "0" + val;
					}else{
						return  val;
					}
				}
				return tk(date.getHours()) + ":" + tk(date.getMinutes());
			}
		},
		methods: {
			demo(){	//测试代码。页面右上角点击事件
				let that = this;
				//console.log("距离底部理论距离" +(153+document.body.scrollHeight-window.screen.height) )
				//console.log("距离底部实际距离" +document.documentElement.scrollTop)
				// console.log(JSON.parse(JSON.stringify(that.logs)))
				let now = document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;

				this.$alert('该按钮事件还未编写哦~' + that.dips + "<br>" + "距离底部理论距离" +(153+document.body.scrollHeight-window.screen.height) + "距离底部实际距离" + now, '提示框' , {
					confirmButtonText: '确定',
					customClass: "dilogs",
					callback: action => {
						if(that.dips == true){
							that.dips = false;
						}else{
							that.dips = true;
						}
					}
				});

			},
			send(){	//用户发送新消息
				let that = this;
				that.is_send = true;//开启发送中状态
				let inputs = that.inputs;
				let socket = that.socket;
				socket.send(inputs);
			},
			refresh_s(){//重新链接socket服务器
				if(confirm("是否需要刷新服务器链接状态，发不出消息时使用！")){
					const that = this;
					const socket = that.socket;
					that.is_refresh = true;
					socket.close();
					that.socket = {};
					setTimeout(function(){
						that.get_socket()
					},100)
				}

			},
			setCookie(name,value){	//写入数据进cookie
				var Days = 30;
				var exp = new Date();
				exp.setTime(exp.getTime() + Days*24*60*60*1000);
				document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
			},
			getCookie(name){	//读取cookie
				var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
				if(arr=document.cookie.match(reg)){
					return unescape(arr[2]);
				}else{
					return null;
				}
			},
			setname(){//页面初次启动 -> 设置昵称
				const that = this;
				this.$prompt('请先设置一个昵称~', '欢迎进入聊天室', {
					confirmButtonText: '确定',
					showCancelButton:false,
					showClose:false,
					closeOnClickModal:false,
					closeOnPressEscape:false,
					closeOnHashChange:false,
					customClass: "dilogs",
					center:true,
					inputPattern: /^[A-Za-z0-9\u4E00-\u9FA5_\?\(\)\[\]\=\.\,]{0,}$/,
					inputErrorMessage: '昵称格式不正确'
				}).then(({ value }) => {
					if(value.length<2 || value.lenth>10){
						alert("昵称为2到10个字符哦~")
						setTimeout(function(){
							that.setname()
						},100)
					}else{
						//提交昵称注册账号
						this.$http.post(that.session_api+"/api/api.php?type=get_session",{
							name: value
						}).then(function(data){
							if(data.data.code==1 || data.data.code=="1"){//下一步,链接socket
								that.setCookie("session_key",data.data.session_key);
								that.init();
							}else{
								alert(data.data.message);
								setTimeout(function(){
									that.setname()
								},100)
							}
						},function(){
							alert("链接服务器失败！请检查您的网络设置");
							setTimeout(function(){
								that.setname()
							},100)
						})
					}
				}).catch(() => {
					alert("请输入昵称哦~");
					setTimeout(function(){
						that.setname()
					},100)
				});
			},
			get_socket(){	//链接socket并设置各事件
				const that = this;
				const session_key = that.session_key;
				const url = that.websocket_server+'?session_key='+session_key;
				that.is_send = false;
				let socket = new WebSocket(url);

				that.socket = socket;
				socket.onopen = function(res){
					that.is_refresh = false;
					that.start_socket(res);
				}
				socket.onmessage = function(res){
					//console.log(res)
					that.socket_get_message(res.data);
				}
				socket.onclose = function(){
					//这里是断开链接提示 -> 重连
					if(that.is_refresh==false){	//非手动重新连接
						that.is_get_socket()
					}
				}
				socket.onerror = function(res){
					console.log("websocket报错了 -> ")
					console.log(res)
				}
			},
			is_get_socket(){	//被动断开socket事件
				const that = this;
				this.$confirm('与服务器断开链接！是否重新连接或刷新页面', '提示', {
					confirmButtonText: '重链',
					cancelButtonText: '刷新',
					customClass: "dilogs",
					type: 'warning'
				}).then(() => {
					const socket = that.socket;
					that.is_refresh = true;
					socket.close();
					that.socket = {};
					setTimeout(function(){
						that.get_socket()
					},100)
				}).catch(() => {
					if(confirm("是否确认刷新页面？聊天数据将无法恢复")){
						location.href = location.href;
					}else{
						that.is_get_socket();
					}
				});
			},
			start_socket(res){ //socket链接成功事件
				this.logs.unshift({"type":"get_news"})
			},
			socket_get_message(res){	//接收到服务器的消息
				let that = this;
				let data = JSON.parse(res);
				that.logs.push(data);
				if(data.type=="new_text" && data.name==that.name){ //用户自己发布消息
					that.is_send = false;
					that.inputs = "";
					that.goto_page_bottom()
				}else if(data.type=="new_text" && data.name!=that.name){ //其他用户发布消息
					if(that.dips == false){ //需要进行判断
						that.is_goto_page_bottom();
					}else{
						that.dips_num = that.dips_num + 1;
					}
				}else if(data.type == "users_login"){
					//更新在线人数
					that.online_num = data.online_num;
				}else if(data.type == "users_logout"){
					that.online_num = data.online_num;
				}
			},
      is_goto_page_bottom(){  //新消息提示或直接显示
        let that = this;
        let bottom = 153 + ( document.body.scrollHeight - window.screen.height );
        let now = document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
        let c = bottom - now;
        if(c > 150 ){ //显示提示框并监控页面
          if(that.dips == false){
            that.dips = true;
          }
          setTimeout(function(){
            that.is_goto_page_bottom();
          },200)
        }else{  //直接滚动去底部
          that.dips = false;
          that.dips_num = 1;
          that.goto_page_bottom();
        }

      },
      goto_page_bottom(){//让页面平滑滚动到最底部
        setTimeout(function() {
          //延迟500ms执行，等vue解析完再滚
          window.scrollTo({
            top: 153 + ( document.body.scrollHeight - window.screen.height ),
            behavior: "smooth"
          });
        }, 200);
      },
      get_news(){ //获取服务器消息缓存
        let that = this;
        let data = that.logs;
        // data.splice(0,1);
        data = JSON.parse(JSON.stringify(data));
        data.splice(0,1);
        let time = 0;
        var i = 0;
        while(i < data.length){
          if( data[i]['type'] == "new_text"){
            break;
          }
          i = i + 1;
        }
        //console.log(data);
        if(i == data.length){
          time = Math.round(new Date().getTime()/1000);
        }else{
          time = data[i]['time'];
        }

        const loading = this.$loading({
          lock: true,
          text: '加载中...',
          spinner: 'el-icon-loading',
          background: 'rgba(0, 0, 0, 0.7)'
        });

        that.$http.post(that.session_api+"/api/api.php?type=get_logs",{
          last_time: time,
          session_key: that.session_key
        }).then(function(res){
          loading.close();
          let list = JSON.parse(JSON.stringify(res.data.data));
          if(list.length < 1){

            //已无可加载内容！
            that.logs = data;

          }else{

            list = list.concat(data);
            that.logs = list;
            // console.log(list);

            that.logs.unshift({"type":"get_news"})

          }
        },function(){
          loading.close();
          this.$alert('加载聊天记录失败，请检查您的网络设置！', '系统提示', {
            confirmButtonText: '确定',
            customClass: "dilogs",
            callback: action => {
            }
          });
        })


      },
			init(){ //页面加载事件
				const that = this;
				const session_key = that.getCookie("session_key");
				if(session_key==null || session_key=="" || session_key==undefined){
					//首次登陆 -> 设置昵称进行注册
					that.setname();
				}else{
					//已有登陆记录 -> 获取昵称
					this.$http.post(that.session_api+"/api/api.php?type=get_name",{
						session_key: session_key
					}).then(function(data){
						if(data.data.code==1 || data.data.code=="1"){
							//获取name成功，写入变量
							that.name = data.data.name;
							that.session_key = session_key;
							that.get_socket();	//链接socket
						}else{
							//登陆状态失效，重设cookie
							that.setCookie("session_key","");
							alert(data.data.message);
							that.init();
						}
					},function(){
						this.$alert('网络链接失败！请检查您的网络设置', '系统提示', {
							confirmButtonText: false,
							showCancelButton:false,
							showClose:false,
							closeOnClickModal:false,
							closeOnPressEscape:false,
							closeOnHashChange:false,
							customClass: "dilogs",
							callback: action => {
								location.href = location.href
							}
						});
					})
				}
			},
      dips_c(){ //新消息提示框点击事件
        let that = this;
        that.dips = false;
        setTimeout(function(){
          that.dips = false;
        },100);
        that.dips_num = 1;
        that.goto_page_bottom();
      }
		},
		mounted(){
			this.init()
		}

	}
</script>

<style>
	#app{
		margin: 0;
		padding: 0;
		font-size: 13px;
		color: #676a6c;

	}
	div{
		display: block;
	}


	.dips{
		position: fixed;
		right: 10px;
		bottom: 55px;
	}
	.dips-range{
		border-radius:50px
	}


	.dilogs{
		width: 80%;
		margin-left: 10%;
		margin-right: 10%;
		margin-top: 150px;
	}

	.head{
		position: fixed;
		top: 0;
		left: 0;
		height: 48px;
		width: 100%;
		background-color: #409eff;
		z-index: 100;
		color: #fff;
	}
	.heads{
		margin-top: 0.25rem;
		margin-bottom: 0.25rem;
		padding-left: 0.5rem;
		padding-right: 0.5rem;
	}
	.heads-t{
		text-align: center;
		line-height: 41px;
		font-size: 20px;
	}
	.heads-title{
		height: 40px;
		line-height: 20px;
		text-align: center;
	}
	.heads-title-text{
		font-size: 16px;
		white-space:nowrap;

	}
	.heads-title-span{
		font-size: 9px;
	}



	.slimScrollDiv{
		height: auto;
		position: relative;
		width: auto;
	}
	.content{
		width: auto;
		padding: 2px 2px;
		overflow: hidden;
		padding-bottom: 40px;
		padding-top: 48px;
	}
	.left{
	    text-align: left;
		clear: both;
		padding-bottom: 20px;
	}
	.cen-tag{
		text-align: center;
		clear: both;
	}
	.author-name {
		font-weight: bold;
		margin-bottom: 3px;
		font-size: 11px;
	}
	.chat-date {
		opacity: 0.6;
		font-size: 10px;
		font-weight: normal;
	}
	.chat-message {
		float: right;
		padding: 5px 10px;
		border-radius: 6px;
		font-size: 11px;
		line-height: 14px;
		max-width: 80%;
		background: #f3f3f4;
		margin-bottom: 10px;
	}
	.active{
		float: left;
		background: #1ab394;
		color: #fff;
	}
	.right {
		text-align: right;
		clear: both;
	}
	.foot{
		position: fixed;
		left: 0;
		bottom: 0;
		width: 100%;
		background-color: #fff;
	}



	.inputs{
		margin-top: 0.25rem;
		margin-bottom: 0.25rem;
		padding-left: 0.5rem;
		padding-right: 0.5rem;
	}
	.inputs-ipt{
		padding-right: 0.125rem;
	}
	.inputs-bt{
		text-align: center;
	}

</style>
