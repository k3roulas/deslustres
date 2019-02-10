<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Slideshow
*
* Exhibition format
*
* @version 1.0.0.0.0.0.0.0.0.0.1 (Because Simon ate a banana for lunch)
* @author Simon Lagneaux
* @author Vaska
*/


// defaults from the general libary - be sure these are installed
$exhibit['dyn_css'] = dynamicCSS();
$exhibit['exhibit'] = createExhibit();
$exhibit['lib_js'] = array('jquery.cycle.all.js');
$exhibit['dyn_js'] = dynamicJS();

		// $('#s1').cycle({
			// fx:'fade',
			// speed:'2000',
			// end: traitementEnd,
			// timeout: 0,
			// nowrap: 1,
			// next:'.next', prev:'#prev',
			// pagerAnchorBuilder: constructionPager,
			// pager: '#nav'
		// });
		
function dynamicJS()
{
	return "

$(document).ready(function(){


		$('#s1').cycle({
			fx:'fade',
			speed:'2000',
			end: traitementEnd,
			timeout: 0,
			nowrap: 1,
			next:'.next', 
			//prev:'#prev',
			containerResize: false,
			before: avant
		});

		
			
		function avant () {
			$('#img-info').html(this.libellePosition);
		}

		function traitementEnd(options) {
				document.location.href = document.referrer;
		}
		

	});";

}


function createExhibit()
{
	$OBJ =& get_instance();
	global $rs;

	$pages = $OBJ->db->fetchArray("SELECT *
		FROM ".PX."media, ".PX."objects_prefs
		WHERE media_ref_id = '$rs[id]'
		AND obj_ref_type = 'exhibit'
		AND obj_ref_type = media_obj_type
		AND media_hide = 0
		ORDER BY media_order ASC, media_id ASC");


	// ** DON'T FORGET THE TEXT ** //
	$s = "<div id=\"txt-container\">";
	$s .= $rs['content'];
	$s .= "</div>";

	if (!$pages) return $s;

		$i = 1; $a = '';

	// people will probably want to customize this up
	foreach ($pages as $go)
	{
	    $title 		= ($go['media_title'] == '') ? '' : $go['media_title'] . '&nbsp;';
	    $caption 	= ($go['media_caption'] == '') ? '&nbsp;' : $go['media_caption'];

		list($width, $height, $type, $attr) = getimagesize( "./" . GIMGS . '/' . $go['media_file']);
		$a .= "\n<div><img width='$width' height='$height' class=next src='" . BASEURL . GIMGS . "/$go[media_file]' class='img-bot' /><p>{$title}{$caption}</p></div>\n";

		$i++;
	}

	// images
	$s .= "<div id='img-container'>\n";
	$s .= "<p class='nav'><a id='prev' href='#'>&lt;</a>&nbsp&nbsp<a id='next' class=next href='#'>&gt;</a>
	       <span id='num'></span></p>";
	$s .= "<div id='s1' class='pics'>\n";
	$s .= $a;
	$s .= "</div>\n";
	$s .= "<div id='img-info'></div>";		
	$s .= "</div>\n\n";
	
	return $s;
}


function dynamicCSS()
{
	return "#num {padding-left: 6px;}
	.img-bot {margin-bottom: 6px; display: block; }";
}



?>