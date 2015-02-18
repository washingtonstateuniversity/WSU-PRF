// JavaScript Document
(function($){
	$.wsuwp_catpdf={
		int:function(){
			$(function() {
				$.wsuwp_catpdf.ready();
			});			
		},
		ready:function(){
			$(document).ready(function() {
				$( "#tabs" ).tabs();
				$( ".tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
				$( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
			
				// Add jQuery ui datepicker
				$('.datepicker').datepicker({
					dateFormat: 'y-m-d'
				});
			
				// Select field select all event
				$('.all-btn').on("click",function(){
					var self = $(this);
					var selectbox = self.siblings('select');
					var options = selectbox.find('option');
					if(self.is('.ALL_ON')){
						self.removeClass("ALL_ON").addClass('ALL_OFF');
						options.removeAttr("selected");
						self.val("Select All");
					}else{
						self.addClass("ALL_ON").removeClass('ALL_OFF');
						options.attr("selected","selected");
						self.val("Deselect All");
					}
					
				});
			
				// Shortcode insert event
				$('.code-list ul li a').on("click",function(){
					var str = '';
					var item = $(this).attr('rel');
			
					// Filter shortcodes // should be from php @todo
					if( window.tinyMCE.activeEditor.editorId=='looptemplate' ) {
						var arr = ['title','excerpt','content','permalink','date','author','author_photo','author_description','status','featured_image','category','tags','comments_count'];
					} else if( window.tinyMCE.activeEditor.editorId=='bodytemplate' ) {
						var arr = ['loop','site_title','site_tagline','site_url','date_today','from_date','to_date','categories','post_count'];
					}
					// Insert shortcode to active tinyMCE field
					if( $.inArray(item, arr)!=-1 ) {
						str = '[' + item + ']';
						window.tinyMCE.execInstanceCommand(window.tinyMCE.activeEditor.id, 'mceInsertContent', false, str);
					}
				});
				
				function trigger_usage(jObj){
					if(jObj.attr('checked')){
						jObj.closest('.field').find('.inner').slideDown();
					}else{
						jObj.closest('.field').find('.inner').slideUp();
					}	
				}
				// Select field select all event
				$.each($('.useful [type="checkbox"]'), function(){
					var tar = $(this);
					tar.on("click",function(){
						trigger_usage(tar);
					});
					trigger_usage(tar);
				});
				
				$(".field-wrap .help").on('click',function(e){
					e.preventDefault();
					var self = $(this);
					var block = self.closest('.field-wrap').find('.note_block');
					if(block.is(".active")){
						block.slideUp(250,"easeOutQuint",function(){ block.removeClass("active"); });
					}else{
						block.slideDown(500,"easeOutQuint",function(){ block.addClass("active"); });
					}
				});
				
				$("#postdl").on("click",function(){
					var self = $(this);
					var block = $("#single_post_generation");
					if( self.is(":checked") ){
						block.slideDown(500,"easeOutQuint",function(){ block.addClass("active"); });
					}else{
						block.slideUp(250,"easeOutQuint",function(){ block.removeClass("active"); });
					}
				});
				
				
				$(".alter_all").on("click",function(e){
					var self = $(this);
					var parent = self.closest('.select_area');
					var checkboxes = parent.find('[type="checkbox"]');
					if(self.is('.ALL_ON')){
						self.removeClass("ALL_ON").addClass('ALL_OFF');
						checkboxes.removeAttr("checked");
						self.removeAttr("checked");
						parent.find('.select.block label span').text("Select");
					}else{
						self.addClass("ALL_ON").removeClass('ALL_OFF');
						checkboxes.attr("checked",true);
						self.attr("checked",true);
						parent.find('.select.block label span').text("Deselect");
					}
				});
				
				
				$("#catpdf-shortcode").on('click',function(e){
					e.preventDefault();
					var tag = "";
					$.each($("input,select","#catpdf_form"),function(){
						var self = $(this);
						var name = self.attr("name");
						var type = self.attr("type");
						if( typeof self.attr("name")!=="undefined" && name!="" && type!="submit"){
							if(self.is('select')){
								var selected = "";
								$.each($('option:selected',self),function(){
									selected+=(selected!=""?",":"")+($(this).val());
								});
								if(selected!=""){
									tag += (tag!=""?" ":"")+(name.split("[]").join('')+"='"+selected+"'");
								}
							}else{
								var val = self.val();
								if(val!=""){
									tag += (tag!=""?" ":"")+(name.split("[]").join('')+"='"+val+"'");
								}
							}
						}
					});
					tag = "[catpdf"+(tag==""?"":" ")+tag+"]";
					$("#shortcode_box").text(tag);
					if( ! $("#shortcode_area").is(":visible") ){
						$("#shortcode_area").fadeIn(500);
					}
				});
				
				
				
				
				
				
			});
		},
	};
	$.wsuwp_catpdf.int();
})(jQuery);

