<?php 

// This publishes each agencie 
$dbserver = "";
$dbname = "";
$dbuser = "";
$dbpassword = "";

mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);
		
$test = 0;

$Stacks['published'] = date('m/d/Y');	
$Stacks['agencies'] = array();

$PostQuery = "SELECT ID,agency_id,agency_name as Name,agency_url as URL FROM federalagencies fa ORDER BY agency_name";				
//echo $PostQuery . "<br />";
$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());

$AgencyCount = 0;

if($PostResult && mysql_num_rows($PostResult))
	{
			
	$aCount = 1;
	$test = 1;
	
	while ($PostRow = mysql_fetch_assoc($PostResult))
		{                              
        $agency_id = $PostRow['agency_id'];
        $Name = $PostRow['Name'];
        $URL = $PostRow['URL'];

		$Agency = array();
		$Agency['agency_id'] = $agency_id;
		$Agency['name'] = $Name;
		$Agency['url'] = $URL;
		$Agency['logo'] = "http://kinlane-productions.s3.amazonaws.com/digital-strategy/logos/" . $agency_id . ".png";		
		
		$Agency['digital_strategy_html'] = $URL . '/digitalstrategy/';		
		$Agency['digital_strategy_json'] = $URL . '/digitalstrategy.json';	
		$Agency['digital_strategy_xml'] = $URL . '/digitalstrategy.xml';

        array_push($Stacks['agencies'], $Agency);
		$AgencyCount++;							
			
        $aCount++;
		}
	 } 	

$Stacks['agency_count'] = $AgencyCount;	
$ReturnStack = json_encode($Stacks);
$Data_Store_File = "data/federal-agencies.json";

$WriteDataFile = $Project_Repo_Path . "/" . $Data_Store_File;
echo "Writing to " . $WriteDataFile . "<br />";
$fh = fopen($WriteDataFile, 'w') or die("can't open file");
fwrite($fh, $ReturnStack);
fclose($fh);

?>
