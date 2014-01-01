
jQuery(document).ready(function() {

		// Add jQuery ui datepicker
		jQuery('.datepicker').datepicker({
			dateFormat: 'y-m-d'
		});
		
		// Select field select all event
		jQuery('.all-btn').click(function(){
			jQuery(this).siblings('select').find('option').attr("selected","selected");
		});
		
		// Shortcode insert event
		jQuery('.code-list ul li a').click(function(){
			var str = '';
			var item = jQuery(this).attr('rel');
			
			// Filter shortcodes
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
		
});