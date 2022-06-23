<style>body{ background-color: #000; color: #FFF; }</style>
<?php
set_time_limit(0);
//error_reporting(0);
if(!isset($_GET['link'])){
    $url = "/play/the-greatest-demon-lord-is-reborn-as-a-typical-nobody.CnMSK/JRF2zf";
    $ep = 1;
}else{
    $url = $_GET['link'];
    $ep = $_GET['ep'];
}
$html = file_get_contents("https://www.animeworld.tv".$url);
$dom = new DOMDocument;
@$dom->loadHTML($html);



$link = $dom->getElementById('alternativeDownloadLink');
$link_download = $link->getAttribute('href');
$file_name = explode("/", $url);
unset($file_name[count($file_name)-1]);
$file_name = end($file_name);
$file_name = substr($file_name, 0, strpos($file_name, "."));
$file_name = $file_name." ".$ep.".mp4";


$fp = fopen ($file_name, 'w+');
$ch = curl_init($link_download);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);
fclose($fp);



$l = [];
$listas = $dom->getElementsByTagName('div');
foreach ($listas as $lista){
    if(in_array("server", explode(" ", $lista->getAttribute('class'))) &&
    in_array("active", explode(" ", $lista->getAttribute('class')))){
        $episodes = $lista->getElementsByTagName('li');
        foreach ($episodes as $li){
            if($li->getAttribute('class') != "episode") continue;
            $href = $li->firstChild->getAttribute('href');
            $episode = $li->firstChild->getAttribute('data-episode-num');
            if(!in_array($href, $l)){
                $l[] = [
                    "episode" => $episode,
                    "href" => $href
                ];
            }
        }
    }
}

$currentIndex = array_search($url, array_column($l, "href"));
$next = intval($l[$currentIndex]['episode'])+1;
$nextIndex = array_search($next, array_column($l, "episode"));

if($nextIndex > 0){
    $link_new = "http://localhost/animedownloader/?link=".str_replace("https://www.animeworld.tv", "", $l[$nextIndex]['href'])."&ep=".$next;
    echo "<script>location.href = '$link_new';</script>";
    exit;
}