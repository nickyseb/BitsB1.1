<?php
/*
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
+------------------------------------------------
*/

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

require_once "include/user_functions.php";
require_once "include/html_functions.php";
staffonly();

if ($CURUSER['class'] < UC_SYSOP)
stderr('Permission Denied', '...');

if($_SERVER['REQUEST_METHOD'] == 'POST') {

	$limits = isset($_POST['limit']) && is_array($_POST['limit']) ? $_POST['limit'] : 0;
	
	foreach($limits as $class=>$limit)
		if($limit == 0) unset($limits[$class]);
		
	if(file_put_contents($TBDEV['flood_file'],serialize($limits))) {
		header('Refresh: 2; url=admin.php?action=floodlimit');
		stderr('Success','Limits saved! returning to main page');
	} else
		stderr('Err','Something went wrong make sure '.$_file.' exists and it is chmoded 0777');

} else {

if(!file_exists($TBDEV['flood_file']) || !is_array($limit = unserialize(file_get_contents($TBDEV['flood_file']))))
	$limit = array();

$out = begin_main_frame().begin_frame('Edit flood limit');
$out .= '<form method=\'post\' action=\'\' ><table width=\'60%\' align=\'center\'><tr><td class=\'colhead\'>User class</td><td class=\'colhead\'>Limit</td></tr>';
	for($i=UC_USER;$i<=UC_SYSOP;$i++)
		$out .= '<tr><td align=\'left\'>'.get_user_class_name($i).'</td><td><input name=\'limit['.$i.']\' type=\'text\' size=\'10\' value=\''.(isset($limit[$i]) ? $limit[$i] : 0).'\'/></td></tr>';
$out .= '<tr><td colspan=\'2\'>Note if you want no limit for the user class set the limit to 0</td></tr><tr><td colspan=\'2\' class=\'colhead\'><input type=\'submit\' value=\'Save\' /></td></tr>';
$out .= '</table></form>'.end_frame().end_main_frame();

print(stdhead('Flood limit').$out.stdfoot());
}
?>