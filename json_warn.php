<?php
require_once('config_json.php');
date_default_timezone_set("Europe/Berlin");

function entitiesReplace($string){
    $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´");
    $replace = array("AE", "OE", "UE", "ae", "oe", "ue", "ss", "");
    return str_replace($search, $replace, $string);
}

array_map('unlink', glob($path . "/warnings/warn_*"));
echo "\nAlte Dateien wurden geloescht.\n\n";

foreach ($gemkey as $key=>$region) {
        $stream = file_get_contents("http://www.wetterdienst.de/warnwetter/json_parser.php?region[]=$key");
        $json = substr($stream, 24, -2);
        $data = json_decode($json,true);

        if (!empty($data["warnings"])) {
            echo "\033[01;31m Verarbeite gefundene Warnungen fuer " . $region . "...\033[0m\n";

            foreach ($data["warnings"][$key] as $eventNumber=>$warnevent) {
                $filename = $path . "/warnings/warn_" . strtolower($region) . "_" . $eventNumber;
                $eventEndTime = substr($warnevent["end"],0,-3);

                if ($eventEndTime >= time()) {
                    $content = ":BLN1WX" . strtoupper($region) . ":" . entitiesReplace(str_replace("Amtliche", "DWD",$warnevent["headline"])) .
                    " - " . strtoupper($warnevent["regionName"]) . " bis " . date("d.m. H:i", $eventEndTime) . "\n";
                    echo $content;
                    $file = fopen($filename,"w");
                    fwrite($file,"$content");
                    fclose($file);
                    chmod($filename, 0644);
                }
            }
            echo "\n";
        } else {
            echo "\033[01;31m Es liegen keine Warnungen fuer " . $region . " vor.\033[0m\n\n";
        }
}
?>