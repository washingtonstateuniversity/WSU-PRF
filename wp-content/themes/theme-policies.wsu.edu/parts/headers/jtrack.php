 <hr/><hr/>
    <h1>jTrack -- a Google Analytics Plugin || Examples</h1>
    
   
    
    <hr/><hr/>
    <h4 style="color:rgb(255,184,28)">Note: all clicks are suppressed to keep only the GA events and actions </h4>
    <hr/>
    <h2>Deferred loading of Google Analytics</h2>
    <code>
    <pre>
// Load Google Analytics script and track page views
&lt;script type="text/javascript"&gt;
    $.jtrack({ 
    	load_analytics:{
            account:'UA-xxx-xxx',
    	    options:{onload: true, status_code: 200}
        }
    });
&lt;/script&gt;
    </pre>
    </code>
		
	<blockquote>Why have a choice? Why postpone the GA from loading before the page loads? If you only want to count when a user truly saw the page, then that <code class="inlinecode">onload</code> option is the way to go. This approach will help reduce false click traffic. </blockquote>
		
