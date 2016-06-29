<?php
// Update plugins Dominique92
$dirorg = 'ext/Dominique92/';
$dirdest = '../github.Dominique92/';

$dir = opendir($dirdest); 
while (false !== ($sdir = readdir($dir)) )
	if ($sdir != '.' && $sdir != '..' && is_dir ($dirorg.$sdir)) {
		echo"<pre style='background-color:white;color:black;font-size:14px;'>COPY ".var_export($dirorg.$sdir.'/... TO '.$dirdest.$sdir.'/...',true).'</pre>';
		copy ($dirorg.$sdir.'/README.md', $dirdest.$sdir.'/README.md');
		@mkdir ($dirdest.$sdir.'/ext/');
		@mkdir ($dirdest.$sdir.'/ext/Dominique92/');
		recurse_copy ($dirorg, $dirdest.$sdir.'/ext/Dominique92/');
	}

function recurse_copy($src,$dst) { 
	$dir = opendir($src); 
	@mkdir($dst); 
	while(false !== ( $file = readdir($dir)) )
		if (( $file != '.' ) && ( $file != '..' )) { 
			if ( is_dir($src . '/' . $file) )
				recurse_copy($src . '/' . $file,$dst . '/' . $file); 
			else
				copy($src . '/' . $file,$dst . '/' . $file); 
		} 
	closedir($dir); 
}
