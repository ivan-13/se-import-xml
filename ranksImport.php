<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    
    if(false == isRanks($xmlFile)) continue;
    
    $reader = new \SimpleXMLReader;
    $reader->open($xmlFile);
    $reader->setParserProperty(XMLReader::VALIDATE, true);
    if(!$reader->isValid()) continue;
    
    $count_ranks = 0;

    // parse and upsert team ranks
    $reader->registerCallback($_ENV['TABLE_PREFIX'] . "se_teams", function($reader) use($dbh, &$count_ranks) {
        $element = $reader->expandSimpleXml();
        if(!isset($element->DATA_RECORD->id)) exit('no DATA_RECORD specified in XML, script aborted');

        $values = [
            ":id"   => $element->DATA_RECORD->id,
            ":rank" => $element->DATA_RECORD->rank
        ];

        $sql = "UPDATE ". $_ENV['TABLE_PREFIX'] . "se_teams SET rank = :rank WHERE id = :id";
        $stmt = $dbh->prepare($sql);

        $stmt->execute($values);

        if($stmt->rowCount() > 0) $count_ranks++; 
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();

    print("Parsed $xmlFile file, updated $count_ranks rows in se_teams table.\n");
}

function isRanks($filename) {
    if(false === strpos($filename, 'team_ranks')) return false;
    return true;
}