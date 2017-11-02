<?php
/**
 * HA上传类
 *
 */
class HA_Upload{
    private $files;
    private $cur_file;
    private $upload_dir;
    private $allowed_exts;
    private $debug;
    private $keep_name;
    private $max_size = 10240000;
    private $min_size = 0;

    
    private $error;
    private $uploaded_files = array();

    /**
     * 构造函数
     *
     */
    function __construct($files, $str_exts = 'zip,rar,txt', $upload_dir = UPLOAD_DIR){
        $this->files = $files;
        $this->upload_dir = $upload_dir;
        $this->allowed_exts = array();
        $this->addAllowedExts($str_exts);
        $this->error = '';
        $this->debug = false;
    }

    /**
     * 析构函数
     *
     */
    function __destruct(){
        
    }

    /**
     * 获取变量方法
     */
    function __get($key){
        switch($key){
        case 'error':
            return $this->error;
            break;
        default:
            die('get - unknown key - ' . $key);
        }
    }

     /**
     * 获取变量方法
     */
    function __set($key, $value){
        switch($key){
        default:
            die('set - unknown key - ' . $key);
        }
    }

    /**
     * 是否保持原文件名
     * 选择否的话会产生一个随机的文件名
     */
    public function keepName($bool = true){
        $this->keep_name = $bool;
    }

    /**
     * 是否输出调试信息
     */
    public function debug($bool = null){
        if(is_null($bool)){
            $bool = true;
        }
        $this->debug = $bool;
    }

    /**
     * 重新设置文件允许大小,默认为0-10M
     */
    public function setSizescale($min, $max){
        $this->min_size = $min;
        $this->max_size = $max;
    }

    // 检查上传文件夹
    private function checkUploadDir(){
        if(is_dir($this->upload_dir) && is_writable($this->upload_dir)){
            return true;
        }
        if(false == @mkdir($this->upload_dir)){
            $this->addError('上传目录错误');
            return false;
        }
        return true;
    }

    // 添加允许类型
    public function addAllowedExts($str_exts){
        $str_exts = strtolower($str_exts);
        $arr = explode(',', str_replace(' ', '', $str_exts));
        $this->allowed_exts = array_merge($this->allowed_exts, $arr);
    }

    // 添加错误信息
    private function addError($error, $file = null){
        if(!empty($this->cur_file['error'])){
            $this->cur_file['error'] .= ' - ';
        }
        $this->cur_file['error'] .= $error;
    }

    // 检查文件大小
    private function checkSize(){
        return $this->cur_file['size'] >= $this->min_size && 
            $this->cur_file['size'] <= $this->max_size;
    }

    // 检查错误编号
    private function checkError(){
        $error = $this->cur_file['error'];
        $name = $this->cur_file['name'];
        switch($error){
            case '0':
                return true;
			case '1':
                $this->addError('上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值', $name);
                return false;
			case '2':
				$this->addError('上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值', $name);
                return false;
			case '3':
				$this->addError('文件只有部分被上传', $name);
                return false;
			case '4':
				$this->addError('没有文件被上传', $name);
                return false;
			case '6':
				$this->addError('找不到临时文件夹', $name);
                return false;
			case '7':
				$this->addError('文件写入失败', $name);
                return false;
			case '7':
				$this->addError('文件写入失败', $name);
                return false;
            default:
                $this->addError('unknown error : ' . $error, $name);
                return false;
        }
    }

    // 检查文件类型
    private function checkExt($file_name){
        $str_ext = ha_get_ext($file_name);
        if(!in_array(strtolower($str_ext), $this->allowed_exts)){
            return false;
        }
        return true;
    }

    private function getDestpath(){
        // 是否保持文件名
        if($this->keep_name){
            $dest_name = ha_get_name($this->cur_file['name']);
        }else{
            $index = uniqid();
            $dest_name = $index . '.' . ha_get_ext($this->cur_file['name']);
        }
        
        // 防止文件重复
        $num = null;
        while(file_exists($this->upload_dir . '/' . $dest_name . $num)){
            $num++; 
        }
        $dest_name = $dest_name . $num;

        return $this->upload_dir . '/' . $dest_name;
    }

    public function getUploadedFiles($i = null){
        if(is_null($i)){
            return $this->uploaded_files;
        }else{
            return $this->uploaded_files[$i];
        }        
    }

    /**
     * 上传单个文件
     *
     */
    public function uploadOne(){
        // 检查文件大小
        if(false == $this->checkSize()){
            $this->addError('文件大小不在 ' . $this->min_size . ' - ' . $this->max_size . ' 范围', $this->cur_file['name']);
            return false;
        }

        // 检查扩展名
        if(false == $this->checkExt($this->cur_file['name'])){
            $this->addError('出于安全原因,只允许以下文件类型 : ' . join(', ', $this->allowed_exts), $this->cur_file['name']);
            return false;
        }

        // 通过错误信息获取上传验证
        if(false == $this->checkError()){
            return false;
        }
        
        //输出调试信息
        if($this->debug){
            echo "Upload: " . $this->cur_file["name"] . "<br />";
            echo "Type: " . $this->cur_file["type"] . "<br />";
            echo "Size: " . ha_bytesize($this->cur_file["size"]) . "<br />";
            echo "Temp file: " . $this->cur_file["tmp_name"] . "<br />";
        }

        // 获取目的文件名
        $dest_path =  $this->getDestpath();

        // 检查是否是合法的上传文件,来保证安全
        if(!is_uploaded_file($this->cur_file["tmp_name"])){
            $this->addError('非上传文件,系统拒绝执行', $this->cur_file['name']);
            return false;
        }
       
        // 将检查正确无误的文件挪到上传路径
        if(!@move_uploaded_file($this->cur_file['tmp_name'], $dest_path)){
            $this->addError('移动文件失败, 未知因素', $this->cur_file['name']);
            return false;
        }

        // 在destination中记录上传成功的文件的文件名,等待使用者来读取
        $this->cur_file['destination'] = $dest_path;
        $this->uploaded_files[] = $this->cur_file;
        return true;
    }

    /**
     * 上传方法
     */
    public function doUpload(){
        // 检查上传路径
        if(false == $this->checkUploadDir()){
            $this->error = '上传路径不可用';
            return false;
        }

        // 批量上传函数
        foreach($this->files as $cur_file){
            // 忽略空文件
            if(empty($cur_file['name'])){
                continue;
            }
            
            if(is_array($cur_file['name'])){
                foreach($cur_file['name'] as $key => $value){
                    $this->cur_file = array();
                    $this->cur_file['name'] = $cur_file['name'][$key];
                    $this->cur_file['type'] = $cur_file['type'][$key];
                    $this->cur_file['tmp_name'] = $cur_file['tmp_name'][$key];
                    $this->cur_file['error'] = $cur_file['error'][$key];
                    $this->cur_file['size'] = $cur_file['size'][$key];
                    $this->uploadOne();
                }
            }else{
                $this->cur_file = $cur_file;
                $this->uploadOne();
            }
        }
        return true;
    }

    static function html($action = 'upload_action.php'){
    ?>
    <form action="<?php echo $action;?>" method="post" enctype="multipart/form-data">
        <label for="file">上传&nbsp;:&nbsp;</label>
        <input type="file" name="file" id="file" /> 
        <input type="submit" value="Submit">
    </form>
    <?php
    }
}
