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

initObjet();

// defaults from the general libary - be sure these are installed
$exhibit['dyn_css'] = dynamicCSS();
$exhibit['lib_js'] = array('loading.js', 'jquery.js', 'jquery.cycle.all.js', 'jquery-scroll.js', 'jquery.mousewheel.js', 'jquery.jscrollpane.min.js', 'specific.acd.js');
$exhibit['exhibit'] = createExhibit();
$exhibit['dyn_js'] = dynamicJS();

class Expo {
	var $_field;
	var $_liste_media;
	function Expo($field, $liste_media) { $this->_field = $field; $this->_liste_media = $liste_media; }
};

class Annee {
	var $_annee;
	var $_liste_expo;
	function Annee($annee) { $this->_annee = $annee; }
	function ajouteExpo($expo) { $this->_liste_expo[] = $expo; }
};

class Section {
	var $_sec_path;
	var $_sec_desc;
	var $_anchor;
	var $_liste_expo;
	function Section($sec_path, $sec_desc) { $this->_sec_path = $sec_path; $this->_sec_desc = $sec_desc; $this->_anchor = pathToAnchor($sec_path); }
	function getListeExpo() { return $this->_liste_expo; }
	function getNbExpo() { return count($this->_liste_expo); }
	function ajouteExpo($expo) { $this->_liste_expo[] = $expo; }
};

class SectionAnnee {
	var $_sec_path;
	var $_sec_desc;
	var $_liste_annee;
	function SectionAnnee($sec_path, $sec_desc) { $this->_sec_path = $sec_path; $this->_sec_desc = $sec_desc; $this->_anchor = pathToAnchor($sec_path); }
	function ajouteAnnee($annee) { $this->_liste_annee[] = $annee; }
	function getListeExpo() {
		$ret_liste = array();
		foreach ($this->_liste_annee as $annee) { 
			$ret_liste = array_merge($ret_liste, $annee->_liste_expo); 
		}
		return $ret_liste;
	}
};

class Site {
	var $_field;
	var $_liste_section;
	var $_liste_pre_expo;
	var $_liste_post_expo;
	function Site($field) { $this->_field = $field; }
	function ajouteSection( $section ) { $this->_liste_section[] = $section; }
	
	function cout() {		
		foreach ($this->_liste_section as $section) {
			if (strtolower(get_class($section)) == "sectionannee") {
				foreach ($section->_liste_annee as $annee) {
					echo "-annee : " . $annee->_annee . "<br>";
					foreach ($annee->_liste_expo as $expo) {
						echo "--title expo : " . $expo->_field['title'] . "<br>";	
						foreach ($expo->_liste_media as $media) {
							echo "---media : " . $media['media_file'] . "<br>";
						}
					}					
				}
			} else { // class Section
				foreach ($section->_liste_expo as $expo) {
					echo "-title expo : " . $expo->_field['title'] . "<br>";
					foreach ($expo->_liste_media as $media) {
						echo "---media : " . $media['media_file'] . "<br>";
					}
				}
			}
		}		
	}
};

function compare_desc ($a, $b) {
    if ($a == $b) return 0;
    return ($a > $b) ? -1 : 1;
}

function initObjet()
{
	global $rs, $site;
	
	$site = new Site($rs);

	$liste_expo_cache = getBaseExpoDesSectionsCachees();
	$expo_par_section = getBaseExpoParSection();
	$media_par_expo = getBaseMediaParExpoId();
	
	// pre expo
	$list_path_pre = explode (";", strtolower($site->_field['obj_listexpopretitle']));	
	foreach ($list_path_pre as $url) {
		if (isset($liste_expo_cache[$url]) && $liste_expo_cache[$url] != "" ) {
			$liste_media = array();
			if (isset($media_par_expo[$liste_expo_cache[$url]['id']])) { 
				$liste_media = $media_par_expo[$liste_expo_cache[$url]['id']];
			}
			$obj_expo = new Expo($liste_expo_cache[$url], $liste_media);
			$site->_liste_pre_expo[] = $obj_expo;
		}
	}
	
	// post expo
	$list_path_post = explode (";", strtolower($site->_field['obj_listexpoposttitle']));
	foreach ($list_path_post as $url) {
		if (isset($liste_expo_cache[$url]) && $liste_expo_cache[$url] != "" ) {
			$liste_media = array();
			if (isset($media_par_expo[$liste_expo_cache[$url]['id']])) { 
				$liste_media = $media_par_expo[$liste_expo_cache[$url]['id']];
			}		
			$obj_expo = new Expo($liste_expo_cache[$url], $liste_media);
			$site->_liste_post_expo[] = $obj_expo;		
		}
	}



	// le site
	foreach ($expo_par_section as $sec_desc => $liste_expo) {
				
		if (isset($liste_expo[0])) {
		
			if ($liste_expo[0]['sec_year']) {
			
				$sec_path = $liste_expo[0]['sec_path'];
				$obj_section = new SectionAnnee($sec_path, $sec_desc);			
				$reord = array();
				
				foreach ($liste_expo as $expo) {
			
					$reord[$expo['year']][] = $expo;	
			
				}			
				uksort ($reord, "compare_desc");
				foreach ($reord as $annee => $liste_expo_par_annee) {
				
					$obj_annee = new Annee($annee);
					foreach ($liste_expo_par_annee as $expo) {
						$obj_expo = new Expo($expo, $media_par_expo[$expo['id']]);						
						$obj_annee->ajouteExpo($obj_expo);						
					}
					$obj_section->ajouteAnnee($obj_annee);
				}
				$site->ajouteSection($obj_section);
				
			} else {
				$sec_path = $liste_expo[0]['sec_path'];
				$obj_section = new Section($sec_path, $sec_desc);
				foreach ($liste_expo as $expo) {
					$obj_expo = new Expo($expo, $media_par_expo[$expo['id']]);
					$obj_section->ajouteExpo($obj_expo);				
				}				
				$site->ajouteSection($obj_section);
			}
		}
	}
	//$site->cout();
}

