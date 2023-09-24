<?php
function getValue($directory)
{
    $value = file_get_contents($directory . '/value.txt');
    $measurement = file_get_contents($directory . '/measurement.txt');

    return $value . $measurement;
}

function getImage($directory)
{
    $value = file_get_contents($directory . '/value.txt', FILE_IGNORE_NEW_LINES);

    if (strcmp($value, 'On') == 0 || strcmp($value, 'Open') == 0 || strcmp($value, 'Yes') == 0)
        return $directory . '/images/2';

    if (strcmp($value, 'Off') == 0 || strcmp($value, 'Closed') == 0 || strcmp($value, 'No') == 0)
        return $directory . '/images/1';


    $range = file($directory . '/range.txt', FILE_IGNORE_NEW_LINES);
    $images = new FilesystemIterator($directory . '/images', FilesystemIterator::SKIP_DOTS);
    $imgs_num = iterator_count($images);

    for ($i = 1; $i <= $imgs_num; $i++) {
        if ((($range[1] - $range[0]) / $imgs_num) * $i + $range[0] >= $value)
            return $directory . '/images/' . $i;
    }
}


function getJSON(){

$request_body = file_get_contents('php://input');

$data = json_decode($request_body, true);

if (json_last_error() != JSON_ERROR_NONE) {

    http_response_code(400);
    die("Invalid JSON");
}

return $data;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    header('Content-Type: application/json');

    $data_post = getJSON();

    //Verify if all parameters are set
    if (!isset($data_post['type']) || !isset($data_post['name']) || !isset($data_post['value']) || !isset($data_post['time'])){
        
        http_response_code(400);
        die("Missing parameters");
    }


    //Divide time into array to check validity of input

    $timearray = str_split($data_post['time']);

    if($timearray[4] != "/" || $timearray[7] != "/" || $timearray[10] != " " || $timearray[11] != "-" || $timearray[12] != " " || $timearray[15] != ":" || $timearray[18] != ":")
    {
        http_response_code(422);
        die("Unsupported time format");
    }

    //Set variables with respective values
    
    $year = intval($timearray[0] . $timearray[1] . $timearray[2] . $timearray[3],10);

    $month = intval($timearray[5] . $timearray[6],10);

    $day = intval($timearray[8] . $timearray[9],10);

    $hour = intval($timearray[13] . $timearray[14],10);

    $minute = intval($timearray[16] . $timearray[17],10);

    $seconds = intval($timearray[19] . $timearray[20],10);

    //Validate time

    if(!checkdate($month,$day,$year) || $hour < 0 || $hour > 24 || $minute < 0 || $minute > 59 || $seconds <0 || $seconds > 59)
    {
        http_response_code(422);
        die("Invalid time");
    }

    //Srt to low to parameters be compared

    $data_post['name'] = strtolower($data_post['name']);
    $data_post['type'] = strtolower($data_post['type']);

    //Search for a directory equal type input

    foreach (new DirectoryIterator('./files/') as $apifiles) {
        if(strcmp(substr($apifiles, 1),$data_post['type']) == 0)
        {
            $typedirectory = $apifiles->getBasename();
            break;
        }
    }

    //If not found a directory with same name, return a 'Not Found' error

    if(!isset($typedirectory) || (!file_exists("files/". $typedirectory . "/" . $data_post['name']) && !is_dir("files/". $typedirectory . "/" . $data_post['name'])))
    {
        http_response_code(404);
        die("<h1> Not Found </h1><p>The requested URL was not found on this server.</p>"); 
    }


    //Set value

    $value = file_get_contents("files/". $typedirectory . "/" . $data_post['name'] . "/value.txt");


    //Compare if the value in file are the same type (int or str), if not, return 'Unsuported value'
    //Validation made with xor because only when two values have the same type needs return an error
    if(is_numeric($value) xor is_numeric($data_post['value']))
    {
        http_response_code(422);
        die("Unsupported value");
    }

    //If its a binary type, dont need to update if have the same value as the previous one

    if(!is_numeric($data_post['value']) && strcmp($data_post['value'],$value) == 0)
    {
        http_response_code(208);
        die("Already set");
    }

    //If its a numeric type, when have same value and same date, the input is considered equal    

    if(is_numeric($data_post['value']) && $data_post['value'] == $value && strcmp(file_get_contents("files/". $typedirectory . "/" . $data_post['name'] . "/time.txt"),$data_post['time']) == 0)
    {
        http_response_code(208);
        die( "Already set");
    }
    
    //Set range

    $range = file("files/". $typedirectory . "/" . $data_post['name'] . "/range.txt", FILE_IGNORE_NEW_LINES);

    //Verify if value exceeds the range

    if(is_numeric($data_post['value']) && ($data_post['value'] < $range[0] || $data_post['value'] > $range[1]))
    {
        http_response_code(416);
        die( "Value exceeds the range");
    }

    //If its a binary type, verify if input are valid for each sensor, because a sensor that receive On or Off,
    //cant receive Open or Closed for example

    if (!is_numeric($data_post['value']))
    {
    $data_post['value'] = ucfirst(strtolower($data_post['value']));

    switch($data_post['value']){
        case 'On':
        case 'Off':
            if(strcmp($value,"On")!=0 && strcmp($value,"Off")!=0)
                goto error;
            break;

        case 'Open':
        case 'Closed':
            if(strcmp($value,"Open")!=0 && strcmp($value,"Closed")!=0)
                goto error;
            break;

        case 'Yes':
        case 'No':
            if(strcmp($value,"Yes")!=0 && strcmp($value,"No")!=0)
                goto error;
            break;
        
        default:
        error:
        http_response_code(422);
        die ("Unsupported value");
    }

    }  

    //If pass all conditions, update values and write into log file

    file_put_contents("files/" . $typedirectory . "/" . $data_post['name'] . "/value.txt", $data_post['value']);
    file_put_contents("files/" . $typedirectory . "/" . $data_post['name'] . "/time.txt", $data_post['time']);
    file_put_contents("files/" . $typedirectory . "/" . $data_post['name'] . "/log.txt", $data_post['time'] . "; " . $data_post['value'] . PHP_EOL, FILE_APPEND);
    
} elseif ($_SERVER['REQUEST_METHOD'] == "GET") {

    header('Content-Type: application/json');

    //Verify if all parameters are set

    if(!isset($_GET["name"]) || !isset($_GET["type"])) {
        
        
    foreach (new DirectoryIterator('./files/') as $apifiles) {
        if ($apifiles->isDot() || $apifiles->isFile()) continue;
        foreach (new DirectoryIterator('./files/' . $apifiles) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isFile()) continue;
            $directory = "files/" . $apifiles . "/" . $fileInfo;
            $data[] = array (
                "id" => substr($apifiles, 1) . '-'. file_get_contents($directory . "/name.txt"),
                "name" => file_get_contents($directory . "/name.txt"),
                "time" => file_get_contents($directory . "/time.txt"),
                "value" => getValue($directory),
                "image" => "api/".getImage($directory).".svg"
            );
        }
    }

    }

    //Str to low to be compared

    $_GET['name'] = strtolower($_GET['name']);

    //Search for directory equal input value

    foreach (new DirectoryIterator('./files/') as $apifiles) {
        if(strcmp(substr($apifiles, 1),$_GET['type']) == 0)
        {
            $typedirectory = $apifiles->getBasename();
            break;
        }
    }

    //If isnt set or dont exist, return a 'Not Found' error

    if(!isset($typedirectory) || (!file_exists("files/". $typedirectory . "/" . $_GET['name']) && !is_dir("files/". $typedirectory . "/" . $_GET['name'])))
    {
        http_response_code(404);
        die("<h1> Not Found </h1><p>The requested URL was not found on this server.</p>"); 
    }


    //Set value
    $time = file_get_contents("files/". $typedirectory . "/" . $_GET['name'] . "/time.txt");

    $data["time"] = $time;

    //Set value
    $value = file_get_contents("files/". $typedirectory . "/" . $_GET['name'] . "/value.txt");

    //Print value
    $data["value"] = $value;

    //If is numeric, beyond value, also prints unit of measurement and min and max possible values
    if (is_numeric($value))
    {
        $range = file("files/". $typedirectory . "/" . $_GET['name'] . "/range.txt", FILE_IGNORE_NEW_LINES);
        $measurement = file_get_contents("files/". $typedirectory . "/" . $_GET['name'] . "/measurement.txt", FILE_IGNORE_NEW_LINES);
        $data["measurement"] = $measurement;
        $main_range = array(
            "min-value" => $range[0],
            "max-value" => $range[1]);

        $data["range"] = $main_range;
    }

    $json = json_encode($data);

    echo $json;
   
} else
{
    //If been requested another method, return an error
    echo "Unauthorized method\n";
    http_response_code(403);
}
