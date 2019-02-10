<html>
<body>
<table>





<?php

$tab_d = array(1,6);
$tab_l = array(2,3,6);
$tab_e = array(1,2,5);
$tab_a = array(1,2,3);

$nb=0;
foreach ($tab_d as $d) {	
	foreach ($tab_l as $l) {	
		if ($l != $d) {
			foreach ($tab_e as $e) {
				if (($e != $l) && ($e != $d) ){
					foreach ($tab_a as $a) {
						if ( ($a != $e) && ($a != $e) && ($a != $l) && ($a != $d) ) {
							// test au moins trois different
							if ( (! (($d == $e) && ($a == $l)) ) || ( ($d != $e) ) ) {
								echo "<tr style=\"height: 70px;\" ><td>$d$l$e$a</td>\n";

								$img =  "<img src='logo-$d". "d.jpg'>";
								$img .= "<img src='logo-$l". "l.jpg'>";
								$img .= "<img src='logo-$e". "e.jpg'>";
								$img .= "<img src='logo-$a". "a.jpg'>";
								echo "<td>$img</td></tr>";

								$nb++;
							}
						
						}			
					}		
				}
			}
		}
	}
}
echo "nb : $nb";


?>
</table>
</body>
</html>