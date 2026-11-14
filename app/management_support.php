<?php
/** Read a decimal ID without coercing arrays or malformed strings. */
function postId(string $name,bool $optional=false,bool $allowZero=false):int {
    if(!array_key_exists($name,$_POST)){
        if($optional)return 0;
        reject('Thiếu ID hợp lệ: '.$name.'.');
    }
    $value=$_POST[$name];
    if((!is_string($value)&&!is_int($value))||preg_match('/\A(?:0|[1-9][0-9]*)\z/',(string)$value)!==1){
        reject('ID không hợp lệ: '.$name.'.');
    }
    $id=filter_var($value,FILTER_VALIDATE_INT,['options'=>['min_range'=>$allowZero?0:1]]);
    if($id===false)reject('ID không hợp lệ: '.$name.'.');
    return $id;
}

/** Bounded pagination for management lists. Only validated integers enter SQL. */
function managementPage(int $total):array {
    $size=filter_var($_GET['per_page']??12,FILTER_VALIDATE_INT);
    $size=$size===false?12:max(1,min(50,$size));
    $pages=max(1,(int)ceil($total/$size));
    $page=filter_var($_GET['p']??1,FILTER_VALIDATE_INT);
    $page=$page===false?1:max(1,min($pages,$page));
    $offset=($page-1)*$size;
    return ['total'=>$total,'page'=>$page,'pages'=>$pages,'per_page'=>$size,'sql'=>' LIMIT '.$size.' OFFSET '.$offset];
}
