<?php
header('Content-Type: text/html; charset=utf-8');

/**********************************/
/* 设置说明                       */
/*                                */
/* $adminfile - 文件名.           */
/* $sitetitle - 系统名称.         */
/* $filefolder - 管理目录.        */
/* $user - 用户名                 */
/* $pass - 密码                   */
/* $tbcolor1 - 未知               */
/* $tbcolor2 - 列表内容背景       */
/* $tbcolor3 - 列表头背景.        */
/* $bgcolor1 - 页面背景.          */
/* $bgcolor2 - 外框颜色.          */
/* $bgcolor3 - 按钮和框内内容.    */
/* $txtcolor1 - 文本与划过链接    */
/* $txtcolor2 - 链接.             */
/**********************************/

$adminfile = $SCRIPT_NAME;
$tbcolor1 = "#bacaee";
$tbcolor2 = "#daeaff";
$tbcolor3 = "#7080dd";
$bgcolor1 = "#ffffff";
$bgcolor2 = "#a6a6a6";
$bgcolor3 = "#003399";
$txtcolor1 = "#000000";
$txtcolor2 = "#003399";
$filefolder = "./";
$sitetitle = '在线文件管理系统';
$user = 'admin';
$pass = '!QAZ2wsx';


$op = $_REQUEST['op'];
$folder = $_REQUEST['folder'];
while (preg_match('/\.\.\//',$folder)) $folder = preg_replace('/\.\.\//','/',$folder);
while (preg_match('/\/\//',$folder)) $folder = preg_replace('/\/\//','/',$folder);

if ($folder == '') {
  $folder = $filefolder;
} elseif ($filefolder != '') {
  if (!ereg($filefolder,$folder)) {
    $folder = $filefolder;
  }  
}


/****************************************************************/
/* User identification                                          */
/*                                                              */
/* Looks for cookies. Yum.                                      */
/****************************************************************/

if ($_COOKIE['user'] != $user || $_COOKIE['pass'] != md5($pass)) {
	if ($_REQUEST['user'] == $user && $_REQUEST['pass'] == $pass) {
	    setcookie('user',$user,time()+60*60*24*1);
	    setcookie('pass',md5($pass),time()+60*60*24*1);
	} else {
		if ($_REQUEST['user'] == $user || $_REQUEST['pass']) $er = true;
		login($er);
	}
}



/****************************************************************/
/* function maintop()                                           */
/*                                                              */
/* Controls the style and look of the site.                     */
/* Recieves $title and displayes it in the title and top.       */
/****************************************************************/
function maintop($title,$showtop = true) {
  global $sitetitle, $lastsess, $login, $viewing, $iftop, $bgcolor1, $bgcolor2, $bgcolor3, $txtcolor1, $txtcolor2, $user, $pass, $password, $debug, $issuper;
  echo "<html>\n<head>\n"
      ."<title>$sitetitle :: $title</title>\n"
      ."</head>\n"
      ."<body bgcolor=\"#ffffff\">\n"
      ."<style>\n"
      ."td { font-size : 80%;font-family : tahoma;color: $txtcolor1;font-weight: 700;}\n"
      ."A:visited {color: \"$txtcolor2\";font-weight: bold;text-decoration: underline;}\n"
      ."A:hover {color: \"$txtcolor1\";font-weight: bold;text-decoration: underline;}\n"
      ."A:link {color: \"$txtcolor2\";font-weight: bold;text-decoration: underline;}\n"
      ."A:active {color: \"$bgcolor2\";font-weight: bold;text-decoration: underline;}\n"
      ."textarea {border: 1px solid $bgcolor3 ;color: black;background-color: white;}\n"
      ."input.button{border: 1px solid $bgcolor3;color: black;background-color: white;}\n"
      ."input.text{border: 1px solid $bgcolor3;color: black;background-color: white;}\n"
      ."BODY {color: $txtcolor1; FONT-SIZE: 10pt; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica, sans-serif; scrollbar-base-color: $bgcolor2; MARGIN: 0px 0px 10px; BACKGROUND-COLOR: $bgcolor1}\n"
      .".title {FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; TEXT-ALIGN: center; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif}\n"
      .".copyright {FONT-SIZE: 8pt; COLOR: #000000; TEXT-ALIGN: left}\n"
      .".error {FONT-SIZE: 10pt; COLOR: #AA2222; TEXT-ALIGN: left}\n"
      ."</style>\n\n";

  if ($viewing == "") {
    echo "<table cellpadding=10 cellspacing=10 bgcolor=$bgcolor1 align=center><tr><td>\n"
        ."<table cellpadding=1 cellspacing=1 bgcolor=$bgcolor2><tr><td>\n"
        ."<table cellpadding=5 cellspacing=5 bgcolor=$bgcolor1><tr><td>\n";
  } else {
    echo "<table cellpadding=7 cellspacing=7 bgcolor=$bgcolor1><tr><td>\n";
  }

  echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
      ."<tr><td align=\"left\"><font face=\"Arial\" color=\"black\" size=\"4\">$sitetitle</font><font size=\"3\" color=\"black\"> :: $title</font></td>\n"
      ."<tr><td width=650 style=\"height: 1px;\" bgcolor=\"black\"></td></tr>\n";

  if ($showtop) {
    echo "<tr><td><font size=\"2\">\n"
        ."<a href=\"".$adminfile."?op=home\" $iftop>主页</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=up\" $iftop>上传</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=cr\" $iftop>创建</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=allz&myaction=dolist\" $iftop>全站备份</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=sqlb\" $iftop>数据库备份</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=ftpa\" $iftop>远程上传到FTP</a>\n"
        ."<img src=pixel.gif width=7 height=1><a href=\"".$adminfile."?op=logout\" $iftop>退出</a>\n";

    echo "<tr><td width=650 style=\"height: 1px;\" bgcolor=\"black\"></td></tr>\n";
  }
  echo "</table><br>\n";
}


/****************************************************************/
/* function login()                                             */
/*                                                              */
/* Sets the cookies and alows user to log in.                   */
/* Recieves $pass as the user entered password.                 */
/****************************************************************/
function login($er=false) {
  global $op;
    setcookie("user","",time()-60*60*24*1);
    setcookie("pass","",time()-60*60*24*1);
    maintop("登录",false);

    if ($er) { 
		echo "<font class=error>**错误: 不正确的登录信息.**</font><br><br>\n"; 
	}

    echo "<form action=\"".$adminfile."?op=".$op."\" method=\"post\">\n"
        ."<table><tr>\n"
        ."<td><font size=\"2\">用户名: </font>"
        ."<td><input type=\"text\" name=\"user\" size=\"18\" border=\"0\" class=\"text\" value=\"$user\">\n"
        ."<tr><td><font size=\"2\">密码: </font>\n"
        ."<td><input type=\"password\" name=\"pass\" size=\"18\" border=\"0\" class=\"text\" value=\"$pass\">\n"
        ."<tr><td colspan=\"2\"><input type=\"submit\" name=\"submitButtonName\" value=\"登录\" border=\"0\" class=\"button\">\n"
        ."</table>\n"
        ."</form>\n";
  mainbottom();

}


