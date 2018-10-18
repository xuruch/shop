<?php 

// 加载视图函数
function view($file,$data=[]){
    extract($data);
    $path = str_replace('.', '/', $file) . '.html';
    require(ROOT . 'views/' . $path);   
}

function redirect($url){
    header('Location:'.$url);
    exit;
}