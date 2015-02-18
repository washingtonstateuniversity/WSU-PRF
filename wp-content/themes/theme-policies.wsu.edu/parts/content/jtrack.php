		<h2>Custom event tracking</h2>
	
		<pre><code>
	$(function(){
		$('a#custom-event').click(function() {
			$.jtrack.trackEvent('category', 'action', 'label', 'value');
			//or
			/*
			$(this).jtrack({
				category : function(element) {},
				action : function(element) {},
				label : function(element) {},
				value : function(element) {},
				//more options are available 
			});
			*/
		});
	});
		</code></pre>