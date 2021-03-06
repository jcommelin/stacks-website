<?php

# TODO add .htaccess to remove this from indexing!

require_once("../tags.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// read configuration file
$config = parse_ini_file("../../config.ini");

// initialize the global database object
try {
  $database = new PDO("sqlite:../../" . $config["database"]);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

function startsWith($haystack, $needle) {
    return !strncmp($haystack, $needle, strlen($needle));
}

function removeProofs($content) {
  $lines = explode("\n", $content);
  $output = "";
  $inProof = false;

  foreach ($lines as $i => $line) {
    if (startsWith($line, "\begin{proof}"))
      $inProof = true;

    if (!$inProof)
      $output .= $line . "\n";

    if (startsWith($line, "\end{proof}"))
      $inProof = false;
  }

  return $output;
}


if (!isset($_GET["statement"]))
  $type = "statement";
else
  $type = $_GET["statement"];

if (!isValidTag($_GET["tag"])) {
  print "This tag is not valid.";
  exit;
}

if (!tagExists($_GET["tag"])) {
  print "This tag does not exist.";
  exit;
}

$tag = getTag($_GET["tag"]);

// if a tag is an equation we remove its number
function isNotLineWithLabel($line) {
  return strncmp($line, "\label", 6);
}
if ($tag["type"] == "equation") {
  $tag["value"] = implode("\n", array_filter(explode("\n", $tag["value"]), "isNotLineWithLabel"));
}

if (isset($_GET["format"]) and $_GET["format"]) {
  switch ($type) {
    case "full":
      print $tag["value"];
      break;
    case "statement":
      print removeProofs($tag["value"]);
      break;
  }
}
else {
  switch ($type) {
    case "full":
      print convertLaTeX($_GET["tag"], $tag["file"], $tag["value"]);
      break;
    case "statement":
      print convertLaTeX($_GET["tag"], $tag["file"], removeProofs($tag["value"]));
      break;
  }
}
?>
