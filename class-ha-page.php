<?php
/**
 * class-ha-page.php 分页类
 */
class HA_Page{
    private $page_key = 'page';
    private $width;
    private $next_width;
    private $prev_width;
    private $block_width;
    private $height = 22;
    private $count;
    
    var $first_page;
    var $last_page;
    var $uri;
    var $per_page;
    var $debug;
    var $page;

    // 初始化
    var $left_most_page;
    var $right_most_page;
    var $side_buttom_count = 4;
 
    // 构造函数
    function __construct($uri, $count, $per_page = DEFAULT_ROW_COUNT, $width = 500){
        // 如果uri未指定,则从server变量总获取
        if(empty($uri)){
            $this->uri = $_SERVER['REQUEST_URI'];
        }else{
            $this->uri = $uri;
        }

        $this->per_page = $per_page; 
        $this->count = $count;
        $this->width = $width;

        // 获取当前页码
        $this->page = HA_Uri::queryValue($this->page_key, $this->uri);
    }

    // uri处理函数,替换其中的page
    function url_prefix($uri, $page){
        return HA_Uri::queryUpdate(compact('page'), $uri);
    }

    // 输出分页的html
    function html(){
        // 计算第一页和最后一页
        $this->first_page = 0;
        $this->last_page = intval(($this->count - 1) / $this->per_page);
        $this->left_most_page = max(0, $this->page - $this->side_buttom_count);
        $this->right_most_page = min($this->left_most_page + $this->side_buttom_count * 2, $this->last_page);
        $this->left_most_page = max(0, $this->right_most_page - $this->side_buttom_count * 2);
?>
    <div class=ha_divpage>
    <a class="prev<?php if($this->page == $this->first_page) echo ' disabled'; else echo ' mid';?>" <?php if($this->page != $this->first_page) echo 'href="' . $this->url_prefix($this->uri, $this->page - 1) . '"';?>>上一页</a>
<?php
        // 从最左边的一个开始,先检查是不是第一页,如果不是,就把最左边的变成第一页
        for($i = $this->left_most_page; $i <= $this->right_most_page; $i++){
            if($i == $this->left_most_page && $i > 0){
                echo '<a class="mid" href="' . $this->url_prefix($this->uri, 0) . '">' . '1...' . '</a>';
            }elseif($i == $this->page){
                echo '<a class="mid current">' . ($i + 1) . '</a>';
            }elseif($i == $this->right_most_page && $i < $this->last_page){
                echo '<a class="mid" href="' . $this->url_prefix($this->uri, $this->last_page) . '">' . '...' . ($this->last_page + 1) . '</a>';
            }else{
                echo '<a class="mid" href="' . $this->url_prefix($this->uri, $i) . '">' . ($i + 1) . '</a>';
            }
        }
    ?>
    <a class="next<?php if($this->page == $this->last_page) echo ' disabled'; else echo ' mid';?>" <?php if($this->page != $this->last_page) echo 'href="' . $this->url_prefix($this->uri, $this->page + 1) . '"';?>>下一页</a>
</div>
<?php
    }

    // 魔术方法getter
    function __get($key){
        switch($key){
            case 'page_key':
                return $this->page_key;
            case 'width':
                return $this->width;
            case 'heith':
                return $this->heith;
            default:
                if($this->debug){
                    die('__get - unknown key');
                }else{
                    return null;
                }
        }
    }

    // 魔术方法setter
    function __set($key, $value){
        switch($key){
            case 'page_key':
                $this->page_key = $value;
                break;
            case 'width':
                $this->width = $value;
                break;
            case 'heith':
                $this->heith = $value;
                break;
            default:
                if($this->debug){
                    die('__set - unknown key');
                }else{
                    return null;
                }
        } 
    }

    // 输出分页的css
    function css(){
        // 计算宽度
        $this->next_width = $this->prev_width = 43;
        $this->block_width = 35;
        $block_width = $this->block_width;
        $prev_width = $this->prev_width;
        $next_width = $this->next_width;
        $height = $this->height;
        $width = $this->width;
?>
<style>
    .ha_divpage{height:<?php echo $height;?>px;}
    .ha_divpage a{border:1px solid #456813; background-color:#fff; float:left; width:<?php echo $block_width;?>px; font:bold 12px Arial, Helvetica, sans-serif; text-decoration:none; text-align:center; display:block; margin:0 3px; color:#456813;}
    .ha_divpage a:hover, .ha_divpage a.current, .ha_divpage a:active{background-color:#6B9F1F; color:#fff;}
    .ha_divpage a.next{width:<?php echo $next_width;?>px;}
    .ha_divpage a.prev{width:<?php echo $prev_width;?>px;}
    .ha_divpage a.mid, .ha_divpage a.disabled{line-height:<?php echo $height;?>px; }
    .ha_divpage a.disabled{cursor:default; border:1px solid #DDD; color:#999; background:#fff;}
</style>
<?php
    }
}

