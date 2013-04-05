<?php

include_once('../../config.include.php');

$server = $GLOBALS['config']['DBAccess']['host'];
$login = $GLOBALS['config']['DBAccess']['user'];
$password = $GLOBALS['config']['DBAccess']['password'];
$base = $GLOBALS['config']['DBAccess']['databaseName'];

function dump_MySQL($server, $login, $password, $base, $mode)
{
    $connexion = mysql_connect($server, $login, $password);
    mysql_select_db($base, $connexion);
    
    $header  = "-- ----------------------\n";
    $header .= "-- '".$base."' DATABASE DUMP - ".date("d-M-Y")."\n";
    $header .= "-- ----------------------\n\n\n";
    $tableCreates = "";
    $inserts = "\n\n";
    
    $listeTables = mysql_query("show tables", $connexion);
    while($table = mysql_fetch_array($listeTables))
    {
        // mode 1 = dump structure only / mode 2 = full content dump
        if($mode == 1 || $mode == 2)
        {
            $tableCreates .= "-- -----------------------------\n";
            $tableCreates .= "-- '".$table[0]."' TABLE STRUCTURE \n";
            $tableCreates .= "-- -----------------------------\n\n";
            $createdTablesLists = mysql_query("show create table ".$table[0], $connexion);
            while($creationTable = mysql_fetch_array($createdTablesLists))
            {
              $tableCreates .= $creationTable[1].";\n\n";
            }
        }
        if($mode > 1)
        {
            $data = mysql_query("SELECT * FROM ".$table[0]);
            $inserts .= "-- -----------------------------\n";
            $inserts .= "-- '".$table[0]."' TABLE CONTENT\n";
            $inserts .= "-- -----------------------------\n\n";
            while($nuplet = mysql_fetch_array($data))
            {
                $inserts .= "INSERT INTO ".$table[0]." VALUES(";
                for($i=0; $i < mysql_num_fields($data); $i++)
                {
                  if($i != 0)
                     $inserts .=  ", ";
                  if(mysql_field_type($data, $i) == "string" || 
                     mysql_field_type($data, $i) == "blob")
                     $inserts .=  "'";
                  $inserts .= addslashes($nuplet[$i]);
                  if(mysql_field_type($data, $i) == "string" || 
                     mysql_field_type($data, $i) == "blob")
                    $inserts .=  "'";
                }
                $inserts .=  ");\n";
            }
            $inserts .= "\n";
        }
    }
 
    mysql_close($connexion);
 
    $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saterday');
    
    $dumpFile = fopen('./db_daily_dumps/'.$days[date('w')].".sql", "w+");
    fwrite($dumpFile, $header);
    fwrite($dumpFile, $tableCreates);
    fwrite($dumpFile, $inserts);
    fclose($dumpFile);
    
}

dump_MySQL($server, $login, $password, $base, 2);

?>