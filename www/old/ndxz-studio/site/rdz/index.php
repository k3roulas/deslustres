<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
 <link rel="icon" type="image/png" href="<%baseurl%>/favicon.png" />

<title><plug:striptitle title='<%obj_name%> <%title%>' /></title>
<meta name="description"content= "Harry Graphic Design Studio - Harry est un studio de communication et de création graphique basé à Bruxelles."/>
<meta name="keywords" content= "harry, harrystudio, harry studio, harryvachercherlavoiture, harry va chercher la voiture, harry vas chercher la voiture, graphic design, graphisme, graphiste, communication graphique, communication visuelle, affiche, logo, bruxelles, brussels, belgique, belgium, Eve Giordani, Ronan Deriez, agence, studio"/>
<meta name="author" content= "Eve Giordani, Ronan Deriez"/>
<meta name="identifier-url" content= "http://www.harryvachercherlavoiture.com"/>
<meta name="identifier-url" content= "http://www.harrystudio.com"/>

<link rel='stylesheet' href='<%baseurl%><%basename%>/site/<%obj_theme%>/style.css' type='text/css' />
<!--[if IE 6]>
<link rel='stylesheet' href='<%baseurl%><%basename%>/site/<%obj_theme%>/ie_6.css' type='text/css' />
<![endif]-->
<plug:front_lib_css />
<plug:front_dyn_css />
<script type='text/javascript' src='<%baseurl%><%basename%>/site/js/jquery.js'></script>
<script type='text/javascript' src='<%baseurl%><%basename%>/site/js/cookie.js'></script>
<plug:front_lib_js />
<plug:front_dyn_js />
<plug:backgrounder color='<%color%>', img='<%bgimg%>', tile='<%tiling%>' />
</head>

<body class='section-<%section_id%>'>


<div id='logo'>
<a href='<%baseurl%>'><img src='./files/gimgs/logo.jpg'></a>
</div>

<div id='menu'>
<%obj_itop%>
<plug:front_index />
<%obj_ibot%>
</div>

<div id='content'>
<!-- text and image -->
<plug:front_exhibit />
<!-- end text and image -->
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-13084125-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
