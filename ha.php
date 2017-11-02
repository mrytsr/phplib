<?php 
// 流量统计
//require(dirname(__FILE__) . "/../open/audistat/stats.php"); 
?>

<?php
/**
    * @file ha.php
    * @brief 
    * @author tjx
    * @version 0.5
    * @date 2013-11-09
 */

// AES加密
include 'AES.php';
define('DEFAULT_ROW_COUNT', 10);
// 上传目录
define('HA_UPLOAD_ROOT', 'D:\AppServ\www\upload');

if(!function_exists('__autoload')){
    function __autoload($class_name){
        // 将形如 HA_Date_Calc 的类名 转换成 class-ha-date-clac.php的类文件名 
        $class_name = strtolower(strtr($class_name, '_', '-'));
        $class_file = "class-{$class_name}.php";
        include $class_file;
    }
}

class HA_Auth{
    var $use_cookie = false;
    var $use_session = true;

    function __construct(){
    
    }

    function getUserId(){
        $user_id = ha_user_id();
        /*if(empty($user_id)){
            ha_location('../auth/login.php');
        }*/
        return $user_id;
    }

    function getUserName($dbh, $user_id){
        return ha_user_name($dbh, $user_id);
    }

    function setUserId(){
    
    }

    static function password($text){
        $encrypt = md5(str_rot13((md5($text))));
        return $encrypt;
    }
}
function ha_user_id(){
    return ha_user('id');
}

function ha_user_name($dbh, $id = null){
    return ha_user('name', $dbh, $id);
}

function user_power($dbh, $username){
    return ha_user('power', $dbh, $username);
}

function ha_user($field, $dbh = null, $w = null){
    switch($field){
    case 'id':
        session_start();
        $id = $_SESSION['hauser']['id'];
        if(empty($id) || !($id > 0)){
            return false;
        }
        return intval($id);
        break;
    case 'name':
    case 'power':
        //先检查数据库头
        if(empty($dbh)){
            die('db handler is necessary');
        }
        //再从session获取用户id
        $condition = '';
        
        if(null === $w){
            $w = ha_user('id');
            return ha_user($field, $dbh, $w);
        }elseif(false === $w){
            return null;
        }elseif(is_int($w)){
            $condition =  'id = \'' . $w . '\'';
        }elseif(is_string($w)){
            $condition = 'name = \'' . $w . '\'';
        }else{
            die('ha fatal error');
        }
        //再取得想要的列
        $field = $dbh->single($field, $condition) or ha_exit();
        return $field;
        break;
    default:
        return false;
        break;
    }
}

function ha_exit(){
    if(!session_start()){
        return false;
    }
    @$_SESSION['hauser']['id'] = -1;
    return true;
}


function ha_password($text){
    return HA_Auth::password($text);
}

function ha_errusername($str){
    $err = '用户名须符合<br>1.由数字,字母,下划线组成2.长度6~20<br>';
    $reg = '/^[a-zA-Z0-9_]{6,12}|root$/';

    if(preg_match($reg, $str)){
        return false;
    }else{
        return $err;
    }
}

function ha_errpassword($pwd, $repwd = null){ 
    if($repwd !== null && $pwd !== $repwd){
        return '两次输入密码不一致';
    }
    $reg = '/^[a-zA-Z0-9\~\`\!\@\#\$\%\^\&\*\(\)\_\-\+\=\{\}\[\]\:\;\<\>\,\.\?\/]{6,200}$/';
    if(preg_match($reg, $pwd)){
        return false;
    }else{
        return '密码须符合1.由数字,字母,如下字符[~`!@#$%^&*()_-+={}[]:;<>,.?/]组成2.必须有一个大写字母3.要有英文与数字相结合3.长度6~200';
    }
}

class HA_Error{
/*
 	//自己处理报错
	
	//echo E_WARNING;exit;//2
	
	function my_error_handler($level,$message,$file,$line){
	
		$str  = '';
		$str .= "错误等级：$level<br>\r\n";
		$str .= "错误消息：$message<br>\r\n";
		$str .= "错误文件：$file<br>\r\n";
		$str .= "错误行号：$line<br>\r\n";
		
		//echo $str;
		error_log($str,3,'./error.log');
	}
	set_error_handler('my_error_handler');

	echo 'aaaa<br>';
	
	in_array();
 */ 
}


