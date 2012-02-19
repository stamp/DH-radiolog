<HTML><BODY>
<FORM><INPUT TYPE="text" NAME="cmd"></FORM>
<PRE></PRE>
<?php
$data = '';

if($_GET['cmd'])
    $data = 'CMD: '.$_GET['cmd'];

$data .= '<pre>'.var_export($_SERVER,true);
$data .= var_export($_SESSION,true).'</pre>';
//$data .= var_export(get_defined_vars());

mail('root', 'alert', $data, 'From: dh@stamp.se');
die();


?>
</BODY></HTML>
