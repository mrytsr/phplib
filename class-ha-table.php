
<?php
/*
 * 使用demo
    $arr = $dbh->select_page('*', 'true', $page);
    $hatb = new HA_Table($arr, '表格列表');
    //$hatb->index_col = 'term_id'; //这一项需要好好检查
    //$hatb->show_batch_delete();
    $hatb->divpage_init($_SERVER['REQUEST_URI'], $dbh->count('true'));
    $hatb->show();
*/
class HA_Table{
    //打印表格及相关附件
    function show(){
        $show_check = $this->show_check;
        $index_col = $this->index_col;
        $edit_prefix = $this->edit_prefix;
        if($show_check && $index_col){
            $this->arr = $this->addcheck($this->arr, $index_col); 
        }
        if($edit_prefix && $index_col){
            $this->arr = $this->addedit($this->arr, $edit_prefix, $index_col);
        }

        //这里开始打印整个表格和插件
        echo '<div>';
        //先打印表格的标题
        if($this->caption){
            echo '<h1 style = "padding-bottom:10px; font:normal 200 40px/50px Microsoft YaHei, DengXian, SimSun; color:#454545; text-align:left";>' . $this->caption . '</h1>';
        }

        echo $this->topecho;
        if($this->show_form){
            echo '<form name=haadminform  id=haadminform style="width:auto;" action="' . $this->form_action . '" method="post" accept-charset="utf-8" enctype="multipart/form-data">';
        }

        //打印表格实体
        if(false === $this->techo()){
            return;
        }
        echo $this->bottomecho;
        
        if($this->show_batch_delete){
            if($this->batch_delete_table){
                if(!$this->batch_delete_id_col){
                    $this->batch_delete_id_col = $this->index_col;
                }
                echo '<input type=hidden name=id_col value="' . $this->batch_delete_id_col . '">';
            }
            if($this->batch_delete_table){
                echo '<input type=hidden name=table value="' . $this->batch_delete_table . '">';
            }
            echo '<input style="display:block; float:left" type="submit" name="batch_delete" value="批量删除"></button>';
        }

        //打印分页条
        if($this->show_divpage){
            echo '<div style="margin:10px 0; float:right">';
            ha_divpage_echohtml($this->divpage_uri, $this->divpage_count, $this->divpage_per_page, 400);
            echo '</div>';
        }

        if($this->show_form){
            echo '</form>';
        }
        echo '<div style="clear:both"></div>';
        echo '</div>';
    }

 
    //打印表格实体
    private function techo(){
        $rand = $this->rand;
        echo '<style>' . $this->style . '</style>';
        //如果为空集合则提示表格为空
        if(empty($this->arr)){
            echo '<table style="text-align:center;" class= ' . $rand . ' cellpadding=0 cellspacing=0><tr class=header><td></td></tr><tr class="tbcontent even"><td>空空如也</td></tr></table>';
            return false;    
        } 

        echo '<table class= ' . $rand . ' cellpadding=0 cellspacing=0>';

        $i = 0;
        foreach($this->arr as $rowid => $rowval){
            if($i == 0){
                //echo '<td style="font-weight:600; ">';
                //以下是显示行号的列标签
                //echo '<tr style="font-weight:600; "><td>[had]</td>';
                echo '<tr class=header>';
                if(is_array($rowval)){
                    foreach($rowval as $colid => $colval){
                        //如果colnames中的列设置为了false,不输出
                        if($this->colnames[$colid] === false){
                            continue;
                        }
                        //这里加入代码来支持列名的自定义10.30
                        if($this->colnames[$colid] !== null){
                            $colid = $this->colnames[$colid];
                        }
                        echo '<td>' . $colid . '</td>';
                    }
                }else{
                    echo '<td>values</td>';
                }
                echo '</tr>';
            }

            //显示行号
            //echo '<td>' . $rowid . '</td>';
            if($i % 2){
                echo '<tr class="tbcontent odd">';
            }else{
                echo '<tr class="tbcontent even">';
            }
            if(is_array($rowval))
            {
                foreach($rowval as $colid => $colval){
                    //如果colnames中的列设置为了false,不输出
                    if($this->colnames[$colid] === false){
                        continue;
                    }

                    if(empty($colval)){
                    echo '<td>&nbsp;-&nbsp;</td>';
                    }else{
                        echo '<td>' . $colval . '</td>';
                    }
                }
            }else{
                if(empty($rowval)){
                    echo '<td>&nbsp;-&nbsp;</td>';
                }else{
                    echo '<td>' . $rowval . '</td>';
                }
            }
            echo '</tr>';
            $i++;
        }
        echo '</table>'; 
    }

