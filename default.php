<?php include("head.php");?>
<h1>DH security</h1>

<?php 

$link = mysql_connect("localhost", "dh", "") or die("Could not connect : " . mysql_error());
mysql_select_db("dh") or die("Couald not select database");

error_reporting(0);

if (((!isset($_GET['st']))&&(isset($_GET['id'])))&&(!isset($_GET['bt']))) {  ?>
<form action="default.php" method="get">
<h3>Låna ut radio/Felanmäl</h3>
<input type=hidden name="id" value="<?php echo $_GET['id']; ?>"><input type=hidden name="id" value="<?php echo $_GET['id']; ?>"><input type=hidden name="status" value="<?php echo $_GET['status']; ?>">
<input type=hidden name=st value=1>
<?php if(($_GET['status']==1)||($_GET['status']==3)) { ?>
Namn:<br>
<input type="text" name="namn" value=""><br><br>

Team:<br>
<select name="team">
<?php
$query = "select * from teams ORDER BY namn";
$result = mysql_query($query) or die("query failed : " . mysql_error());
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "<option value='{$line['id']}'>{$line['namn']}</option>";
} 
?>

</select><?php } 
if(($_GET['status']==1)) { ?>

<br><br>Headset<br>
Nej<input type="radio" name="headset" value="&nbsp;"><br>
Monofon<input type="radio" name="headset" value="Monofon" checked><br>
Öronsnäcka<input type="radio" name="headset" value="Snäcka"><br>
<br>

<?
}
?>


