<style>body{ background-color: #000; color: #FFF; }</style>
<?php

function retrieve_remote_file_size($url){
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return $size;
}

if(!isset($_GET['k'])){
    $k = 0;
}else{
    $k = intval($_GET['k']);
}
$lista = file_get_contents("lista.json");
$lista_json = json_decode($lista, true);

set_time_limit(0);
//error_reporting(0);
if(!isset($_GET['link'])){
    if(!isset($lista_json[$k])){
        die("FINITO");
    }
    $url = $lista_json[$k];
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
$code = substr($file_name, strpos($file_name, ".")+1);
$file_name = substr($file_name, 0, strpos($file_name, "."));
$folder_name = $file_name;
$file_name = $file_name." ".$ep.".mp4";

if(!file_exists("anime")){
    mkdir("anime");
}
if(!file_exists("anime/".$folder_name)){
    mkdir("anime/".$folder_name);
    $img = "https://img.animeworld.tv/locandine/".$code.".jpg";
    file_put_contents("anime/".$folder_name."/".$folder_name.".jpg", file_get_contents($img));
}
$folder_name = "anime/".$folder_name;
$dir = $folder_name."/".$file_name;

$fp = fopen ($dir, 'w+');
$ch = curl_init($link_download);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);
fclose($fp);

$size = floatval(retrieve_remote_file_size($link_download));
$size_file = floatval(filesize($dir));
if($size != $size_file){
    $link_new = "http://localhost".$_SERVER['REQUEST_URI'];
    echo "<script>location.href = '$link_new';</script>";
    exit;
}


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
    $link_new = "http://localhost/animedownloader/?link=".str_replace("https://www.animeworld.tv", "", $l[$nextIndex]['href'])."&ep=".$next."&k=".$k;
    echo "<script>location.href = '$link_new';</script>";
    exit;
}
$k++;
$link_new = "http://localhost/animedownloader/?k=".$k;
echo "<script>location.href = '$link_new';</script>";
exit;