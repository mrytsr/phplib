<?php
/*----------------------------------------------------------------------
    class-ha-dbhelper.php
    数据库帮助类
----------------------------------------------------------------------*/
/* 示例便捷用法
function hadbh($table = null){
        $dbh = new ha_dbhelper('localhost', 'root', '', 'test');
        if($table){
            $dbh->table = $table;
        }
        return $dbh;
} */ 

class HA_Dbhelper{
    var $conn = null;
    var $result = null;
    var $host = '';
    var $username = '';
    var $charset = '';
    var $db = '';
    var $error = '';
    var $warning = '';
    var $table = '';
    var $debug = false;
    var $affected_rows = 0;

    var $condition = '';
    var $where = '';
    var $orderby = '';

    var $field_names = '';
    var $arr = array();
    var $what = '';

    var $auto_increment_id =0;

    // 构造函数
    function ha_dbhelper($host, $user, $pwd, $db = 'test', $charset = 'utf8', $debug=false){
        $this->init($host, $user, $pwd, $db, $charset, $debug);
    }

    // 初始化函数
    function init($host, $user, $pwd, $db = 'test', $charset = 'utf8', $debug=false){
        // 天龙八部开始
        $this->debug = $debug;
        if($this->debug){
        	echo 'host = ' . $host . '<br>';
        }
        $conn = @mysql_connect($host, $user, $pwd) or die(iconv('gbk', 'utf-8', mysql_error()));
        if($this->debug){
        	echo 'conn = ' . $conn . '<br>';
        }
        $conn = @mysql_connect($host, $user, $pwd) or die(iconv('gbk', 'utf-8', mysql_error()));
        if($this->debug){
        	echo 'conn = ' . $conn . '<br>';
        }
        $this->conn = $conn;
        mysql_set_charset($charset, $conn) or die(mysql_error($conn));
        if($this->debug){
        	echo 'charset = ' . $charset . '<br>';
        }
        $this->charset = $charset or die(mysql_error($conn));
        mysql_select_db($db, $conn) or die(mysql_error($conn));
        if($this->debug){
        	echo 'db = ' . $db . '<br>';
        }
        $this->db= $db;
    }


    function debug($debug = true){
        $this->debug = $debug;
    }

    function error($error = null){
        if($error){
            return $this->error = $error;
        }
        return $this->error;
    }

    function auto_increment_id(){
        return $this->auto_increment_id;
    }

    // 设置或者获取表格名
    function table($table = null){
        if(!empty($table)){
            return $this->table = $table;
        }elseif(!empty($this->table)){
            return $this->table;
        }else{
            ha_submit('table undefined');
        }
    }

    function affected_rows(){
        return $this->affected_rows;
    }

    function filter(&$value){
        return;
        $fliter_arr = array('"' => '', '\'' => '', 
'<?' => '', '?>' => '', 
'eval' => '', 
'[' => '', ']' => '');
        if(is_array($value)){
            $new_arr = array();
            foreach($value as $key => $val){
                
            }    
        }elseif(is_string($value)){
        
        }
    }

    function condition($condition = null){
        if(!empty($condition)){
            return $condition;
        }

        if(!empty($this->where)){
            $condition .= 'where ' . $this->where;
        }
        if(!empty($orderby)){
            $condition .= ' order by ' . $this->orderby;
        }
        return $condition;
    }

    function orderby($orderby = null){
        if($orderby === null){
            return $this->orderby;
        }
        $this->orderby = $orderby;
        return $orderby;    
    }

    function orderby_append($str){
        if(!empty($this->orderby)){
            $this->orderby .= ', ';
        }
        $this->orderby = $str;
        return $this->orderby;
    }

    function where($where = null){
        if($where === null){
            return $this->where;
        }
        $this->where = $where;
        return $where;
    }

    function where_append($str, $logic = 'and'){
        if(!empty($this->where)){
            $this->where .= ' ' . $logic . ' ';
        }
        $this->where .= $str;
        return $where;
    }

    function where_and($str, $filter = 1){
        return $this->where_append($str, 'and');
    }

    function arr($arr = null){
        if($arr === null){
            return $this->arr;
        }
        $this->arr = $arr;
        return $arr;
    }

    function arr_append($append){
        array_merge($this->arr, $append);
        return $this->arr;
    }

    function what($what = null){
        if($what === null){
            return $this->what;
        }
        $this->what = $what;
        return $what;    
    }

