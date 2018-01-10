<?



$U='';        #Anmeldename/Leg.-ID
$P='';        #PIN(5)

  /*
                   
            /////           
           $$$$$$$          
          $$$$$$$$$         
          /$$$$$$$/         
           /$$$$$/          
                            
                            
   $$$$$$$$$$$$$$$$$$$$$$$  
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  $$$$$///////////////////                    
  $$$$$/                    
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  $$$$$$$$$$$$$$$$$$/$$$$$$ 
                     /$$$$$ 
  $$$$$$$$$$$$$$$$$$/$$$$$$ 
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  $$$$$$$$$$$$$$$$$$$$$$$$$ 
  ////////////////////////   
  
  hzspk.php - v0.3 (Jan 10, 2018)
   

  */
                                      


ini_set('user_agent','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
if(!$U||strlen($U)>16)die('E: start: invalid \'Anmeldename\'');if(strlen($P)!=5)die('E: start: invalid pin');
$_=@file_get_contents(($Q='https://www.harzsparkasse.de/de/home/onlinebanking/finanzstatus.html'));#print_r($http_response_header);


# init: get cookies (C,I,J), form-action-url, POST vars: u/p/x/y
$__=implode($http_response_header);
preg_match("#JSESSIONID\=(.*?);#",$__,$J);$J=@$J[0];
preg_match("#IF6CONTEXT\=(.*?);#",$__,$I);$I=@$I[0];
preg_match("#IFCLONE\=(.*?);#",$__,$C);$C=trim(@$C[0],';');
if(strlen($J)<20||strlen($I)<20||strlen($C)<10)die('E: init: cookie issue');

preg_match("#\?sp:ac.*?\"#msi",$_,$f);$f=substr(@$f[0],0,-1);
preg_match("#Anmeldena.*?<in.*?name=\"(.*?)\"#msi",$_,$u);$u=@$u[1];
preg_match("#type=\"password\" name=\"(.*?)\"#msi",$_,$p);$p=@$p[1];
preg_match("#Anmeldena.*?type=\"submit\".*?name=\"(.*?)\".*?value=\"(.*?)\"#msi",$_,$x);$x=@$x[1].'='.urlencode(@$x[2]);
preg_match("#Anmeldena.*?type=\"hidden\".*?name=\"(.*?)\".*?value=\"(.*?)\"#msi",$_,$y);$y=@$y[1].'='.urlencode(@$y[2]);
if(strlen($f)<10||strlen($u)<10||strlen($p)<10||strlen($x)<10||strlen($y)<10)die('E: init: html parsing issue');

$c=array('http'=>array(
'max_redirects'=>0,
'method'=>'POST',
'header'=>"Cookie: $J $I $C\r\n",
'content'=>"$u=".urlencode($U)."&$p=".urlencode($P)."&$x&$y"
));


#login
@file_get_contents($Q.$f,!1,stream_context_create($c));#print_r($http_response_header);
if(!preg_match("#IF6STCONTEXT=(.*?);#",implode($http_response_header),$_))die('E: login: failed (check id/pin)');
@file_get_contents($Q,!1,stream_context_create(array('http'=>array('max_redirects'=>0,'header'=>"Cookie: $J $I {$_[0]} $C\r\n"))));
preg_match("#JSESSIONID\=(.*?);#",implode($http_response_header),$J);$J=@$J[0];if(!$J)die('E: login: cookie issue (JSESSIONID)');
$_=@file_get_contents($Q,!1,stream_context_create(array('http'=>array('max_redirects'=>0,'header'=>"Cookie: $J $I {$_[0]} $C\r\n"))));
if(!$_)die('E: login: no content');


#logout (get new J cookie, QS, form-vars)
preg_match("#\?sp:ac.*?\"#msi",$_,$f);$f=substr(@$f[0],0,-1);
preg_match("#=\"logout\".*?\"submit\".*?value=\"(.*?)\".*?name=\"(.*?)\"#msi",$_,$x);$x=@$x[2].'='.urlencode(@$x[1]);
preg_match("#=\"logout\".*?\"hidden\".*?name=\"(.*?)\".*?value=\"(.*?)\"#msi",$_,$y);$y=@$y[1].'='.urlencode(@$y[2]);
preg_match("#=\"hidden\".*?\"hidden\".*?name=\"(.*?)\".*?value=\"(.*?)\"#msi",$_,$z);$z=@$z[1].'='.urlencode(@$z[2]);
if(!$x||!$y||!$z||!$f)die('E: logout: html parsing issue');
@file_get_contents($Q.$f,!1,stream_context_create(array('http'=>array('max_redirects'=>0,'method'=>'POST','header'=>"Cookie: $J $I $C\r\n",'content'=>"$x&$y&$z"))));


preg_match("#>([\-.,0-9]+)&nbsp;E#msi",$_,$_);die((@$_[1])?$_[1]:'E: hzspk: parsing issue (no account balance)');

?>
