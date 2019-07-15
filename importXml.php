<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    $xml = XMLReader::open($xmlFile);
    $xml->setParserProperty(XMLReader::VALIDATE, true);
    if(!$xml->isValid()) continue;

    // check for language in file name
    if(false == $lang = get_lang($xmlFile)) continue;

    $reader = new \SimpleXMLReader;
    $reader->open($xmlFile);
    
    $reader->registerCallback("tournament", function($reader) use($dbh, $lang) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_tournaments (`id`, `unique_id`, `category_id`, `name_" . $lang . "`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`) ";
        $values = array($attributes['id'], $attributes['uniqueid'], 1, $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();
}

function get_lang($filename) {
    $arr = explode('_', $filename);
    $lang_code = $arr[1][0].$arr[1][1];
    $languages = explode(',', $_ENV['XML_LANGUAGES']);
    
    if(!in_array($lang_code, $languages)) return false;
    return strtolower($lang_code);
}