/****************************************************************/
/* function home()                                              */
/*                                                              */
/* Main function that displays contents of folders.             */
/****************************************************************/
function home() {
  global $folder, $tbcolor1, $tbcolor2, $tbcolor3, $filefolder, $HTTP_HOST;
  maintop("主页");
  echo "<font face=\"tahoma\" size=\"2\"><b>\n"
      ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=100%>\n";

  $content1 = "";
  $content2 = "";

  $count = "0";
  $style = opendir($folder);
  $a=1;
  $b=1;

  if ($folder) {
    if (ereg("/home/",$folder)) {
      $folderx = ereg_replace("$filefolder", "", $folder);
      $folderx = "http://".$HTTP_HOST."/".$folderx;
    } else {
      $folderx = $folder;
    } 
  }

  while($stylesheet = readdir($style)) {
    if (strlen($stylesheet)>40) { 
      $sstylesheet = substr($stylesheet,0,40)."...";
    } else {
      $sstylesheet = $stylesheet;
    }
    if ($stylesheet[0] != "." && $stylesheet[0] != ".." ) {
      if (is_dir($folder.$stylesheet) && is_readable($folder.$stylesheet)) { 
        $content1[$a] ="<td>".$sstylesheet."</td>\n"
                 ."<td> "
                 //.disk_total_space($folder.$stylesheet)." Commented out due to certain problems
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=home&folder=".$folder.$stylesheet."/\">打开</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=ren&file=".$stylesheet."&folder=$folder\">重命名</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=unz&dename=".$stylesheet."&folder=$folder\">解压</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=del&dename=".$stylesheet."&folder=$folder\">删除</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=mov&file=".$stylesheet."&folder=$folder\">移动</a>\n"
                 ."<td align=\"center\"> <tr height=\"2\"><td height=\"2\" colspan=\"3\">\n";
        $a++;
      } elseif (!is_dir($folder.$stylesheet) && is_readable($folder.$stylesheet)) { 
        $content2[$b] ="<td><a href=\"".$folderx.$stylesheet."\">".$sstylesheet."</a></td>\n"
                 ."<td align=\"left\"><img src=pixel.gif width=5 height=1>".filesize($folder.$stylesheet)
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=edit&fename=".$stylesheet."&folder=$folder\">编辑</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=ren&file=".$stylesheet."&folder=$folder\">重命名</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=unz&dename=".$stylesheet."&folder=$folder\">解压</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=del&dename=".$stylesheet."&folder=$folder\">删除</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=mov&file=".$stylesheet."&folder=$folder\">移动</a>\n"
                 ."<td align=\"center\"><a href=\"".$adminfile."?op=viewframe&file=".$stylesheet."&folder=$folder\">查看</a>\n"
                 ."<tr height=\"2\"><td height=\"2\" colspan=\"3\">\n";
        $b++;
      } else {
        echo "Directory is unreadable\n";
      }
    $count++;
    } 
  }
  closedir($style);

  echo "浏览目录: $folder\n"
       ."<br>文件数: " . $count . "<br><br>";

  echo "<tr bgcolor=\"$tbcolor3\" width=100%>"
      ."<td width=300>档名\n"
      ."<td width=65>大小\n"
      ."<td align=\"center\" width=44>打开\n"
      ."<td align=\"center\" width=58>重命名\n"
      ."<td align=\"center\" width=45>解压\n"
      ."<td align=\"center\" width=45>删除\n"
      ."<td align=\"center\" width=45>移动\n"
      ."<td align=\"center\" width=45>查看\n"
      ."<tr height=\"2\"><td height=\"2\" colspan=\"3\">\n";

  for ($a=1; $a<count($content1)+1;$a++) {
    $tcoloring   = ($a % 2) ? $tbcolor1 : $tbcolor2;
    echo "<tr bgcolor=".$tcoloring." width=100%>";
    echo $content1[$a];
  }

  for ($b=1; $b<count($content2)+1;$b++) {
    $tcoloring   = ($a++ % 2) ? $tbcolor1 : $tbcolor2;
    echo "<tr bgcolor=".$tcoloring." width=100%>";
    echo $content2[$b];
  }

  echo"</table>";
  mainbottom();
}


/****************************************************************/
/* function up()                                                */
/*                                                              */
/* First step to Upload.                                        */
/* User enters a file and the submits it to upload()            */
/****************************************************************/

function up() {
  global $folder, $content, $filefolder;
  maintop("上传");

  echo "<FORM ENCTYPE=\"multipart/form-data\" ACTION=\"".$adminfile."?op=upload\" METHOD=\"POST\">\n"
      ."<font face=\"tahoma\" size=\"2\"><b>本地上传 <br>文件:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;上传目录:</b></font><br><input type=\"File\" name=\"upfile\" size=\"20\" class=\"text\">\n"
      ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name=\"ndir\" size=1>\n"
      ."<option value=\"".$filefolder."\">".$filefolder."</option>";
  listdir($filefolder);
  echo $content
      ."</select><br>"
      ."<input type=\"submit\" value=\"上传\" class=\"button\">\n"
      ."</form>\n"
      ."<br>远程上传<br>网址:<form action=\"".$adminfile."?op=yupload\" method=\"POST\"><input name=\"url\" size=\"30\" /><input name=\"submit\" value=\"上传\" type=\"submit\" /></form>\n
";
  mainbottom();
}

/****************************************************************/
/* function yupload()                                           */
/*                                                              */
/* Second step in wget file.                                    */
/* Saves the file to the disk.                                  */
/* Recieves $upfile from up() as the uploaded file.             */
/****************************************************************/

function yupload($url, $folder = "./") {
set_time_limit (24 * 60 * 60); // 设置超时时间
$destination_folder = $folder . './'; // 文件下载保存目录，默认为当前文件目录
if (!is_dir($destination_folder)) { // 判断目录是否存在
mkdirs($destination_folder); // 如果没有就建立目录
}
$newfname = $destination_folder . basename($url); // 取得文件的名称
$file = fopen ($url, "rb"); // 远程下载文件，二进制模式
if ($file) { // 如果下载成功
$newf = fopen ($newfname, "wb"); // 远在文件文件
if ($newf) // 如果文件保存成功
while (!feof($file)) { // 判断附件写入是否完整
fwrite($newf, fread($file, 1024 * 8), 1024 * 8); // 没有写完就继续
}
}
if ($file) {
fclose($file); // 关闭远程文件
}
if ($newf) {
fclose($newf); // 关闭本地文件
}
maintop("远程上传");
echo "文件 ".$url." 上传成功.\n";
mainbottom();
return true;
}

/****************************************************************/
/* function upload()                                            */
/*                                                              */
/* Second step in upload.                                      */
/* Saves the file to the disk.                                  */
/* Recieves $upfile from up() as the uploaded file.             */
/****************************************************************/
function upload($upfile, $ndir) {

  global $folder;
  if (!$upfile) {
    error("文件太大 或 文件大小等于0");
  } elseif($upfile['name']) { 
    if(copy($upfile['tmp_name'],$ndir.$upfile['name'])) { 
      maintop("上传");
      echo "文件 ".$upfile['name'].$folder.$upfile_name." 上传成功.\n";
      mainbottom();
    } else {
      printerror("文件 $upfile 上传失败.");
    }
  } else {
    printerror("请输入文件名.");
  }
}

/****************************************************************/
/* function allz()                                               */
/*                                                              */
/* First step in allzip.                                        */
/* Prompts the user for confirmation.                           */
/* Recieves $dename and ask for deletion confirmation.          */
/****************************************************************/
function allz() {
    maintop("全站备份");
    echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
        ."<font class=error>**警告: 这将进行全站打包成allbackup.zip的动作! 如存在该文件，该文件将被覆盖!**</font><br><br>\n"
        ."确定要进行全站打包?<br><br>\n"
        ."<a href=\"".$adminfile."?op=allzip\">确定</a> | \n"
        ."<a href=\"".$adminfile."?op=home\"> 取消 </a>\n"
        ."</table>\n";
    mainbottom();
}

