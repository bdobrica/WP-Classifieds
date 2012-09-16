jQuery(document).ready(function(){
	var u='/wp-content/plugins/wp-classifieds',l=1,m=0,c=0;
	var w=function(s,h){ if(s){m=1;jQuery(window).scrollTop(0);jQuery('.wp-classifieds-shadow').css('opacity',0).height(jQuery(document).height()).width(jQuery(document).width()).animate({'opacity':0.7}, function(){jQuery('.wp-classifieds-window-content').html(h);jQuery('.wp-classifieds-window').show('slow');b();});}else{jQuery('.wp-classifieds-window').hide('slow');jQuery('.wp-classifieds-shadow').animate({'opacity':0}, function(){jQuery('.wp-classifieds-shadow').height(0).width(0);jQuery('.wp-classifieds-window-content').empty();m=0;});}};
	var b=function(){jQuery('.wp-classifieds-window-content a').each(function(i,a){jQuery(a).click(function(e){e.preventDefault();x(jQuery(a).attr('href'));});});jQuery('.wp-classifieds-window-content button').each(function(i,a){jQuery(b).click(function(e){e.preventDefault();var f=jQuery(b).closest('form');x(jQuery(f).attr('action'),jQuery(f).serialize(),1);});});f();};
	var f=function(){
jQuery('.wp-classifieds-upload-button').each(function(n,i){var fo=new SWFUpload({
flash_url:'/wp-includes/js/swfupload/swfupload.swf',
upload_url:u+'/ajax/actions/upload.php',
post_params:{'PHPSESSID':''},
file_size_limit:'1 MB',
file_types:'*.jpg',
file_type_description:'All Files',
file_upload_limit:5,
file_queue_limit:0,
custom_settings:{
	progressTarget:'',
	cancelButtonId:'',
	},
debug:false,

button_placeholder: i,
button_width: 61,
button_height: 22,
button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
button_cursor: SWFUpload.CURSOR.HAND,

file_queued_handler:fileQueued,
file_queue_error_handler : fileQueueError,
file_dialog_complete_handler : fileDialogComplete,
upload_start_handler : uploadStart,
upload_progress_handler : uploadProgress,
upload_error_handler : uploadError,
upload_success_handler : uploadSuccess,
upload_complete_handler : uploadComplete,
queue_complete_handler : queueComplete });});
		};
	var x=function(g,d,p){if(p)jQuery.get(g,d,function(h){if(m)jQuery('.wp-classifieds-window-content').html(h);else w(1,h);});else jQuery.get(g,d,function(h){if(m)jQuery('.wp-classifieds-window-content').html(h);else w(1,h);});};
	jQuery('body').prepend('<div class="wp-classifieds-shadow"></div><div class="wp-classifieds-window"><p class="wp-classifieds-window-title"></p><img class="wp-classifieds-window-close" src="'+u+'/icons/close.png" alt="" title="" /><div class="wp-classifieds-window-content"></div></div>');
	jQuery('.wp-classifieds').each(function(n,d){jQuery(d).append('<button class="wp-classifieds-search">Search</button><button class="wp-classifieds-add">Add</button><img src="'+u+'/icons/loading.gif" alt="" title="" />');jQuery.get(u+'/ajax/actions/list.php',{'c':c},function(h){jQuery(d).find('img').last().remove();jQuery(d).append(h);l=1;});});
	jQuery('.wp-classifieds-window-close').click(function(e){w(0);});
	jQuery('.wp-classifieds-add').click(function(e){x(u+'/ajax/actions/add.php');});
	jQuery(window).scroll(function(e){
		if ((jQuery(window).scrollTop()>(jQuery(document).height()-jQuery(window).height())-10) && l){
			jQuery('.wp-classifieds').each(function(n,d){
				l=0;jQuery(d).append('<img src="'+u+'/icons/loading.gif" alt="" title="" />');
				jQuery.get(u+'/ajax/actions/list.php',{'c':c++},function(h){jQuery(d).find('img').last().remove();jQuery(d).append(h);l=1;});
				});}});
	});
