<?php
// Optional learning utilities share #22's display preferences.
function ut(string $message,array $values=[]):string {
    static $english;
    if(uiLanguage()==='en') {
        $english??=require __DIR__.'/lang/learning.en.php';
        $message=$english[str_replace("\r\n","\n",$message)]??$message;
    }
    foreach($values as $key=>$value)$message=str_replace('{'.$key.'}',(string)$value,$message);
    return $message;
}
