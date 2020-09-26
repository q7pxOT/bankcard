//移动端弹出
function _alert(new_opt){
    var default_opt={
        type:0,content:'提示',time:1,
        style:'border:none;width:51%;height:auto;color:#222;',//background-color:rgba(0,0,0,0.5);color:#fff;
        success:function(elem){
                $(elem).find('.layui-m-layercont').css({padding:'0.7rem 0.5rem'});
        },
        end:function(){}
    };
    if(typeof new_opt=='string'){
        default_opt.content=new_opt;
        var opt=default_opt;
    }else{
        var opt=$.extend(default_opt,new_opt);
    }
    layer.open(opt);
}

//移动端ajax调用
function ajax(opt_p){
	var opt_default={
        type:'post',
        url:'',
        dataType:'json',
        data:{},
        xhrFields:{withCredentials:true},
        beforeSend:function(){},
        success:function(){},
        error:function(){},
        complete:function(){}
	};
	var token=getToken();
	if(token){
		opt_default.data={token:token};
	}
    var opt=$.extend(true,opt_default,opt_p);
	$.ajax(opt);
}

//文件上传
//<script type="text/javascript" src="public/layui/layui.js"></script>
function fileUpload(new_opt){
	var default_opt={
		url:global.appurl+'a=upload&token='+getToken(), //上传接口
		elem:''	//监听的元素
		//ext:'jpg|png|gif'	//允许的扩展类型
	}
	var opt=$.extend(default_opt,new_opt);
	layui.upload.render(opt);
}

//移动端分页获取数据
function dataPage(opt){
	if(window.IS_PAGEING){
		return false;
	}
	var default_opt={};
	var opt=$.extend(default_opt,opt);
	opt.data=$.extend(opt.data,{page:window.NOW_PAGE?window.NOW_PAGE:1});
	ajax({
		url:opt.url,
		data:opt.data,
		beforeSend:function(){
			window.IS_PAGEING=true;
			var loading_txt='加载中...';
			if(opt.loading&&opt.loading.txt){
					loading_txt=opt.loading.txt;
			}
			$('.moreBtn').html(loading_txt).show();
			if(opt.loading){
					_alert({type:2,content:''});
			}
			if(opt.beforeSend){
					opt.beforeSend();
			}
			$('.noData').remove();
		},
		success:function(json){
			if(json.code!='1'){
					_alert(json.msg);
					return false;
			}
			if(json.data.pages>0&&json.data.page<=json.data.pages){
					$('.noData').remove();
					$('.moreBtn').html('点击加载更多').show();
			}else{
					$('.moreBtn').hide();
					$('.moreBtn').before('<div class="noData">没有更多了</div>');
			}
			window.NOW_PAGE=json.data.page;
			if(opt.success){
					opt.success(json);
			}
			setTimeout(function(){
					window.IS_PAGEING=false;
			},500);
		},
		error:function(){
			if(opt.error){
					opt.error();
			}else{
					_alert('获取数据失败');
			}
		}
	});
}

//禁止移动端下拉回弹事件
function preventDefault(){
	document.addEventListener('touchmove',function(event){
		event.preventDefault();
	}, false);
}

//获取用户信息
function getUserinfo(cb){
	ajax({
		url:global.appurl+'c=Login&a=userinfo',
		success:function(json){
			if(json.code!=1){
				location.href=global.appurl+'c=Login';
				return;
			}
			cb(json.data);
		}
	});
}