<?php
include '../system/db.class.php';
include '../system/user.php';
include '../system/backups.php';
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html;charset=".IN_CHARSET);
header("Access-Control-Allow-Origin:*");

$ac = SafeRequest("ac","get");
if($ac == 'getpathj'){
    $id = intval(SafeRequest("id","get"));
    if(is_int($id)){
        $row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
        if($row){
            $plist = $row['in_plist'];
//            if(strpos($plist,IN_DOMAIN)===false){
//                $path=$GLOBALS['db']->getone("select path from prefix_path where uid=$id");
//                if($path){
//                    $plist=$path;
//                }else{
//                    $plist="";
//                }
//            }
			$data['version']=$row['in_bvs'];
			$data['url']=IN_ADLINK.'/data/attachment/'.$plist;
			$data['vername']=$row['in_bsvs'];
			
            echo json_encode($data);
            return ;
        }
    }

    echo "";
    return ;
}
$ac = SafeRequest("ac","get");
if($ac == 'getpath'){
    $id = intval(SafeRequest("id","get"));
    if(is_int($id)){
        $row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
        if($row){
            $plist = $row['in_plist'];
//            if(strpos($plist,IN_DOMAIN)===false){
//                $path=$GLOBALS['db']->getone("select path from prefix_path where uid=$id");
//                if($path){
//                    $plist=$path;
//                }else{
//                    $plist="";
//                }
//            }
            echo $row['in_bvs']."|".IN_ADLINK.'/data/attachment/'.$plist."|".$row['in_bsvs'];
            return ;
        }
    }

    echo "";
    return ;
}
$GLOBALS['userlogined'] or exit('-1');

if($ac == 'del'){
	$id = intval(SafeRequest("id","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
	$row or exit('-2');
	$row['in_uid'] == $GLOBALS['erduo_in_userid'] or exit('-3');
	$GLOBALS['db']->query("delete from ".tname('app')." where in_id=".$id);
	$GLOBALS['db']->query("delete from ".tname('signlog')." where in_aid=".$id);
	@unlink(IN_ROOT.'./data/attachment/'.$row['in_icon']);
	@unlink(IN_ROOT.'./data/attachment/'.str_replace('.png', '.plist', $row['in_icon']));
	@unlink(IN_ROOT.'./data/attachment/'.str_replace('.png', '.ipa', $row['in_icon']));
	@unlink(IN_ROOT.'./data/attachment/'.str_replace('.png', '.apk', $row['in_icon']));
	echo '1';
}elseif($ac == 'edit'){
	$id = intval(SafeRequest("id","get"));
	$link = SafeRequest("link","get");
	$name = unescape(SafeRequest("name","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
	$row or exit('-2');
	$row['in_uid'] == $GLOBALS['erduo_in_userid'] or exit('-3');
	in_array($link, array('data', 'source', 'static')) and exit('-4');
	is_numeric($link) and exit('-4');
	$one = $GLOBALS['db']->getone("select in_id from ".tname('app')." where in_link='$link' and in_id<>".$id);
	$link and $one and exit('-5');
	$link and !IN_REWRITE and exit('-6');
	$GLOBALS['db']->query("update ".tname('app')." set in_name='$name',in_link='$link' where in_id=".$id);
	echo '1';
}elseif($ac == 'editsuper'){
	$id = intval(SafeRequest("id","get"));
	$name = unescape(SafeRequest("name","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
	$row or exit('-2');
	$GLOBALS['db']->query("update ".tname('app')." set in_ios_super='$name' where in_id=".$id);
    //back_path_su($id,$name);
	echo '1';
}elseif($ac == 'info'){
	$mobile = SafeRequest("mobile","get");
	$qq = SafeRequest("qq","get");
	$firm = unescape(SafeRequest("firm","get"));
	$job = unescape(SafeRequest("job","get"));
	updatetable('user', array('in_mobile' => $mobile,'in_qq' => $qq,'in_firm' => $firm,'in_job' => $job), array('in_userid' => $GLOBALS['erduo_in_userid']));
	echo '1';
}elseif($ac == 'pwd'){
	$old = substr(md5(SafeRequest("old","get")), 8, 16);
	$new = substr(md5(SafeRequest("new","get")), 8, 16);
	$old == $GLOBALS['erduo_in_userpassword'] or exit('-2');
	updatetable('user', array('in_userpassword' => $new), array('in_userid' => $GLOBALS['erduo_in_userid']));
	echo '1';
}elseif($ac == 'each_del'){
	$aid = intval(SafeRequest("aid","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$aid);
	$row['in_uid'] == $GLOBALS['erduo_in_userid'] or exit('-2');
	updatetable('app', array('in_kid' => 0), array('in_id' => $aid));
	updatetable('app', array('in_kid' => 0), array('in_id' => $row['in_kid']));
	echo '1';
}elseif($ac == 'each_add'){
	$aid = intval(SafeRequest("aid","get"));
	$kid = intval(SafeRequest("kid","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$aid);
	$row or exit('-2');
	$row['in_uid'] == $GLOBALS['erduo_in_userid'] or exit('-3');
	getfield('app', 'in_uid', 'in_id', $kid) == $GLOBALS['erduo_in_userid'] or exit('-3');
	getfield('app', 'in_form', 'in_id', $kid) == $row['in_form'] and exit('-4');
	updatetable('app', array('in_kid' => $kid), array('in_id' => $aid));
	updatetable('app', array('in_kid' => $aid), array('in_id' => $kid));
	echo '1';
}elseif($ac == 'remove_ad'){
	$id = intval(SafeRequest("id","get"));
	$row = $GLOBALS['db']->getrow("select * from ".tname('app')." where in_id=".$id);
	$row or exit('-2');
	$row['in_uid'] == $GLOBALS['erduo_in_userid'] or exit('-3');
	$GLOBALS['erduo_in_points'] < IN_ADPOINTS and exit('-4');
	$GLOBALS['db']->query("update ".tname('user')." set in_points=in_points-".IN_ADPOINTS." where in_userid=".$GLOBALS['erduo_in_userid']);
	updatetable('app', array('in_removead' => 1), array('in_id' => $id));
	echo '1';
}
?>