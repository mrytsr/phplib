<?php
/**
 * 单例模式类,需要实现单例模式继承这个类就可以了
 * 注意不要覆盖构造函数和静态变量函数等
 */
class HA_Singleton{
    static private $singleton_objs = array();

    // 单例模式不需要构造函数, 两个构造函数声明为final
    final function __construct(){ 
        ;
    }

    final public function HA_Singleton(){
        ;
    }

    final function getObject($i = null){
        if(is_null($i)){
            $i = 0;
        }
        if(isset(HA_Singleton::$singleton_objs[$i])){
            return HA_Singleton::$singleton_objs[$i];
        }
        return HA_Singleton::$singleton_objs[$i] = new HA_Singleton();
    }    
}

