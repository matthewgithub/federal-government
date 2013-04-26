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
	while ($Agency = mysql_fetch_assoc($PostResult))
		{	
		
		$Federalagencies_ID = $Agency['ID'];
	    $Name = $Agency['agency_name'];
	    $url = $Agency['agency_url'];
	    $agency_id = $Agency['agency_id'];
	    
	    $htmlurl = $url. "/digitalstrategy";
		$htmlurl = str_replace("//digitalstrategy","/digitalstrategy",$htmlurl);
		
		if($agency_id == 'usda')
			{
			$htmlurl = 'http://www.usda.gov/wps/portal/usda/usdahome?navid=DIGITALSTRATEGY';
			}
		
		//Pull the HTML Version of Digital Strategy
		$http = curl_init();  
		curl_setopt($http, CURLOPT_URL, $htmlurl);  
		curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
		
		$output = curl_exec($http);
		$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
		$info = curl_getinfo($http);
	
		$Redirect = false;
		if($http_status=='302'||$http_status=='301')
			{
			$Redirect = true;	
			$htmlurl = $info['redirect_url'];
	
			$http = curl_init();  
			curl_setopt($http, CURLOPT_URL, $htmlurl);  
			curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
			
			$output = curl_exec($http);
			$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
			
			$info = curl_getinfo($http);		
			}
		
		// Pull the JSON format and get detail
		
		// Some hardcoding to get some that I know are there
	    if($agency_id=='ed')  // Just for Department of Ed
	    	{
	    	// Override because of redirect they have which returns 301
	    	$jsonurl = "http://www2.ed.gov/digitalstrategy.json";
	    	}
	    else
	    	{
	    	$jsonurl = $url . "/digitalstrategy.json";
			}
		
		if($agency_id == 'usda')
			{
			$jsonurl = 'http://www.usda.gov/digitalstrategy.json';
			}	
			
		if($agency_id == 'dhs')
			{
			$jsonurl = 'http://www.dhs.gov/sites/default/files/publications/digital-strategy/digitalstrategy.json';
			}		
			
		if($agency_id == 'va')
			{
			$jsonurl = 'http://www.oit.va.gov/digitalstrategy/digitalstrategy.json';
			}
		
		$jsonurl = str_replace("//digitalstrategy","/digitalstrategy",$jsonurl);
		
		$http = curl_init();  
		curl_setopt($http, CURLOPT_URL, $jsonurl);  
		curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
		
		$output = curl_exec($http);
		$info = curl_getinfo($http);
		$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);	
		
		// The range of HTTP Status codes you get
		if($http_status=='302'||$http_status=='301'||$http_status=='403')
			{
			$info = curl_getinfo($http);
			
			$jsonurl = $info['redirect_url'];
			
			$http = curl_init();  
			curl_setopt($http, CURLOPT_URL, $jsonurl);  
			curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
			
			$output = curl_exec($http);
			$info = curl_getinfo($http);	
			$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
			
			// Sometimes we get redirects on the JSON file
			if($http_status=='302'||$http_status=='301')
				{
				$info = curl_getinfo($http);
				
				$jsonurl = $info['redirect_url'];
	
				$http = curl_init();  
				curl_setopt($http, CURLOPT_URL, $jsonurl);  
				curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
				
				$output = curl_exec($http);
				$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);		
				}		
		
			}
		
		$report = json_decode($output);
	
		if(is_null($report))
			{
			echo "<strong>JSON was not found!!</strong><br />";
			$http_status = "404";
			}
		else
			{
			$generated = $report->generated;	
			
			$Generated_Date = date('Y-m-d H:i:s', strtotime($generated));
	
			// For each report Item
			if(isset($report->items))
				{
				foreach($report->items as $reportitem)
					{
					$id = $reportitem->id;
					$text = $reportitem->text;
					$parent = $reportitem->parent;
					$due = $reportitem->due;
					$due_date = $reportitem->due_date;
					
					echo "id: " . $id . "<br />";
					echo "text: " . $text . "<br />";
					echo "parent: " . $parent . "<br />";
					echo "due: " . $due . "<br />";
					echo "due_date: " . $due_date . "<br />";
					echo "<br />";
					
					// Check Report Item
					$PostCheckQuery = "SELECT * FROM federalagencies_report_items WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "'";
					$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
					
					if($CheckResult && mysql_num_rows($CheckResult))
						{						
						$CheckResult = mysql_fetch_assoc($CheckResult);	
						$query = "UPDATE federalagencies_report_items SET text = '" . mysql_real_escape_string($text) . "' WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "'";
						//echo $query;
						mysql_query($query) or die('Query failed: ' . mysql_error());
						}
					else
						{
						$query = "INSERT INTO federalagencies_report_items(id,text,parent,due,due_date,federalagency) VALUES('" . $id . "','" . mysql_real_escape_string($text) . "','" . $parent . "','" . $due . "','" . $due_date . "','" . $agency_id . "')";
						//echo $query;
						mysql_query($query) or die('Query failed: ' . mysql_error());				
						}			
							
					$sortcount = 1;
					foreach($reportitem->fields as $reportfield)
						{
	
						$type = $reportfield->type;
						$name = $reportfield->name;
						$label = $reportfield->label;
						$value = $reportfield->value;
						
						echo "type: " . $type . "<br />";
						echo "name: " . $name . "<br />";
						echo "label: " . $label . "<br />";
						
						if(is_array($value))
							{
							$acount = 0;
							foreach($value as $v)
								{
										
								echo "Value: " . $v . "<br />";
								
								// Check Report Field
								$PostCheckQuery = "SELECT * FROM federalagencies_report_fields WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "' AND name = '" . $name . "' AND value = '" . mysql_real_escape_string($v) . "'";
								$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
								
								if($CheckResult && mysql_num_rows($CheckResult))
									{						
									$CheckResult = mysql_fetch_assoc($CheckResult);	
									$query = "UPDATE federalagencies_report_fields SET value = '" . mysql_real_escape_string($v) . "' WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "' AND name = '" . $name . "' AND value = '" . mysql_real_escape_string($v) . "'";
									//echo $query;
									mysql_query($query) or die('Query failed: ' . mysql_error());
									}
								else
									{
									$query = "INSERT INTO federalagencies_report_fields(id,name,label,value,federalagency,acount,multiple,sortcount) VALUES('" . $id . "','" . mysql_real_escape_string($name) . "','" . mysql_real_escape_string($label) . "','" . mysql_real_escape_string($v) . "','" . $agency_id . "',$acount,1,$sortcount)";
									echo $query . "<br />";
									mysql_query($query) or die('Query failed: ' . mysql_error());				
									}
	
								$acount++;
								}
							
							}
						elseif(is_null($value)||$value='')
							{
							$value = '';
							
							// Check Report Field
							$PostCheckQuery = "SELECT * FROM federalagencies_report_fields WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "' AND name = '" . $name . "'AND value = '" . $value . "'";
							$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
							
							if($CheckResult && mysql_num_rows($CheckResult))
								{						
								$CheckResult = mysql_fetch_assoc($CheckResult);	
								$query = "UPDATE federalagencies_report_fields SET value = '" . mysql_real_escape_string($value) . "' WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "'";
								//echo $query;
								mysql_query($query) or die('Query failed: ' . mysql_error());
								}
							else
								{
								$query = "INSERT INTO federalagencies_report_fields(id,name,label,value,federalagency) VALUES('" . $id . "','" . mysql_real_escape_string($name) . "','" . mysql_real_escape_string($label) . "','" . mysql_real_escape_string($value) . "','" . $agency_id . "')";
								//echo $query;
								mysql_query($query) or die('Query failed: ' . mysql_error());				
								}							
							
							}
						else
							{
							// Check Report Field
							$PostCheckQuery = "SELECT * FROM federalagencies_report_fields WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "' AND name = '" . $name . "'AND value = '" . mysql_real_escape_string($value) . "'";
							//echo $PostCheckQuery;
							$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
							
							if($CheckResult && mysql_num_rows($CheckResult))
								{						
								$CheckResult = mysql_fetch_assoc($CheckResult);	
								$query = "UPDATE federalagencies_report_fields SET value = '" . mysql_real_escape_string($value) . "' WHERE federalagency = '" . $agency_id . "' AND id = '" . $id . "'";
								//echo $query;
								mysql_query($query) or die('Query failed: ' . mysql_error());
								}
							else
								{
								$query = "INSERT INTO federalagencies_report_fields(id,name,label,value,federalagency) VALUES('" . $id . "','" . mysql_real_escape_string($name) . "','" . mysql_real_escape_string($label) . "','" . mysql_real_escape_string($value) . "','" . $agency_id . "')";
								//echo $query;
								mysql_query($query) or die('Query failed: ' . mysql_error());				
								}							
							
							}																	
						
						echo "value: " . $value . "<br />";	
	
						$sortcount++;
						}
	
					}
	
				}						
			}		
		  
		} 	
	}
?>
