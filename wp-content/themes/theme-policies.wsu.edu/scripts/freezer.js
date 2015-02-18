// JavaScript Document
(function($){
	$.fn.freezeGif = function(){
		return $.each($(this),function(i,v){
			var img = this;
			
			if($(img).parent('.freezeWrapped').length<=0){
				$(img).wrap('<div class="freezeWrapped"/>');
			}
			var wrapper=$(img).closest('.freezeWrapped');
			
			var width = $(img).width(),
				height = $(img).height(),
				min_width = $(img).css("min-width"),
				min_height = $(img).css("min-height"),
				attr,
				i = 0;
			var canvas = $('<canvas/>')
						.width(width)
						.height(height);
			wrapper.css({
				"width":width,
				"height":height,
				"min-width":min_width=="0px"?"auto":min_width,
				"min-height":min_height=="0px"?"auto":min_height,
				"position":"relative",
				"display":"inline-block",
			});
			var freeze = function() {
					wrapper.prepend(canvas);
					canvas.css({
						"width":width,
						"height":height,
						"min-width":min_width=="0px"?"auto":min_width,
						"min-height":min_height=="0px"?"auto":min_height,
					});
					canvas[0].getContext('2d').drawImage($(img)[0], 0, 0, width, height);
					for (i = 0; i < $(img)[0].attributes.length; i++) {
						attr = $(img)[0].attributes[i];
						if (attr.name !== '"') { // test for invalid attributes
							canvas.attr(attr.name, attr.value);
						}
					}
					$(img).addClass('frozen');
					$(img).css({"position":"absolute","display":"none"});
				},
				unfreeze = function() {
					wrapper.find('canvas').remove();
					$(img).css({"position":"initial","display":"initial"});
					$(img).removeClass('frozen');
				};
			
			if($(img).is('.frozen')){
				unfreeze();
			}else{
				if (img.complete) {
					freeze();
				} else {
					img.addEventListener('load', freeze, true);
				}
			}
		});
	}
})(jQuery);