<?php 
$themePath = get_stylesheet_directory_uri(); // we want the child theme url
$code = get_gauntlet_attr("code");
?>
<aside>
	<a href="#" id="debug">Set debug to <i>true</i></a>
     <h1>Running code</h1>
    <pre><code id="codeblob">
$(function(){
    /*
    example of a json feed controlling the triggers since this is something that is cached this is a  super easy way to control may distrobutions with out having users that don't know, have to make edits
    */
    $.jtrack.defaults.debug = true;
    $.jtrack({
        analytics:{
            ga_name:"_wsuGA",
            accounts:[{
                id:'<?=get_gauntlet_attr("group_ga")?>',
                settings:{
                    namedSpace:'WSUTestGlobal',
                    cookieDomain:".wsu.edu",
                    dimension:[
                        {'name':'dimension1','val': window.location.protocol }
                    ]
                }
            },{
                id:'<?=get_gauntlet_attr("site_ga")?>',
                settings:{
                    namedSpace:'siteScope',
                    cookieDomain:".wsu.edu",
                    events:'<?=get_site_url()?>?ajax&load=test'
                }
            },{
                id:'UA-52133513-1',
                settings:{
                    namedSpace:'wp_platform',
                    dimension:[
                        {'name':'dimension1','val':'Front End'},
                        {'name':'dimension2','val':'Not Authenticated'},
                    ]
                }
            }]
        }
    });
	
	/*
	$('a#debug').on('click',function(){
		var newstate = $.jtrack.defaults.debug==true?false:true;
		$.jtrack.defaults.debug = newstate;
		$(this).find('i').text(newstate==true?"false":"true");
	});
    //few other examples
    $('.normal a').jtrack();
    $('.sidebar a').jtrack({
        category : 'sidebar'
    });
    $('.complex a').jtrack({
        category : function(element) {
            element.removeClass('tracked');
            return "A special Cat to use";
        }
    });*/

});
    </code></pre>
        <h3>Json Feed <em>(loaded from track/test.txt)</em></h3>
    <pre><code id="jsonfeed">
    </code></pre>
			
				<h2>Sidebar</h2>
				<ul>
					<li>Fight, fight, fight for Washington State! Win the victory!</li>
					<li>Win the day for Crimson and Gray! Best in the West, we know you'll all do your best, so</li>
					<li>On, on, on, on! Fight to the end! Honor and Glory you must win! So</li>
					<li>Fight, fight, fight for Washington State and victory!</li>
					<li>W-A-S-H-I-N-G-T-O-N-S-T-A-T-E-C-O-U-G-S! <strong>GO COUGS!!</strong></li>
				</ul>
				<small>The song appears in the 1985 film <i>Volunteers</i>, sung by John Candy's character Tommy Tuttle from Tacoma, Washington, and later used as a war cry by the Communists.</small>
		</aside>
