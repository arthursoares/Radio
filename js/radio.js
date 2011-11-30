soundManager.flashVersion = 9;
soundManager.preferFlash = true;
soundManager.useHighPerformance = false;
soundManager.wmode = 'transparent';
soundManager.useFastPolling = true;
soundManager.url = '/wp-content/plugins/radio/swf/';
soundManager.debugMode = false;

var PP_CONFIG = {
  autoStart: false,
  playNext: true,
  useThrottling: false,
  usePeakData: true,
  useWaveformData: false,
  useEQData: false,
  useFavIcon: false,
  useMovieStar: true
}
	
soundManager.onready(function() {
	playlist = [];
	
	// primeira musica
	var list = $("#radio-programas > ul > li").toArray();
	var elemlength = list.length;
	var randomnum = Math.floor(Math.random()*elemlength);
	var randomitem = list[randomnum];
	loadPrograma($(randomitem).attr("rel"), true, false, 1);
	first_music = 1;
	// fim primeira musica
	
	// events
	$('#radio-play').click(function(){
		if ($(this).attr("class") == "radio-play") {
			if (first_music) {
				soundManager.play('radioclasta');
				first_music = 0;
			}
			else {
				soundManager.resume('radioclasta');
			}
		}
		else {
			soundManager.pause('radioclasta');
		}
	});
	$('#radio-next').click(function(){
		playNext();
		return false;
	});
	$('#radio-prev').click(function(){
		playPrev();
		return false;
	});
	$('#radio-programas > ul > .lista-playlist').click(function(){
		loadPrograma($(this).attr("rel"), false);
		return false;
	});
	is_sliding = 0;
	$('#radio-progresso').mousedown(function(e){
		is_sliding = 1;
		setPosition(e, this);
	}).mouseup(function(){
		is_sliding = 0;
	}).mouseout(function(){
		is_sliding = 0;
	}).mousemove(function(e) {
		if (is_sliding) {
			setPosition(e, this);
		}
	});
	$('#radio-mm').click(function(){
		mm();
		return false;
	});
	$('#radio-like').click(function(){
		var btn = $(this);
		if (btn.attr("class") == "btn like"){ var like = 1; }
		else { var like = 0; }
		$.post('/wp-content/plugins/radio/like.php', {id:$(this).attr("rel"), like:like}, function(data){
			if (data != "error") {
				$(btn).removeClass("like");
				$(btn).removeClass("liked");
				$(btn).addClass(data);
			}
		});
		return false;
	});
	vol_is_sliding = 0;
	$('#radio-volume').mousedown(function(e){
		vol_is_sliding = 1;
		setVolume(e, this);
	}).mouseup(function(){
		vol_is_sliding = 0;
	}).mouseout(function(){
		vol_is_sliding = 0;
	}).mousemove(function(e) {
		if (vol_is_sliding) {
			setVolume(e, this);
		}
	});
});
function setVolume(e, o){
	var x = e.pageX - $(o).offset().left;
	var volume = (x/$(o).width()*100)-5;
	if (volume <= 100) {
		soundManager.setVolume('radioclasta', volume);
		$('#radio-volume-current').css("width", volume+"%");
	}
}
// ajaxify
$(document).ready(function(){
	createEvents_js();
});
function setPosition(e, o){
	var x = e.pageX - $(o).offset().left;
	var percent = (x/$(o).width()*100);
	var position = ((percent*total_length)/100);
	soundManager.setPosition('radioclasta', position);
}
function createEvents_js(){
	FB.XFBML.parse();
	twttr.widgets.load();
	// embed post
	$('.radio-musica').click(function(){
		var rel = $(this).attr("rel").split(":");
		loadPrograma(rel[0], true, true, $(this).closest("li").attr("class").substr(7), rel[1]);
		//loadPlaylist(rel[1], true, true, $(this).closest("li").attr("class").substr(7));
		return false;
	});
}
// abre programa
function loadPrograma(id, open, play, tracknum, playlist) {
	if (open) {
		$('#radio-playlist').load('/wp-content/plugins/radio/ajax-playlists.php', {id:id}, function(){
			var list = $("#radio-playlist > ul > li").toArray();
			if (!playlist) { playlist = $(list[0]).attr("rel"); }
			loadPlaylist(playlist, true, play, tracknum);
			$('#radio-programas').find('li[rel='+id+']').addClass("active");
			triggerPrograma();
		});
	}
	// preview
	else {
		$('#radio-playlist').hide();
		$('#radio-playlist-preview').show();
		$('#radio-playlist-preview').load('/wp-content/plugins/radio/ajax-playlists.php', {id:id}, function(){
			$('#radio-playlist-preview li').click(function(){
				loadPlaylist($(this).attr("rel"), false, false, 1);
				return false;
			});
		});
	}
}
// abre playlist
function loadPlaylist(id, open, play, tracknum){
	// current
	if (open) {
		$('#radio-musicas').load('/wp-content/plugins/radio/ajax-musicas.php', {id:id}, function(){
			$('#radio-musicas').show();
			$('#radio-musicas-preview').hide();
			if (play) {
				first_music = 0;
			}
			if (!currentPlaylist() || play) {
				if (!tracknum) {
					tracknum = $('#radio-musicas > ol > li').size();
				}
				$('#radio-playlist').find('li[rel='+id+']').addClass("active");
				toPlay($('#radio-musicas > ol > li:nth-child('+tracknum+') > a'));
			}
			$('#radio-musicas > ol > li > a').click(function(){
				$('#radio-musicas > ol > li > a').filter('.active').removeClass('active');
				$(this).addClass("active");
				first_music = 0;
				toPlay($(this));
				return false;
			});
		});
	}
	// preview
	else {
		$('#radio-musicas').hide();
		$('#radio-musicas-preview').show();
		$('#radio-musicas-preview').load('/wp-content/plugins/radio/ajax-musicas.php', {id:id}, function(){
			$('#radio-musicas-preview li a').click(function(){
				loadPlaylist(id, true, true, $('#radio-musicas-preview li').index($(this).parent())+1);
				
				$('#radio-playlist').html('');
				$($('#radio-playlist-preview').html()).appendTo($('#radio-playlist')).each(function(){ triggerPrograma(); });
				$('#radio-playlist').show();
				$('#radio-playlist-preview').hide();
				return false;
			});
		});
	}
}
function triggerPrograma (){
	$('#radio-playlist > ul > .lista-playlist').click(function() {
		if ($(this).attr("rel") != currentPlaylist()) {
			loadPlaylist($(this).attr("rel"), false, false, 1);
		}
		else {
			$('#radio-musicas').show();
			$('#radio-musicas-preview').hide();
		}
		$('#radio-playlist > ul > li > a').filter('.active').removeClass('active');
		$(this).addClass("active");
		return false;
	});
}
// minimiza ou maximiza
function mm (i) {
	var bt_mm = $('#radio-mm')
	if (bt_mm.text() == "-" || i == "close") {
		bt_mm.text("+");
		bt_mm.removeClass("min").addClass("max");
		$('#radio-more').hide('slow');
		$('#radio-playlist-preview').html("");
		$('#radio-playlist-preview').hide();
		$('#radio-playlist').show();
		$('#radio-musicas-preview').html("");
		$('#radio-musicas-preview').hide();
		$('#radio-musicas').show();
	}
	else {
		bt_mm.text("-");
		bt_mm.removeClass("max").addClass("min");
		$('#radio-more').show('slow');
	}
}
// tocar proxima
function playNext(){
	var next = $('#radio-musicas > ol > li:nth-child('+(currentMusic()+1)+') > a');
	if (!next.attr("href")) {
		loadPlaylist($('#radio-playlist > ul').find('.active').next().attr("rel"), true, true, 1);
	}
	toPlay(next);
}
// tocar anterior
function playPrev(){
	var prev = $('#radio-musicas > ol > li:nth-child('+(currentMusic()-1)+') > a');
	if (!prev.attr("href")) {
		loadPlaylist($('#radio-playlist > ul').find('.active').prev().attr("rel"), true, true, false);
	}
	else {
		toPlay(prev);
	}
}
// get cookie
function getCookie(c_name) {
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
{
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
}
// fix time
function checkTime(i) { if (i<10) { i="0" + i; } return i; }
// cria player
function toPlay(e){
	var li_pl = $('#radio-playlist').find('li[rel='+e.attr("rel")+']');
	$('#radio-playlist > ul > li').filter(".active").removeClass("active");
	li_pl.addClass("active");
	$('#radio-display').html("<strong>"+li_pl.text()+":</strong> "+$(e).text());
	$('#radio-like').attr("rel", e.attr("id_music"));
	$('#radio-like').removeClass("liked");
	$('#radio-like').addClass("like");
	var cookie = getCookie('radio_likes');
	if (cookie) {
		var cookie = cookie.split("|");
		for (i = 0; i < cookie.length; i++) {
			if (cookie[i] == e.attr("id_music")) {
				$('#radio-like').removeClass("like");
				$('#radio-like').addClass("liked");
			}
		}
	}
	$('#radio-musicas > ol > li > a').filter(".active").removeClass("active");
	soundManager.unload('radioclasta');
	soundManager.destroySound('radioclasta');
	soundManager.createSound({
		id:'radioclasta',
		url:e.attr("href"),
		onplay:function() {
			$('#radio-play').removeClass("radio-play");
			$('#radio-play').addClass("radio-pause");
			
			// TODO: GA track
		},
		onresume:function() {
			$('#radio-play').removeClass("radio-play");
			$('#radio-play').addClass("radio-pause");
		},
		onpause:function(){
			$('#radio-play').removeClass("radio-pause");
			$('#radio-play').addClass("radio-play");
		},
		onfinish:function() {
			soundManager._writeDebug(this.sID+' finished playing');
			playNext();
		},
		whileloading:function() {
			var loaded = (this.bytesLoaded/this.bytesTotal*100);
			$('#radio-progresso-loading').width(loaded+"%");
		},
		whileplaying:function() {
			var pos = (this.position/this.durationEstimate*100);
			total_length = this.durationEstimate;
			$('#radio-progresso-playing').width(pos+"%");
			music_length = new Date(this.position);
			$('#radio-time').text(checkTime(music_length.getMinutes())+":"+checkTime(music_length.getSeconds()));
			
		    $('#peak-box > span.l').css("margin-top", (13-(Math.floor(15*this.peakData.left))+'px'));
		    $('#peak-box > span.r').css("margin-top", (13-(Math.floor(15*this.peakData.right))+'px'));
		},
		onload:function() {
			$('#radio-progresso-playing').width("0%");
			total_length = this.duration;
		}
	});
	if (!first_music) { soundManager.play('radioclasta'); }
	e.addClass("active");
	_gaq.push(['_trackEvent', 'Radio', 'Play', li_pl.text()+" - "+$(e).text()]);
}
// retorna playlist atual
function currentPlaylist () {
	return $('#radio-playlist > ul').find("li.active").attr("rel");
}
// retorna musica atual
function currentMusic() {
	var cur = $('#radio-musicas > ol').find("li > a.active");
	return $('#radio-musicas > ol > li').index($(cur).parent())+1;
}
function currentPrograma () {
	return $('#radio-programas > ul').find("li.active").attr("rel");
}