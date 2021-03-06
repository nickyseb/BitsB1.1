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

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('delete') );

    if (!mkglobal("id"))
      stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");

    $id = 0 + $id;
    if (!is_valid_id($id))
      stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");
      


function deletetorrent($id) {
    global $TBDEV;
    mysql_query("DELETE FROM torrents WHERE id = $id");
    mysql_query("DELETE FROM bookmarks WHERE torrentid = $id");
    mysql_query("DELETE FROM snatched WHERE torrentid = $id");
    foreach(explode(".","peers.files.comments.ratings") as $x)
        @mysql_query("DELETE FROM $x WHERE torrent = $id");
    unlink("{$TBDEV['torrent_dir']}/$id.torrent");
}

$res = mysql_query("SELECT name,owner,seeders FROM torrents WHERE id = $id");
$row = mysql_fetch_assoc($res);
if (!$row)
	stderr("{$lang['delete_failed']}", "{$lang['delete_not_exist']}");

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)
	stderr("{$lang['delete_failed']}", "{$lang['delete_not_owner']}\n");

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 5)
	bark("{$lang['delete_invalid']}");

//$r = $_POST["r"]; // whats this
$reason = $_POST["reason"];

if ($rt == 1)
	$reasonstr = "{$lang['delete_dead']}";
elseif ($rt == 2)
	$reasonstr = "{$lang['delete_dupe']}" . ($reason[0] ? (": " . trim($reason[0])) : "!");
elseif ($rt == 3)
	$reasonstr = "{$lang['delete_nuked']}" . ($reason[1] ? (": " . trim($reason[1])) : "!");
elseif ($rt == 4)
{
	if (!$reason[2])
		stderr("{$lang['delete_failed']}", "{$lang['delete_violated']}");
  $reasonstr = $TBDEV['site_name']."{$lang['delete_rules']}" . trim($reason[2]);
}
else
{
	if (!$reason[3])
		stderr("{$lang['delete_failed']}", "{$lang['delete_reason']}");
  $reasonstr = trim($reason[3]);
}

    deletetorrent($id);

    write_log("{$lang['delete_torrent']} $id ({$row['name']}){$lang['delete_deleted_by']}{$CURUSER['username']} ($reasonstr)\n");
    //===remove karma 
    @mysql_query("UPDATE users SET seedbonus = seedbonus-15.0 WHERE id = ".sqlesc($row["owner"])."") or sqlerr(__FILE__, __LINE__);
    //===end

    if (isset($_POST["returnto"]))
      $ret = "<a href='" . htmlspecialchars($_POST["returnto"]) . "'>{$lang['delete_go_back']}</a>";
    else
      $ret = "<a href='{$TBDEV['baseurl']}/index.php'>{$lang['delete_back_index']}</a>";

    $HTMLOUT = '';
    $HTMLOUT .= "<h2>{$lang['delete_deleted']}</h2>
    <p><$ret</p>";


    print stdhead("{$lang['delete_deleted']}") . $HTMLOUT . stdfoot();

?>