    // 构造函数
    function __construct($arr, $caption = null, $index_col = null, $show_check = null, 
        $edit_prefix = null, $style = null){            
        $this->arr = $arr;
        $this->caption = $caption;
        $this->index_col = $index_col;
        $this->show_check = $show_check;
        $this->edit_prefix = $edit_prefix;
        if(!$style){
        $rand = $this->rand;
        $style = <<<html
.$rand{width:100%; margin:0 auto; color:#454545; font:normal 13px/20px 'Microsoft YaHei', DengXian, SimSun; text-align:left;}
.$rand a{text-decoration: none; color:#21759b;}
.$rand .header{text-align:center; font-weight:600;background:#D3DCE3;}
.$rand tr.even{background:#E5E5E5}
.$rand tr.odd{background:#D5D5D5}
.$rand tr.tbcontent:hover{background:#CCFFCC;}
.$rand tr td{padding:5px 10px; padding-left:10px;}
.$rand tr td{border-bottom: solid 3px #fff; border-right: solid 3px #fff;}
html;
}
        $this->style = $style;
    }


    var $bottomecho = null;
    function bottomecho($str){
        $this->bottomecho = $str;
    }

    var $topmecho = null;
    function topecho($str){
        $this->topecho = $str;
    }

    var $colnames = null;
    function colnames($colnames){
        $this->colnames = $colnames;
    } 

    var $arr = null;
    var $caption = null;
    function caption($caption){
        $this->caption = $caption;
    }
    var $index_col = null;
    function index_col($index_col){
        $this->index_col = $index_col;
    }
    var $show_check = null;
    function show_check($show_check = true){
        $this->show_check = $show_check;
    }

    var $edit_prefix = null;
    function edit_prefix($edit_prefix){
        $this->edit_prefix = $edit_prefix;
    }

    //此处的rand字符串用于唯一定位我们的table表格
    var $rand = 'c_ax5h';
    var $style = null;
    function style($style){
        $this->style = $style;
    }
    function style_append($astyle){
        $astyle = str_replace('}', '}.' . $this->rand . ' ', $astyle);
        $astyle = '.' . $this->rand . ' ' . $astyle;
        $pos = strrpos($astyle, '.' . $this->rand . ' ');
        $astyle = substr($astyle, 0, $pos);
        $this->style .= $astyle;
    }

    var $show_divpage = null;
    var $divpage_per_page = null;
    var $divpage_uri = null;
    var $divpage_count = null;

    var $batch_delete_table = null;
    function batch_delete_table($batch_delete_table){
        $this->batch_delete_table = $batch_delete_table;
    }
    var $batch_delete_id_col = null;
    function batch_delete_id_col($batch_delete_id_col){
        $this->batch_delete_id_col = $batch_delete_id_col;
    }
    var $show_batch_delete = false;
    function show_batch_delete(){
        $this->show_check();
        $this->show_form();
        $this->show_batch_delete = true;
    }

    var $form_action = null;
    function form_action($form_action){
        $this->form_action = $form_action;
    }
    var $show_form;
    function show_form(){
        $this->show_form = true;
    }

    //分页初始化 这里注意uri是包含page属性的建议使用
    function divpage_init($uri = null, $count, $per_page = DEFAULT_ROW_COUNT){
        if(!$uri){
            $uri = $_SERVER['REQUEST_URI'];
        }
        $this->show_divpage = true;
        $this->divpage_per_page = $per_page;
        $this->divpage_uri = $uri;
        $this->divpage_count = $count;
    }
    
    //添加前置checkbox初始化
    function addcheck($arr, $value_col){
        foreach($arr as &$qline){
            if($qline[$value_col] !== null){
                array_unshift($qline, 
                    '<input type=checkbox name=checked[] value="' . $qline[$value_col] . '">');
            }
        }
        unset($qline);
        return $arr;
    }

    //添加编辑超链接初始化
    function addedit($arr, $edit_prefix, $edit_col){
        foreach($arr as &$qline){
            if($qline[$edit_col] !== null){
                array_push($qline, '<a href="' . $edit_prefix . $qline[$edit_col] . '">编辑</a>');
            }
        }
        return $arr;
    }
}