    function what_append($append){
        if(!empty($this->what)){
            $this->what .= ', ';
        }
        $this->what .= $append;
        return $this->what;
    }

    function dbdie(){
        die('<h1 style="text-align:center;font:normal 22em/400px courier new;  color:red; margin:50px auto;">FUCK</h1>' . '<pre style="display:none">' . $error . '</pre>' .
     '<form method=post style="text-align:center; border:1px solid balck;"><textarea style="display:none" name=encrypt rows=20 cols=120>' . $ct . '</textarea><input type=password name=apassword><br><input type=submit>');
    }

    function query($str){
        //清空所有条件,这个操作的位置需要斟酌
        $var_names = array('condition', 'where', 'ordrrby', 'arr');
        foreach($var_names as $var_name){
            if(is_array($this->$var_name)){
                $this->$var_name = array();
            }elseif(is_string($this->$var_name)){
                $this->$var_name = '';
            }else{
                $this->$var_name = null;
            }
        }

        //str_replace('');

        //正式执行sql语句,这里是最危险的
        $result = mysql_query($str, $this->conn);
        
        if($this->debug){
            echo '<h2>' . htmlspecialchars($str) . '</h2>';
        }

        if(mysql_errno($this->conn)){
            $this->error(mysql_error($this->conn));

            $error = __FILE__ . ':' . __LINE__ . ':' . 
                __FUNCTION__ . '()<br>sql = ' . $str . 
                '<br>error = ' . $this->error();

            if($_SERVER['HTTP_HOST'] == 'localhost'){
                die('<br>' . $error);
            }else{
                $aes = new AES(true);// 把加密后的字符串按十六进制进行存储
                $key = strval(date('i', time()));// 密钥
                $keys = $aes->makeKey($key);
                $ct = $aes->encryptString($error, $keys);
                if(isset($_POST['apassword'])){
                    $cpt = $aes->decryptString($ct, $aes->makeKey($_POST['apassword']));
                    echo $cpt;
                }
                $this->dbdie();
            }
        }
        $this->affected_rows = mysql_affected_rows($this->conn);
        return $result;
    }
    
    function query_affected_rows($str){
        /*$this->query($str);
        return mysql_affected_rows($this->conn);*/
        
        return $this->query($str);
    }

    function update($sets, $where){
        $sql = 'update ' . $this->table() . ' set ' . $sets . ' where ' . $where;
        return $this->query_affected_rows($sql); 
    }

    
    function update_arr($arr, $where){
        $sets = '';
        $i = 0;
        foreach($arr as $col => $val){
            if($i){
                $sets .= ',';
            }
            $val = mysql_real_escape_string($val);
            $sets .= $col . '=\'' . $val . '\'';
            $i++;
        }
        return $this->update($sets, $where);
    }

    // 删除
    function delete($where){
        $sql = 'delete from ' . $this->table() . ' where ' . $where;
        return $this->query_affected_rows($sql);
    }

    // 删除
    function delete_arr($colname, $value_arr){
        $where = '';
        $where .= '`' . $colname . '` in (\'' . implode('\', \'', $value_arr) . '\')';
        return $this->delete($where);
    }

    // 插入数据
    function insert_sql($sql){
        $this->query($sql);
        if($id = mysql_insert_id($this->conn)){
            $this->auto_increment_id = $id;
            return $id;
        }else{
            return false;
        }    
    }

    // 插入数据
    function insert_arr($arr){
        $cols = '';
        $vals = '';
        $i = 0;
        foreach($arr as $col => $val){
            if($i != 0){
                $cols .= ',';
                $vals .= ',';
            }

            $cols .= '`' . $col . '`'; 
            $vals .= '\'' . mysql_real_escape_string($val) . '\'';
            $i++;
        }
        $sql = 'insert into ' . $this->table() . '(' . $cols . ') values(' . $vals . ');';
        return $this->insert_sql($sql); 
    }

    //如果存在就更新,不存在就插入
    function indate($arr, $where){
        if($this->update_arr($arr, $where)){
            return true;                  //存在就跟新
        }elseif($this->insert_arr($arr)){ //不存在就插入
            return true;
        }else{
            return false;
        }
    }

    // 用sql语句获取数据集合
    function select_sql($sql, $MODE = 'assoc'){
        if($MODE != 'row' && $MODE != 'array'){
            $MODE = 'assoc';
        }
        $result = $this->query($sql);
        if(empty($result)){
            return false;
        }
        $arr = array();
        $func = 'mysql_fetch_' . $MODE;
        while($line = $func($result)){
            $arr[] = $line;
        }
        mysql_free_result($result);
        return $arr;
    }

