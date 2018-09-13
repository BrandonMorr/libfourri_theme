<?php

// this is an *auxiliary* PHP page/tool to find the image that belongs to a given CSS class, since there seems to be
// no way to add a CSS file in a Durpal content node/page when we only can (want to) use the 'Full HTML' editing mode.

$txtError = "CSS class needed: ?cl=className";

$cssClass = @trim(($_GET['cl']));
if ( !empty($cssClass) && strlen($cssClass) < 50 && !strchr($cssClass,"/") ) {

	$modPath = dirname($_SERVER['SCRIPT_FILENAME']) . "/";	// = "https://www.dora.lib4ri.ch/eawag/sites/all/themes/libfourri_theme/css/";
	$cssFile = "../css/styles.css";		// assuming the css folder in in the same level as the folder this file here is contained.

	$imgPath = "";
	$imgType = "";
	
	if ( !( $cssRow = file_get_contents( $modPath.$cssFile ) ) ) {
		$txtError = "File 'styles.css' not found";
	} else {
		if ( substr($cssClass,0,1) != "." && substr($cssClass,0,1) != "#" ) { $cssClass = ".".$cssClass; }
		
		$cssAry = explode( "/"."*", $cssRow );		// part 1/2 to get rid of / * comments * /
		foreach( $cssAry as $cssRow ) {
			$cssRow = ( $tmp = strchr($cssRow,"*"."/") ) ? trim(substr($tmp,2)) : trim($cssRow);	// part 2/2 to get rid of / * comments * /
			if ( empty($cssRow) || substr($cssRow,0,2) == "//" ) {
				continue;
			}
			if ( empty($imgType) ) {
				if ( strchr($cssRow,$cssClass) ) { $imgType = "any"; }
				continue;
			}
			if ( $imgPath = strchr($cssRow,"url") ) {
				$imgPath = str_replace(": url", ":url", $cssRow );
				$imgPath = str_replace("url (", "url(", $imgPath );
				if ( $imgPath = strchr($imgPath,":url(") ) {
					// also something like this is possible: url('data:image/png;base64,iVBORw0KGgoAAAANAAAABlBMVEX///8AAABVwtAXRSTlMAQObTkSuQmCC')
					$imgPath = substr(strchr(strtr($imgPath,"\"","'"),"'"),1);
					$imgPath = trim( strtok(strtok($imgPath,"'")."?","?") );
					if ( $cssRow = strrchr($imgPath,".") ) {
						$imgType = strtolower(substr($cssRow,1));
						break;
					}
				}
			}
		}

		if ( empty($imgType) ) {
			$txtError = "class '" . substr($cssClass,1) . "' not found";
		} elseif ( @!file_exists($modPath.$imgPath) ) {
					echo $modPath.$imgPath;
					exit;

			$txtError = "file '{$imgPath}' not found";
		} else {
			header( "Content-type: image/" . ( $imgType == "jpg" ? "jpeg" : $imgType ) );
			readfile($modPath.$imgPath);
			exit;
		}
	}
}


// show an error image:
header( "Content-type: image/png" );
$im = imagecreate(640,120);
imagecolorallocate($im, 31, 159, 191);		// background color
imagestring($im, 8, 20, 25,  "*** Lib4RI image placeholder ***", imagecolorallocate($im, 0, 15, 127) );
$tColor = imagecolorallocate($im, 143, 15, 0);
imagestring($im, 8, 20, 55,  "ERROR:", $tColor);
imagestring($im, 8, 20, 70,  $txtError, $tColor);
imagepng($im);
imagedestroy($im);

?>
