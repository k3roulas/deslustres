
function ImageAddress(src, dest, alt, legende) {
	this.src = src;
    this.dest = dest;
    this.alt = alt;
    this.legende = legende;
}
 
function ImagePreloader(_progressBar, array_image_address)
{
    // initialize internal state.
   this.nLoaded = 0;
   
   // progressBar
   this.progressBar = _progressBar;

   // array contain image to create
   this.aImages = new Array;

   // stock image address in array
   if(typeof(array_image_address) !== 'undefined')
		this.aImageAddress = array_image_address;
   else {
		this.aImageAddress = new Array;
   }

   // record the number of images.
   this.nImages = this.aImageAddress.length; 

};

ImagePreloader.prototype.addImageAddress = function(src, dest, alt, legende) {	
	this.aImageAddress.push(new ImageAddress(src, dest, alt, legende));
}

ImagePreloader.prototype.preloadAllImage = function()
{  
   this.progressBar.setTotal(this.aImageAddress.length);
   // for each image, call preload()
  for ( var i = 0; i < this.aImageAddress.length; i++ )
      this.preload(this.aImageAddress[i], i);
};

ImagePreloader.prototype.flush = function() {
	for ( var i=0; i < this.aImageAddress.length; i++) {
		this.aImageAddress[i].dest.attr("src", this.aImageAddress[i].src );
		//this.aImageAddress[i].dest.append("<img height='100' width='100' src='" + this.aImageAddress[i].src + "'></img>");
		//this.aImageAddress[i].dest.append("<img height='100' width='100' src='" + this.aImageAddress[i].src + "'></img>");
	}
};

ImagePreloader.prototype.preload = function(image_address, position)
{
   // create new Image object and add to array
   var oImage = new Image;
   this.aImages.push(oImage);
   this.nImages++;
   
   // set up event handlers for the Image object
   oImage.onload = ImagePreloader.prototype.onload;
   oImage.onerror = ImagePreloader.prototype.onerror;
   oImage.onabort = ImagePreloader.prototype.onabort;
   oImage.onComplete = ImagePreloader.prototype.onComplete;

   // assign pointer back to this.
   oImage.oImagePreloader = this;
   oImage.bLoaded = false;

   // assign the .src property of the Image object
   oImage.src = image_address.src;
   oImage.dest = image_address.dest;

};

ImagePreloader.prototype.onComplete = function()
{
   this.nProcessed++;
   this.progressBar.loadedFile();
}
 

ImagePreloader.prototype.onload = function() {
   this.bLoaded = true;
   // update progress information
    //$('#cadre_info_image').html("<img height='100' width='100'  src='" + this.src + "'></img>");
    //$('#cadre_info_image').html("<img height='182' width='134'  src='" + this.src + "'></img>");
    //$('#cadre_info_progress').html("image " + this.oImagePreloader.nLoaded + " / " + this.oImagePreloader.nImages + "<br>");
	// update progressBar
   this.oImagePreloader.onComplete();   
}

ImagePreloader.prototype.onerror = function()
{
   this.bError = true;
   this.oImagePreloader.onComplete();
}

ImagePreloader.prototype.onabort = function()
{
   this.bAbort = true;
   this.oImagePreloader.onComplete();
}




