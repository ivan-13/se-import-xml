<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    
    // check for language in file name, if not specified, continue
    if(false == $lang = get_lang($xmlFile)) continue;
    
    $reader = new \SimpleXMLReader;
    $reader->open($xmlFile);
    // $reader->setParserProperty(XMLReader::VALIDATE, true);
    // if(!$reader->isValid()) continue;

    // get the parent node id
    $sport_id = [];
    $category_id = [];
    $tourney_id = [];

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
        $stmt = $dbh->prepare($sql);

        $stmt->execute($values) or die(print_r($stmt->errorInfo(), true));

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
        $stmt->execute($values) or die(print_r($stmt->errorInfo(), true));

        if($stmt->rowCount() > 0) $count_categories++; 
    });
    
    // parse and upsert tournament nodes
    $reader->registerCallback("tournament", function($reader) use($dbh, $lang, &$category_id, &$tourney_id, &$count_tournaments) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_tournaments (`id`, `unique_id`, `category_id`, `name_" . $lang . "`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`), `category_id` = VALUES(`category_id`), `unique_id` = VALUES(`unique_id`)";
        $values = array($attributes['id'], $attributes['uniqueid'], $category_id[0], $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values) or die(print_r($stmt->errorInfo(), true));

        $tourney_id = (array) $attributes->id;

        if($stmt->rowCount() > 0) $count_tournaments++; 
    });

    // parse and upsert team nodes
    $reader->registerCallback("team", function($reader) use($dbh, $lang, &$tourney_id, &$count_teams) {
        $element = $reader->expandSimpleXml();
        $attributes = $element->attributes();
        $sql = "INSERT INTO ". $_ENV['TABLE_PREFIX'] ."se_teams (`id`,`name_" . $lang . "`) VALUES (?,?) ON DUPLICATE KEY UPDATE `name_" . $lang . "` = VALUES(`name_" . $lang . "`)";
        $values = array($attributes['superId'], $attributes['name']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values) or die(print_r($stmt->errorInfo(), true));

        if($stmt->rowCount() > 0) $count_teams++; 

        $values = [
            ':tournament_id' => $tourney_id[0], 
            ':team_id' => $attributes->superId
        ];

        $sql = "INSERT IGNORE INTO ". $_ENV['TABLE_PREFIX'] ."se_tournaments_teams (`tournament_id`, `team_id`) VALUES (:tournament_id,:team_id)";
        
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values) or die(print_r($stmt->errorInfo(), true));
    });

    while($reader->read()){
        $reader->parse();
    }

    $reader->close();

    print("Parsed $xmlFile file, upserted $count_sport_ids rows in sportids table.\n");
    print("Parsed $xmlFile file, upserted $count_categories rows in se_categories table.\n");
    print("Parsed $xmlFile file, upserted $count_tournaments rows in se_tournaments table.\n");
    print("Parsed $xmlFile file, upserted $count_teams rows in se_teams table.\n");
}

function get_lang($filename) {
    $arr = explode('_', $filename);
    $lang_code = $arr[1][0].$arr[1][1];
    $languages = explode(',', $_ENV['XML_LANGUAGES']);
    
    if(!in_array($lang_code, $languages)) return false;
    return strtolower($lang_code);
}