<?php

class DescriptionFetcher {

    public static function getDescription($base_uri, $collection_alias){
        $url   = sprintf("%s%s", $base_uri, $collection_alias);
        $html  = file_get_contents($url);
        $file  = strtolower($collection_alias);

        $xml   = DOMDocument::loadHTML($html);
        $out   = new DOMDocument();
        $div   = $out->createElement('div');

        $xpath = new DOMXpath($xml);
        $item  = $xpath->query('//div[@id="lp_description_container"]');
        $descr = $out->importNode($item->item(0), true);
        $div->appendChild($descr);
        $out->appendChild($div);
        $text = "";
        $paras = $out->getElementsByTagName('p');
        foreach($paras as $para){
            
            $chunk =  str_replace(array("\t", "\n"), '', $para->nodeValue);
            //            $chunk =  preg_replace("/\n/", '', $chunk);
            $placeholder = "Describe your collection for visitors to your site.";
            if(strpos($chunk, $placeholder) === false){
                $text .= sprintf("<p>%s</p>",$chunk);
            }
        }
        return strlen($text) > 0 ? $text : "No description";
    }
}

?>