function ha_client_ip() {
    if ($_SERVER["HTTP_X_FORWARDED_FOR"])
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    else if ($_SERVER["HTTP_CLIENT_IP"])
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    else if ($_SERVER["REMOTE_ADDR"])
        $ip = $_SERVER["REMOTE_ADDR"];
    else if (getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else
        $ip = "Unknown";
    return $ip;
}


//这里处理报错的方法不可取,应该返回结果后在这里统一处理,这里只是苟且为之
function ha_batch_delete($dbh, $col, $checked){
    if(empty($checked)){
        ha_submit('批量删除集合为空');
    }
    $where = '`' . $col . '` in (\'' . implode('\', \'', $checked) . '\')';
    if($dbh->delete($where)){
        ha_submit('批量删除成功');
    }
    ha_submit('批量删除失败');
}

function ha_get($params, $die = true){
    $param_error = false;
    if(!isset($_GET[$params])){
        echo ha_get_name(__FILE__) . ': Missing parameter: [' .  $params . '] is necessary<br>';
        $param_error = true;
    }
    
    if($param_error && $die){
        exit;
    }
    return $_GET[$params];
}

function ha_post($params, $die = true){
    $param_error = false;
    if(!isset($_POST[$params])){
        echo ha_get_name(__FILE__) . ': Missing parameter: [' .  $params . '] is necessary';
        $param_error = true;
    }
    
    if($param_error && $die){
        exit;
    }
    return $_POST[$params];
}

/**
 * 上传类的静态方法,目前只能传一个文件先
 *
 */
function ha_doupload($ufile, $upload_path = './upload/', 
    $str_types = 'rar,zip,txt,jpg,jpeg,png,giff', $debug = false){
    $upl = new HA_Upload($ufile, $str_types, $upload_path);
    $upl->doUpload();
    $file = $upl->getDestfiles(0);
    if(empty($file)){
        ha_submit($upl->errors);
    }
    return $upl->getDestfiles(0);
}


function ha_uploadhtml(){
    return HA_Upload::html('uplaod_file.php');
}

function ha_array_insert($arr, $index, $value){
    $dest_arr = array();
    $dest_arr = array_slice($arr, 0, $index);
    $dest_arr[] = $value;
    $dest_arr = array_merge($dest_arr, array_slice($arr, $index));
    return $dest_arr;
}


function ha_now($format = null, $time = null){
    if(empty($format)){
        $format = 'Y-m-d H:i:s';
    }
    if(empty($time)){
        $time = time();
    }
    date_default_timezone_set('Asia/Shanghai');
    return date($format, $time);
}
  

function sessval2($father = null, $son = null, $val = null){
    session_start();
    $element = null;
    if(empty($son)){
        $element = &$_SESSION[$father];    
    }else{
        $element = &$_SESSION[$father][$son];    
    }
    
    if($val == null){
        return $element;
    }else{
        return $element = $val;
    }
}

function sessval($father, $val){
    return sessval2($father, null, $val);
}

function ha_session($valname, $value){
    return sessval2($valname, $value);
}

function ha_submit($str, $dest_url = null, $count_down = null){
    if(empty($dest_url)){
        $dest_url = $_SERVER['HTTP_REFERER'];
    }
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>
    <style>
        body{background:white;}
        div.main{text-align : center; margin:100px auto; background:#2672EC; color:white; font:normal 2.2em/1.2em 'microsoft yahei'; width:750px; padding:30px 15px;}
        a{color:#eee; text-decoration:none;}
    </style>
</head>
<body>
<div class=main>
    
    <span>
<?php
    $count_str = '';
    if($count_down){
        $count_str = '&nbsp;|&nbsp;' . $count_down . '秒后跳转';
        header('');
    }
    echo $str . $count_str . '&nbsp;|&nbsp;<a href=' . $dest_url . '>跳转</a>';?>
    </span>
    <div style="clear:both;"></div>
</div>
</body>
</html>

<?php
    die();
}

/**
 * @验证码
 */
class HA_Nrcode{
    function nrcode(&$code, $width = 100, $height = 40, $num = 4, $type = 3, $mime = 'jpeg'){
        switch($type){
        case 1:
            $string = join('', array_rand(range(0, 9), $num));
            break;
        case 2:
            $string = join('', array_rand(array_flip(array_merge(range('a', 'z'), range('A', 'Z'))), $num));
            break;
        case 3:
            $string = substr(str_shuffle('abdefghnpqrtABDEFGHNPQRT'), 0, $num);
            break;
            default: $string = '';break;
        }
        $img = imagecreatetruecolor($width, $height);

        ha_imagebg($img, '#cccccc');
        $x = intval($width / 8);
        $y = intval($height * 0.65);
        $delta = intval($height / 10);
        $pace = intval($width / $num * 0.8);
        for($i = 0; $i < $num; $i++){
        imagettftext($img, intval($height / 2.2), mt_rand(-35, 35), $x, $y + mt_rand(-$delta, $delta), 
        ha_color($img, '#333333'), dirname(__FILE__) . '\ha.ttf', $string[$i]);
            $x += $pace;
        }
        $code = $string;
        return $img;
    }

    function html(){
    ?>
    <style>
        .refresh{color:#2672EC;}
        .refresh:hover{cursor:pointer;}
    </style>
                    <span class=info>输入你看得到的字符<br>
                        <a class=refresh onclick="change()">刷新字符</a>
                    </span>
    <img width=300 height=48 src="nrcode.php" id="nrcodeimg" class="nrimg" />
                    <script>
                        function change(){
                            document.getElementById('nrcodeimg').src= "<?php echo '/proj/hawp/user/nrcode.php?a=';?>" + Math.random();
                        }
                    </script>
                <div class="clearfix wipe">
                    <input type=text name=nrcode id=nrcode></input>
                    <span class=err id=nrcode_err></span>
                </div>
    <?php
    }
}

/*
 * @验证码静态调用接口
 */
function ha_nrcode(&$code, $width = 100, $height = 40, $num = 4, $type = 3, $mime = 'jpeg'){
    return HA_Nrcode::nrcode($code, $width, $height, $num, $type, $mime);
}

function ha_nrcodehtml(){
    return HA_Nrcode::html();
}

/**
 * @经过风格化的html类
 */
class HA_Html{
    static function get_style($style_class){
        $style_arr = array();
        $style_arr['youpai'] = <<<html
input[type=submit]%my_class%, form%my_class% input[type=submit]{
display: inline-block;
zoom: 1;
vertical-align: baseline;
margin: 0 2px;
outline: none;
cursor: pointer;
text-align: center;
text-decoration: none;
font-weight: bold;
font-size: 14px;
padding: 6px 15px 7px;
text-shadow: 0 1px 1px rgba(0,0,0,.3);
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
-moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
box-shadow: 0 1px 2px rgba(0,0,0,.2);
transition: all linear .2s;
-moz-transition: all linear .2s;
-webkit-transition: all linear .2s;
color: #e8f0de!important;
border: solid 1px #538312;
background: #64991e;
background: -webkit-gradient(linear,left top,left bottom,from(#7db72f),to(#4e7d0e));
background: -moz-linear-gradient(top,#7db72f,#4e7d0e);
background: -o-linear-gradient(top,#7db72f,#4e7d0e);
}
input[type=submit]%my_class%:hover, form%my_class% input[type=submit]:hover{
text-decoration: none;
background: #538018;
background: -webkit-gradient(linear,left top,left bottom,from(#6b9d28),to(#436b0c));
background: -moz-linear-gradient(top,#6b9d28,#436b0c);
background: -o-linear-gradient(top,#6b9d28,#436b0c);
}
input[type=text]%my_class%, form%my_class% input[type=text]{background: #fff url(/img/input_bg.gif) no-repeat left top;
padding: 5px 3px;
border: 1px solid #cfcfcf;
outline: none;
border-radius: 5px;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
-o-border-radius: 5px;
transition: border linear .2s,box-shadow linear .2s;
-moz-transition: border linear .2s,-moz-box-shadow linear .2s;
-webkit-transition: border linear .2s,-webkit-box-shadow linear .2s;
}
h1%my_class%, %my_class% h1{font-size: 14px;
color: #393939;
margin-bottom: 5px;
}
p%my_class%, %my_class% p{font-size: 1.0em;
line-height: 1.3em;
margin: 1.2em 0 1em 0;
}
select%my_class%, %my_class% select{
font-size: 12px;
color: #444;
font-family: 'Helvetica Neue',Helvetica,Arial,Sans-serif;
}

html;
        return $style_arr[$style_class];
}
    /*
    static $style_arr = array(
        'youpai' => <<<html
.%prefix% input[type=submit]{
display: inline-block;
zoom: 1;
vertical-align: baseline;
margin: 0 2px;
outline: none;
cursor: pointer;
text-align: center;
text-decoration: none;
font-weight: bold;
font-size: 14px;
padding: 6px 15px 7px;
text-shadow: 0 1px 1px rgba(0,0,0,.3);
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;
-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
-moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
box-shadow: 0 1px 2px rgba(0,0,0,.2);
transition: all linear .2s;
-moz-transition: all linear .2s;
-webkit-transition: all linear .2s;
color: #e8f0de!important;
border: solid 1px #538312;
background: #64991e;
background: -webkit-gradient(linear,left top,left bottom,from(#7db72f),to(#4e7d0e));
}
html;
    )*/

    static function style($style_class, $my_class = null){
        $style = self::get_style($style_class);
        if(is_null($my_class)){
            $my_class = $style_class; 
        }
        return str_replace('%my_class%', '.' . $my_class, $style);
    }


    function inputSubmit(){
    
    }

    function inputTextAlt($id, $name, $class, $value, $alt){?>
    <input  onblur="fonblur(this.id, this.alt)" onfocus="fonfocus(this.id, this.alt)" id=<?php echo $id;?> type=text name=<?php echo $name;?> class="<?php echo $class; ?>" value="<?php echo $value; ?>" alt="<?php echo $alt; ?>">
    <script type=text/javascript>
    function fonfocus(id, v){
        str = document.getElementById(id).value;
        if(str == v){
            document.getElementById(id).value = "";
        }
    }
    function fonblur(id, v){
        str = document.getElementById(id).value;
        if(str.length == 0){
            document.getElementById(id).value = v;
        }
    }
    </script>
    <?php
    }
}

/*<?php hj_inputalt('id', 'name', 'class', 'value', 'alt');?>*/
function hj_inputalt($id, $name, $class, $value, $alt){
    return HA_Html::inputTextAlt($id, $name, $class, $value, $alt);
}




//电子邮件的正则验证,暂时留空
function ha_erremail(){
    return false;
}


/*
 * @遍历文件夹
 */
function ha_dirwalk($root_path, $cb = false){
    return HA_Path::dirwalk($root_path, $cb);
}

//显示每个文件夹下面有多少个文件和文件夹的方法
function ha_dirwalk_count($root_path, $cb = false){
    return HA_Path::dirwalk_count($root_path, $cb);
}

/*
 * @递归删除目录，输入值可以为文件或者文件夹路径
 */
function ha_pathdel($path){
    return HA_Path::pathdel($path);
}

/*
 * @统计路径大小，可以输入文件或者文件夹
 */
function ha_pathsize($path){
    return HA_Path::pathsize($path);
}

function ha_pathcopy($src_path, $dst_path, $HA_PATHCOPY_MODE = HA_PATHCOPY_MIRROR){
    return HA_Path::pathcopy($src_path, $dst_path, $HA_PATHCOPY_MODE);
}

function ha_get_prename($path){
    return strtok(ha_get_name($path), '.');
}

function ha_get_name($path){
    $s = str_replace('\\', '/', $path);
    $offset = 0;
    if(false !== ($os = strrpos($s, '/'))){
        $offset = $os + 1;
    }
    return substr($s, $offset);
}

function ha_get_ext($path){
    return substr($path, strrpos($path, '.') + 1);
}

/*------------------------------------------------------------------------------------------------------
class-ha-image.php
------------------------------------------------------------------------------------------------------*/

class HA_Image{
    /*
     * @给图片添加水印，water可以为字符串或者图像句柄
     */
    static function wmark($img, $water, $height_pct, $width_pct, $color = 'red', $font = 'simkai.ttf'){
        list($w, $h) = ha_imagesize($img);
        $x = $width_pct * $w;
        $y = $height_pct * $h;
        
        if(is_string($water)){
            imagettftext($img, 30, 0, $x, $y, 
                ha_color($img, $color), $font, $water);
            return $img;
        }else{
            list($w_w, $w_h) = ha_imagesize($water);
            imagecopy($img, $water, $x, $y, 0, 0, $w_w, $w_h);
        }
        return $img;
    }

    /*
     * @图片缩放 - 按照宽高
     */
    static function zoom_size($img, $dst_w, $dst_h, $resampled = true){
        list($src_w, $src_h) = ha_imagesize($img);

        // 兼容高或者款为空的情况
        if(empty($dst_w)){
            $dst_w = $src_w;
        }elseif(empty($dst_h)){
            $dst_h = $src_h;
        }

        if($dst_w / $dst_h > $src_w / $src_h){
            return HA_Image::zoom_percent($img, $dst_h / $src_h, $resample);
        }else{
            return HA_Image::zoom_percent($img, $dst_w / $src_w, $resample);
        }
    }

    static function resize($img, $dst_w, $dst_h, $resampled = true){
        return self::zoom_size($img, $dst_w, $dst_h, $resampled);    
    }

    static function toResource($what){
        if(is_string($what)){
            return self::createFrom($what);
        }elseif(true){
        
        }else{
            return null;
        }
    }
/*
■imagecreatefromgif — 由文件或URL创建一个新图象
■imagecreatefromjpeg — 由文件或URL创建一个新图象
■imagecreatefrompng — 由文件或URL创建一个新图象
■imagecreatefromwbmp — 由文件或URL创建一个新图象
*/    
    static function ext2func($ext, $func_prefix){
        // 先排除大小写的干扰
        $ext = strtolower($ext);

        // 定义转换数组
        $convert_arr = array(
            'jpg' => 'jpeg',
            'bmp' => 'wbmp'
        );

        // 转换字符串
        if(in_array($ext, array_keys($convert_arr))){
            $ext = $convert_arr[$ext];
        }

        // 如果函数不存在 就返回错误
        $func_name = $func_prefix . $ext;
        if(!function_exists($func_name)){
            return false;
        }
        return $func_name;
    }
    /*
image/bmp bmp 
image/cis-cod cod 
image/gif gif 
image/ief ief 
image/jpeg jpe 
image/jpeg jpeg 
image/jpeg jpg 
image/pipeg jfif 
image/svg+xml svg 
image/tiff tif 
image/tiff tiff 
image/x-cmu-raster ras 
image/x-cmx cmx 
image/x-icon ico 
image/x-portable-anymap pnm 
image/x-portable-bitmap pbm 
image/x-portable-graymap pgm 
image/x-portable-pixmap ppm 
image/x-rgb rgb 
image/x-xbitmap xbm 
image/x-xpixmap xpm 
image/x-xwindowdump xwd 
*/
    static function ext2mine($ext){
        $cvt_arr = array(
            'bmp' => 'bmp',
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
            'jpe' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'ico' => 'ico',
        );
       return $cvt_arr[strtolower($ext)]; 
    }

    static function createFrom($file, $ext = null){
        // 如果没有指定ext,就从file中获取
        if(is_null($ext)){
            $ext = ha_get_ext($file);
        }

        $func = self::ext2func($ext, 'imagecreatefrom');
        if(false == $func){
            return false;
        }
        return $func($file);
    }

    // 把图片资源输出到文件或者网络流中,$path为null则输出到网络流
    static function toFile($img, $path = null, $ext = null){
        // 如果没有指定ext,就从file中获取
        if(is_null($ext)){
            $ext = ha_get_ext($path);
        }

        // 先判断函数是否存在
        $func = self::ext2func($ext, 'image');
        if(!$func){
            return false;
        }

        // 如果指定了path,就输出到指定的路径
        if(!is_null($path)){
            return $func($img, $path);
        }


        // 如果没有指定路径,就输出到流中
        $mine = self::ext2mine($ext);
        // 如果mine没有转换成功,输出错误图像
        if(!$mine){
            return false;
        }
	 	header("content-type:image/{$mine}");		//header("content-type:image/jpeg");
		$func($img);							//imagejpeg($img);
        die();
    }

    static function toHtml(){
    
    }

    /*
     * @图片缩放 - 按照比例
     */
    function zoom_percent($img, $zoompct, $resampled = true){
        list($src_w, $src_h) = ha_imagesize($img);        
        $dst_w = $zoompct * $src_w;
        $dst_h = $zoompct * $src_h;

        $dst_img = imagecreatetruecolor($dst_w, $dst_h);
        if($resampled){
            imagecopyresampled($dst_img, $img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }else{
            imagecopyresized($dst_img, $img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        return $dst_img;
    }

}


/*
 * @给图片添加水印，water可以为字符串或者图像句柄
 */
function ha_wmark($img, $water, $height_pct, $width_pct, $color = 'red', $font = 'simkai.ttf'){
    return HA_Image::wmark($img, $water, $height_pct, $width_pct, $color, $font);
}

/*
 * @图片缩放 - 按照宽高
 */
function ha_imgzoom_size($img, $dst_w, $dst_h, $resampled = true){
    return HA_Image::zoom_size($img, $dst_w, $dst_h, $resampled);
}

/*
 * @图片缩放 - 按照比例
 */
function ha_imgzoom_percent($img, $zoompct, $resampled = true){
    return HA_Image::zoom_percent($img, $zoompct, $resampled);
}
function ha_imgcut($img, $x, $y, $width, $height){
    $newimg = imagecreatetruecolor($width, $height);
    imagecopy($newimg, $img, 0, 0, $x, $y, $width, $height);
    return $newimg;
}

function ha_imagebg($img, $color){
    imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), 
    ha_color($img, $color));
}

function ha_imagesize($imgor){
    if(is_string($imgor)){
        return getimagesize($imgor); 
    }else{
        return array(imagesx($imgor), imagesy($imgor));
    }
}

function ha_imageecho($imgor){
    if(is_string($imgor)){
        echo "<img alt='{$imgor}' title='{$imgor}' src='{$imgor}'>";
    }else{
        if(!is_dir('_tmp')){
            mkdir('_tmp', 0777);
        }
        
        static $ha_imageecho_count = 0;
        $rname = time() . '_' . str_pad($ha_imageecho_count++, 3, '0', STR_PAD_LEFT);
        $file = '_tmp/' . $rname . '.jpg';
        imagejpeg($imgor, $file);
        ha_imageecho($file);
    }
}

function ha_color($img, $hexcolor){
    return ha_imagecolorallocate($img, $hexcolor);
}

function ha_imagecolorallocate($img, $hexcolor){
    if($hexcolor[0] == '#'){
    $arr = sscanf($hexcolor, '#%2x%2x%2x');
    return imagecolorallocate($img, $arr[0],
        $arr[1],
        $arr[2]);
    }else{
        switch($hexcolor){
            case 'yellow':return ha_imagecolorallocate($img, '#FFFF00');break;
            case 'blue':return ha_imagecolorallocate($img, '#0000FF');break;
            case 'red':return ha_imagecolorallocate($img, '#FF0000');break;
            case 'green':return ha_imagecolorallocate($img, '#00FF66');break;
            case 'purple':return ha_imagecolorallocate($img, '#AA2266');break;
            case 'black':return ha_imagecolorallocate($img, '#000000');break;
            case 'white':return ha_imagecolorallocate($img, '#ffffff');break;
            default:
                return ha_imagecolorallocate($img, '#000000');
                break;
        }    
    }
}




/**
 * 输出分页标签的静态方法
 */
function ha_divpage_echohtml($uri, $count, $per_page = DEFAULT_ROW_COUNT, $width = 400){
    $divpage = new HA_Page($uri, $count, $per_page, $width);
    $divpage->css();
    $divpage->html();
}


/*
 * @替换ha_filesize,命名更合理的体积转换函数
 */
function ha_bytesize($or){
    if(!is_int($or)){
        return ha_bytesize(HA_Path::pathsize($or));
    }
    $isize = $or;

    $st_arr = array('B', 'KB', 'MB', 'GB', 'TB');
    
    $i = 0;
    while($isize >= 1024){
        $isize /= 1024;
        $i++;
    }
    return number_format($isize, 2) . '&nbsp;' . $st_arr[$i];
}

function ha_microsecond(){    
    $arr = explode('.', microtime());
    list($mic, $sec) = explode(' ', $arr[1]);
    return (int) $mic;
}

function hadr($x){
    echo '<pre>';
    var_dump($x);
    echo '</pre>';
    return;
}
/**
    * @brief 
    *
    * @param $a
    *
    * @return 
 */
function had($a){
    if(is_string($a) && (strrchr($a, '.') == '.jpeg' || strrchr($a, '.') == '.jpg')){
        return ha_imageecho($a);
    }elseif (is_array($a)){
        $hatb = new HA_Table($a);
        $hatb->show();
    }elseif(empty($a) || is_bool($a) || is_numeric($a) || is_string($a)){
        echo '<hr>';
        echo '<pre>';
        var_dump($a);
        echo '</pre>';
        echo '<hr>';
    }else{
        $type = get_resource_type($a);
        switch($type){
            case 'mysql link' :;
            case 'file':;
            case 'domxml document':;
            case 'file' : 
                echo $type; 
                return;
            default:
        return ha_imageecho($a);
        }
    }
}


/**
    * @brief 
    *
    * @param $url
    *
    * @return 
 */
function ha_location($url){
    /* Redirect browser */
    header('Location: ' . $url);
}

function ha_utf8(){
    header('content-type:text/html; charset=utf-8');
    //echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
}

function ha_gbk(){
    header('content-type:text/html; charset=gbk');
}

/**如下是[hawp]专有的代码*************************************/

/*
 * 这一段是将形如123,432,345的mindex转换为url数组的函数,做多输出count个的数组,
 * 如果count=1,则输出数组第一个元素的字符串
 */ 
function ha_mindex2url($dbh, $mindex, $count = null){
    $arr = $dbh->select(
        'url', 'mindex in (' . '\'' . implode('\', \'', explode(',', $mindex)) . '\'' . ')');
    if(empty($arr)){
        return array();
    }
    $dest_arr = array();
    $i = 0;
    foreach($arr as $a){
        $dest_arr[] = $a['url'];
        if(++$i === $count){
            break;
        }
    }
    if($count == 1){
        return $dest_arr[0];
    }
    return $dest_arr;
}

function ha_url2mindex($dbh, $url){
    $arr = parse_url($url);
    $type = ha_get_ext($arr['path']);
    $uname = $url;
    $utime = ha_now();
    $mindex =  date('ymdHis', time()) . mt_rand(10, 99);
    if(!$dbh->insert_arr(compact('type', 'mindex', 'url', 'uname', 'utime'))){
        ha_submit('数据库操作失败,请联系管理员');
    }
    return $mindex;
}

function ha_path2mindex($dbh, $path, $uname = null, $type=null){
    if(!file_exists($path)){
        return null;
    }
    if(empty($type)){
        $type = ha_get_ext($path);
    }
    if(empty($uname)){
        $uname = $path;
    }

    $url = '/proj/hawp/upload/' . ha_get_name($path);
    $utime = ha_now();
    $mindex = ha_get_prename($path);
    if(!$dbh->insert_arr(compact('type', 'mindex', 'url', 'uname', 'utime'))){
        ha_submit('数据库操作失败,请联系管理员');
    }
    return $mindex;
}

function ha_option($option_name, $option_value = null){
    $dbh = mydbh(TABLE_HA_OPTION);
    $result = null;
    if($option_value !== null){
        $result = $dbh->set_single('option_value', $option_value, 'option_name = \'' . $option_name . '\'');
    }else{
        $result = $dbh->single('option_value', 'option_name = \'' . $option_name . '\'');
    }
    $dbh->close();
    return $result;
}


function ha_get_header(){
    include(THEME_DIR . '/header.php');
}

function ha_get_footer(){
    include(THEME_DIR . '/footer.php');
}



function ha_tree_html_echo($str, $input_id = '', $clicked_js = null, $title = null, $style = null){

if(!$style){
$style = <<<html
.tree_html{overflow:hidden; margin-top:5px; margin-left:5px;}
.tree_html h2{font-size:12px; width:80px; text-align:center; line-height:24px; border:1px solid #D4D0C8}
.tree_html span{background:#ECECEC; display:block; width:8em;}
.tree_html span:hover{background:#fff; cursor:pointer;}
.tree_html .entity{display:none; position:absolute; background:white; font-size:12px;}
.tree_html:hover .entity{display:block; }
.tree_html .deep_1, .tree_html .deep_2{margin-left:25px;}
.tree_html span{border-bottom:1px solid #999}
html;
}

echo '<style>';
echo $style;
echo '</style>';
?>
    <div class = tree_html>
        <?php if(!empty($title)) echo '<h2>' . $title . '</h2>'; ?>
        <div class=entity>
            <?php echo $str;?>
        </div>
    </div>
    <script type=text/javascript>
function tree_clicked(id){
<?php
    if(empty($clicked_js)){
        echo 'document.getElementById("' . $input_id . '").value = id';
    }else{
        echo $clicked_js;
    }
?>
}
    </script>
<?php
}

function ha_tree_html($arr){
    function insert_parent(&$arr, $parent, $term_id, $term_name){
        if($parent == 0){
            $arr[$term_id . '-' . $term_name] = array();
            return true; 
        }
        foreach($arr as $key => &$val){
            if(intval($key) == $parent){
                $val[$term_id . '-' . $term_name] = array();
                return true;
            }elseif(!empty($val)){
                insert_parent($val, $parent, $term_id, $term_name);
            }
        }
        return false;
    }
    $dest_arr = array();
    foreach($arr as $line){
        insert_parent($dest_arr, $line['parent'], $line['term_id'], $line['term_name']);
    }
    function tree_html($arr, $deep = 0){
        $html = '';
        $deep_class='class="deep_' . $deep . ' leaf"';
        foreach($arr as $key => $value){
        $onclick = 'onclick=tree_clicked("' . $key . '")'; 
            if(empty($value)){
                $html .= '<div ' . $deep_class . ' id="' . $key . '"><span ' . 
                    $onclick . ' >' . $key . '</span></div>';
            }else{
                $html .= '<div ' . $deep_class . ' id="' . $key . '"><span ' . 
                    $onclick . ' >' . $key . '</span>' . tree_html($value, $deep + 1) . '</div>';
            }
        }
        return $html;
    }
    return tree_html($dest_arr);
}

// 状态编码转成操作
function ha_state2do_user($state, $trade_mark, $point = null){
    switch($state){
    case 'ordered':
        return '<a href="pay.php?from=' . $_SERVER['REQUEST_URI'] . '&trade_mark=' . $trade_mark . '">付款</a><br>取消';
        break;
    case 'payed':
        return '退款';
        break;
    case 'confirmed':
        return '评价';
        break;
    case 'shipped':
        if(!empty($point)){
            $point = '&point=' . $point;
        }else{
            $point = '';
        }
        return '<a href="action.php?action=trade_confirm&user_id=' . ha_user_id() . '&trade_mark=' . $trade_mark . $point . '">确认</a><br>退货';
        break;
    default:
        return '未知';
    }
}

function ha_state2do_admin($state, $trade_mark){
    switch($state){
        case 'payed':
        return '<a href="trade-express.php?trade_mark=' . $trade_mark . '">发货</a>';
        break;
    }
}

function ha_state2str($state){
    switch($state){
        case'ordered' :
            return '已下单';
        case'payed' :
            return '已付款';
        case'shipped' :
            return '已发货';
        case'confirmed' :
            return '已确认';
        case'ranked' :
            return '已评价';
        default:
            return $state;
    }
}
