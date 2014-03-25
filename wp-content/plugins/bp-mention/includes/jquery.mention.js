function setuppreview(el){
	var jq=jQuery;
	/* do setup only if textarea has an id to avoid complications*/
	if (jq(el).attr('id')!= ''){
		/*generating the preview html */
		/*
		var html = 	"<div class='wdw_ta_preview' style='display:none;' id='wdw_ta_preview_"+ jq(el).attr('id') +"'><h4>Preview</h4><div id='wdw_ta_preview_content_"+ jq(el).attr('id') +"'></div>";
		jq(el).after(html);
		var preview = "wdw_ta_preview_content_"+ jq(el).attr('id');
		
		jq(el).focus(function(){
			jq(this).next('div.wdw_ta_preview').show(200);
		});
		jq(el).blur(function(){
			jq(this).next('div.wdw_ta_preview').hide(200);
		});
		*/
		jq(el).bind('textchange', function (event, previousText) {
			//jq("#"+preview).html(jq(this).val());
			//lets replace @something with <span>something</a>
			var input = jq(this).val();
			var diff = input.substring(previousText.length, input.length);
			switch(diff){
				case '@':
					initMention(diff, el);
					break;
				case ' ':
					stopMentions(el);
					break;
				default:
					updateMention(diff, el);
			}
			
			var result = input.replace(/@([a-z\d_-]+)/ig, '<span>$1</span>');
			//jq("#"+preview).html(result);
		});
		
	}
}
var m_calling = false;
var load_autosuggest = false;
function initMention(diff, el){
	var jq = jQuery;
	jq(el).after("<div class='m_a_s_container'><ul id='a_s_ul_"+jq(el).attr('id')+"'><li><a>loading..</a></li></ul></div>");
	updateMention(diff, el);
	load_autosuggest = true;
}
function updateMention(diff, el){
	if(!m_calling && load_autosuggest==true){
		var data = {
			action: 'wdw_mentions_autoload',
			search: diff,
			limit: 10
		};
		
		m_calling = true;
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jq.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response) {
				
				wdw_updateAutoSuggetList(el, response);
				/*var result = jQuery.parseJSON(response);*/
				/*alert(result);*/
				m_calling = false;
			}
		});
	}
}
function stopMentions(el){
	var jq = jQuery;
	m_calling = false;
	load_autosuggest = false;
	var id = 'a_s_ul_'+jq(el).attr('id');
	jq("#" + id).parent().remove();
}
function wdw_updateAutoSuggetList(el, response){
	var jq = jQuery;
	var id = jq(el).attr('id');
	jq("#a_s_ul_"+ id +" > li").remove();
	
	var list = "";
	var result = jQuery.parseJSON(response);
	if(result.status == 0){
		jq.each(result.friends, function(i, object){
			var method = 'wdw_append_u_name('+id+', '+object.name+')';
			list += "<li><a onclick='wdw_append_u_name(\""+id+"\",\""+ object.name+"\")'>"+object.fullname+"(@"+object.name+")"+"</a></li>";
		});
	}
	else{
		list = "<li>nothing found..</li>";
	}
	jq("#a_s_ul_"+jq(el).attr('id')).append(list);
}
function wdw_append_u_name(el, handle){
	var jq = jQuery;
	var str = jq("#"+ el).val();
	var n = str.lastIndexOf("@");
	var r = str.substring(0, n);
	r += "@" + handle;
	jq("#" + el).val(r);
	stopMentions(jq("#" + el));
}