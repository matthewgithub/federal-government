<?php 

// This publishes each agencie 
$dbserver = "";
$dbname = "";
$dbuser = "";
$dbpassword = "";

mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$test = 0;
$PullFacebookLikes = 0;
$PullTwitterFollowers = 0;
$PullGithubRepos = 0;

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
		
		$Agency['twitter'] = array();
		$Agency['facebook'] = array();
		$Agency['github'] = array();	
	
		$FacebookCount = 0;	
		$FacebookLikeCount = 0;	
							
        // Social Media URLs
		$FacebookQuery = "SELECT * FROM federalagencies_socialmedia WHERE agency_id = '" . $agency_id . "' AND type = 'facebook'  ORDER BY user_name ASC";				
		//echo $FacebookQuery . "<br />";
		$FacebookResult = mysql_query($FacebookQuery) or die('Query failed: ' . mysql_error());
		
		if($FacebookResult && mysql_num_rows($FacebookResult))
			{
			while ($Facebook = mysql_fetch_assoc($FacebookResult))
				{
				$ID = $Facebook['ID'];	                              
		        $user_name = $Facebook['user_name'];	     
		        $url = $Facebook['url'];
				$like_count = $Facebook['like_count'];
		       			        
				$Facebook = array();
				$Facebook['user_name'] = $user_name;
				$Facebook['url'] = $url;
				//$Facebook['like_count'] = $like_count;	
				
				array_push($Agency['facebook'], $Facebook);		
				$FacebookCount++;
				$FacebookLikeCount = $FacebookLikeCount + $like_count;				      
		        
				}
			}	

		//$Agency['facebook_account_count'] = $FacebookCount;	    
		//$Agency['facebook_like_count'] = $FacebookLikeCount;	     

		$TwitterCount = 0;
		$TwitterFollowerCount = 0;	

        // Social Media URLs
		$TwitterQuery = "SELECT * FROM federalagencies_socialmedia WHERE agency_id = '" . $agency_id . "' AND type = 'twitter'  ORDER BY user_name ASC";				
		//echo $TwitterQuery . "<br />";
		$TwitterResult = mysql_query($TwitterQuery) or die('Query failed: ' . mysql_error());
		
		if($TwitterResult && mysql_num_rows($TwitterResult))
			{

			while ($Twitter = mysql_fetch_assoc($TwitterResult))
				{
				$ID = $Twitter['ID'];	                              
		        $user_name = $Twitter['user_name'];	     
		        $url = $Twitter['url'];
				$follower_count = $Twitter['follower_count'];

				$Twitter = array();
				$Twitter['user_name'] = $user_name;
				$Twitter['url'] = $url;
				//$Twitter['follower_count'] = $follower_count;	
				
				array_push($Agency['twitter'], $Twitter);	
				
				$TwitterCount++;
				$TwitterFollowerCount = $TwitterFollowerCount + $follower_count;															        

				}

			}

		//$Agency['twitter_account_count'] = $TwitterCount;	    
		//$Agency['twitter_follower_count'] = $TwitterFollowerCount;		
					
		$GithubCount = 0;
		$GithubRepoCount = 0;					
        // Social Media URLs
		$FacebookQuery = "SELECT * FROM federalagencies_socialmedia WHERE agency_id = '" . $agency_id . "' AND type = 'github'  ORDER BY user_name ASC";				
		//echo $FacebookQuery . "<br />";
		$FacebookResult = mysql_query($FacebookQuery) or die('Query failed: ' . mysql_error());
		
		if($FacebookResult && mysql_num_rows($FacebookResult))
			{						
			while ($Facebook = mysql_fetch_assoc($FacebookResult))
				{                              
		       	$ID = $Facebook['ID'];
			    $user_name = $Facebook['user_name'];	     
		        $url = $Facebook['url'];
				$repo_count = $Facebook['repo_count'];
				
				if($PullGithubRepos==1)
					{
					$web_page = http_get($url,'');	
					//echo $url . "<br />";

						
					$Begin_Tag = '<meta name="description" content="';
					$End_Tag = '/>';
					$RepoCount = return_between($web_page['FILE'], $Begin_Tag, $End_Tag, INCL);	
					
					$RepoCount = "<" . $RepoCount;
					$RepoCount = strip_tags($RepoCount);
					$RepoCount = trim($RepoCount);		

					if($RepoCount=='')
						{
						$Begin_Tag = '<ul class="stats">';
						$End_Tag = '</strong>';
						$RepoCount = return_between($web_page['FILE'], $Begin_Tag, $End_Tag, INCL);	
						$RepoCount = strip_tags($RepoCount);
						$RepoCount = trim($RepoCount);																			
						}
					
					if(strlen($RepoCount)>100){ $RepoCount = 0;}
					
					$test++;
					$RepoCount = $RepoCount - 0;
					//echo $RepoCount . "<br />";
					
					$repo_count = $RepoCount;
					
					$UpdateQuery = "UPDATE federalagencies_socialmedia SET repo_count = " . $RepoCount . " WHERE ID = " . $ID;
					//echo $UpdateQuery . "<br />";
					$UpdateResult = mysql_query($UpdateQuery) or die('Query failed: ' . mysql_error());									
						
					}									

				$Github = array();
				$Github['user_name'] = $user_name;
				$Github['url'] = $url;
				//$Github['repo_count'] = $repo_count;	
				
				array_push($Agency['github'], $Facebook);	
				
				$GithubCount++;
				$GithubRepoCount = $GithubRepoCount + $repo_count;															        														        
		        
				}
			}	

		//$Agency['github_account_count'] = $GithubCount;	    
		//$Agency['github_repo_count'] = $GithubRepoCount;					

       	array_push($Stacks['agencies'], $Agency);
		$AgencyCount++;					
		}					
				
	 } 	

$Stacks['agency_count'] = $AgencyCount;	
$ReturnStack = json_encode($Stacks);
$Data_Store_File = "data/federal-agencies-with-social.json";

$WriteDataFile = $Project_Repo_Path . "/" . $Data_Store_File;
echo "Writing to " . $WriteDataFile . "<br />";
$fh = fopen($WriteDataFile, 'w') or die("can't open file");
fwrite($fh, $ReturnStack);
fclose($fh);

?>