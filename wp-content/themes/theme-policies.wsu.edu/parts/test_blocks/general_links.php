<div class="test_unit">
	<h2>General link tracking tests</h2>
	<div class="row halves gutter narrow pad-bottom short"> 
		<div class="column one">
			<h4>Internal/external link tracking</h4>
			<div class="normal">
			  <a href="/abcdef" data-action="click">Internal link</a><br/>
			  <a href="http://www.complex.com" data-action="click">External link</a><br/>
			  <a href="http://beastieboys.com/" target="_blank" data-action="click">External link in new window</a><br/>
			  <a href="#" data-action="click">Named link</a><br/>
			  <a href="javascript:void(0)" data-action="click">JavaScript link</a>
			</div>
			<h4>Configured link tracking</h4>
			<h5>Targeting</h5>
			<h6>_blank</h6>
			<div class="normal">
			  <a href="/abcdef" target="_blank" data-action="click">Internal link <code>target="_blank"</code></a><br/>
			  <a href="http://www.complex.com" target="_blank" data-action="click">External link <code>target="_blank"</code></a><br/>
			</div>
			<h6>_self</h6>
			<div class="normal">
			  <a href="/abcdef" target="_self" data-action="click">Internal link <code>target="_self"</code></a><br/>
			  <a href="http://www.complex.com" target="_self" data-action="click">External link <code>target="_self"</code></a><br/>
			</div>
		</div>
		<div class="column two">
			<h4>Metadata extraction using callbacks</h4>
			<div class="sidebar">
			  <a href="http://www.sidebar.com" data-action="click">Sidebar link</a>
			</div>
			<div class="footer">
			  <a href="http://www.footer.com" data-action="click">Footer link</a>
			</div>
			<div class="complex">
			  <a href="http://www.complex.com" class="complex" data-action="click">Complex link</a>
			</div>
			
			<h4>Form tracking</h4>
			<div class="normal">
			  <form>
					<label>text<input type="text" name="text-test"/></label><br/>
					<label>date<input type="date" name="date-test"/></label><br/>
					<label>tel<input type="tel" name="tel-test"/></label><br/>
					<label>checkbox<input type="checkbox" name="checkbox-test"/></label><br/>
					<label>radio 1<input type="radio" name="radio-test"/></label><label>radio 2<input type="radio" name="radio-test"/></label><br/>
					<label>select<select name="select-test"><option value="one">one</option><option value="two">two</option><option value="three">three</option></select></label><br/>
					<label>textarea<textarea name="textarea-test"></textarea></label><br/>
					<label>button<button name="button-test">Test button</button></label><br/>
					<label>submit<input type="submit" name="submit-test" value="Test submit"/></label><br/>
			  </form>
			</div>
			
		</div>
	</div>
    
    <h3>Mouse over event tracking</h3>
    <a href="http://www.google.com" id="hover" data-action="mouseover">Hover over me</a><br/>
	<a href="http://www.google.com" id="touch"  data-action="touchstart">Touch me</a>
    
    <div>
		<hr/><hr/>
		<h2>Test Level: <em>Boss</em></h2>
		<hr/>
		<hr/>
		<div class="row thirds gutter narrow pad-bottom short"> 
			 <div class="column one">
				<a href="http://i.imgur.com/dsDO3.gif" class="level boss click one"  data-action="click">
					<span>One Click EX:</span>
					<img src="http://i.imgur.com/dsDO3.gif" width="100%"/>
				 </a>
			</div>
			<div class="column two"  data-action="dblclick">
				<a href=" http://i.imgur.com/YeXDp.gif" class="level boss click two">
					<span>DBL click EX:</span>
					<img src="http://i.imgur.com/YeXDp.gif" width="100%"/>
				</a>
			</div>
			<div class="column three">
				<a href="http://i.imgur.com/Tyz0s.gif" class="level boss load">
					<span>onLoad EX:</span>
					<img src="http://i.imgur.com/Tyz0s.gif" width="100%"/>
				</a>
			</div>
		</div>
	</div>
</div>