function basename(path) {
	return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

function dirname(path) {
	return path.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
}

function scrollToArea(destination){
    //$.scrollTo($(destination), 1300, {axis:"xy", offset:{top:-134, left:-156}});
    $('#main').scrollTo($(destination), 1300, {axis:"xy", offset:{top:-134, left:-156}});
}

function addClassDelayed(jqObj, c, to) {    
    setTimeout(function() { jqObj.addClass(c); }, to);
}

function setActiveDelayed(the_class, the_id, c, to) {
    setTimeout(function() { the_class.removeClass(c); the_id.addClass(c); }, to);
}

function removeClassDelayed(jqObj, c, to) {    
    setTimeout(function() { jqObj.removeClass(c); }, to);
}

function changeSection(url_section) {	

	// analytics
	try{
		var pageTracker = _gat._getTracker(ID_TRACKER);
		pageTracker._trackPageview(url_section);
	} catch(err) { } 

	$('#sous-menu').find('a').css('text-decoration', 'none');
	
	$('.menu-section').css('text-decoration', 'none')	
	setTimeout(function() { $('#menu'+url_section).css('text-decoration', 'underline'); }, 1300);	

	scrollToArea('#'+url_section);
	
	$(".sous-menu-list").hide();
	$(".year-section").hide();
	$('#sous-menu-contener').hide();
	
	setTimeout(function() { genereLogo(); }, 900);

}

function scrollToExpo(url_destination){

	// analytics
	try{
		var pageTracker = _gat._getTracker(ID_TRACKER);
		pageTracker._trackPageview(url_destination);
	} catch(err) { } 
	$('#sous-menu').find('a').css('text-decoration', 'none');
	
	// gestion du sous menu annee
	if (!( TabYearExpo[url_destination] === undefined)) {
		// desactiver le surlignage du sous menu annees
		$('#sous-menu-year').find('a').css('text-decoration', 'none');
		// lien du sous sous menu vers l'annee
		var url_tmp = TabYearExpo[url_destination][1] + '-' + TabYearExpo[url_destination][0] ;
		// affichage du panneau contenant les sous menus 
		$('#' + url_tmp).parent().parent().parent().parent().show();
		// surligner le lien de l'annee
		$('#'+url_tmp).css('text-decoration', 'underline');
		// positionnement du sous menu sur la "page correspondante" (8 annees par pages)
		$('#sous-menu-year-contener').scrollTo(		$('#' + url_tmp).parent().parent().parent(), 500, {axis:'xy', offset:{top:0, left:0}});;		
	}


	// lance le positionnement du lien vers l'expo dans les sous menus
	setTimeout(function() {	
		$('#ssmenu'+url_destination).css('text-decoration', 'underline');
		$('#ssmenu'+url_destination).parent().parent().parent().parent().show();			
		$('#sous-menu-contener').fadeIn(0);
		$('#sous-menu-contener').scrollTo($('#ssmenu'+url_destination).parent().parent().parent(),0);		
	}, 1300);
	
	// retrouver les images associees a l'expo
	if (!( TabExpoImage[url_destination] === undefined)) {
		var tab_image = TabExpoImage[url_destination];
		// charger les images
		$('#'+url_destination+' .miniature-nav-contener img').each(function(i) { 
			if (this.src == CHEMIN_IMAGES + "sys-plnwhite.jpg") {			
				this.src = CHEMIN_IMAGES + "sys-" + tab_image[i]; 
			}
		});	
		$('#slideshow'+url_destination+' img').each(function(i) { 
			if (this.src == CHEMIN_IMAGES + "plnwhite.jpg") {
				this.src = CHEMIN_IMAGES + tab_image[i]; 
			}	
		});	

	}
	
	// reninit du slideshow
	$('#slideshow'+url_destination).cycle(0);
	// lance le mouvement vers l'expo
    $('#main').scrollTo($('#'+url_destination), 1300, {axis:"xy", offset:{top:-134, left:-156}});
	
	// genere un logo dans 0.9 s
	setTimeout(function() { genereLogo(); }, 900);
	
}

function scrollToSousMenu(destination){
	$('#sous-menu-contener').scrollTo(destination, 0, {axis:"xy", offset:{top:0, left:0}});
}


function genereLogo () {
			
	if (affichage_logo_en_cours == 1) return;
	affichage_logo_en_cours = 1;
	
	// nombre aleatoire de 0 a 3
	var tab = [0,1,2,3];
	tab[logo_id] = "rem";
	for (var i=0; i< tab.length; i++) { if (tab[i]== "rem" ) {tab.splice(i,1);i--;}}
	aleatoire=Math.floor(Math.random()*tab.length);
	logo_id = tab[aleatoire];				

	//$('.logo-lettre').hide();
	laps_temps = 200;
	$('.logo:visible').fadeOut(laps_temps);
	
    setTimeout(function() { 
		$('#logo'+ logo_id  ).fadeIn(laps_temps);
		setTimeout(function() { affichage_logo_en_cours = 0 }, laps_temps);	
	}, laps_temps);
		
}


function genereLogo2 () { /* PLN A SUPPRIMER */
			
	if (affichage_logo_en_cours == 1) return;
	affichage_logo_en_cours = 1;
	
	// nombre aleatoire de 0 a 2
	var tab = [0,1,2,3,4];
	tab[lettre_d] = "rem";
	for (var i=0; i< tab.length; i++) { if (tab[i]== "rem" ) {tab.splice(i,1);i--;}}
	aleatoire=Math.floor(Math.random()*tab.length);
	lettre_d = tab[aleatoire];				
				
	var tab = [0,1,2,3,4];
	tab[lettre_d] = "rem";
	tab[lettre_l] = "rem";
	for (var i=0; i< tab.length; i++) { if (tab[i]== "rem" ) {tab.splice(i,1);i--;}}
	aleatoire=Math.floor(Math.random()*tab.length);
	lettre_l = tab[aleatoire];
				
	var tab = [0,1,2,3,4];
	tab[lettre_d] = "rem";
	tab[lettre_l] = "rem";
	tab[lettre_et] = "rem";
	for (var i=0; i< tab.length; i++) { if (tab[i]== "rem" ) {tab.splice(i,1);i--;}}
	aleatoire=Math.floor(Math.random()*tab.length);
	lettre_et = tab[aleatoire];		

	var tab = [0,1,2,3,4];
	tab[lettre_d] = "rem";
	tab[lettre_l] = "rem";
	tab[lettre_et] = "rem";
	tab[lettre_a] = "rem";
	for (var i=0; i< tab.length; i++) { if (tab[i]== "rem" ) {tab.splice(i,1);i--;}}
	lettre_a = tab[0];		

	//$('.logo-lettre').hide();
	laps_temps = 200;
	$('.logo-lettre:visible').fadeOut(laps_temps);
	
    setTimeout(function() { 
		$('#logo-lettre-'+ (lettre_d+1) +'d').fadeIn(laps_temps);
		$('#logo-lettre-'+ (lettre_l+1) +'l').fadeIn(laps_temps);
		$('#logo-lettre-'+ (lettre_et+1) +'et').fadeIn(laps_temps);
		$('#logo-lettre-'+ (lettre_a+1) +'a').fadeIn(laps_temps);
		setTimeout(function() { affichage_logo_en_cours = 0 }, laps_temps);	
	}, laps_temps);
		
}

function redimentionneHauteur() {

		// PLN a reecrire
		if ($(window).width() <= 800) $('#main').width(800);
		if ($(window).height() <= 600) $('#main').height(600);

		// ratio des images
		ratio = 602/444;
		
		// definition minimale
		//def_dim_min = { x: 960, y:640 };
		def_dim_min = { x: 800, y:600 };
		
		// taille des images max 746px × 550px
		//img_dim_max = { x: 746, y:550 };
		img_dim_max = { x: 637, y:469 };

		// a partir des deux dimentions précédentes
		y_tmp = def_dim_min.y -245;
		x_tmp = Math.floor(y_tmp * ratio);	
		
		// calcul dimention et espacement des elements de la barre de navigation du slideshow
		x_m = Math.floor(44*y_tmp/444);			
		e_m = Math.floor(12*y_tmp/444);			
		x_tmp = (x_m * 11) + (e_m * 10);
		y_tmp = Math.floor(x_tmp * ratio);	

		img_dim_min = { x: x_tmp, y :y_tmp };	

		//img_dim_min = { x: 529, y :390 };		
		
		// calculer nlle dimention des images
		//nle_hauteur = $(window).height()-245;
		nle_hauteur = $(window).height()-215;
		if (nle_hauteur > img_dim_max.y) { 
			nle_hauteur = img_dim_max.y; 
		}
		nle_largeur = Math.floor(nle_hauteur * ratio);	
		
		if (nle_largeur > $(window).width() -360 ) {
			nle_largeur = $(window).width() -360;
			if (nle_largeur > img_dim_max.x) { 
				nle_largeur = img_dim_max.x; 
			}
			nle_hauteur = Math.floor(nle_largeur/ratio);
		}
			
		
		// calcul dimention et espacement des elements de la barre de navigation du slideshow
		nle_largeur_miniature = Math.floor(44*nle_hauteur/444);			
		nl_espacement_miniature = Math.floor(12*nle_hauteur/444);	
		
		// fixe les nouvelles dimensions a partir du calcul précedent
		nle_largeur = (nle_largeur_miniature * 11) + (nl_espacement_miniature * 10);
		
		if (nle_largeur < img_dim_min.x) { 
			nle_largeur = img_dim_min.x; 
			nle_largeur_miniature = Math.floor(44*nle_largeur/602);			
			nl_espacement_miniature = Math.floor(12*nle_largeur/602);	
		}
		if (nle_largeur > img_dim_max.x) { nle_largeur = img_dim_max.x; }
		nle_hauteur = Math.floor(nle_largeur / ratio);			
		
		if ( $(".img-container img").height() ==  nle_hauteur) {
			return;
		}
		
		// mise a jour du cadre main (contenant des expos)
		if ($(window).width() > 1450) {
			$('#main').width(1450); //(nle_largeur+350)		
		} else { 
			$('#main').width($(window).width()); //(nle_largeur+350)
		}
		$('#main').height($(window).height());
		
		
		// mise a jour de la largeur des expos
		$('.expo').width(nle_largeur+210);		
		$('.expo').css('margin-right', 960+450-(nle_largeur+350));
		
		// mise a jour de la hauteur des listes de miniexpo		
		$('.minature-nav img').height(nle_largeur_miniature);
		$('.minature-nav img').width(nle_largeur_miniature);
		$('.minature-nav li').css('margin-right', nl_espacement_miniature);
		$('.miniature-nav-contener').width(nle_largeur);
		$('.miniature-nav-contener').height(nle_largeur_miniature+5);
		$('.minature-nav').height(nle_largeur_miniature+5);
		

		// mise a jour de la dimension des images
		$(".img-container img").height(nle_hauteur);
		$(".img-container img").width(nle_largeur);
		$(".img-container").height(nle_hauteur);
		$(".img-container").width(nle_largeur);
		$(".slideshow").height(nle_hauteur);
		$(".slideshow").width(nle_largeur);
		
		// mise a jour de la dimention de l'image de contact
		$("#img-container-cache-contact- img").width(nle_largeur-330);
		$("#img-container-cache-contact- img").height(Math.floor((nle_largeur-330) / ratio));
		

		// txt-expo-avec-image : laisser la place pour le share au dessous 
		//$(".txt-expo-avec-image").css("height", nle_hauteur - 32 + 'px');
		$(".txt-expo-avec-image").css("height", nle_hauteur + 'px');
		$("#-cache-contact- .txt-expo-avec-image").css("height", nle_hauteur+65 + 'px');
		$(".txt_contact").css('height', nle_hauteur - $(".info_site").height()-30 + 'px');
		$(".info_site").css('margin-top', $(".txt_contact").height() + 70 + 'px');		
		$(".with-scrollbar").jScrollPane();		
		

		// positionner les bloc de description		
		//$(".txt-expo-avec-image").css('margin-right', 960-(nle_largeur + 10 +192));		
		
		// positionner le logo
		$("#logo").css('left', nle_largeur + 160);	
		

		$(".miniature-nav-suivante").css('margin-left', nle_largeur + 11);
		$(".miniature-nav-precedente").css('margin-left', nle_largeur + 11);

		$('.lst-miniexpo').each(function() {$(this).data('jsp').destroy();});	
		$(".lst-miniexpo").css('height', nle_hauteur + nle_largeur_miniature + 30 + 'px');
		$(".lst-miniexpo").css('width', nle_largeur + 176 + 'px');
		$(".lst-miniexpo .jspContainer").css('width', nle_largeur + 176 + 'px');
		$(".lst-miniexpo .jspPane").css('width', nle_largeur + 176 + 'px');		
		$(".lst-miniexpo").jScrollPane();


}

function cyclePagerAnchorBuilder(idx, slide) {   
	return '<li><a href="#"><img src="' + dirname(slide.childNodes[0].src) + '/sys-' + basename(slide.childNodes[0].src) +  '" width="44" height="44" /></a></li>';
}  		
	
function initCycle(id_slideshow, num, fct_end) {
	$('#' + id_slideshow).cycle( {
		fx:'fade',
		pagerEvent: 'mouseover',
		speed:'2000',
		timeout: 0,
		nowrap: 1, 
		nextPagerPage: '#miniature-nav-suivante' + num, 
		prevPagerPage: '#miniature-nav-precedente' + num, 
		next: '.next' + num, 
		prev: '.prev' + num, 
		containerResize: false, 
		pager:  '#minature-nav' + num, 
		pagerAnchorBuilder: function (idx, slide) { return cyclePagerAnchorBuilder(idx, slide); },
		end: function(opts) { fct_end(opts); }
	});	
}