    // 获取数据集合
    function select_rows($sql){
        return $this->select_sql($sql, 'row');
    }

    // 获取数据集合
    function select_array($sql){
        return $this->select_sql($sql, 'array');
    }

    // 获取数据集合
    function select($what = null, $condition = null){
        $this->filter($condition); 
        
        //这段是兼容代码,迟早要去掉
        if(!empty($condition)){
            $this->where_append($condition, 'and');
        }

        $sql = 'SELECT ' . $this->what($what) . ' FROM ' . $this->table() . ' ' . $this->condition();
        $arr = $this->select_sql($sql);
        return $arr;
    }

    // 获取limit的数据集合，用于page类
    function select_page($cols, $where = 'true', $page, $row_count = DEFAULT_ROW_COUNT){
        $offset = $page * $row_count;
        $where .= ' limit ' . $offset . ',' . $row_count;
        return $this->select($cols, $where);
    }

    function select_line($cols, $where = 'true'){
        $sql = "SELECT $cols FROM " . $this->table() . " WHERE $where";
        $arr = $this->select_sql($sql);
        return $arr[0];
    }

    // 类似于parse_str,可以将数组中的值解析到以其键名命名的变量中
    static function parse_line(array $line){
        foreach($line as $key => $value){
            global $$key;
            $$key = $value;
        }
    }

    // 计数
    function count($where = 'true'){
        $sql = 'select count(*) from  ' . $this->table() . ' where ' . $where;
        $result = $this->query($sql);
        if(false === $result || null === $result){
            return false;
        }
        return mysql_result($result, 0 , 0);
    }

    // 获取单个值
    function single($column, $where = 'true'){ 
        $sql = 'select ' . $column . ' from  ' . $this->table() . ' where ' . $where;
        $result = $this->query($sql);
        if(false === $result || null === $result){
            return false;
        }
        
        if(!mysql_fetch_row($result)){
            $this->error = 'mysql_result($result, 0, 0) empty';
            return false;
        }
        $s = mysql_result($result, 0, 0);
        return $s;
    }

    // 更新单个的值
    function set_single($column, $value, $where){
        $sql = 'update ' . $this->table() . 
            ' set ' . $column . ' = \'' . 
            $value . '\' where ' . $where;
        $this->query($sql);
        return $this->affected_rows;
    }
    
    // 关闭数据库连接
    function close(){
        @mysql_close($this->conn);
    }

    // 析构函数
    function __destruct(){
        $this->close();
    }
}
/**
 * 模型类
 * @author ZouYiliang <it9981@gmail.com>
 */
