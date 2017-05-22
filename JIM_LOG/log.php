<?php
$fn  = $_POST['fn'];
$endCard = $_POST['endCard']; 
$player_hand1 = $_POST['player_hand1'];
$player_hand2 = $_POST['player_hand2'];
$player_hand3 = $_POST['player_hand3'];
$player_hand4 = $_POST['player_hand4'];
$Round = $_POST['Round'];
$rounds_number = $_POST['rounds_number'];
//$file = fopen("/opt/lampp/htdocs/passVal/".$fn.".record","w");
//echo fwrite($file,$str);
//fclose($file);
/*
$file = fopen($fn . "test.txt", "w");
echo fwrite($file, "Hello World. Testing!" . $str);
fclose($file);
*/
$output1=implode(",",$endCard);
$output2=implode(",",$player_hand1);
$output3=implode(",",$player_hand2);
$output4=implode(",",$player_hand3);
$output5=implode(",",$player_hand4);
$output6=implode(",",$Round);
$output7=implode(",",$rounds_number);

$file = $fn .'.txt';
$current = file_get_contents($file);
$current .= "-------------------------\n";
$current .= date("Y-m-d H:i:s")."write" . "\n" ;
$current .= "剩餘牌數 : ".$output1."\n";
$current .=  "player_hand1 : ".$output2."\n";
$current .=  "player_hand2 : ".$output3."\n";
$current .=  "player_hand3 : ".$output4."\n";
$current .=  "player_hand4 : ".$output5."\n";
$current .=  "Round : ".$output6."\n";
$current .=  "rounds_number : ".$output7."\n";
$current .= "-------------------------\n";
file_put_contents($file, $current);
