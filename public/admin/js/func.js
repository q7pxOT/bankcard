function getScreen(){
    var width = $(window).width();
    if(width > 1200){
        return 3; //大屏幕
    } else if(width > 992){
        return 2; //中屏幕
    } else if(width > 768){
        return 1; //小屏幕
    } else {
        return 0; //超小屏幕
    }
}

function showImg(ts){
    var obj=$(ts);
	var src=obj.attr('src');
	layer.photos({ photos: {"data": [{"src": src}]} });
}

function _alert(msg){
    var p1 = arguments[1] ? arguments[1] : {time:1500};
    var p2 = arguments[2] ? arguments[2] : function(){};
    layer.msg(msg,p1,p2);
}

//ajax调用
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
    var opt=$.extend(true,opt_default,opt_p);
	$.ajax(opt);
}

//文件上传
function fileUpload(new_opt){
	var default_opt={
		url:global.appurl+'c=Default&a=upload', //上传接口
		elem:''	//监听的元素
		//ext:'jpg|png|gif'	//允许的扩展类型
	}
	var opt=$.extend(default_opt,new_opt);
	layui.upload.render(opt);
}

function dataPage(opt){
    var def={
        elem: '#dataTable',
        //url: './json/table/user.js',
        method:'post',
        where:{token:getToken()},
        page:{
            limit:15,
            groups:7,
            theme:'mypage'
        },
        toolbar:false,
        autoSort:false,
        cellMinWidth: 30, //全局定义常规单元格的最小宽度，layui 2.2.1 新增
        parseData:function(res){
            if(res.code!=1){
                if(res.code=='-98'){
                    _alert(res.msg,{},function(){
                        location.href='/ht.php';
                    });
                    return;
                }else{
                    _alert(res.msg);
                }
            }
            var odata={};
            for(var i in res.data){
                if(i=='list'){
                    continue;
                }
                odata[i]=res.data[i];
            }
            return {
                "code": res.code=='1'?0:res.code, //解析接口状态
                "msg": res.msg, //解析提示文本
                "count": res.data.count, //解析数据长度
                "data": res.data.list, //解析数据列表
                "odata":odata
            };
        },
        cols: null,
        /*[
            
            [
                {field:'id', width:80, title: 'ID'},
                {field:'username', width:80, title: '用户名'},
                {field:'sex', width:80, title: '性别'},
                {field:'city', width:80, title: '城市'},
                {field:'sign', title: '签名', width: '30%', minWidth: 100}, //minWidth：局部定义当前单元格的最小宽度，layui 2.2.1 新增
                {field:'experience', title: '积分'},
                {field:'score', title: '评分'},
                {field:'classify', title: '职业'},
                {field:'wealth', width:137, title: '财富'},
                {field:'', width:180, title: '操作',toolbar:'#barItemAct'}
            ]*
        ]*/
        done:function(res, curr, count){
            //console.log(res);
        }
    }
    var options=$.extend(true,def,opt);
    layui.table.render(options);
}