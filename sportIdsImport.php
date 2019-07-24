<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    
    if(false == isSportIds($xmlFile)) continue;
    
    $reader = new \SimpleXMLReader;
    $reader->open($xmlFile);
    $reader->setParserProperty(XMLReader::VALIDATE, true);
    if(!$reader->isValid()) continue;
    
    $count = 0;

    // parse and upsert team ranks
    $reader->registerCallback($_ENV['TABLE_PREFIX'] . "sportids", function($reader) use($dbh, &$count) {
        $element = $reader->expandSimpleXml();
        if(!isset($element->DATA_RECORD->id)) exit('no DATA_RECORD specified in XML, script aborted');

        $values = [
            ":id"   => $element->DATA_RECORD->id,
            ":name_ru" => $element->DATA_RECORD->name_ru
        ];

        print_r($element);

        $sql = "UPDATE ". $_ENV['TABLE_PREFIX'] . "sportids SET name_ru = :name_ru WHERE id = :id";
        $stmt = $dbh->prepare($sql);

        $stmt->execute($values);

        if($stmt->rowCount() > 0) $count++; 
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();

    print("Parsed $xmlFile file, updated $count rows in se_teams table.\n");
}

function isSportIds($filename) {
    if(false === strpos($filename, 'sportids')) return false;
    return true;
}