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

    // get the parent node id
    $sport_id = [];
    $category_id = [];

    // counters for affected rows
    $count_sport_ids = 0;
    $count_categories = 0;
    $count_tournaments = 0;
    $count_teams = 0;

    // parse and upsert sport nodes
    $reader->registerCallback("sport", function($reader) use($dbh, $lang, &$sport_id, &$count_sport_ids) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();

        $sport_id = (array) $attributes->id;

        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."sportids (`id`, `name_" . $lang . "`) VALUES (?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`) ";
        $values = array($attributes['id'], $attributes['name']);
        
        // $sql = "
        //         UPDATE ". $_ENV['TABLE_PREFIX'] ."sportids
        //         SET name_" . $lang . " = :name,
        //         WHERE id = :id
        //         ";
        
        // $values = [
        //     ':name' => $attributes['name'],
        //     ':id'   => $attributes['id']
        // ];
        
        $stmt = $dbh->prepare($sql);

        
        if (!$stmt) {
            echo "\nPDO::errorInfo():\n";
            print_r($dbh->errorInfo());
        }
        $stmt->execute($values);

        if($stmt->rowCount() > 0) $count_sport_ids++; 
    });

    // parse and upsert categories nodes
    $reader->registerCallback("category", function($reader) use($dbh, $lang, &$sport_id, &$category_id, &$count_categories) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        
        $category_id = (array) $attributes->id;

        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_categories (`id`, `sport_id`, `name_" . $lang . "`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`), `sport_id` = VALUES(`sport_id`)";
        $values = array($attributes['id'], $sport_id[0], $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);

        if($stmt->rowCount() > 0) $count_categories++; 
    });
    
    // parse and upsert tournament nodes
    $reader->registerCallback("tournament", function($reader) use($dbh, $lang, &$category_id, &$count_tournaments) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_tournaments (`id`, `unique_id`, `category_id`, `name_" . $lang . "`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`), `category_id` = VALUES(`category_id`), `unique_id` = VALUES(`unique_id`)";
        $values = array($attributes['id'], $attributes['uniqueid'], $category_id[0], $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);

        if($stmt->rowCount() > 0) $count_tournaments++; 
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();

    print("Parsed $xmlFile file, upserted $count_sport_ids rows in sportids table.\n");
    print("Parsed $xmlFile file, upserted $count_categories rows in se_categories table.\n");
    print("Parsed $xmlFile file, upserted $count_tournaments rows in se_tournaments table.\n");
}

function get_lang($filename) {
    $arr = explode('_', $filename);
    $lang_code = $arr[1][0].$arr[1][1];
    $languages = explode(',', $_ENV['XML_LANGUAGES']);
    
    if(!in_array($lang_code, $languages)) return false;
    return strtolower($lang_code);
}