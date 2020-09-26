//音效模块
var Music=function(path){
	this.path=arguments[0] ? arguments[0] : 'public/music/';
	this.source=[];
	
	this.audioContext=null;
	this.audioBuffers=[];
	this.isloaded=false;
	
    this.init=function() {
        this.audioBuffers = [];
        window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext || window.msAudioContext;
		if(window.AudioContext){
			this.audioContext = new window.AudioContext();
		}
    }
    
     
	
    this.stopSound=function(name) {
        var buffer = this.audioBuffers[name];
        if (buffer) {
            if (buffer.source) {
                buffer.source.stop(0);
                buffer.source = null;
            }
        }
    }
    
    this.AndroisIos = function(){
        let u = navigator.userAgent;
        let isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
        let isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isIOS) {
          return true;
        } else {
        	return false;
      }
    }
      
	
    this.playSound=function(name, isLoop) {
		if(!this.audioContext){
			return;
		}
		var _this=this;
		var name=name.trim('/');
		if (_this.AndroisIos()) {
			window.location.href="protocol://native?code=playSound&data=1";
		}else{
			if(!_this.audioBuffers[name]){
			var url=_this.path+name+'.mp3';
			_this.loadAudioFile(url,name,function(){
				_this.playDo(name,isLoop);
			});
		}else{
			_this.playDo(name,isLoop);
		}
			
			
		}
    }
	
	this.playDo=function(name,isLoop){
		var _this=this;
        var buffer = _this.audioBuffers[name];
        if (buffer) {
            if (!window.WeixinJSBridge) {
                buffer.source = null;
                buffer.source = _this.audioContext.createBufferSource();
                buffer.source.buffer = buffer.buffer;
                buffer.source.loop = false;
                var gainNode = _this.audioContext.createGain();
                if (isLoop == true) {
                    buffer.source.loop = true;
                    gainNode.gain.value = 0.7;
                } else {
                    gainNode.gain.value = 1.0;
                }
                buffer.source.connect(gainNode);
                gainNode.connect(_this.audioContext.destination);
                buffer.source.start(0);
            } else {
                WeixinJSBridge.invoke('getNetworkType', {}, function(e){
                    buffer.source = null;
                    buffer.source = _this.audioContext.createBufferSource();
                    buffer.source.buffer = buffer.buffer;
                    buffer.source.loop = false;
                    var gainNode = _this.audioContext.createGain();
                    if (isLoop == true) {
                        buffer.source.loop = true;
                        gainNode.gain.value = 0.7;
                    } else {
                        gainNode.gain.value = 1.0;
                    }
                    buffer.source.connect(gainNode);
                    gainNode.connect(_this.audioContext.destination);
                    buffer.source.start(0);
                });
            }
        }
	}
	
    this.initSound=function(arrayBuffer, name,callBack) {
		var _this=this;
        this.audioContext.decodeAudioData(arrayBuffer, function(buffer){
            _this.audioBuffers[name] = {
                "name": name,
                "buffer": buffer,
                "source": null
            };
			/*
            if (Databus.pauseMusic==1&&name == "bgm") {
                _this.isloaded = true;
                _this.playSound(name, true);
            }*/
			if(callBack){
				callBack();
			}
        }, function(e) {
            console.warn('Error decoding file');
        });
    }
	
    this.loadAudioFile=function(url, name,callBack) {
		var _this=this;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function(e){
            _this.initSound(xhr.response, name,callBack);
        }
        xhr.send();
    }

	//预先加载
	/*
    this.load=function() {
        if (this.isloaded)
            return;
        for (var i in this.source) {
			var name=this.source[i];
			var url=this.path+name+'.mp3';
            this.loadAudioFile(url,name);
        }
    }*/
	
	//播放背景音乐
	this.playBgm=function(bgmName){
		var _this=this;
		if(Databus.pauseMusic==0){

            /*
            if(this.audioContext.state=='running'){
                this.audioContext.suspend();
            }else{
                this.audioContext.resume();
            }*/

			if(!isWeiXin()){
				_this.playSound(bgmName,true);
			}else{
                if (window.WeixinJSBridge) {
                    WeixinJSBridge.invoke('getNetworkType', {}, function (e) {
                        _this.playSound(bgmName,true);
                    }, false);
                } else {
                    document.addEventListener("WeixinJSBridgeReady", function () {
                        WeixinJSBridge.invoke('getNetworkType', {}, function (e) {
                            _this.playSound(bgmName,true);
                        });
                    }, false);
                }
            }
		}
    }
    
    this.stopBgm=function(bgmName){
        this.stopSound(bgmName);
    }
	
	//播放音效
    this.play=function(name, sex) {
        if (Databus.pauseSound==1) {
            return;
        }
		sex=typeof sex ==='undefined'?0:sex;
		var fname='';
		if(sex==1){
			fname=name+'_man';
		}else if(sex==2){
			fname=name+'_woman';
		}else{
			fname=name;
		}
		this.playSound(fname);
    }
	
	this.init();
}
