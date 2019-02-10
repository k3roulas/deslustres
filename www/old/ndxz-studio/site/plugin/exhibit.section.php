<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Section
*
* Exhbition format
*
* @version 1.0
* @author Vaska
* @author Daniel Eatock
*/

// defaults from the general libary - be sure these are installed
$exhibit['dyn_css'] = dynamicCSS();
$exhibit['dyn_js'] = dynamicJS();
$exhibit['exhibit'] = createExhibit();

function createExhibit()
{
	$OBJ =& get_instance();
	global $rs, $exhibit;

	// modif pln
	$secid = $rs['secid'];

	if (isset ($secid) ) {

		$pages = $OBJ->db->fetchArray("	select id, title, url, year, no1.media_file as media_file1, no2.media_file as media_file2
		FROM ndxz_sections, ndxz_media as no1, ndxz_objects
		left outer join ndxz_media as no2 on ndxz_objects.id = no2.media_ref_id and no2.media_rollback = 1 or no2.media_file is null
		where ndxz_objects.id = no1.media_ref_id
		and ndxz_sections.secid = ndxz_objects.section_id
		and no1.media_order = 1
		and hidden != '1'
		and status = '1'
		and section_id = $secid
		and ndxz_sections.sec_disp = 1
		order by ord ASC");				

	} else {


		$pages = $OBJ->db->fetchArray("	select id, title, url, year, no1.media_file as media_file1, no2.media_file as media_file2
		FROM ndxz_sections, ndxz_media as no1, ndxz_objects
		left outer join ndxz_media as no2 on ndxz_objects.id = no2.media_ref_id and no2.media_rollback = 1 or no2.media_file is null
		where ndxz_objects.id = no1.media_ref_id
		and ndxz_sections.secid = ndxz_objects.section_id
		and no1.media_order = 1
		and hidden != '1'
		and status = '1'
		and ndxz_sections.sec_disp = 1
		order by ord ASC");		

	}

	if (!$pages) return "Error with pages query";

	foreach($pages as $reord)
	{
		$order[$reord['sec_desc']][] = array(
			'id' => $reord['id'],
			'title' => $reord['title'],
			'url' => $reord['url'],
			'year' => $reord['year'],
			'secid' => $reord['secid'],
			'media_file1' => $reord['media_file1'],
			'media_file2' => $reord['media_file2']);
	}

	$s = '';
	// ** DON'T FORGET THE TEXT ** //
	$s = $rs['content'];
	// $s .= "<div id='info-dynamique'></div>\n";
	$acpt = 0;
	
	foreach($order as $key => $out)
	{

		foreach($out as $page)
		{
			$active = ($rs['id'] == $page['id']) ? " class='active'" : '';

			$acpt += 1;
			$classa = empty($page['media_file2']) ? "" : "class=\"RollAndRock$acpt\"";
			$stylea = empty($page['media_file2']) ? "" : "<style> a.RollAndRock$acpt { display: block; background-image: url('" . BASEURL . GIMGS . "/th2-$page[media_file2]') } a.RollAndRock$acpt:hover { visibility: visible } a.RollAndRock$acpt:hover img { visibility: hidden } </style>";							
			
			$span_txt = str_replace ( "/", "-", $page['url'] );
			//$s .= "\n<div class='miniexpo'><span class=miniexpo$span_txt>";
			$s .= "\n<div class='miniexpo'>";
			$s .= "\n	<div class=\"img-miniexpo\">";
			$s .= "\n $stylea";
			$s .= "\n		<a $classa href='" . BASEURL . ndxz_rewriter($page['url']) . "' onclick=\"do_click();\" onMouseOver=\"javascript:afficherLibelle('" . $page['title'] . "');\" onMouseOut=\"javascript:afficherLibelle('');\" ><img src='" . BASEURL . GIMGS . "/th2-$page[media_file1]' alt='$caption' title='$title'  /></a>";
			$s .= "\n	</div>";	
			$s .= "\n	<span>";
			$s .= "\n		<div class=\"txt-miniexpo\">";
			$s .= "\n			<a href='" . BASEURL . ndxz_rewriter($page['url']) . "' onclick=\"do_click();\">" . $page['title'] . "</a>";
			$s .= "\n		</div>\n";
			$s .= "\n	</span>";
			$s .= "\n</div>";
			
		}

	}
	
	return $s;
}



function dynamicCSS()
{
	return ".backgrounded { margin-right: 1px; }
	.backgrounded a { border: none; }
	.backgrounded a img { border: 3px solid #fff; height: 25px; width: 25px; }
	.backgrounded-text { margin-top: 9px; }";
}


function dynamicJS()
{
	global $rs;

	$tile = ($rs['tiling'] != 1) ? ", backgroundRepeat: 'no-repeat'" : '';

	return "function swapImg(a, image)
	{
		var the_path = '" . BASEURL . GIMGS ."/' + image;
		show = new Image;
		show.src = the_path;
		$('body').css({ backgroundImage: 'url(' + show.src + ')', backgroundPosition: '215px 0' $tile });

		var title = $('#img' + a).attr('title');
		var caption = $('#img' + a).attr('alt');

		if (title != 'N/A')
		{
			caption = (caption != 'N/A') ? ': ' + caption : '';
			$('#backgrounded-text').html('<span style=\"background: white; line-height: 24px;\">' + title + caption + '</span>');
		}
		else
		{
			$('#backgrounded-text').html('');
		}
	}
	
	function afficherLibelle(txt) {
		$('#info-dynamique').html(txt);
	}
	
	
	";
}

?>