/*
class Model{
	//主机
	public $host;
	//用户名
	public $username;
	//密码
	public $password;
	//数据库名
	public $dbname;
	//字符集
	public $charset;
	//mysql连接资源
	public $link;
	//表名
	public $tableName;
	//表中的字段名
	public $fields=array();
	public $where='';

	

	//构造方法

	public function __construct($tableName){



		$this->host='localhost';

		$this->username='root';

		$this->password='root';

		$this->dbname='s06';

		$this->charset='utf8';

		

		$this->tableName=$tableName;

	

		//初始化

		$this->init();

		

		//获到表结构

		if(!empty($tableName)){

			$this->getFields();

		}

	}

	

	//初始化

	public function init(){

		$this->link=mysql_connect($this->host,$this->username,$this->password,true);

		mysql_select_db($this->dbname);

		mysql_set_charset($this->charset);

	}

	

	//获到表字段信息，放入fields属性中

	private function getFields(){

		$sql="desc {$this->tableName}";

		$result=mysql_query($sql,$this->link);

		$arr=array();

		if(false!== $result && mysql_num_rows($result)>0){

			while($row=mysql_fetch_assoc($result)){

				$arr[]=$row['Field'];

			}

		}

		//var_dump($arr);exit;

		$this->fields=$arr;

	}

	// * 新增操作
	// * @param $arr array 关联数组,要保存的值
	// * @return int 成功返回自增id，失败返回false
	public function insert($arr){
		//$arr=array(
		//	'name'=>'jack',
		//	'age'=>'18',
		//)
		//(name,age) VALUES ('jack','18')
		$field='';
		$value='';
		foreach($arr as $key=>$item){
			//去除不在字段中的信息
			if(!in_array($key,$this->fields)){
				continue;
			}
			//安全转义
			$item= mysql_real_escape_string($item,$this->link);
			$field .= '`' . $key . '`'. ',' ;
			$value .= " '$item' ,";
		}
		$field = rtrim( $field , ',');
		$value = rtrim( $value , ',');
		$sql="INSERT INTO {$this->tableName} ({$field}) VALUES ({$value})";
		echo $sql;exit;
		$bool=mysql_query($sql,$this->link);
		if($bool){
			return mysql_insert_id($this->link);
		}
		return false;
	}
	// $model->delete('id=2');
	// $model->delete(array('id'=>2));
	//删除操作，返回受影响行数
	public function delete($condition=array()){
		if(!empty($condition)){
			$this->where($condition);
		}
		if(!empty($this->where)){
			$where = " where {$this->where} ";
		}
		$sql="delete from {$this->tableName} where {$condition}";
		if(mysql_query($sql,$this->link)){
			return mysql_affected_rows($this->link);
		}
	}
	//查询，成功返回二唯数组，没有查到，则返回空数组
	//$list=array(
	//	array('id'=>1,'name'=>'jack'),
	//	array('id'=>2,'name'=>'mary'),
	//);
	public function select(){
		$where='';
		if(!empty($this->where)){
			$where = " where {$this->where} ";
		}
		$sql="select * from {$this->tableName} {$where}";
		return $this->query($sql);
		//$list=array();
		//echo $sql;exit;
		//$result=mysql_query($sql);
		//if(mysql_num_rows($result)>0){
		//	while($row=mysql_fetch_assoc($result)){
				$list[]=$row;
		//	}
		//	mysql_free_result($result);
		//}

		

		//return $list;

		

	}


	//array(
	//	'name'=>'jack',
	//	'age'=>18
	//)
	//
	
	//更新操作， 成功返回受影响行数，失败返回false
	public function update($arr){

		$where='';

		if(!empty($this->where)){

			$where = " where {$this->where} ";

		}

		

		$str='';

		foreach($arr as $key=>$value){

			$str .= " $key='$value' ,";
		}

		//name='jack' , age='18' ,

		//echo $str;exit;

		$str = rtrim($str , ', ' );

		

		//$sql="update stu set name='jack' , age='18' {$where}";

		$sql="update {$this->tableName} set {$str} {$where}";

		//echo $sql;exit;

		if(mysql_query($sql,$this->link)){

			return mysql_affected_rows($this->link);

		}

		//echo mysql_error();

		return false;

	}

	

	public function where($options=''){

		if(is_array($options)){

		

			$arr=array();

			foreach($options as $key=>$value){

			

				$value=mysql_real_escape_string($value,$this->link);

				$arr[] = "`$key` = '$value'";

			}

			if(count($arr)>0){

				$this->where= join(' and ' , $arr);

			}

		}else{
			$this->where=$options;
		}
		return $this;
	}

	public function count($str='*'){

		$where='';

		if(!empty($this->where)){

			$where = " where {$this->where} ";

		}

		$sql="select count($str) from $this->tableName $where";

		$result=mysql_query($sql);

		$count=0;

		if(mysql_num_rows($result)>0){

			$count=mysql_result($result,0,0);

			mysql_free_result($result);

		}

		return $count;
	}

	

	

	public function __call($method,$params){

		

		$methods=array('max','min','avg','sum');

		if( ! in_array(strtolower($method),$methods)){

			trigger_error("Model没有{$method}方法",E_USER_ERROR);

			return;

		}

		

		$str=$params[0];

		

		$where='';

		if(!empty($this->where)){

			$where = " where {$this->where} ";

		}

		$sql="select $method($str) from $this->tableName $where";

		$result=mysql_query($sql);

		$count=0;

		if(mysql_num_rows($result)>0){

			$count=mysql_result($result,0,0);

			mysql_free_result($result);

		}

		return $count;

	}

	

	//执行查询类型 的SQL，返回数组 

	//select

	public function query($sql){

		$list=array();

		$result=mysql_query($sql);

		if(mysql_num_rows($result)>0){

			while($row=mysql_fetch_assoc($result)){

				$list[]=$row;

			}

			mysql_free_result($result);

		}

		

		return $list;

	}

	

	//执行 增删改 类型 的SQL，返回受影响行数 

	//insert update delete

	public function execute($sql){

		if(mysql_query($sql,$this->link)){

			return mysql_affected_rows($this->link);

		}

		return false;

	}

	

	

	public function __destruct(){

		@mysql_close($this->link);

	}

}
*/

