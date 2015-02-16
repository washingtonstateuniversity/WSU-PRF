jQuery(document).ready(function() {
	jQuery( "#tabs" ).tabs();
	jQuery( ".tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	jQuery( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	// Add jQuery ui datepicker
	jQuery('.datepicker').datepicker({
		dateFormat: 'y-m-d'
	});

	// Select field select all event
	jQuery('.all-btn').on("click",function(){
		jQuery(this).siblings('select').find('option').attr("selected","selected");
	});

	// Shortcode insert event
	jQuery('.code-list ul li a').on("click",function(){
		var str = '';
		var item = jQuery(this).attr('rel');

		// Filter shortcodes // should be from php @todo
		if( window.tinyMCE.activeEditor.editorId=='looptemplate' ) {
			var arr = ['title','excerpt','content','permalink','date','author','author_photo','author_description','status','featured_image','category','tags','comments_count'];
		} else if( window.tinyMCE.activeEditor.editorId=='bodytemplate' ) {
			var arr = ['loop','site_title','site_tagline','site_url','date_today','from_date','to_date','categories','post_count'];
		}
		// Insert shortcode to active tinyMCE field
		if( jQuery.inArray(item, arr)!=-1 ) {
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
	jQuery.each(jQuery('.useful [type="checkbox"]'), function(){
		var tar = jQuery(this);
		tar.on("click",function(){
			trigger_usage(tar);
		});
		trigger_usage(tar);
	});
	
	jQuery(".field-wrap .help").on('click',function(e){
		e.preventDefault();
		var self = jQuery(this);
		var block = self.closest('.field-wrap').find('.note_block');
		if(block.is(".active")){
			block.slideUp(250,"easeOutQuint",function(){ block.removeClass("active"); });	
		}else{
			block.slideDown(500,"easeOutQuint",function(){ block.addClass("active"); });	
		}
	});
	
	jQuery("#postdl").on("click",function(){
		var self = jQuery(this);
		var block = jQuery("#single_post_generation");
		if( self.is(":checked") ){
			block.slideDown(500,"easeOutQuint",function(){ block.addClass("active"); });
		}else{
			block.slideUp(250,"easeOutQuint",function(){ block.removeClass("active"); });
		}
	});
	
	
	jQuery(".alter_all").on("click",function(e){
		var self = jQuery(this);
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
	
	
});