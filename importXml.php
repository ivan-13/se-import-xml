<?php
include "bootstrap.php";

foreach (glob($_ENV['XMLDIR_PATH'] . "/*.xml") as $xmlFile) {
    $xml = XMLReader::open($xmlFile);
    $xml->setParserProperty(XMLReader::VALIDATE, true);
    if(!$xml->isValid()) continue;
}