<?php
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