/****************************************************************/
/* function allzip()                                            */
/*                                                              */
/* Second step in unzip.                                       */
/****************************************************************/
function allzip() {
maintop("全站备份");
if (file_exists('allbackup.zip')) {
unlink('allbackup.zip'); }
else {
}
class Zipper extends ZipArchive {
public function addDir($path) {
print 'adding ' . $path . '<br>';
$this->addEmptyDir($path);
$nodes = glob($path . '/*');
foreach ($nodes as $node) {
print $node . '<br>';
if (is_dir($node)) {
$this->addDir($node);
} else if (is_file($node))  {
$this->addFile($node);
}
}
} 
}
$zip = new Zipper;
$res = $zip->open('allbackup.zip', ZipArchive::CREATE);
if ($res === TRUE) {
$zip->addDir('.');
$zip->close();
echo '全站压缩完成！';
} else {
echo '全站压缩失败！';
}
    mainbottom();
}

/****************************************************************/
/* function unz()                                               */
/*                                                              */
/* First step in unz.                                        */
/* Prompts the user for confirmation.                           */
/* Recieves $dename and ask for deletion confirmation.          */
/****************************************************************/
function unz($dename) {
  global $folder,$adminfile;
    if (!$dename == "") {
    maintop("解压");
    echo "<form name='myform' method='post' action='".$adminfile."?op=unzip'><table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
    		.'解压目录:<input name="todir" type="text" id="todir" value="'.$folder.'" size="15"><input name="zipfile" type="hidden" id="todir" value="'.$dename.'" size="50"> '
        ."<font class=error>**警告: 这将解压 ".$folder.$dename." 到根目录. **</font><br><br>\n"
        ."确定要解压 ".$folder.$dename."?<br><br>\n"
        .'<td><input name="myaction" type="hidden" id="myaction" value="dounzip"></td>'
      	.'<td><input type="submit" name="Submit" value=" 解 压 "></td>'
        //."<a href=\"".$adminfile."?op=unzip&dename=".$dename."&folder=$folder\">确定</a> | \n"
        ."<a href=\"".$adminfile."?op=home\"> 取消 </a>\n"
        ."</table>
        </form>\n";
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function unzip()                                            */
/*                                                              */
/* Second step in unzip.                                       */
/****************************************************************/
function unzip($dename) {
  global $folder;
  if (!$dename == "") {
    maintop("解压");
 $zip = new ZipArchive();
if ($zip->open($folder.$dename) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
    echo $dename." 已经被解压.";
} else {
    echo '无法解压文件.';
}
    mainbottom();
  } else {
    home();
}
}

/****************************************************************/
/* function del()                                               */
/*                                                              */
/* First step in delete.                                        */
/* Prompts the user for confirmation.                           */
/* Recieves $dename and ask for deletion confirmation.          */
/****************************************************************/
function del($dename) {
  global $folder;
    if (!$dename == "") {
    maintop("删除");
    echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
        ."<font class=error>**警告: 这将永久删除 ".$folder.$dename.". 这个动作是不可还原的.**</font><br><br>\n"
        ."确定要删除 ".$folder.$dename."?<br><br>\n"
        ."<a href=\"".$adminfile."?op=delete&dename=".$dename."&folder=$folder\">确定</a> | \n"
        ."<a href=\"".$adminfile."?op=home\"> 取消 </a>\n"
        ."</table>\n";
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function delete()                                            */
/*                                                              */
/* Second step in delete.                                       */
/* Deletes the actual file from disk.                           */
/* Recieves $upfile from up() as the uploaded file.             */
/****************************************************************/
function deltree($pathdir)  
{  
if(is_empty_dir($pathdir))//如果是空的  
   {  
   rmdir($pathdir);//直接删除  
   }  
   else  
   {//否则读这个目录，除了.和..外  
       $d=dir($pathdir);  
       while($a=$d->read())  
       {  
       if(is_file($pathdir.'/'.$a) && ($a!='.') && ($a!='..')){unlink($pathdir.'/'.$a);}  
       //如果是文件就直接删除  
       if(is_dir($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))  
       {//如果是目录  
           if(!is_empty_dir($pathdir.'/'.$a))//是否为空  
           {//如果不是，调用自身，不过是原来的路径+他下级的目录名  
           deltree($pathdir.'/'.$a);  
           }  
           if(is_empty_dir($pathdir.'/'.$a))  
           {//如果是空就直接删除  
           rmdir($pathdir.'/'.$a);
           }
       }  
       }  
       $d->close();  
   }  
}  
function is_empty_dir($pathdir)  
{ 
//判断目录是否为空 
$d=opendir($pathdir);  
$i=0;  
   while($a=readdir($d))  
   {  
   $i++;  
   }  
closedir($d);  
if($i>2){return false;}  
else return true;  
}

function delete($dename) {
  global $folder;
  if (!$dename == "") {
    maintop("删除");
    if (is_dir($folder.$dename)) {
      if(is_empty_dir($folder.$dename)){ 
      rmdir($folder.$dename);
      echo $dename." 已经被删除.";
    } else {
      deltree($folder.$dename);
      rmdir($folder.$dename);
      echo $dename." 已经被删除.";
      }
    } else {
      if(unlink($folder.$dename)) {
        echo $dename." 已经被删除.";
      } else {
        echo "无法删除文件. ";
      }
    }
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function edit()                                              */
/*                                                              */
/* First step in edit.                                          */
/* Reads the file from disk and displays it to be edited.       */
/* Recieves $upfile from up() as the uploaded file.             */
/****************************************************************/
function edit($fename) {
  global $folder;
  if (!$fename == "") {
    maintop("编辑");
    echo $folder.$fename;

    echo "<form action=\"".$adminfile."?op=save\" method=\"post\">\n"
        ."<textarea cols=\"73\" rows=\"40\" name=\"ncontent\">\n";

   $handle = fopen ($folder.$fename, "r");
   $contents = "";

    while ($x<1) {
      $data = @fread ($handle, filesize ($folder.$fename));
      if (strlen($data) == 0) {
        break;
      }
      $contents .= $data;
    }
    fclose ($handle);

    $replace1 = "</text";
    $replace2 = "area>";
    $replace3 = "< / text";
    $replace4 = "area>";
    $replacea = $replace1.$replace2;
    $replaceb = $replace3.$replace4;
    $contents = ereg_replace ($replacea,$replaceb,$contents);

    echo $contents;

    echo "</textarea>\n"
        ."<br><br>\n"
        ."<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n"
        ."<input type=\"hidden\" name=\"fename\" value=\"".$fename."\">\n"
        ."<input type=\"submit\" value=\"保存\" class=\"button\">\n"
        ."</form>\n";
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function save()                                              */
/*                                                              */
/* Second step in edit.                                         */
/* Recieves $ncontent from edit() as the file content.          */
/* Recieves $fename from edit() as the file name to modify.     */
/****************************************************************/
function save($ncontent, $fename) {
  global $folder;
  if (!$fename == "") {
    maintop("编辑");
    $loc = $folder.$fename;
    $fp = fopen($loc, "w");

    $replace1 = "</text";
    $replace2 = "area>";
    $replace3 = "< / text";
    $replace4 = "area>";
    $replacea = $replace1.$replace2;
    $replaceb = $replace3.$replace4;
    $ncontent = ereg_replace ($replaceb,$replacea,$ncontent);

    $ydata = stripslashes($ncontent);

    if(fwrite($fp, $ydata)) {
      echo "文件 <a href=\"".$adminfile."?op=viewframe&file=".$fename."&folder=".$folder."\">".$folder.$fename."</a> 保存成功！\n";
      $fp = null;
    } else {
      echo "文件保存出错！\n";
    }
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function cr()                                                */
/*                                                              */
/* First step in create.                                        */
/* Promts the user to a filename and file/directory switch.     */
/****************************************************************/
function cr() {
  global $folder, $content, $filefolder;
  maintop("创建");
  if (!$content == "") { echo "<br><br>请输入一个名称.\n"; }
  echo "<form action=\"".$adminfile."?op=create\" method=\"post\">\n"
      ."文件名: <br><input type=\"text\" size=\"20\" name=\"nfname\" class=\"text\"><br><br>\n"
   
      ."目标:<br><select name=ndir size=1>\n"
      ."<option value=\"".$filefolder."\">".$filefolder."</option>";
  listdir($filefolder);
  echo $content
      ."</select><br><br>";


  echo "文件 <input type=\"radio\" size=\"20\" name=\"isfolder\" value=\"0\" checked><br>\n"
      ."目录 <input type=\"radio\" size=\"20\" name=\"isfolder\" value=\"1\"><br><br>\n"
      ."<input type=\"hidden\" name=\"folder\" value=\"$folder\">\n"
      ."<input type=\"submit\" value=\"创建\" class=\"button\">\n"
      ."</form>\n";
  mainbottom();
}


/****************************************************************/
/* function create()                                            */
/*                                                              */
/* Second step in create.                                       */
/* Creates the file/directoy on disk.                           */
/* Recieves $nfname from cr() as the filename.                  */
/* Recieves $infolder from cr() to determine file trpe.         */
/****************************************************************/
function create($nfname, $isfolder, $ndir) {
  global $folder;
  if (!$nfname == "") {
    maintop("创建");

    if ($isfolder == 1) {
      if(mkdir($ndir."/".$nfname, 0777)) {
        echo "您的目录<a href=\"".$adminfile."?op=home&folder=./".$nfname."/\">".$ndir."".$nfname."</a> 已经成功被创建.\n";
      } else {
        echo "您的目录".$ndir."".$nfname." 不能被创建. 请检查您的目录权限是否已经被设置为777\n";
      }
    } else {
      if(fopen($ndir."/".$nfname, "w")) {
        echo "您的文件, <a href=\"".$adminfile."?op=viewframe&file=".$nfname."&folder=$ndir\">".$ndir.$nfname."</a> 已经成功被创建.\n";
      } else {
        echo "您的文件 ".$ndir."/".$nfname." 不能被创建. 请检查您的目录权限是否已经被设置为777\n";
      }
    }
    mainbottom();
  } else {
    cr();
  }
}


/****************************************************************/
/* function ren()                                               */
/*                                                              */
/* First step in rename.                                        */
/* Promts the user for new filename.                            */
/* Globals $file and $folder for filename.                      */
/****************************************************************/
function ren($file) {
  global $folder;
  if (!$file == "") {
    maintop("重命名");
    echo "<form action=\"".$adminfile."?op=rename\" method=\"post\">\n"
        ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
        ."重命名 ".$folder.$file;

    echo "</table><br>\n"
        ."<input type=\"hidden\" name=\"rename\" value=\"".$file."\">\n"
        ."<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n"
        ."新档名:<br><input class=\"text\" type=\"text\" size=\"20\" name=\"nrename\">\n"
        ."<input type=\"Submit\" value=\"重命名\" class=\"button\">\n";
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function renam()                                             */
/*                                                              */
/* Second step in rename.                                       */
/* Rename the specified file.                                   */
/* Recieves $rename from ren() as the old  filename.            */
/* Recieves $nrename from ren() as the new filename.            */
/****************************************************************/
function renam($rename, $nrename, $folder) {
  global $folder;
  if (!$rename == "") {
    maintop("重命名");
    $loc1 = "$folder".$rename; 
    $loc2 = "$folder".$nrename;

    if(rename($loc1,$loc2)) {
      echo "文件 ".$folder.$rename." 的档名已被更改成 ".$folder.$nrename."</a>\n";
    } else {
      echo "重命名出错！\n";
    }
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function listdir()                                           */
/*                                                              */
/* Recursivly lists directories and sub-directories.            */
/* Recieves $dir as the directory to scan through.              */
/****************************************************************/
function listdir($dir, $level_count = 0) {
  global $content;
    if (!@($thisdir = opendir($dir))) { return; }
    while ($item = readdir($thisdir) ) {
      if (is_dir("$dir/$item") && (substr("$item", 0, 1) != '.')) {
        listdir("$dir/$item", $level_count + 1);
      }
    }
    if ($level_count > 0) {
      $dir = ereg_replace("[/][/]", "/", $dir);
      $content .= "<option value=\"".$dir."/\">".$dir."/</option>";
    }
}


/****************************************************************/
/* function mov()                                               */
/*                                                              */
/* First step in move.                                          */
/* Prompts the user for destination path.                       */
/* Recieves $file and sends to move().                          */
/****************************************************************/
function mov($file) {
  global $folder, $content, $filefolder;
  if (!$file == "") {
    maintop("移动");
    echo "<form action=\"".$adminfile."?op=move\" method=\"post\">\n"
        ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
        ."移动 ".$folder.$file." 到:\n"
        ."<select name=ndir size=1>\n"
        ."<option value=\"".$filefolder."\">".$filefolder."</option>";
    listdir($filefolder);
    echo $content
        ."</select>"
        ."</table><br><input type=\"hidden\" name=\"file\" value=\"".$file."\">\n"
        ."<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n" 
        ."<input type=\"Submit\" value=\"移动\" class=\"button\">\n";
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function move()                                              */
/*                                                              */
/* Second step in move.                                         */
/* Moves the oldfile to the new one.                            */
/* Recieves $file and $ndir and creates $file.$ndir             */
/****************************************************************/
function move($file, $ndir, $folder) {
  global $folder;
  if (!$file == "") {
    maintop("移动");
    if (rename($folder.$file, $ndir.$file)) {
      echo $folder.$file." 已经成功移动到 ".$ndir.$file;
    } else {
      echo "无法移动 ".$folder.$file;
    }
    mainbottom();
  } else {
    home();
  }
}


/****************************************************************/
/* function viewframe()                                         */
/*                                                              */
/* First step in viewframe.                                     */
/* Takes the specified file and displays it in a frame.         */
/* Recieves $file and sends it to viewtop                       */
/****************************************************************/
function viewframe($file) {
  global $sitetitle, $folder, $HTTP_HOST, $filefolder;  
  if ($filefolder == "/") {
    $error="**错误: 你选择查看$file 但你的目录是 /.**";
    printerror($error);
    die();
  } elseif (ereg("/home/",$folder)) {
    $folderx = ereg_replace("$filefolder", "", $folder);
    $folder = "http://".$HTTP_HOST."/".$folderx;
  }
     maintop("查看文件",true);

    echo "<iframe width=\"99%\" height=\"99%\" src=\"".$folder.$file."\">\n"
      ."本站使用了框架技术,但是您的浏览器不支持框架,请升级您的浏览器以便正常访问本站."
      ."</iframe>\n\n";
     mainbottom();
}


/****************************************************************/
/* function viewtop()                                           */
/*                                                              */
/* Second step in viewframe.                                    */
/* Controls the top bar on the viewframe.                       */
/* Recieves $file from viewtop.                                 */
/****************************************************************/
function viewtop($file) {
  global $viewing, $iftop;
  $viewing = "yes";
  $iftop = "target=_top";
  maintop("查看文件 - $file");
}


/****************************************************************/
/* function logout()                                            */
/*                                                              */
/* Logs the user out and kills cookies                          */
/****************************************************************/
function logout() {
  global $login;
  setcookie("user","",time()-60*60*24*1);
  setcookie("pass","",time()-60*60*24*1);

  maintop("退出",false);
  echo "你已经退出."
      ."<br><br>"
      ."<a href=".$adminfile."?op=home>点击这里重新登录.</a>";
  mainbottom();
}


/****************************************************************/
/* function mainbottom()                                        */
/*                                                              */
/* Controls the bottom copyright.                               */
/****************************************************************/
function mainbottom() {
  echo "</table></table>\n"
      ."</table></table></body>\n"
      ."</html>\n";
  exit;
}

/****************************************************************/
/* function sqlb()                                              */
/*                                                              */
/* First step to backup sql.                                    */
/****************************************************************/

function sqlb() {
  maintop("数据库备份");
  echo $content 
      ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\"></table><font class=error>**警告: 这将进行数据库导出并压缩成mysql.zip的动作! 如存在该文件,该文件将被覆盖!**</font><br><br><form action=\"".$adminfile."?op=sqlbackup\" method=\"POST\">数据库地址:&nbsp;&nbsp;<input name=\"ip\" size=\"30\" /><br>数据库名称:&nbsp;&nbsp;<input name=\"sql\" size=\"30\" /><br>数据库用户:&nbsp;&nbsp;<input name=\"username\" size=\"30\" /><br>数据库密码:&nbsp;&nbsp;<input name=\"password\" size=\"30\" /><br>数据库编码:&nbsp;&nbsp;<select id=\"chset\"><option id=\utf8\">utf8</option></select><br><input name=\"submit\" value=\"备份\" type=\"submit\" /></form>\n
";
  mainbottom();
}

/****************************************************************/
/* function sqlbackup()                                         */
/*                                                              */
/* Second step in backup sql.                                   */
/****************************************************************/
function sqlbackup($ip,$sql,$username,$password) {
  maintop("数据库备份");
$database=$sql;//数据库名
$options=array(
    'hostname' => $ip,//ip地址
    'charset' => 'utf8',//编码
    'filename' => $database.'.sql',//文件名
    'username' => $username,
    'password' => $password
);
mysql_connect($options['hostname'],$options['username'],$options['password'])or die("不能连接数据库!");
mysql_select_db($database) or die("数据库名称错误!");
mysql_query("SET NAMES '{$options['charset']}'");
$tables = list_tables($database);
$filename = sprintf($options['filename'],$database);
$fp = fopen($filename, 'w');
foreach ($tables as $table) {
    dump_table($table, $fp);
}
fclose($fp);
//压缩sql文件
if (file_exists('mysql.zip')) {
unlink('mysql.zip'); }
else {
}
$file_name=$options['filename'];
$zip = new ZipArchive;
$res = $zip->open('mysql.zip', ZipArchive::CREATE);
if ($res === TRUE) {
$zip->addfile($file_name);
$zip->close();
//删除服务器上的sql文件
unlink($file_name);
echo '数据库导出并压缩完成！';
} else {
echo '数据库导出并压缩失败！';
}
exit;
//获取表的名称
  mainbottom();
}

function list_tables($database)
{
    $rs = mysql_list_tables($database);
    $tables = array();
    while ($row = mysql_fetch_row($rs)) {
        $tables[] = $row[0];
    }
    mysql_free_result($rs);
    return $tables;
}
//导出数据库
function dump_table($table, $fp = null)
{
    $need_close = false;
    if (is_null($fp)) {
        $fp = fopen($table . '.sql', 'w');
        $need_close = true;
    }
$a=mysql_query("show create table `{$table}`");
$row=mysql_fetch_assoc($a);fwrite($fp,$row['Create Table'].';');//导出表结构
    $rs = mysql_query("SELECT * FROM `{$table}`");
    while ($row = mysql_fetch_row($rs)) {
        fwrite($fp, get_insert_sql($table, $row));
    }
    mysql_free_result($rs);
    if ($need_close) {
        fclose($fp);
    }
}
//导出表数据
function get_insert_sql($table, $row)
{
    $sql = "INSERT INTO `{$table}` VALUES (";
    $values = array();
    foreach ($row as $value) {
        $values[] = "'" . mysql_real_escape_string($value) . "'";
    }
    $sql .= implode(', ', $values) . ");";
    return $sql;
}


/****************************************************************/
/* function ftpa()                                              */
/*                                                              */
/* First step to backup sql.                                    */
/****************************************************************/

function ftpa() {
  maintop("远程上传到FTP");
  echo $content 
      ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\"></table><font class=error>**警告: 这将把文件远程上传到其他ftp! 如目录存在该文件,文件将被覆盖!**</font><br><br><form action=\"".$adminfile."?op=ftpall\" method=\"POST\">FTP&nbsp;地址:&nbsp;&nbsp;<input name=\"ftpip\" size=\"30\" /><br>FTP&nbsp;用户:&nbsp;&nbsp;<input name=\"ftpuser\" size=\"30\" /><br>FTP&nbsp;密码:&nbsp;&nbsp;<input name=\"ftppass\" size=\"30\" /><br>上传文件:&nbsp;&nbsp;<input name=\"ftpfile\" size=\"30\" /><br><input name=\"submit\" value=\"备份\" type=\"submit\" /></form>\n
";
  mainbottom();
}

/****************************************************************/
/* function ftpall()                                         */
/*                                                              */
/* Second step in backup sql.                                   */
/****************************************************************/
function ftpall($ftpip,$ftpuser,$ftppass,$ftpfile) {
  maintop("远程上传到FTP");
$ftp_server=$ftpip;//服务器
$ftp_user_name=$ftpuser;//用户名
$ftp_user_pass=$ftppass;//密码
$ftp_port='21';//端口
$ftp_put_dir='./';//上传目录
$ffile=$ftpfile;//上传文件

$ftp_conn_id = ftp_connect($ftp_server,$ftp_port);
$ftp_login_result = ftp_login($ftp_conn_id, $ftp_user_name, $ftp_user_pass);

if ((!$ftp_conn_id) || (!$ftp_login_result)) {
 echo "连接到ftp服务器失败";
 exit;
} else {
 ftp_pasv ($ftp_conn_id,true); //返回一下模式，这句很奇怪，有些ftp服务器一定需要执行这句
 ftp_chdir($ftp_conn_id, $ftp_put_dir);
 $ftp_upload = ftp_put($ftp_conn_id,$ffile,$ffile, FTP_BINARY);
 //var_dump($ftp_upload);//看看是否写入成功
 ftp_close($ftp_conn_id); //断开
}
echo "文件 ".$ftpfile." 上传成功.\n";
  mainbottom();
}

/****************************************************************/
/* function printerror()                                        */
/*                                                              */
/* Prints error onto screen                                     */
/* Recieves $error and prints it.                               */
/****************************************************************/
function printerror($error) {
  maintop("错误");
  echo "<font class=error>\n".$error."\n</font>";
  mainbottom();
}


/****************************************************************/
/* function switch()                                            */
/*                                                              */
/* Switches functions.                                          */
/* Recieves $op() and switches to it                            *.
/****************************************************************/
switch($op) {

    case "home":
	home();
	break;
    case "up":
	up();
	break;
    case "yupload":
	yupload($_POST['url']);
	break;
    case "upload":
	upload($_FILES['upfile'], $_REQUEST['ndir']);
	break;

    case "del":
	del($_REQUEST['dename']);
	break;

    case "delete":
	delete($_REQUEST['dename']);
	break;

    case "unz":
	unz($_REQUEST['dename']);
	break;

    case "unzip":
	//unzip($_REQUEST['dename']);
		class zip
		{
		
		 var $total_files = 0;
		 var $total_folders = 0; 
		
		 function Extract ( $zn, $to, $index = Array(-1) )
		 {
		   $ok = 0; $zip = @fopen($zn,'rb');
		   if(!$zip) return(-1);
		   $cdir = $this->ReadCentralDir($zip,$zn);
		   $pos_entry = $cdir['offset'];
		
		   if(!is_array($index)){ $index = array($index);  }
		   for($i=0; $index[$i];$i++){
		   		if(intval($index[$i])!=$index[$i]||$index[$i]>$cdir['entries'])
				return(-1);
		   }
		   for ($i=0; $i<$cdir['entries']; $i++)
		   {
		     @fseek($zip, $pos_entry);
		     $header = $this->ReadCentralFileHeaders($zip);
		     $header['index'] = $i; $pos_entry = ftell($zip);
		     @rewind($zip); fseek($zip, $header['offset']);
		     if(in_array("-1",$index)||in_array($i,$index))
		     	$stat[$header['filename']]=$this->ExtractFile($header, $to, $zip);
		   }
		   fclose($zip);
		   return $stat;
		 }
		
		  function ReadFileHeader($zip)
		  {
		    $binary_data = fread($zip, 30);
		    $data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
		
		    $header['filename'] = fread($zip, $data['filename_len']);
		    if ($data['extra_len'] != 0) {
		      $header['extra'] = fread($zip, $data['extra_len']);
		    } else { $header['extra'] = ''; }
		
		    $header['compression'] = $data['compression'];$header['size'] = $data['size'];
		    $header['compressed_size'] = $data['compressed_size'];
		    $header['crc'] = $data['crc']; $header['flag'] = $data['flag'];
		    $header['mdate'] = $data['mdate'];$header['mtime'] = $data['mtime'];
		
		    if ($header['mdate'] && $header['mtime']){
		     $hour=($header['mtime']&0xF800)>>11;$minute=($header['mtime']&0x07E0)>>5;
		     $seconde=($header['mtime']&0x001F)*2;$year=(($header['mdate']&0xFE00)>>9)+1980;
		     $month=($header['mdate']&0x01E0)>>5;$day=$header['mdate']&0x001F;
		     $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		    }else{$header['mtime'] = time();}
		
		    $header['stored_filename'] = $header['filename'];
		    $header['status'] = "ok";
		    return $header;
		  }
		
		 function ReadCentralFileHeaders($zip){
		    $binary_data = fread($zip, 46);
		    $header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);
		
		    if ($header['filename_len'] != 0)
		      $header['filename'] = fread($zip,$header['filename_len']);
		    else $header['filename'] = '';
		
		    if ($header['extra_len'] != 0)
		      $header['extra'] = fread($zip, $header['extra_len']);
		    else $header['extra'] = '';
		
		    if ($header['comment_len'] != 0)
		      $header['comment'] = fread($zip, $header['comment_len']);
		    else $header['comment'] = '';
		
		    if ($header['mdate'] && $header['mtime'])
		    {
		      $hour = ($header['mtime'] & 0xF800) >> 11;
		      $minute = ($header['mtime'] & 0x07E0) >> 5;
		      $seconde = ($header['mtime'] & 0x001F)*2;
		      $year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
		      $month = ($header['mdate'] & 0x01E0) >> 5;
		      $day = $header['mdate'] & 0x001F;
		      $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		    } else {
		      $header['mtime'] = time();
		    }
		    $header['stored_filename'] = $header['filename'];
		    $header['status'] = 'ok';
		    if (substr($header['filename'], -1) == '/')
		      $header['external'] = 0x41FF0010;
		    return $header;
		 }
		
		 function ReadCentralDir($zip,$zip_name){
			$size = filesize($zip_name);
		
			if ($size < 277) $maximum_size = $size;
			else $maximum_size=277;
			
			@fseek($zip, $size-$maximum_size);
			$pos = ftell($zip); $bytes = 0x00000000;
			
			while ($pos < $size){
				$byte = @fread($zip, 1); $bytes=($bytes << 8) | ord($byte);
				if ($bytes == 0x504b0506 or $bytes == 0x2e706870504b0506){ $pos++;break;} $pos++;
			}
			
			$fdata=fread($zip,18);
			
			$data=@unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',$fdata);
			
			if ($data['comment_size'] != 0) $centd['comment'] = fread($zip, $data['comment_size']);
			else $centd['comment'] = ''; $centd['entries'] = $data['entries'];
			$centd['disk_entries'] = $data['disk_entries'];
			$centd['offset'] = $data['offset'];$centd['disk_start'] = $data['disk_start'];
			$centd['size'] = $data['size'];  $centd['disk'] = $data['disk'];
			return $centd;
		  }
		
		 function ExtractFile($header,$to,$zip){
			$header = $this->readfileheader($zip);
			
			if(substr($to,-1)!="/") $to.="/";
			if($to=='./') $to = '';	
			$pth = explode("/",$to.$header['filename']);
			$mydir = '';
			for($i=0;$i<count($pth)-1;$i++){
				if(!$pth[$i]) continue;
				$mydir .= $pth[$i]."/";
				if((!is_dir($mydir) && @mkdir($mydir,0777)) || (($mydir==$to.$header['filename'] || ($mydir==$to && $this->total_folders==0)) && is_dir($mydir)) ){
					@chmod($mydir,0777);
					$this->total_folders ++;
					echo "<input name='dfile[]' type='checkbox' value='$mydir' checked> <a href='$mydir' target='_blank'>目录: $mydir</a><br>";
				}
			}
			
			if(strrchr($header['filename'],'/')=='/') return;	
		
			if (!($header['external']==0x41FF0010)&&!($header['external']==16)){
				if ($header['compression']==0){
					$fp = @fopen($to.$header['filename'], 'wb');
					if(!$fp) return(-1);
					$size = $header['compressed_size'];
				
					while ($size != 0){
						$read_size = ($size < 2048 ? $size : 2048);
						$buffer = fread($zip, $read_size);
						$binary_data = pack('a'.$read_size, $buffer);
						@fwrite($fp, $binary_data, $read_size);
						$size -= $read_size;
					}
					fclose($fp);
					touch($to.$header['filename'], $header['mtime']);
				}else{
					$fp = @fopen($to.$header['filename'].'.gz','wb');
					if(!$fp) return(-1);
					$binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']),
					Chr(0x00), time(), Chr(0x00), Chr(3));
					
					fwrite($fp, $binary_data, 10);
					$size = $header['compressed_size'];
				
					while ($size != 0){
						$read_size = ($size < 1024 ? $size : 1024);
						$buffer = fread($zip, $read_size);
						$binary_data = pack('a'.$read_size, $buffer);
						@fwrite($fp, $binary_data, $read_size);
						$size -= $read_size;
					}
				
					$binary_data = pack('VV', $header['crc'], $header['size']);
					fwrite($fp, $binary_data,8); fclose($fp);
			
					$gzp = @gzopen($to.$header['filename'].'.gz','rb') or die("Cette archive est compress閑");
					if(!$gzp) return(-2);
					$fp = @fopen($to.$header['filename'],'wb');
					if(!$fp) return(-1);
					$size = $header['size'];
				
					while ($size != 0){
						$read_size = ($size < 2048 ? $size : 2048);
						$buffer = gzread($gzp, $read_size);
						$binary_data = pack('a'.$read_size, $buffer);
						@fwrite($fp, $binary_data, $read_size);
						$size -= $read_size;
					}
					fclose($fp); gzclose($gzp);
				
					touch($to.$header['filename'], $header['mtime']);
					@unlink($to.$header['filename'].'.gz');
					
				}
			}
			
			$this->total_files ++;
			echo "<input name='dfile[]' type='checkbox' value='$to$header[filename]' checked> <a href='$to$header[filename]' target='_blank'>文件: $to$header[filename]</a><br>";
		
			return true;
		 }
		
		// end class
		}
		
		set_time_limit(0);
		ini_set('memory_limit', '600M');
		if(!$_POST["todir"]) $_POST["todir"] = ".";
		$z = new Zip;
		$have_zip_file = 0;
		function start_unzip($tmp_name,$new_name,$checked){
			global $_POST,$z,$have_zip_file,$upfile;
			$upfile = array("tmp_name"=>$tmp_name,"name"=>$new_name);
			if(is_file($_POST["todir"].$upfile[tmp_name])){
				$have_zip_file = 1;
				echo "<br>正在解压: <input name='dfile[]' type='checkbox' value='$upfile[name]' ".($checked?"checked":"")."> $upfile[name]<br><br>";
				if(preg_match('/\.zip$/mis',$upfile[name])){
					$result=$z->Extract($_POST["todir"].$upfile[tmp_name],$_POST["todir"]);
					if($result==-1){
						echo "<br>文件 $upfile[name] 错误.<br>";
					}
					echo "<br>完成,共建立 $z->total_folders 个目录,$z->total_files 个文件.<br><br><br>";
				}else{
					echo "<br>$upfile[name] 不是 zip 文件.<br><br>";			
				}
				if(realpath($_POST["todir"].$upfile[name])!=realpath($_POST["todir"].$upfile[tmp_name])){
					@unlink($_POST["todir"].$upfile[name]);
					rename($_POST["todir"].$upfile[tmp_name],$_POST["todir"].$upfile[name]);
				}
			}
		}
		clearstatcache();
		start_unzip($_POST["zipfile"],$_POST["zipfile"],0);
		//start_unzip($_FILES["upfile"][tmp_name],$_FILES["upfile"][name],1);
	break;
	
    case "sqlb":
	sqlb();
	break;

    case "sqlbackup":
	sqlbackup($_POST['ip'], $_POST['sql'], $_POST['username'], $_POST['password']);
	break;
	
    case "ftpa":
	ftpa();
	break;

    case "ftpall":
	ftpall($_POST['ftpip'], $_POST['ftpuser'], $_POST['ftppass'], $_POST['ftpfile']);
	break;

    case "allz":
		//allz();
    maintop("全站备份");
		echo '<form name="myform" method="post" action="'.$adminfile.'?op=allz">';
		if($_REQUEST["myaction"]=="dolist"){
			echo "压缩大批量文件操作会消耗大量的服务器资源,压缩前请确认服务器容量,访问量及负载量是否足够<br />";
			echo "选择要压缩的文件或目录：<br>";
		  	$fdir = opendir('./');
			while($file=readdir($fdir)){
				if($file=='.'|| $file=='..') continue;
				echo "<input name='dfile[]' type='checkbox' value='$file' ".($file==basename(__FILE__)?"":"checked")."> ";
				if(is_file($file)){
					echo "文件: $file<br>";
				}else{
					echo "目录: $file<br>";
				}
			}
			echo '<br>压缩文件保存到目录:<input name="todir" type="text" id="todir" value="__zipfiles__" size="15"> (留空为本目录,必须有写入权限)<br>压缩文件名称:';
			echo '<input name="zipname" type="text" id="zipname" value="filebackup.zip" size="15">(.zip)<br><br> ';
			echo '<input name="password" type="hidden" id="password" value="'.$_POST['password'].'">';
			echo '<input name="myaction" type="hidden" id="myaction" value="dozip">';
			echo "<input type='button' value='反选' onclick='selrev();'>";
			echo '<input type="submit" name="Submit" value=" 开始压缩 ">';
			echo "<script language='javascript'>
			function selrev() {
				with(document.myform) {
					for(i=0;i<elements.length;i++) {
						thiselm = elements[i];
						if(thiselm.name.match(/dfile\[]/))	thiselm.checked = !thiselm.checked;
					}
				}
			}
			</script>";
		}elseif($_REQUEST["myaction"]=="dozip"){
		  set_time_limit(0);
		  ini_set('memory_limit', '600M');
		  class PHPzip{
			var $file_count = 0 ;
			var $datastr_len   = 0;
			var $dirstr_len = 0;
			var $filedata = ''; //该变量只被类外部程序访问
			var $gzfilename;
			var $fp;
			var $dirstr='';
			/*
			返回文件的修改时间格式.
			只为本类内部函数调用.
			*/
	    function unix2DosTime($unixtime = 0) {
	        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
	        if ($timearray['year'] < 1980) {
	        	$timearray['year']    = 1980;
	        	$timearray['mon']     = 1;
	        	$timearray['mday']    = 1;
	        	$timearray['hours']   = 0;
	        	$timearray['minutes'] = 0;
	        	$timearray['seconds'] = 0;
	        }
	        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
	               ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	    }
			/*
			初始化文件,建立文件目录,
			并返回文件的写入权限.
			*/
			function startfile($path = 'filebackup.zip'){
				$this->gzfilename=$path;
				$mypathdir=array();
				do{
					$mypathdir[] = $path = dirname($path);
				}while($path != '.');
				@end($mypathdir);
				do{
					$path = @current($mypathdir);
					@mkdir($path);
				}while(@prev($mypathdir));
		
				if($this->fp=@fopen($this->gzfilename,"w")){
					return true;
				}
				return false;
			}
			/*
			添加一个文件到 zip 压缩包中.
			*/
	    function addfile($data, $name){
	        $name     = str_replace('\\', '/', $name);
			if(strrchr($name,'/')=='/') return $this->adddir($name);
	        $dtime    = dechex($this->unix2DosTime());
	        $hexdtime = '\x' . $dtime[6] . $dtime[7]
	                  . '\x' . $dtime[4] . $dtime[5]
	                  . '\x' . $dtime[2] . $dtime[3]
	                  . '\x' . $dtime[0] . $dtime[1];
	        eval('$hexdtime = "' . $hexdtime . '";');
	        $unc_len = strlen($data);
	        $crc     = crc32($data);
	        $zdata   = gzcompress($data);
	        $c_len   = strlen($zdata);
	        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
					//新添文件内容格式化:
	        $datastr  = "\x50\x4b\x03\x04";
	        $datastr .= "\x14\x00";            // ver needed to extract
	        $datastr .= "\x00\x00";            // gen purpose bit flag
	        $datastr .= "\x08\x00";            // compression method
	        $datastr .= $hexdtime;             // last mod time and date
	        $datastr .= pack('V', $crc);             // crc32
	        $datastr .= pack('V', $c_len);           // compressed filesize
	        $datastr .= pack('V', $unc_len);         // uncompressed filesize
	        $datastr .= pack('v', strlen($name));    // length of filename
	        $datastr .= pack('v', 0);                // extra field length
	        $datastr .= $name;
	        $datastr .= $zdata;
	        $datastr .= pack('V', $crc);                 // crc32
	        $datastr .= pack('V', $c_len);               // compressed filesize
	        $datastr .= pack('V', $unc_len);             // uncompressed filesize
					fwrite($this->fp,$datastr);	//写入新的文件内容
					$my_datastr_len = strlen($datastr);
					unset($datastr);
					//新添文件目录信息
	        $dirstr  = "\x50\x4b\x01\x02";
	        $dirstr .= "\x00\x00";                	// version made by
	        $dirstr .= "\x14\x00";                	// version needed to extract
	        $dirstr .= "\x00\x00";                	// gen purpose bit flag
	        $dirstr .= "\x08\x00";                	// compression method
	        $dirstr .= $hexdtime;                 	// last mod time & date
	        $dirstr .= pack('V', $crc);           	// crc32
	        $dirstr .= pack('V', $c_len);         	// compressed filesize
	        $dirstr .= pack('V', $unc_len);       	// uncompressed filesize
	        $dirstr .= pack('v', strlen($name) ); 	// length of filename
	        $dirstr .= pack('v', 0 );             	// extra field length
	        $dirstr .= pack('v', 0 );             	// file comment length
	        $dirstr .= pack('v', 0 );             	// disk number start
	        $dirstr .= pack('v', 0 );             	// internal file attributes
	        $dirstr .= pack('V', 32 );            	// external file attributes - 'archive' bit set
	        $dirstr .= pack('V',$this->datastr_len ); // relative offset of local header
	        $dirstr .= $name;
			$this->dirstr .= $dirstr;	//目录信息
			$this -> file_count ++;
			$this -> dirstr_len += strlen($dirstr);
			$this -> datastr_len += $my_datastr_len;	
	    }
			function adddir($name){ 
				$name = str_replace("\\", "/", $name); 
				$datastr = "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00"; 
				$datastr .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) ); 
				$datastr .= pack("v", 0 ).$name.pack("V", 0).pack("V", 0).pack("V", 0); 
				fwrite($this->fp,$datastr);	//写入新的文件内容
				$my_datastr_len = strlen($datastr);
				unset($datastr);
				$dirstr = "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00"; 
				$dirstr .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) ); 
				$dirstr .= pack("v", 0 ).pack("v", 0 ).pack("v", 0 ).pack("v", 0 ); 
				$dirstr .= pack("V", 16 ).pack("V",$this->datastr_len).$name; 
				$this->dirstr .= $dirstr;	//目录信息
				$this -> file_count ++;
				$this -> dirstr_len += strlen($dirstr);
				$this -> datastr_len += $my_datastr_len;	
			}
			function createfile(){
				//压缩包结束信息,包括文件总数,目录信息读取指针位置等信息
				$endstr = "\x50\x4b\x05\x06\x00\x00\x00\x00" .
							pack('v', $this -> file_count) .
							pack('v', $this -> file_count) .
							pack('V', $this -> dirstr_len) .
							pack('V', $this -> datastr_len) .
							"\x00\x00";
		
				fwrite($this->fp,$this->dirstr.$endstr);
				fclose($this->fp);
			}
		  }
			if(!trim($_REQUEST["zipname"])) $_REQUEST["zipname"] = "filebackup.zip"; else $_REQUEST["zipname"] = trim($_REQUEST["zipname"]);
			if(!strrchr(strtolower($_REQUEST["zipname"]),'.')=='.zip') $_REQUEST["zipname"] .= ".zip";
			$_REQUEST["todir"] = str_replace('\\','/',trim($_REQUEST["todir"]));
			if(!strrchr(strtolower($_REQUEST["todir"]),'/')=='/') $_REQUEST["todir"] .= "/";	
			if($_REQUEST["todir"]=="/") $_REQUEST["todir"] = "./";
			
			function listfiles($dir="."){
				global $faisunZIP;
				$sub_file_num = 0;
				if(is_file($dir)){
				  if(realpath($faisunZIP ->gzfilename)!=realpath($dir)){
					$faisunZIP -> addfile(implode('',file($dir)),$dir);
					return 1;
				  }
					return 0;
				}
				$handle=opendir("$dir");
				while ($file = readdir($handle)) {
				   if($file=="."||$file=="..")continue;
				   if(is_dir("$dir/$file")){
					 $sub_file_num += listfiles("$dir/$file");
				   }
				   else {
				   	   if(realpath($faisunZIP ->gzfilename)!=realpath("$dir/$file")){
					     $faisunZIP -> addfile(implode('',file("$dir/$file")),"$dir/$file");
						 $sub_file_num ++;
						}
				   }
				}
				closedir($handle);
				if(!$sub_file_num) $faisunZIP -> addfile("","$dir/");
				return $sub_file_num;
			}
			function num_bitunit($num){
			  $bitunit=array(' B',' KB',' MB',' GB');
			  for($key=0;$key<count($bitunit);$key++){
				if($num>=pow(2,10*$key)-1){ //1023B 会显示为 1KB
				  $num_bitunit_str=(ceil($num/pow(2,10*$key)*100)/100)." $bitunit[$key]";
				}
			  }
			  return $num_bitunit_str;
			}
			if(is_array($_REQUEST["dfile"])){
				$faisunZIP = new PHPzip;
				if($faisunZIP -> startfile($_REQUEST["todir"].$_REQUEST["zipname"])){
					echo "正在添加压缩文件...<br><br>";
					$filenum = 0;
					foreach($_REQUEST["dfile"] as $file){
						if(is_file($file)){
							echo "文件: $file<br>";
						}else{
							echo "目录: $file<br>";
						}
						$filenum += listfiles($file);
					}
					$faisunZIP -> createfile();
					echo "<br>压缩完成,共添加 $filenum 个文件.<br><a href='".$_REQUEST["todir"].$_REQUEST["zipname"]."'>".$_REQUEST["todir"].$_REQUEST["zipname"]." (".num_bitunit(filesize($_REQUEST["todir"].$_REQUEST["zipname"])).")</a>";
				}else{
					echo $_REQUEST["todir"].$_REQUEST["zipname"]." 不能写入,请检查路径或权限是否正确.<br>";
				}
			}else{
				echo "没有选择的文件或目录.<br>";
			}
		}
		echo '</form>';
    mainbottom();
	break;

    case "allzip":
	allzip();
	break;

    case "edit":
	edit($_REQUEST['fename']);
	break;

    case "save":
	save($_REQUEST['ncontent'], $_REQUEST['fename']);
	break;

    case "cr":
	cr();
	break;

    case "create":
	create($_REQUEST['nfname'], $_REQUEST['isfolder'], $_REQUEST['ndir']);
	break;

    case "ren":
	ren($_REQUEST['file']);
	break;

    case "rename":
	renam($_REQUEST['rename'], $_REQUEST['nrename'], $folder);
	break;

    case "mov":
	mov($_REQUEST['file']);
	break;

    case "move":
	move($_REQUEST['file'], $_REQUEST['ndir'], $folder);
	break;

    case "viewframe":
	viewframe($_REQUEST['file']);
	break;

    case "viewtop":
	viewtop($_REQUEST['file']);
	break;

    case "printerror":
	printerror($error);
	break;

    case "logout":
	logout();
	break;

    default:
	home();
	break;
}
?>