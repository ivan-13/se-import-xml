<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    $xml = XMLReader::open($xmlFile);
    $xml->setParserProperty(XMLReader::VALIDATE, true);
    if(!$xml->isValid()) continue;

    $reader = new \SimpleXMLReader;
    $reader->open($xmlFile);
    
    $reader->registerCallback("tournament", function($reader) use($dbh){
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_tournaments (`id`, `unique_id`, `category_id`, `name_de`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `name_de` = VALUES(`name_de`) ";
        $values = array($attributes['id'], $attributes['uniqueid'], 1, $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();
}