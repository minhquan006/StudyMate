<?php
require dirname(__DIR__,2).'/app/bootstrap.php';
$checks=0;
function verify($condition,$name):void {global $checks;if(!$condition)throw new RuntimeException($name);$checks++;}
$_COOKIE=[];$_GET=[];$_SERVER['REQUEST_METHOD']='GET';initializeDisplayPreferences();
verify(uiLanguage()==='vi'&&uiTheme()==='system','default display preferences');
$_GET=['ui_language'=>'en','ui_theme'=>'dark'];initializeDisplayPreferences();
verify(uiLanguage()==='en'&&uiTheme()==='dark','allowed choices');
verify(t('Học để hiểu.')==='Learn to understand.','glass translation');
verify(countedLabel('tài khoản',1)==='account'&&countedLabel('tài khoản',2)==='accounts','singular and plural units');
$keys=json_decode(file_get_contents(__DIR__.'/fixtures/display_message_keys.json'),true);
foreach($keys as $key)if(preg_match('/[À-ỹ]/u',$key))verify(t($key)!==$key,'missing UI translation: '.$key);
$_COOKIE=['studymate_language'=>'en','studymate_theme'=>'light'];$_GET=['ui_language'=>'fr','ui_theme'=>'unknown'];initializeDisplayPreferences();
verify(uiLanguage()==='en'&&uiTheme()==='light','invalid query does not replace saved choice');
$_COOKIE=['studymate_language'=>['en'],'studymate_theme'=>['dark']];$_GET=['ui_language'=>['en'],'ui_theme'=>['dark']];initializeDisplayPreferences();
verify(uiLanguage()==='vi'&&uiTheme()==='system','array preferences rejected');
$_COOKIE=[];$_GET=['ui_language'=>'en','ui_theme'=>'dark'];$_SERVER['REQUEST_METHOD']='POST';initializeDisplayPreferences();
verify(uiLanguage()==='vi'&&uiTheme()==='system','display query ignored during data POST');
$_GET=['tab'=>'content','lesson'=>'13','q'=>'<img src=x onerror=alert(1)>','subject'=>'2','ui_theme'=>'dark'];
$fields=preferenceFields('edit',3);
verify(str_contains($fields,'name="subject" value="2"')&&str_contains($fields,'name="lesson" value="13"'),'route context retained');
verify(str_contains($fields,'&lt;img')&&!str_contains($fields,'<img'),'query text escaped');
verify(!str_contains($fields,'ui_theme'),'old preference query not carried forward');
echo "PASS: $checks display preference and translation checks\n";
