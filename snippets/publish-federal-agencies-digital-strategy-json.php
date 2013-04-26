<?php 

// This is pretty crude script to check if all 246 agencies have a digital strategy and if they do it pulls it and stores into a database

$dbserver = "";
$dbname = "";
$dbuser = "";
$dbpassword = "";

mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$PostQuery = "SELECT ID,agency_name,agency_url, agency_id FROM federalagencies ORDER BY Name ASC";
$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());

if($PostResult && mysql_num_rows($PostResult))
	{	

	$aCount = 1;
	$test = 1;
	
	while ($PostRow = mysql_fetch_assoc($PostResult))
		{
			                              
        $agency_id = $PostRow['agency_id'];
        $Name = $PostRow['Name'];
        $URL = $PostRow['URL'];
        $Check_Status = $PostRow['Check_Status'];	
		
		$Agency = array();
		$Agency['agency_id'] = $agency_id;
		$Agency['name'] = $Name;
		$Agency['url'] = $URL;
		$Agency['logo'] = "http://kinlane-productions.s3.amazonaws.com/digital-strategy/logos/" . $agency_id . ".png";		
		
		$Agency['digital_strategy_html'] = $URL . '/digitalstrategy/';		
		$Agency['digital_strategy_json'] = $URL . '/digitalstrategy.json';	
		$Agency['digital_strategy_xml'] = $URL . '/digitalstrategy.xml';
		
		$Agency['2.2'] = array();
		$Agency['7.2'] = array();												

		// Get Report Field
		$DataReportFieldQuery = "SELECT * FROM federalagencies_report_fields WHERE federalagency = '" . $agency_id . "' AND Label IN('System Name','System Description') AND id = '2.1.2' ORDER BY id,acount ASC";
		//echo $ReportFieldQuery . "<br />";
		$DataReportFieldResult = mysql_query($DataReportFieldQuery) or die('Query failed: ' . mysql_error());
		
		$DataCount = mysql_num_rows($DataReportFieldResult);
		
		// Get Report Field
		$MobileReportFieldQuery = "SELECT * FROM federalagencies_report_fields WHERE federalagency = '" . $agency_id . "' AND Label IN('System Name','System Description') AND id = '7.1.2' ORDER BY id,acount ASC";
		//echo $ReportFieldQuery . "<br />";
		$MobileReportFieldResult = mysql_query($MobileReportFieldQuery) or die('Query failed: ' . mysql_error());			
		
		$MobileCount = mysql_num_rows($MobileReportFieldResult);							
		
		if($DataReportFieldResult && mysql_num_rows($DataReportFieldResult))
			{						
						
			$LastSortCount = 1;
			$System_Name = "";
			$System_Description = "";
			
			while ($ReportField = mysql_fetch_assoc($DataReportFieldResult))
				{
				$name = $ReportField['name'];
				$label = $ReportField['label'];
				$value = $ReportField['value'];
				$sortcount = $ReportField['sortcount'];

				// Only Show Field if it has value
				if($value!='')
					{
					
					if($sortcount==1)
						{
						$System_Name = $value;	
						}
						
					if($sortcount==2)
						{
						$System_Description = $value;		
						}
						
					if($sortcount==2)
						{

						$Data = array();
						$Data['name'] = $System_Name;
						$Data['description'] = $System_Description;
						
						array_push($Agency['2.2'], $Data);	
						
						$System_Name = "";
						$System_Description = "";																		
						
						}
					}
				$LastSortCount = $sortcount;
				}						
			}
		
		if($MobileReportFieldResult && mysql_num_rows($MobileReportFieldResult))
			{
				
			$LastSortCount = 1;
			$System_Name = "";
			$System_Description = "";
			
			while ($ReportField = mysql_fetch_assoc($MobileReportFieldResult))
				{
				$name = $ReportField['name'];
				$label = $ReportField['label'];
				$value = $ReportField['value'];
				$sortcount = $ReportField['sortcount'];
				
				//echo $sortcount . " = " . $value . "<br />";
				
				// Only Show Field if it has value
				if($value!='')
					{
					
					if($sortcount==1)
						{
						$System_Name = $value;	
						}
						
					if($sortcount==2)
						{
						$System_Description = $value;		
						}
						
					if($sortcount==2)
						{
						
						$Mobile = array();
						$Mobile['name'] = $System_Name;
						$Mobile['description'] = $System_Description;
						
						array_push($Agency['7.2'], $Mobile);										
						
						$System_Name = "";
						$System_Description = "";
						}
					}
				$LastSortCount = $sortcount;
				}

	        array_push($Stacks['agencies'], $Agency);
			$AgencyCount++;							
	        }

        $aCount++;
		}
	 } 	

$Stacks['agency_count'] = $AgencyCount;	
$ReturnStack = json_encode($Stacks);
$Data_Store_File = "data/federal-agencies-digital-strategy.json";

$WriteDataFile = $Project_Repo_Path . "/" . $Data_Store_File;
echo "Writing to " . $WriteDataFile . "<br />";
$fh = fopen($WriteDataFile, 'w') or die("can't open file");
fwrite($fh, $ReturnStack);
fclose($fh);
?>
