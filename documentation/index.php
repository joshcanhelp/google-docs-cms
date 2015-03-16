<?php
	
	/***************************************************
	The Google Docs CMS by Josh Cunningham v0.5
	Last update: 11/04/2009
	Contact: josh@joshcanhelp.com
	Site: http://www.joshcanhelp.com
	Download: http://www.joshcanhelp.com/google-docs-cms/gdocs-cms.zip
	***************************************************/
	
	/********** CONFIGURATION OPTIONS ******************/
	
	// The RSS version of your Google spreadsheet goes in between the quotes below.
	// Click the "Share" button in a Google spreadsheet then "Publish as web page." 
	// Change "Web page" to "RSS" and select "Cells." Now, copy the link and paste it below.
	$feedName = 'http://spreadsheets.google.com/feeds/cells/tDnaIRLX1kMMO02Fcgb7vkg/od6/public/basic?alt=rss';
	
	// Are you hosting the images you'll use on the page?
	// If you have access to your web host and can upload images, leave the option below blank. 
	// Just enter the name of an image into the Google Doc and upload it to /images/ and it will display automatically. 
	// If you do not have access to your host, enter yes and use direct image URLs in your Google spreadsheet
	$externalHost = '';
	
	// Should the page show an image at the top? 
	// Enter the file name in between the quotes and place the image in the "images" folder
	// Default is no logo 
	$headLogo = 'logo.png';
	
	// Should the page show your email address as the author at the bottom? 
	// Enter "yes" between the ticks to display the email address of the Google Doc creator. 
	$showAuthor = '';
	
	// Should the page show the last date it was updated at the bottom? 
	// Enter "yes" between the ticks to dislpay the last time the Google Doc was updated.
	$showUpdate = 'yes';
	
	// Should the page show the Google spreadsheet sheet name? 
	// Enter "yes" between the ticks to display the name of the Google Doc.
	$showSheetName = '';
	
	/********** END CONFIGURATION OPTIONS ******************/
	
	// Loads the RSS reader function
	$objDOM = new DOMDocument();
	
	// If the URL to your Google spreadsheet is invalid, the script dies and says why
	@$objDOM->load($feedName) or die("Invalid feed!");
	
	// Check for a page name and, if there is none, the home page indicator is activated
	// If there is a page query, this information is stored to determine what page to display
	if ($_SERVER["QUERY_STRING"] == '' || !$_SERVER["QUERY_STRING"])
	{
		$pageRequest = "home";
		
	} else
	{
		$queries = $_SERVER["QUERY_STRING"];
		$query = explode('=', $queries);
		$pageRequest = $query[1];
		
		if (!$pageRequest)
		{
			$pageRequest = "home";
		}
	}
	
	$note = $objDOM->getElementsByTagName("item");
	
	$currentPage = false;
	
	// Setting the variable that will eventually hold the entire page to be output
	$theHeader = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">';
	$theHeader .= '
	<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
		<link rel="stylesheet" href="main.css" type="text/css" media="screen" />';
		
	$theContent = '
						<div id="theContent">';
	
	// Setting the counters to store all the page links and page names
	// Links
	$i = 0;
	
	// Names
	$j = 0;
	
	// Setting 404 indicator
	$is404 = true;
		
	// Iterating through the RSS feed to store all possible URLs and current page content
	foreach ($note as $value)
	{	
		$cells = $value->getElementsByTagName("title");
		$currCell  = $cells->item(0)->nodeValue;	
		
		if (strlen($currCell) == '2')
		{
			$currCol = substr($currCell, 0, 1);
			$currRow = substr($currCell, 1, 2);
			
			
			if ($currCol == 'A')
			{
				$data = $value->getElementsByTagName("description");
				$pageLink  = $data->item(0)->nodeValue;
				
				$allLinks[$i] = $pageLink;
				$i ++;
				
				if ($pageLink == $pageRequest)
				{
					$currentPage = true;
					$is404 = false;
				} else
				{
					$currentPage = false;
				}

			}
			
			if ($currCol == 'B' && $currentPage == true)
			{
				$data = $value->getElementsByTagName("description");
				$pageTitle  = $data->item(0)->nodeValue;
				
				$theHeader .= '
		<title>' . $pageTitle . '</title>';
			}
			
			if ($currCol == 'C')
			{
				$data = $value->getElementsByTagName("description");
				$pageName  = $data->item(0)->nodeValue;
				
				$allPages[$j] = $pageName;
				$j ++;
				
				if ($currentPage == true)
				{
					$data = $value->getElementsByTagName("description");
					$pageHead = $data->item(0)->nodeValue;
					
					$theContent .= '
				<h1>' . $pageHead . '</h1>';
				}

			}
		
		}
			
		if (($currCol == 'D' || $type == 'next') && $currentPage == true)
		{
			$data = $value->getElementsByTagName("description");
			$type = $data->item(0)->nodeValue;
			
			if ($type == 'p')
			{
				$theContent .= '
				<p>';
			} elseif ($type == 'h')
			{	
				$theContent .= '
				<h2>';
			} elseif ($type == 'a')
			{	
				$theContent .= '
				<p class="link"><a href="';
			} elseif ($type == 'i')
			{	
				$theContent .= '
				<p class="image"><img src="';
				
				if ($externalHost != 'yes')
				{
					$theContent .= 'images/';
				}
			}
			
			$contentCheck = null;
		}
			
		if (($type == 'p' || $type == 'h' || $type == 'a' || $type == 'i') && $contentCheck == true && $currentPage == true)
		{
			$data = $value->getElementsByTagName("description");
			$content = $data->item(0)->nodeValue;
			
			$theContent .= $content;
			
			if ($type == 'p')
			{
				$theContent .= '</p>';
			} elseif ($type == 'h')
			{	
				$theContent .= '</h2>';
			} elseif ($type == 'a')
			{	
				$theContent .= '">' . $content . '</a></p>';
			} elseif ($type == 'i')
			{	
				$theContent .= '"></p>';
			} 
			
			$contentCheck = false;
			$type = 'next';
			
		}
		
		if ($contentCheck == null)
		{
			$contentCheck = true;
		}
		
	} // end of feed iteration
	
	$theHeader .= '
	</head>
	<body>
		<div id="theWrapper">		
		';
	if ($headLogo != '')
	{
		$theHeader .= '
			<div id="theHeader">
				<a href="' . $_SERVER['PHP_SELF'] . '" title="Go home"><img src="images/' . $headLogo . '" alt="Header logo cannot be displayed" /></a>
			</div>';
	}
	
	$theNav = '
			<ul id="theNav">';
	
	foreach ($allLinks as $oneLink)
	{
		$linkText = str_replace('-', ' ',$oneLink);
		$theNav .= '
				<li><a href="?page=' . $oneLink . '">' . $linkText . '</a></li>';
	}		
	
	$theNav .= '
			</ul>';	
			
	$theContent .= '
			</div>';
	
	$theFooter = '
			<div id="theFooter">';
	
	
	
	$theFooter .= '
				<p>Template by <a href="http://www.joshcanhelp.com">Josh Can Help web strategy</a></p>
			</div>
		</div>
		   
		<!-- Google Analytics Code Start -->

			<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
			</script>	
				
			<script type="text/javascript">		
			var pageTracker = _gat._getTracker("UA-3604749-12");		
			pageTracker._trackPageview();
			</script>
		
		<!-- Google Analytics Code End -->

	</body>
</html>
	';
	
	$the404 = '
			<div id="theContent">
				<h1>404 - page does not exist</h1>
				<p>Sorry, that page does not exist. All the pages on this site are listed on the left.</p>
			</div>';

	echo $theHeader;
	echo $theNav;
	
	if ($is404)
	{
		echo $the404;
	} else
	{
		echo $theContent;
	}
	echo $theFooter;
	

?>