function getBaseExpoDesSectionsCachees() {
	
	$OBJ =& get_instance();
	
	$expo = $OBJ->db->fetchArray("select url, id, title, content, media_file, media_caption, media_caption, media_title, media_alt FROM ndxz_sections, ndxz_objects
	left outer join ndxz_media on ndxz_objects.id = ndxz_media.media_ref_id
	WHERE ndxz_sections.secid = ndxz_objects.section_id
	and hidden != '1'
	and status = '1'
	and ndxz_sections.sec_disp = 0
	order by sec_ord ASC, ord ASC");
	

	if (!$expo) return "Error with expo query";

	foreach($expo as $reord)
	{	
		$expo_par_url[strtolower($reord['url'])]= array(
			'url' => $reord['url'],
			'id' => $reord['id'],
			'title' => $reord['title'],
			'content' => str_replace(array("\n", "\r"), "", $reord['content']),
			'media_title' => $reord['media_title'],
			'media_caption' => $reord['media_caption'],
			'media_alt' => $reord['media_alt'],
			'media_file' => $reord['media_file']);
	}
	return $expo_par_url;
}

function getBaseExpoParSection()
{
	$OBJ =& get_instance();
	global $rs, $exhibit;
	
	$expo = $OBJ->db->fetchArray("	select id, secid, sec_desc, sec_path, sec_year, content, title, url, year, no1.media_file as media_file1, no2.media_file as media_file2
	FROM ndxz_sections, ndxz_media as no1, ndxz_objects
	left outer join ndxz_media as no2 on ndxz_objects.id = no2.media_ref_id and no2.media_rollback = 1 or no2.media_file is null
	where ndxz_objects.id = no1.media_ref_id
	and ndxz_sections.secid = ndxz_objects.section_id
	and no1.media_order = 1
	and hidden != '1'
	and status = '1'
	and ndxz_sections.sec_disp = 1
	order by sec_ord ASC, ord ASC");
	

	if (!$expo) return "Error with expo query";

	foreach($expo as $reord)
	{
		$expo_par_section[$reord['sec_desc']][] = array(
			'id' => $reord['id'],
			'title' => $reord['title'],
			'url' => $reord['url'],
			'content' => str_replace(array("\n", "\r"), "", $reord['content']),
			'year' => $reord['year'],
			'secid' => $reord['secid'],
			'sec_path' => $reord['sec_path'],
			'sec_year' => $reord['sec_year'],
			'media_file1' => $reord['media_file1'],
			'media_file2' => $reord['media_file2']);
	}	
	return $expo_par_section;
}

function getBaseMediaParExpoId()
{
	$OBJ =& get_instance();
	global $rs, $exhibit;
	
	$media = $OBJ->db->fetchArray("SELECT media_file, media_title, media_caption, media_alt, id, media_id
				FROM ndxz_media, ndxz_objects
				WHERE media_obj_type = 'exhibit'
				AND object = 'exhibit'
				AND ndxz_objects.id = ndxz_media.media_ref_id
				AND media_hide =0
				ORDER BY ndxz_objects.id, media_order ASC , media_id ASC");	
	
	if (!$media) return "";

	foreach($media as $reord)
	{
		$media_par_expo[$reord['id']][] = array(
			'media_file' => $reord['media_file'],
			'media_title' => $reord['media_title'],
			'media_caption' => $reord['media_caption'],
			'media_alt' => $reord['media_alt'],
			'media_id' => $reord['media_id'],
			'id' => $reord['id']);
	}
	
	return $media_par_expo;
}



function getMenu() {

	$OBJ =& get_instance();
	global $rs, $site;

	$s = li(0, "<div id='menu'>");
	$s .= li(1, '<ul>');

	// expo post title
	foreach ($site->_liste_pre_expo as $expo) {
		$titre = $expo->_field['title'];
		$url = pathToAnchor( $expo->_field['url'] );
		$s .= li(2, "<li><a id='menu$url' href=\"javascript:void(null);\" class='menu-section' onclick='changeSection(\"$url\");'>$titre</a></li>");
	}

	// sections
	foreach($site->_liste_section as $section)
	{
		$libelle_section = $section->_sec_desc;
		$url = pathToAnchor($section->_sec_path);
		$s .= li(2, "<li><a id='menu$url' href='javascript:void(null);' class='menu-section' onclick='changeSection(\"$url\");'>$libelle_section</a></li>");
	}


	// expo post title
	foreach ($site->_liste_post_expo as $expo) {
		$titre = $expo->_field['title'];
		$url = pathToAnchor( $expo->_field['url'] );
		$s .= li(2, "<li><a id='menu$url' href=\"javascript:void(null);\" class='menu-section' onclick='changeSection(\"$url\");'>$titre</a></li>");
	}
	
	$s .= li(1, "</ul>");
	$s .= li(0, "</div>");
	
	return $s;
	
}



function genereSousMenu($list_expo, $key) {
	
	// ajout d'un sous-menu
	$s = li(2, "<div id=\"sous-menu-list$key\" class=\"sous-menu-list\" id=\"$key\">");

	$nb_col = 2;
	$nb_ligne = 4;
	
	$current_item=0;
	$current_page=0;
	
	$pos_expo = 0;
	$nb_expo = count($list_expo);
	
	while ($pos_expo < $nb_expo) {
	
		// organisation en ligne/colonne
		if ( ($current_item % $nb_ligne) == 0) {
			if ($current_item != 0) { // si pas la permiere col on ferme la prec
				$s .= li(4, "</ul>");					
			}
		}
	
		// dans le cas d'une nouvelle page 
		if ( ($current_item % ( ( $nb_col * $nb_ligne ) ) ) ==0 ) {
			if ($current_item != 0) { // si pas la premiere on ferme la prec
				$s .= li(3, "</div>");
				$current_page++;
			}
			$s .= li(3, "<div id='sous-menu-$key-page$current_page' class='sous-menu-page'>");
		}
		
		// organisation en ligne/colonne
		if ( ($current_item % $nb_ligne) == 0) {
			$s .= li(4, "<ul>");
		}
		
		
		// fleche de navigation par page			
		
		if ( ( ($current_item % ( ( $nb_col * $nb_ligne ) ) ) ==0 ) && ($current_item != 0) ) {
		
			// si premier item de la page et pas premier item en absolue : prec
			$page_precedente = $current_page - 1; 
			$cmd = "$('#sous-menu-contener').scrollTo('#sous-menu-$key-page$page_precedente', 500, {axis:'xy', offset:{top:0, left:0}});";
			$s .= li(5, "<li><a href=\"javascript:void(null);\" onclick=\"$cmd;\"><img src='". BASEURL . GIMGS . "/fleche_prec_menu.png' alt='previous' /></a><br></li>");
			
		} else if ( ( ($current_item % ( ( $nb_col * $nb_ligne ) -1 ) ) ==0 ) 
			&& ($current_item != 0)			
			&& ($pos_expo != $nb_expo -1 ) ) {
		
			// si dernier item de la page, pas premier item en absolue, et il existe au moins un expo apres : suiv
			$page_suivante = $current_page + 1; 
			$cmd = "$('#sous-menu-contener').scrollTo('#sous-menu-$key-page$page_suivante', 500, {axis:'xy', offset:{top:0, left:0}});";
			$s .= li(5, "<li><a href=\"javascript:void(null);\" onclick=\"$cmd;\"><img  src='". BASEURL . GIMGS . "/fleche_suiv_menu.png' alt='next' /></a><br></li>");
			
		} else {
		
			// affiche le lien de l'expo 			
			$expo = $list_expo[$pos_expo];
			$title = $expo->_field['title'];
			$url = pathToAnchor($expo->_field['url']);			
			$s .= li(5, "<li><a id='ssmenu$url' href=\"javascript:void(null);\" onclick=\"scrollToExpo('$url');\">$title</a><br></li>");
			// iteration des expos
			$pos_expo++;
		}
		
		// iteration des items
		$current_item++;
	}
	
	// fin de la liste
	$s .= li(4, "</ul>");
	
	// fin de la page du sous menu
	$s .= li(3, "</div>");
	
	// fin du sous-menu
	$s .= li(2, "</div>");

	return  $s;

}

function li($nb, $txt) {	
	$spa = "";
	for ($i=0; $i<$nb; $i++) { $spa .="  "; }
	return "\n$spa$txt";
}

function getSousMenu() {

	global $site;

	// debut de la liste des sous-menu
	// -------------------------------
	$smys = li(0, "<div id='sous-menu-contener'>");
	$smys .= li(1, "<div id='sous-menu'>");
	
	$smy = li(0, "<div id='sous-menu-year-contener'>");
	$smy .= li(1, "<div id='sous-menu-year'>");
	
	foreach ($site->_liste_section as $section) {

		$sec_anchor = $section->_anchor;
	
		if (strtolower(get_class($section)) == "sectionannee") {
		
			$smy .= li(2, "<div id='year-section-$sec_anchor' class='year-section'>");
			
			$nb_col = 2;
			$nb_ligne = 4;
	
			$current_item=0;
			$current_page=0;
	
			$pos_year = 0;
			$nb_year = count($section->_liste_annee);	
			
			while ($pos_year < $nb_year) {

				$smys .= genereSousMenu( $section->_liste_annee[$pos_year]->_liste_expo ,$sec_anchor);

			
				$libelle_annee = $section->_liste_annee[$pos_year]->_annee;

				// organisation en ligne/colonne
				if ( ($current_item % $nb_ligne) == 0) {
					if ($current_item != 0) { // si pas la permiere col on ferme la prec
						$smy .= li(4, "</ul>");	
					}
				}
			
				// dans le cas d'une nouvelle page 
				if ( ($current_item % ( ( $nb_col * $nb_ligne ) ) ) ==0 ) {
					if ($current_item != 0) { // si pas la premiere on ferme la prec
						$smy .= li(3, "</div>");
						$current_page++;
					}
					$smy .= li(3, "<div id='year-$sec_anchor-page$current_page' class='year-page'>");
				}
		
				// organisation en ligne/colonne
				if ( ($current_item % $nb_ligne) == 0) {
					$smy .= li(4, "<ul>");
				}
								
				// fleche de navigation par page			
				
				if ( ( ($current_item % ( ( $nb_col * $nb_ligne ) ) ) ==0 ) && ($current_item != 0) ) {
				
					// si premier item de la page et pas premier item en absolue : prec
					$page_precedente = $current_page - 1; 
					$cmd = "$('#sous-menu-year-contener').scrollTo('#year-$sec_anchor-page$page_precedente', 500, {axis:'xy', offset:{top:0, left:0}});";											
					$smy .= li(5, "<li><a href=\"javascript:void(null);\" onclick=\"$cmd;\"><img src='". BASEURL . GIMGS . "/fleche_prec_menu.png' alt='previous'/></a><br></li>");
					
				} else if ( ( ($current_item % ( ( $nb_col * $nb_ligne ) -1 ) ) ==0 ) 
					&& ($current_item != 0)			
					&& ($pos_year != $nb_year -1 ) ) {
				
					// si dernier item de la page, pas premier item en absolue, et il existe au moins un expo apres : suiv
					$page_suivante = $current_page + 1; 						
					$cmd = "$('#sous-menu-year-contener').scrollTo('#year-$sec_anchor-page$page_suivante', 500, {axis:'xy', offset:{top:0, left:0}});";					
					$smy .= li(5, "<li><a href=\"javascript:void(null);\" onclick=\"$cmd;\"><img src='". BASEURL . GIMGS . "/fleche_suiv_menu.png' alt='next'/></a><br></li>");
					
				} else {
				
					$expo = $section->_liste_annee[$pos_year]->_liste_expo[0];
					$url = pathToAnchor($expo->_field['url']);									
					$smy .= li(5, "<li><a id=\"$sec_anchor-$libelle_annee\" href=\"javascript:void(null);\" onclick=\"scrollToExpo('$url');\">$libelle_annee</a></li>");	
					$pos_year++;					
					
				}
		
				// iteration des items
				$current_item++;
			}
			
			$smy .= li(4, "</ul>");	
			$smy .= li(3, "</div>");				
			$smy .= li(2, "</div>"); // fin de year-section-$section
							
		} else { // cas d'une section (pas annee)
		
			$smys .= genereSousMenu( $section->_liste_expo ,$sec_anchor);
		
		}
	
	}
	
	$smy .= li(1, "</div>");
	$smy .= li(0, "</div>");	
	$smys .= li(1, "</div>");
	$smys .= li(0, "</div>");
	
	$s = "\n\n" . $smy;
	$s .= "\n\n" . $smys;
	
	// fin de la liste des sous-menu
	
	return $s;

}



function createExhibit()
{
	$OBJ =& get_instance();
	global $rs, $exhibit, $site;

	$s = getMenu();
	$s .= getSousMenu();

	$s .= "\n\n";
	$s .= li(0, "<div id=\"full\">");

	/////////////////////////////
	// affichage des pre-expos
	/////////////////////////////	

	if (!empty($site->_liste_pre_expo)) {
		$s .= getHtmlExpoOrpheline($site->_liste_pre_expo);
	}
		
	
	////////////////////////
	// affichage des expos
	////////////////////////
	
	$acpt = 0;
	
	$q=1;
	foreach($site->_liste_section as $section)
	{
		$s .= li(1, "<div class='section'>");
		
		// pour chaque nouvelle section on ajoute un sous menu contenant toutes les minexpo
	    $s .= getListeMiniExpo($section);

		$s .= li(2, "<div class='section-expos'>");
		
		$singularSection = singular($section->_sec_desc);

		$liste_expo = $section->getListeExpo();		
		$nb_out = count($liste_expo);
		
		for($pos_out=0; $pos_out < $nb_out; $pos_out++) { 
		
			$expo = $liste_expo[$pos_out];
			$acpt += 1;			
			$expo_id = $expo->_field['id'];
			$liste_media = $expo->_liste_media;
			
			// lien suiv
			$lien_suiv = "";
			if ($pos_out < $nb_out-1) {
				$url = pathToAnchor($liste_expo[$pos_out+1]->_field['url']);
				$l=$q+1;
				$lien_suiv = "<li>|<a href=\"javascript:void(null);\" onclick=\"scrollToExpo('$url');\">$singularSection SUIV</a></li>";
			}

			// lien prec
			$lien_prec = "";
			if ($pos_out != 0) {			
				$url = pathToAnchor($liste_expo[$pos_out-1]->_field['url'] );
				$l=$q-1;				
				$lien_prec = "<li>|<a href=\"javascript:void(null);\" onclick=\"scrollToExpo('$url');\">$singularSection PREC</a></li>";				
			}
			
			// lient tout
			$url = pathToAnchor($section->_sec_path);
			$lien_tout = "<li><a href=\"javascript:void(null);\" onclick=\"changeSection('$url');\">TOUS</a></li>";				
		
			$url = pathToAnchor($expo->_field['url']);
			$s .= li(0, "");
			$s .= li(3, "<!-- Expo -->");
			$s .= li(0, "");
			$s .= li(3, "<div class='expo' id='$url'>");
			
			if ( $lien_suiv != "" ||  $lien_prec != "" ) {
				$s .= li(4, "<ul class='expo-nav'>");
				if ($lien_suiv != "") {	$s .= li(5, $lien_suiv); }
				if ($lien_prec != "") {	$s .= li(5, $lien_prec); }
				$s .= li(5, $lien_tout);
				$s .= li(4, "</ul>");
			}
			
			// titre de l'expo
			$title = $expo->_field['title'];	

			$s .= li(4, "<div class='expo-titre'>");
			$s .= li(5, "<img class='fleche-titre-projet' src='" . BASEURL . GIMGS . "/fleche_titre_projet.png' alt='arrow_down'>");
			$s .= li(5, "<span class='expo-titre-libelle'>$title</span>");
			$s .= li(4, "</div>");

			if (!$liste_media) break;

			$nb_image = 0; $a = '';

			// people will probably want to customize this up
			foreach ($liste_media as $media)
			{
				$file 		= $media['media_file'];
				$title 		= ($media['media_title'] == '') ? '' : $media['media_title'] . '&nbsp;';
				$caption 	= ($media['media_caption'] == '') ? '&nbsp;' : $media['media_caption'];
				$alt 	  	= $media['media_alt'];
				$media_id   = $media['media_id'];

				list($width, $height, $type, $attr) = getimagesize( "./" . GIMGS . '/' . $media['media_file']);
				$legende = "";
				if ( "$title$caption" != "&nbsp;" ) {
					$legende = "<span class='border-legende'></span><p>{$title}<p>{$caption}";
				}				
				$urlidmedia = $url . "img-$media_id";
				// on place une image plnwhite pour chrome et consort (si l'image n'est  pas trouvee pour le slideshow, une vilaine icone d'image invalide s'affiche lors du "survol", on place donc l'image plnwhite (toute blanche de 44x44
				$a .= li(6, "<div><img id='$urlidmedia' class=next$acpt  alt='$alt' class='img-bot' src='". BASEURL . GIMGS . "/plnwhite.jpg'/><div class='legende'>{$legende}</div></div>");
				$nb_image++;
			}
			$nb_page = (int)(($nb_image-1)/10)+1;
			
			// images
			if ($nb_image !=0) {
			
				// images			
				   
				$s .= li(4, "<div id='img-container$q' class='img-container'>");

				$s .= li(5, "<div id='slideshow$url' class='slideshow'>");
				$s .= $a;
				$s .= li(5, "</div>");
				$s .= li(5, "<div id='img-info$q'></div>");		
				$s .= li(4, "</div>");				
			}

			
			if (isset($expo->_field['content'])) {			
				if (!$liste_media) {
					$classtxt = "txt-expo-sans-image";
				} else {
					$classtxt = "txt-expo-avec-image";	
				}
				$s .= li(4, "<div class=\"$classtxt with-scrollbar\">");
				$s .= li(5, $expo->_field['content']);
				$s .= li(4, "</div>");
			}	



			// share
			$share_url = BASEURL . "/index.php" . $expo->_field['url'];
			$share_title = $expo->_field['title'];
			$share_description = "Des Lustres et Anna : Bureau de conception d'espace et decoration";
			
			$s .= li(4, "<!-- AddThis Button BEGIN -->");			
			$s .= li(4, "<div class=\"addthis_toolbox addthis_default_style \">");
			$s .= li(4, "<a addthis:url=\"$share_url\" addthis:title=\"$share_title\" addthis:description=\"$share_description\" href=\"http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4dafef007fa16e58\" class=\"lien-share  addthis_button\">Share</a>");
			$s .= li(4, "</div>");
			$s .= li(4, "<script type=\"text/javascript\" src=\"http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4dafef007fa16e58\"></script>");
			$s .= li(4, "<!-- AddThis Button END -->");
																						
			if ($nb_image !=0) {
					
				$s .= li(4, "<div id='miniature-nav-contener$q' class='miniature-nav-contener'>");
				$s .= li(5, "<ul id='minature-nav$q' class='minature-nav'><span id='num'></span></ul>");
				$s .= li(4, "</div>"); // fin de miniature-nav-contener
				// pas de navigation suivant precedent entre les images 
				// $s .= "\n<div id='nav_one_$q' class='nav_one'><a id='prev$q' class=prev$q href=\"javascript:void(null);\">&lt;</a>&nbsp&nbsp<a id='next$q' class=next$q href=\"javascript:void(null);\">&gt;</a></div>";
				$s .= li(4, "<a class='miniature-nav-suivante' id='miniature-nav-suivante$q' href=\"javascript:void(null);\" onclick=\"\">[+]</a>");
				$s .= li(4, "<a class='miniature-nav-precedente' id='miniature-nav-precedente$q' href=\"javascript:void(null);\" onclick=\"\">[-]</a>");
			}
			$q++;

			// fin expo			
			$s .= li(3, "</div>");
			$s .= li(0, "");
			$s .= li(3, "<!-- Fin Expo -->");
			$s .= li(0, "");
			
		}
		/**/	
		// fin section-expos
		$s .= "\n</div>"; 
		
		// fin section
		$s .= "\n</div>"; 

	}	
	
	/////////////////////////////
	// affichage des posts expos
	/////////////////////////////	
	
	if (!empty($site->_liste_post_expo)) {
		$s .= getHtmlExpoOrpheline($site->_liste_post_expo);
	}
	
	
	$s .= "</div>";

	return $s;

}

function pathToAnchor($path) 
{
	return str_replace ( "/", "-", $path );
}

function singular($libelle) 
{
	// supprimer le dernier s
	$mot_array = explode(' ',$libelle);
	$dernier_mot = $mot_array[count($mot_array)-1];
	//$dernier_mot = strtolower($dernier_mot);
	//$dernier_mot = ucwords($dernier_mot);
	if (strtolower(substr($dernier_mot, -1)) == 's') {
		$dernier_mot = substr($dernier_mot, 0, -1);
	}
	return $dernier_mot;	
}



function getMiniExpo($expo, $anchor, $position) {
	$s = "";
	$idexpo = $expo->_field['id'];
	$mf1 = $expo->_field['media_file1'];
	$mf2 = $expo->_field['media_file2'];
	$url = pathToAnchor($expo->_field['url']);
	$title = $expo->_field['title'];
	$id_img = "img_mini_$idexpo";
	$rollandrock = empty($mf2) ? "" : " onmouseover=\"$id_img.src='" . BASEURL . GIMGS . "/th2-$mf2';\" onmouseout=\"$id_img.src='" . BASEURL . GIMGS . "/th2-$mf1';\" ";
	
	$special_margin = "";
	if ( ($position+1) % 4 == 0) { $special_margin = "style='margin-right: 0px;'";}
	$s .= li(5, "<div class='miniexpo' $special_margin>");
	$s .= li(6, "<div class=\"img-miniexpo\">");

	$s .= li(7, "<a href=\"javascript:void(null);\" $rollandrock onclick=\"scrollToExpo('$url');\" ><img name='$id_img' src='" . BASEURL . GIMGS . "/th2-$mf1' title='$title'  /></a>");			
	$s .= li(6, "</div>");	
	$s .= li(6, "<span>");
	$s .= li(7, "<div class=\"txt-miniexpo\">");
	$s .= li(8, "<a href=\"javascript:void(null);\" onclick=\"scrollToExpo('$url');\">" . $expo->_field['title'] . "</a>");
	$s .= li(7, "</div>");
	$s .= li(6, "</span>");
	$s .= li(5, "</div>");
	
	return $s;
}

function getListeMiniExpo($section) 
{
	$s = "";	

	$url_section = $section->_anchor;
	$s .= li(2, "<div class=\"section-miniexpos expo\" id=\"$url_section\">");
	$s .= li(3, "<div class=\"lst-miniexpo\" id=\"lst-miniexpo$url_section\">");
	
	if (strtolower(get_class($section)) == "sectionannee") {
		// section organisee par annee
	
		$nb_annee = 0;
		foreach($section->_liste_annee as $annee) {
		
			$nb_annee++;
			
			$libelle_annee = $annee->_annee;
			$s .= li(4, "<div class='miniexpo-date'>$libelle_annee</div>");
				
			$position = 0;
			foreach($annee->_liste_expo as $expo) {
					
					$s .= getMiniExpo ($expo, $section->_anchor, $position);
					$position++;					
			}
			
			if ($nb_annee != count($section->_liste_annee)) {
				$s .= li(4, "<div class='miniexpo-date miniexpo-separator-date'>&nbsp;</div>");
			}
			
		}
		
	} else {	
		// section contenant directement les expos
	
		$position = 0;
		foreach($section->_liste_expo as $expo) {

			$s .= getMiniExpo ($expo, $section->_anchor, $position);
			$position++;
			
		}
	
	}
	$s .= li(3, "</div>");
	$s .= li(2, "</div>");	
	
	return $s;	
}

function getHtmlExpoOrpheline($list_expo) 
{

	$OBJ =& get_instance();
	global $rs, $exhibit;
	
	$s = "";
	
	foreach ($list_expo as $expo) {
	
		$txt = "";
		
		$s .= li(1, "<div class='section'>");

		$url = pathToAnchor($expo->_field['url']);
		$s .= li(2, "<div class=\"expo\" id=\"$url\">");

		$expoid = $expo->_field['id'];		
		$pages = $expo->_liste_media;
			
		if (isset($expo->_field['content'])) {		
			if (!$pages) {
				$classtxt = "txt-expo-sans-image";
			} else {
				$classtxt = "txt-expo-avec-image";			
			}			
			$txt .= li(3, "<div class=\"$classtxt\">");
			$txt .= $expo->_field['content'];
			$txt .= li(3, "</div>");
		}	
	
		if ($pages) {
		
			$i = 1; $a = '';

			// people will probably want to customize this up
			foreach ($pages as $go)
			{
				$title 		= ($go['media_title'] == '') ? '' : $go['media_title'] . '&nbsp;';
				$caption 	= ($go['media_caption'] == '') ? '&nbsp;' : $go['media_caption'];
				$alt	 	= $go['media_alt'];

				list($width, $height, $type, $attr) = getimagesize( "./" . GIMGS . '/' . $go['media_file']);
				$a .= "<img width='$width' height='$height' class=next$acpt src='" . BASEURL . GIMGS . "/$go[media_file]' alt='$alt' class='img-bot' /><p>{$title}{$caption}</p>";

				$i++;
				break;
			}

			// images
			if ($url == '-cache-contact-') {
				$s .= $txt;
				$s .= li(4, "<div id='img-container-cache-contact-'>");
				$s .= li(5, $a);
				$s .= li(4, "</div>");			
			} else {
				$s .= li(4, "<div class='img-container'>");
				$s .= li(5, $a);
				$s .= li(4, "</div>");	
				$s .= $txt;					
			}
			$q++;
		
		}

		// fin expo
		$s .= li(2, "</div>");
		
		// fin de section
		$s .= li(1, "</div>");
			
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

function createTableauExpoImage() {
	
	global $site;		
	$ret = "";
	$var_tab_expo = "";

	$separateur_expo = "";
	
	foreach($site->_liste_section as $section) {
	
		
		foreach ($section->getListeExpo() as $expo) {
		
			$var_tab_media = '';
			$separateur_media = "";
			
			foreach ($expo->_liste_media as $media) {
				$image = $media['media_file'];
				$var_tab_media .= " $separateur_media \"$image\" ";	
				if ($separateur_media == "") $separateur_media = ",";
			}
			
			$url = pathToAnchor($expo->_field['url']);
			$var_tab_expo .= "\n $separateur_expo \"$url\" : [ $var_tab_media ] ";
			if ($separateur_expo == "") $separateur_expo = ",";
			
		}
			
	}
	
	$ret = "\nvar TabExpoImage = { ". $var_tab_expo . "};";

	return $ret;
}



function dynamicJS()
{
	$OBJ =& get_instance();
	global $rs, $exhibit, $site;	

	$year_expo = null;
	$djs =''; $i = 1;
	
	// contruire le tableau d'image de miniature rollover
	$tab_image_miniature = "";
	foreach ($site->_liste_section as $section) {
		$position = 0;
		foreach ($section->getListeExpo() as $expo) {
			if (!empty($expo->_field['media_file2'])) {
				$id_img = "img_mini_" . $expo->_field['id'];;
				$tab_image_miniature = "\n$id_img = new Image(); $id_img.src = \"". $expo->_field['media_file2'] ."\";";					
			}
			$position++;
		}
	}

	
	// rechercher l'eventuelle uri pour se positionner 
	// sur une expo ou une section en particulier

	$defaut_section = null;
	$defaut_expo = null;
	if ( isset($rs['uri']) && $rs['uri'] != '/' ) {		
		// suppression du dernier slash
		$uri = strtolower($rs['uri']);
		if ($uri[strlen($uri)-1] == '/') { 
			$uri = substr($uri, 0, strlen($uri)-1); 
		}
		
		foreach ($site->_liste_section as $section) {
			if (strtolower($section->_sec_path) == $uri) {				
				$defaut_section = $section;
				break;
			}
			foreach ($section->getListeExpo() as $expo) {
				$uri_candidate = strtolower($expo->_field['url']);
				if (($uri_candidate == $uri) || ($uri_candidate == $uri . '/')) {
					$defaut_expo = $expo;
					break;
				}
			}
			if ( ($defaut_section != null) || ($defaut_expo != null) ) break;	
		}
	
	}
	
	$init_scroll = "";
	if ($defaut_section != null) {
		$url =  pathToAnchor($defaut_section->_sec_path);			
		$ini_scroll = " changeSection(\"$url\");\n";
	} else if ($defaut_expo != null) {
		$url =  pathToAnchor($defaut_expo->_field['url']);			
		$ini_scroll = " scrollToExpo('$url');\n";
	}
	
	foreach($site->_liste_section as $section) {
	
		$liste_expo = $section->getListeExpo();
		$nb_out = count($liste_expo);
		
		for($pos_out=0; $pos_out < $nb_out; $pos_out++) { 
		
			$expo = $liste_expo[$pos_out];
			
			$end_function = "";
			
			$j=$i+1;
			if ($pos_out < $nb_out-1) {
				$url = pathToAnchor($liste_expo[$pos_out+1]->_field['url']);
				//$end_function = ",end: function(opts) { scrollToExpo('$url'); }";					
				$end_function = " function(opts) { scrollToExpo('$url'); }";	
			} else {
				$url_section =  pathToAnchor($liste_expo[0]->_field['sec_path']);			
				//$end_function = ",end: function(opts) {	changeSection(\"$url_section\"); }";			
				$end_function = " function(opts) { changeSection(\"$url_section\"); }";
			}
			$url = pathToAnchor($expo->_field['url']);					
			$djs .= "	initCycle('slideshow$url', $i, $end_function);\n";
			
			$year = $expo->_field['year'];
			$libelle_section = $expo->_field['sec_path'];
			$year_expo["$url"][0] = $year;
			$year_expo["$url"][1] = pathToAnchor($libelle_section);
			
			$i++;
		}
	}

	$var_tab = '';
	$separateur = "";
	foreach ($year_expo as $path => $tab) {
		$year = $tab[0];
		$section = $tab[1];
		$var_tab .= " $separateur \"$path\" : [\"$year\",\"$section\"] ";
		if ($separateur== "") $separateur=",";
	}
	$var_tab = "\nvar TabYearExpo = { " . $var_tab . " };";
	
	
	// construction du tableau de correspondance expo / image
	$var_tab_expo_image = createTableauExpoImage();

	// construction du preload des images
	
	foreach($site->_liste_section as $section) {
	
		$liste_expo = $section->getListeExpo();		
		$nb_out = count($liste_expo);
		
		$pln_cpt =0;
		for($pos_out=0; $pos_out < $nb_out; $pos_out++) { 
		
			$expo = $liste_expo[$pos_out];
			$url = pathToAnchor($expo->_field['url']);
			
			$liste_media = $expo->_liste_media;
			if (!$liste_media) break;

			foreach ($liste_media as $media)
			{
				$file 		= $media['media_file'];
				$media_id   = $media['media_id'];
						
				$dest = $url . "img-$media_id";						
				$src = "'" . BASEURL . GIMGS . "/$file'";
				$dest = "$('#" . $url . "img-$media_id" . "')";				
				$preload_image .= "\npreloader.addImageAddress($src, $dest);";
				// on ne preload que la premiere image des expos
				break;

				
			}				
		
		}
	}
		
		

	
	return "
	
// chemin vers les images
var CHEMIN_IMAGES = \"" . BASEURL . GIMGS . "/\";

// preload des images rollback
$tab_image_miniature	
	
// tableau de correspondance expo/annee
$var_tab

// tableau de correspondance expo/iimage
$var_tab_expo_image


// logo
var logo_id = 0;
var affichage_logo_en_cours = 0;

	
$(window).load(function() {

    setTimeout(function() {  
	preloader.flush();
	

	// slideshow
$djs

	$(\".slideshow\").height(550);
	$(\".slideshow\").width(746);
	
	$(\".img-container img\").height(550);
	$(\".img-container img\").width(746);	
	
	$(\".img-container\").height(550);
	$(\".img-container\").width(746);	


	// attraper l'evenement entrer dans le cadre du logo		
	$(\"#logo\").mouseenter(	function () { genereLogo() } );


	$(\".lst-miniexpo\").jScrollPane();
	
	// attraper l'evenement redimentionnement du navigateur
	$(window).resize(function(){ redimentionneHauteur()	});		


	

		
	// masque la progressbar et affiche le site
	progressbar.hide()
	$('#cadre_info_image').css('display', 'none');
	$('#logo').css('display', 'block');
	$('#menu').css('display', 'block');
	$('#full').css('display', 'block');
	
	
	// ajouter une scrollbarre jquery sur les elements scrollable 
	$(\".with-scrollbar\").jScrollPane();

	genereLogo();
	
$ini_scroll

	// forcer l'evenement de redimentionnement du navigateur
	redimentionneHauteur();
	
}, 300);

});	


var preloader = new ImagePreloader(progressbar);
$(document).ready(function(){

$preload_image

preloader.preloadAllImage();

});

	";		
}

?>