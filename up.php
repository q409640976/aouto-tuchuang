<?php
/*
 * @Author: yumusb
 * @Date: 2020-03-27 14:45:07
 * @LastEditors: yumusb
 * @LastEditTime: 2020-03-27 14:45:34
 * @Description: 
 */
/*
URL https://github.com/yumusb/autoPicCdn

注意事项：
1. php中开启 Curl扩展
2. 如果使用github，则服务器需要能和https://api.github.com正常通信。（建议放到国外 http://renzhijia.com/buy/index/7/?yumu 美国免费空间推荐 优惠码 free2 ）
3. 如果使用Gitee，请保证 上传的文件 遵循国内法律
4. 懒的搭建或者不会搭建，就直接用 http://chuibi.cn/
5. 本源码已经开启智能AI授权模式，请到 http://33.al/donate 打赏5元以后 再开始配置
*/

error_reporting(E_ALL); //0  E_ALL
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set("PRC");


if(!is_callable('curl_init')){
    $return['code'] = 500;
    $return['msg'] = "服务器不支持Curl扩展";
    $return['url'] = null;
    die(json_encode($return));
}

//必选项
define("TYPE","GITHUB");//选择github
//define("TYPE","GITEE");//选择gitee，如果使用gitee，需要手动建立master分支，可以看这里 https://gitee.com/help/articles/4122

define("USER","111");//你的GitHub/Gitee的用户名



define("MAIL","admin@222.com");//邮箱无所谓，随便写

define("TOKEN","333");
// Github 去这个页面 https://github.com/settings/tokens生成一个有写权限的token（repo：Full control of private repositories 和write:packages前打勾）
// gitee  去往这个页面 https://gitee.com/personal_access_tokens

//数据库配置文件
//请确保把当前目录下的 pic.sql 导入到你的数据库
$database = array(
        'dbname' => 'tu',//你的数据库名字
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'tu',//你的数据库用户名
        'pass' => 'tu',//你的数据库用户名对应的密码
    );
    

$table = 'remote_imgs'; //表名字

