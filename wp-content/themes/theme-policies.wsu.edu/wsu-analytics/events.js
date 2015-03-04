window.wsu_analytics.wsuglobal.events   = jQuery.merge( window.wsu_analytics.wsuglobal.events , [] );
window.wsu_analytics.app.events   = jQuery.merge( window.wsu_analytics.app.events , [] );
window.wsu_analytics.site.events   = jQuery.merge( window.wsu_analytics.site.events , [
	{
		element:"a.catpdf-download.single_posts",
		options:{
			action:'click',
			category:"download",
			label:"single post pdf download",
			overwrites:true
		}
	}
]);