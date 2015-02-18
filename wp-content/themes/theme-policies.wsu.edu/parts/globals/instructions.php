<div id="instructions" class="gray-lighter-back"><a href="#" id="instructions_tab" class="gray-lighter-back gray-dark-text"><i class="fa fa-info-circle"></i></a>
	<h3>General Instructions</h3>
	<p>The purpose of this site is to test out user flows/goals/campaigns, and more, against the technologies that are implementing those items.</p>
	<h4>How to run a test</h4>
	<p>Generally there are two types of test that will be run.  A manual test where you will click through and watch the output in the <a href="https://chrome.google.com/webstore/detail/google-analytics-debugger/jnkmfdileelhofjcijamephohjechhna">Google analytics debugger</a>.  The other type of test will be an automated clicking of links where it will move from one page to another and without you doing anything.  A set of links will have the <code>click</code> event triggered to simulate a user.  It'll clear out cookies, reset itself and then start again on a new block of the test until all blocks have been completed.</p>
	<h5>Help/issues</h5>
	<p>You may ask questions or raise issues about the gauntlet here at the github repo of <a href="https://github.com/washingtonstateuniversity/WSU-GA-gauntlet" target="_blank">WSU-GA-gauntlet</a>.  You may also contact the current test maintainer, <a href="mailto:jeremy.bass@wsu.edu?subject=WSU-GA-gauntlet%20question" target="_blank">Jeremy Bass</a> with any questions.  Thank you for your interest and participation.</p>
</div>

<div id="automation" class="yellow-er-back accent"><a href="#" id="automation_tab" class="yellow-er-back accent gray-dark-text"><span class="fa-stack fa-md">
  <i class="fa fa-circle fa-stack-2x"></i>
  <i class="fa fa-cog fa-stack-1x fa-inverse"></i>
</span>
</a>
	<h3>Automated<br/>Testing</h3>
	<p>Settings for automation....</p>
	<input type="submit" id="run_test" value="Run Test"/>
	<div id="console_log" class="gray-er-back accent"></div>
</div>