if(TYPE!=="GITHUB" && TYPE!=="GITEE"){
    $return['code'] = 500;
    $return['msg'] = "Baby，你要传到哪里呢？";
    $return['url'] = null;
    die(json_encode($return));
}
try {
    $db = new PDO("mysql:dbname=" . $database['dbname'] . ";host=" . $database['host'] . ";" . "port=" . $database['port'] . ";", $database['user'], $database['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
} catch (PDOException $e) {
    $return['code'] = 500;
    $return['msg'] = "数据库出错，请检查 up.php中的database配置项.<br> " . $e->getMessage();
    $return['url'] = null;
    die(json_encode($return));
}
//me REPO这个常量,最后在定义,因为需要从数据库抓取
define("QIANZHUI",'444');
define("REPOID",$repoID=GetRepo());

define("REPO",QIANZHUI. REPOID );//必须是上面用户名下的 公开仓库 的前缀
function GetIP(){ 
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
	$ip = getenv("REMOTE_ADDR"); 
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
	$ip = $_SERVER['REMOTE_ADDR']; 
	else
	$ip = "unknow"; 
	return($ip); 
}
function upload_github($filename, $content)
{   //https://developer.github.com/v3/repos/contents/#create-or-update-file-contents
    //PUT /repos/:owner/:repo/contents/:path
    $url = "https://api.github.com/repos/" . USER . "/" . REPO . "/contents/" . $filename;
    $ch = curl_init();
    $defaultOptions=[
        CURLOPT_URL => $url,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST=>"PUT",
        CURLOPT_POSTFIELDS=>json_encode([
            "message"=>"uploadfile",
            "committer"=> [
                "name"=> USER,
                "email"=>MAIL,
            ],
            "content"=> $content,
        ]),
        CURLOPT_HTTPHEADER => [
            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language:zh-CN,en-US;q=0.7,en;q=0.3",
            "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            'Authorization:token '.TOKEN,
        ],
    ];
    curl_setopt_array($ch, $defaultOptions);
    $chContents = curl_exec($ch);
    $chContents=json_decode($chContents, true);
    curl_close($ch);
    if( array_key_exists('message',$chContents) ){
        $return['code'] = 404;
        $return['msg'] = "上传接口失败:repo ".$chContents['message'];
        $return['url'] = null;
        die(json_encode($return));        
    }
    return $chContents;
}


//https://developer.github.com/v3/repos/#create-a-repository-for-the-authenticated-user
//POST /user/repos
function create_repo($repoName)
{   
    //echo 'filename_:'.$filename; 
    $url = "https://api.github.com/user/repos"  ;
    $ch = curl_init();
    $defaultOptions=[
        CURLOPT_URL => $url,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST=>"POST",
        CURLOPT_POSTFIELDS=>json_encode([
            "name"=>$repoName,
			"description"=>"create by php",
			"homepage"=>"https://developer.github.com/v3/repos/#create-a-repository-for-the-authenticated-user",
			"private"=>false,
			"has_issues"=>true,
            "content"=> true,
			"has_wiki"=> true,
        ]),
        CURLOPT_HTTPHEADER => [
            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language:zh-CN,en-US;q=0.7,en;q=0.3",
            "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            'Authorization:token '.TOKEN,
        ],
    ];
    curl_setopt_array($ch, $defaultOptions);
    $chContents = curl_exec($ch);
    
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpCode === 201)
        return true;
    else{
        $return['code'] = $httpCode;
        $return['msg'] = "自动创建库[$repoName]失败,code:".$httpCode;
        $return['url'] = null;
        die(json_encode($return));
    }
        
}

function upload_gitee($filename, $content)
{   
    $url = "https://gitee.com/api/v5/repos/". USER ."/". REPO ."/contents/".$filename;
    $ch = curl_init();
    $defaultOptions=[
        CURLOPT_URL => $url,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST=>"POST",
        CURLOPT_POSTFIELDS=>[
            "access_token"=>TOKEN,
            "message"=>"uploadfile",
            "content"=> $content,
            "owner"=>USER,
            "repo"=>REPO,
            "path"=>$filename,
            "branch"=>"master"
        ],
        CURLOPT_HTTPHEADER => [
            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language:zh-CN,en-US;q=0.7,en;q=0.3",
            "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36"
        ],
    ];
    curl_setopt_array($ch, $defaultOptions);
    $chContents = curl_exec($ch);
    curl_close($ch);
    return $chContents;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES["pic"]["error"] <= 0 && $_FILES["pic"]["size"] >100 ) {
    $filename = md5(time().mt_rand(10,1000)) . get_extension($_FILES["pic"]["name"]);
    $oldname = $_FILES['pic']['name'];
    $mime=$_FILES['pic']['type']; //上传文件的MIME类型  
    $tmpName = './tmp' . md5($filename);
    move_uploaded_file($_FILES['pic']['tmp_name'], $tmpName);
    $filemd5 = md5_file($tmpName);
    $filesize=(int)($_FILES["pic"]["size"] /1024); //KB
    $row = $db->query("SELECT `imgurl` FROM `{$table}` WHERE `imgmd5`= '{$filemd5}' ")->fetch(PDO::FETCH_ASSOC);
    if($row){
    	$remoteimg=$row['imgurl'];
    }else{
    	$content = base64_encode(file_get_contents($tmpName));
    	
    	if(TYPE==="GITHUB"){
    	    $res = upload_github($filename, $content);
    	}
    	else{
    	    $res = json_decode(upload_gitee($filename, $content), true);
    	}
    	
		if($res['content']['path'] != ""){
		    if(TYPE==="GITHUB"){
    	        $remoteimg = 'https://cdn.jsdelivr.net/gh/' . USER . '/' . REPO . '@latest/' . $res['content']['path'];
        	}
        	else{
        	    $remoteimg = $res['content']['download_url'];
        	}
	    	$tmp = $db->prepare("INSERT INTO `{$table}`(`imgmd5`, `imguploadtime`, `imguploadip`,`imgurl`,`repo`,`filesize`,`filename`) VALUES (?,now(),?,?,?,?,?)");  //me增加一个repo字段
	    	$tmp->execute(array($filemd5 , GetIP(), $remoteimg,REPO,$filesize,$oldname));//me增加一个repo字段
	    	//更新repo表的 filesize
	    	$tmp = $db->prepare("update  `repo` set `filesize` = `filesize` + ? where `id`=?");  //me增加一个repo字段
	    	$tmp->execute(array($filesize,REPOID));//me增加一个repo字段
		}
    }
    unlink($tmpName);
    if ($remoteimg != "") {
        $return['code'] = 'success';
        $return['data']['url'] = $remoteimg;
        $return['data']['filemd5'] = $filemd5;
        $return['data']['filename'] = $oldname;
        $return['data']['mime'] = $mime;
    } else {
        $return['code'] = 500;
        $return['msg'] = '上传失败，我们会尽快修复';
        $return['url'] = null;
    }
} else {
    $return['code'] = 404;
    $return['msg'] = '无法识别你的文件';
    $return['url'] = null;
}
exit(json_encode($return));

//me获取当前库repo
function GetRepo(){ 
    global $db;
    $repoID=0;
    //`id` `date` `filesize`
    $row = $db->query("SELECT `id`,`filesize` FROM `repo` order by id desc limit 1 ")->fetch(PDO::FETCH_ASSOC);
    //如果有
    if($row){
        $repoID=$row['id'];
        //如果有,但是超额,filesize>=40M,数据库存的KB;因为单文件10M限制,懒得处理了
        if($row['filesize']>= 40*1024){
            $repoID=$repoID+1;
            create_repo(QIANZHUI.($repoID));
            //插入表
            $tmp = $db->prepare("INSERT INTO `repo` (`id`, `date`, `filesize`) VALUES (?,now(),0)");  //me增加一个repo字段
	    	$tmp->execute(array($repoID));//
            return $repoID;
        }
        //可用,直接返回
        else{
            return $repoID;
        }

    }//如果没有
    else{
        $repoID=$repoID+1;
        create_repo(QIANZHUI.($repoID));
        //插入表
        $tmp = $db->prepare("INSERT INTO `repo` (`id`, `date`, `filesize`) VALUES (?,now(),0)");  //me增加一个repo字段
    	$tmp->execute(array($repoID));//
        return $repoID;
    }
}
//me获取文件后缀
 function get_extension($file)
 {
 $file = explode('.', $file);
 return '.'.end($file);
}

