//###前后台公共方法###

function trim(str,char='',type=''){
	if(char){
		if(type == 'left') {
			return str.replace(new RegExp('^\\'+char+'+', 'g'), '');
		}else if (type == 'right') {
			return str.replace(new RegExp('\\'+char+'+$', 'g'), '');
		}
		return str.replace(new RegExp('^\\'+char+'+|\\'+char+'+$', 'g'), '');
	}
	return str.replace(/^\s+|\s+$/g, '');
}

function extend(src1,src2){
	return $.extend(true,src1,src2);
}

//获取本地存在数据表
function getLocalTable(tbName){
	var data={};
	var local=localStorage.getItem(tbName);
	if(!local){

	}else{
		data=JSON.parse(local);
	}
	return data;
}

//更新本地存储数据表
function updateLocalTable(tbName,dobj){
	var data=getLocalTable(tbName);
	data=$.extend(true,data,dobj);
	var local=JSON.stringify(data);
	localStorage.setItem(tbName,local);
	return data;
}

function bufferToStr(buffer) {
	var array=new Uint8Array(buffer);
	var out, i, len, c;
	var char2, char3;

	out = "";
	len = array.length;
	i = 0;
	while(i < len) {
		c = array[i++];
		switch(c >> 4)
		{ 
		case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
			// 0xxxxxxx
			out += String.fromCharCode(c);
			break;
		case 12: case 13:
			// 110x xxxx   10xx xxxx
			char2 = array[i++];
			out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
			break;
		case 14:
			// 1110 xxxx  10xx xxxx  10xx xxxx
			char2 = array[i++];
			char3 = array[i++];
			out += String.fromCharCode(((c & 0x0F) << 12) |
						((char2 & 0x3F) << 6) |
						((char3 & 0x3F) << 0));
			break;
		}
	}
	return out;
}

function strToBuffer(str,callback){
	var b = new Blob([str],{type:'text/plain'});
	var r = new FileReader();
	r.readAsArrayBuffer(b);
	r.onload = function (){
		callback.call(null,r.result);
	}
}

function ucfirst(str){
	let tmp_str=str.toLowerCase()
	return tmp_str.replace(tmp_str[0],tmp_str[0].toUpperCase())
}

function jsonEncode(data){
	return JSON.stringify(data);
}

function jsonDecode(str){
	return JSON.parse(str);
}

function isWeiXin() {
	var ua = window.navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		return true;
	} else {
		return false;
	}
}

//短信验证码按钮倒计时
function smsTimer(obj){
	if(obj.attr('is-timer')){
		return false;
	}
	var ori_str=obj.val();
	var is_input=true;
	if(!ori_str){
		ori_str=obj.text();
		is_input=false;
	}
	if(window.sms_timer){
		clearInterval(window.sms_timer);
	}
	var sms_t=60;
	window.sms_timer=setInterval(function(){
		sms_t--;
		var str_flag='';
		if(sms_t<0){
			sms_t=30;
			str_flag=ori_str;
			clearInterval(window.sms_timer);
			obj.attr('is-timer','');
			if(is_input){
				obj.val(str_flag);
			}else{
				obj.html(str_flag);
			}
		}else{
			var str_flag=sms_t+'秒后获取';
			if(is_input){
				obj.val(str_flag);
			}else{
				obj.html(str_flag);
			}
			obj.attr('is-timer','1');
		}
	},1000);
}

//倒计时
function formatSeconds(value) {
	var secondTime = parseInt(value);// 秒
	var minuteTime = 0;// 分
	var hourTime = 0;// 小时
	if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
		//获取分钟，除以60取整数，得到整数分钟
		minuteTime = parseInt(secondTime / 60);
		//获取秒数，秒数取佘，得到整数秒数
		secondTime = parseInt(secondTime % 60);
		//如果分钟大于60，将分钟转换成小时
		if(minuteTime > 60) {
			//获取小时，获取分钟除以60，得到整数小时
			hourTime = parseInt(minuteTime / 60);
			//获取小时后取佘的分，获取分钟除以60取佘的分
			minuteTime = parseInt(minuteTime % 60);
		}
	}
	var result = "" + parseInt(secondTime) + "秒";

	if(minuteTime > 0) {
		result = "" + parseInt(minuteTime) + "分" + result;
	}
	if(hourTime > 0) {
		result = "" + parseInt(hourTime) + "小時" + result;
	}
	return result;
}

/////////////////////////////////////////////////////////

//获取token
function getToken(){
	local=getLocalTable(global.tableName);
	var token=local[global.tokenName];
	return token;
}