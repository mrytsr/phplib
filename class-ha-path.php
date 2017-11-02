<?php
/*------------------------------------------------------------------------------------------------------
class-ha-path.php
------------------------------------------------------------------------------------------------------*/
define('HA_PATHCOPY_MIRROR', 0);
define('HA_PATHCOPY_UPDATE', 1);
class HA_Path{

    /*
     * @遍历文件夹
     */
    function dirwalk($root_path, $cb = false){
        if($cb == false){
            function cb($path){
                echo $path . '<br>';
            }
            $cb = 'cb';
        }
        $hdir = opendir($root_path);
        while($sname = readdir($hdir)){
            $path = $root_path . '/' . $sname;
            $cb($path);
            if(is_dir($path) && $sname != '.' && $sname != '..'){
                HA_Path::dirwalk($path, $cb);
            }
        }
        closedir($hdir);
    }

    //显示每个文件夹下面有多少个文件和文件夹的方法
    function dirwalk_count($root_path, $cb = false){
        if($cb == false){
            function cb($path){
                //echo $path . '<br>';
            }
            $cb = 'cb';
        }
        $hdir = opendir($root_path);
        $countd = 0;
        $countf = 0;
        while($sname = readdir($hdir)){
            $path = $root_path . '/' . $sname;
            $cb($path);

            if(is_dir($path) /*&& $sname != '.' && $sname != '..'*/){
                $countd++;
                if($sname != '.' && $sname != '..')
                    ha_dirwalk_count($path, $cb);
            }elseif(true || is_file($path) || is_link($path)){
                $countf++;
            }else{

                die(var_dump($path));
            }
        }
        echo $root_path . ' - 目录:' . $countd . ' 文件:' . $countf . '<br>';
        closedir($hdir);
    }

    /*
     * @递归删除目录，输入值可以为文件或者文件夹路径
     */
    function pathdel($path){
        $bname = basename($path);
        if($bname == '.' || $bname == '..'){
            return true;
        }elseif(is_file($path)){
            echo 'unlink' . $path . '<br>';
            return unlink($path);
        }elseif(is_dir($path)){
            $hdir = opendir($path);
            while($sub = readdir($hdir)){
                if(!HA_Path::pathdel($path . '/' .$sub)){
                    echo 'rmdir false - ' . $path . '<br>';
                    return false;
                }
            }
            closedir($hdir);
            echo 'rmdir' . $path . '<br>';
            return rmdir($path);
        }else{
            return false;
        }
    }
    static function pathsize($path){
        $size = 0;
        if(is_dir($path) && basename($path) != '.' && basename($path) != '..'){
            $hdir = opendir($path);
            while($sub = readdir($hdir)){
                $size += HA_Path::pathsize($path . '/' . $sub);
            }
            closedir($hdir);
            return $size;
        }elseif(is_dir($path)){
            return filesize($path);    
        }else{
            return 0;
        }
    }

    /*
     * @路径复制函数，src可以是文件或者文件夹，dst必须是文件夹，选择MIRROR模式遇到文件冲突就会返回false
     * 选择UPDATE遇到文件冲突会覆盖修改日期较早的文件
     */
    function pathcopy($src_path, $dst_path, $HA_PATHCOPY_MODE = HA_PATHCOPY_MIRROR){
        if(!is_dir($dst_path)){
            return false;
        }

        $bname = ha_get_name($src_path);

        if($bname == '.' || $bname == '..'){
            return true;
        }elseif(is_file($src_path)){
            if(is_dir($dst_path . '/' . $bname)){
                return false;
            }
            if(is_file($dst_path . '/' . $bname)){
                if($HA_PATHCOPY_MODE == HA_PATHCOPY_MIRROR){
 
                    return false;
                }elseif(filemtime($dst_path . '/' . $bname) != filemtime($src_path)){
                    if(!unlink($dst_path . '/' . $bname)){
                        had('unlinkfail - ' . $src_path);
                        return false;
                    }
                }else{
                    had('skip - ' . $src_path);
                    return true;
                }
            }
            return copy($src_path, $dst_path . '/' . $bname);
        }elseif(is_dir($src_path)){
            if(is_dir($dst_path . '/' . $bname)){
                if($HA_PATHCOPY_MODE == HA_PATHCOPY_MIRROR){
                    return false;
                }
            }else{
                if(!mkdir($dst_path . '/' . $bname)){
                    return false;
                }
            }

            $hdir = opendir($src_path);
            while($sub = readdir($hdir)){
                if(!HA_Path::pathcopy($src_path . '/' . $sub, $dst_path . '/' . $bname, $HA_PATHCOPY_MODE)){
                    return false;
                }
            }
            closedir($hdir);
            return true;
        }else{
            return false;
        }
    }

}

