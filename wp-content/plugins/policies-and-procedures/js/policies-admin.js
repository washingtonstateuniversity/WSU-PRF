/**
 * Handle the editing of announcement dates in the admin.
 */
(function( $ ) {
	
	function canInput( type ){
		var i = document.createElement("input");
		i.setAttribute("type", type);
		return i.type !== "text";
	}
	
	function make_maskes(){
		$.mask.definitions['~'] = "[+-]";

		$.each($('[type="date"]'),function(){
			var self = this;
			self.mask("99/99/9999",{completed:function(){ }});

			self.blur(function() {
			}).dblclick(function() {
				self.unmask();
			});
			var dateRange={
					minDate:"-50Y",
					maxDate:"+5Y",
					yearRange: ((new Date().getFullYear())-50)+':'+((new Date().getFullYear())+5)
				};
			var options = $.extend({ changeMonth: true,changeYear: true }, dateRange);
			self.datepicker(options);
		});

	}

	$(document).ready(function() {
		if( ! canInput( "date" )){
			make_maskes();
		}
	});

})( jQuery );