Notering<br>
<textarea name="text"><?php echo $_GET['text']; ?></textarea>
<br><br>
<input type="submit" value="Skicka">
<?
} else { 
?>
Sök på s/n, typ eller frekvens: <font color="#FF0000"><?php if(isset($_GET['filter'])) {if($_GET['filter']>""){echo "&nbsp;<b>Aktiv</b>";}}  ?></font><br>
<form action="default.php" method="get">
<input type="text" name="filter" value="<?php echo $_GET['filter']; ?>">
<input type="submit" value="sök">&nbsp;<a href="default.php">Rensa</a><br></form>
<table border=0 cellpadding=0 cellspacing=0 width=1000>
<tr>
<td class=first>&nbsp;</td>
<td class=rubrik width=1>&nbsp;</td>
<td class=rubrik><b><a href="default.php?sort=sn">serienummer</a></b></td>
<td class=rubrik><b><a href="default.php?sort=typ">typ</a></b></td>
<td class=rubrik><b><a href="default.php?sort=frekvens">team</a></b></td>
<td class=rubrik><b><a href="default.php?sort=status">status</a></b></td>
<td class=rubrik><b><a href="default.php?sort=headset">headset</a></b></td>
<td class=rubrik><b>tidkvar</b></td>
<td class=rubrik><b>&nbsp;</b></td>
</tr>
<?php




    if (isset($_GET['id'])) {
        if ($_GET['status']==2) {
            $tmp = 2;
            $text = "Sattes på laddning<br><i>".nl2br($_GET['text'])."</i>";
        } else if ($_GET['status']==1) {
            $tmp = 8;
            if(!isset($_GET['bt'])) {
            $query = "select * from teams WHERE id={$_GET['team']}";
            $result = mysql_query($query) or die("query failed : " . mysql_error());
            $line = mysql_fetch_array($result, MYSQL_ASSOC);
            
            $text = "Lämmnades ut till {$_GET['namn']} i {$line['namn']}<br><i>".nl2br($_GET['text'])."</i>";
            $team = $line['namn'];
            } else {
                $text = "Bytt batteri";
            }
        } else if ($_GET['status']==3) {
            $query = "select * from teams WHERE id={$_GET['team']}";
            $result = mysql_query($query) or die("query failed : " . mysql_error());
            $line = mysql_fetch_array($result, MYSQL_ASSOC);
            $tmp = 0;
            $text = "Inlämnades sönder av {$_GET['namn']} i {$line['namn']}<br><i>".nl2br($_GET['text'])."</i>";
        } else {
            $tmp = 0;
            $text = "{$_GET['text']}";
        }
        if(!isset($_GET['bt'])) {
            $query = "UPDATE radio SET status={$_GET['status']},headset='{$_GET['headset']}',year=".date("Y").",month=".date("m").",day=".date("j").",hour=".(date("G")+$tmp).",minute=".date("i").",sec=".date("s").",frekvens='".$team."' WHERE id={$_GET['id']}";
       } else {
            $query = "UPDATE radio SET status={$_GET['status']},year=".date("Y").",month=".date("m").",day=".date("j").",hour=".(date("G")+$tmp).",minute=".date("i").",sec=".date("s")." WHERE id={$_GET['id']}";
        }
        $result = mysql_query($query) or die("query failed : " . mysql_error());
        
        $query = "INSERT INTO `logg` (`radio`, `time`, `text`,`team`,`status`,`namn`) VALUES ('{$_GET['id']}', NOW(), '$text','{$_GET['team']}','{$_GET['status']}','{$_GET['namn']}')";
        $result = mysql_query($query) or die("query failed : " . mysql_error());
    } else { ?>
    <script language="javascript">
    setTimeout("location.reload(false);",(60*1000));
    </script>
 <?php }

    if (!isset($_GET['filter'])) {
        if (isset($_GET['sort'])) {
            $query = "select * from radio ORDER BY ".$_GET['sort'];
        } else {
            $query = "select * from radio ORDER BY sn";
        }
    } else {
        $query = "select * from radio where sn LIKE '%{$_GET['filter']}%' OR typ LIKE '%{$_GET['filter']}%' OR frekvens LIKE '%{$_GET['filter']}%'";
    }
    $result = mysql_query($query) or die("query failed : " . mysql_error());
    $antal = mysql_num_rows($result);
     while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "\t<tr><td class=first>&nbsp;</td>\n";
            echo "\t\t<td class=tbl>" . retImg($line['status']) . "</td>\n";
            echo "\t\t<td class=tbl><a href='?filter={$line['sn']}'>{$line['sn']}</a></td>\n";
            echo "\t\t<td class=tbl>{$line['typ']}</td>\n";
            echo "\t\t<td class=tbl>{$line['frekvens']}&nbsp;</td>\n";
            echo "\t\t<td class=tbl>" . status($line['status']) . "</td>\n";
            echo "\t\t<td class=tbl>{$line['headset']}&nbsp;</td>\n";
            echo "\t\t<td class=tbl>" . retTime($line['year'],$line['month'],$line['day'],$line['hour'],$line['minute'],$line['sec'],$line['status']) . "</td>\n";
            echo "\t\t<td class=tbl>";
                if ($line['status']==2) {$tmp=" ";} else {$tmp=" disabled";}
                echo "<input type=button value='Ledig'$tmp onClick=\"location.href='?id={$line['id']}&status=0&text=Blev ledig&st=1'\">";
                if (!$line['status']==0) {$tmp1=" disabled";} else {$tmp1=" ";}
                echo "<input type=button value='Ladda'$tmp1 onClick=\"location.href='?id={$line['id']}&status=2'\">";
                if ($line['status']==1||$line['status']==3) {$tmp2=" disabled";} else {$tmp2=" ";}
                echo "<input type=button value='Låna ut'$tmp2 onClick=\"location.href='?id={$line['id']}&status=1'\">";
                echo "<input type=button value='Batteribyte' onClick=\"location.href='?id={$line['id']}&status=1&text=Batteribyte&st=1&bt=1'\">";
                if ($line['status']==1) {$tmp3=" ";} else {$tmp3=" disabled";}
                echo "<input type=button value='Tillbaka'$tmp3 onClick=\"location.href='?id={$line['id']}&status=0&text=Tillbakalämnad'\">";
                if ($line['status']==3) {
                    echo "<input type=button value='Lagad' onClick=\"location.href='?id={$line['id']}&status=0&text=Lagad&st=1'\">";
                } else {
                    echo "<input type=button value='Felanmäl' onClick=\"location.href='?id={$line['id']}&status=3'\">";
                }
            echo "</td>\n";
        echo "\t</tr>\n";
        $id = $line['id'];
    }
    ?>
    </table>

    <?php

    if ($antal==1) { ?>
<h2>Logg</h2>
<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td class=first>&nbsp;</td>
<td class=rubrik width=1>&nbsp;</td>
<td class=rubrik><b>Tid</b></td>
<td class=rubrik><b>Händelse</b></td>
<td class=rubrik><b>status</b></td>
</tr>
 <? 
 
    $query = "select * from logg WHERE radio=".$id;
    $result = mysql_query($query) or die("query failed : " . mysql_error());
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "\t<tr><td class=first>&nbsp;</td>\n";
        echo "\t\t<td class=tbl>" . retImg($line['status']) . "</td>\n";
        echo "\t\t<td class=tbl nowrap>".tid($line['time'])."</td>\n";
        echo "\t\t<td class=tbl nowrap>{$line['text']}</td>\n";
        echo "\t\t<td class=tbl>" . status($line['status']) . "</td>\n";
    }   
 
 
    }
}
    
    function retTime($ye,$mo,$da,$ho,$mi,$se,$st) {
    $events_time = mktime($ho,$mi,$se,$mo,$da,$ye);
    $time_left = $events_time - time();
    $time_left1 = (floor($time_left/86400))."d ".floor(($time_left-(floor($time_left/86400)*86400))/3600)."h ".floor(($time_left-(floor($time_left/3600)*3600))/60)."m ".floor($time_left-(floor($time_left/60))*60)."s";
        if ($st==1||$st==2) {
            if ($time_left>0) {
                return "<font color='#0000FF'>$time_left1</font>";
            } else {
                return "<font color='#FF0000'>Tiden e ute!</font>";
            }
        } else {return "&nbsp;";}
    }

    function tid($tmp) {
        $tmp = explode("-",wordwrap($tmp,2,"-",1));
        return $tmp[0].$tmp[1].'-'.$tmp[2].'-'.$tmp[3].' '.$tmp[4].':'.$tmp[5].':'.$tmp[6];
    }

    function status($temp) {
    if ($temp==1) {
        return "Utlånad";
    }else if ($temp==0) {
        return "Ledig";
    }else if ($temp==2) {
        return "Laddar";
    }else if ($temp==3) {
        return "Sönder";
    }
        
    }

    function retImg($temp) {
    if ($temp==1) {
            return "<img border=0 src='images/GreySquare.png'>";
        }else if ($temp==0) {
            return "<img border=0 src='images/GreenSquare.png'>";
        }else if ($temp==2) {
            return "<img border=0 src='images/YellowSquare.png'>";
        }else if ($temp==3) {
            return "<img border=0 src='images/RedSquare.png'>";
        }
    }                

?> 
</body>
</html>
