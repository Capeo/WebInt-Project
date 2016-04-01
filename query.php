<?php
	//For testing only
	/*
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	*/
	
	//Define API key
	$api_key = "b24b21443654829ad3a9598b26e64d7ed66b10b2";
	
	//Retrieve keywords from through POST method
	$keywords = $_POST['keywords'];
	$terms = explode(" ", $keywords);
	
	//Add all keywords to query using conjunction
	$query = "A[";
	foreach($terms as $t){                 
		$query = $query . $t . "^";
	}
	$query = rtrim($query, "^");
	$query = $query . "]";
	
	//Generate correct url for query
	$url = "https://gateway-a.watsonplatform.net/calls/data/GetNews?&apikey=" . $api_key . "&outputMode=json&rank=high&dedup=1&start=now-1d&end=now&count=10&q.enriched.url.text=" . $query . "&return=enriched.url.url,enriched.url.docSentiment.score";
	
	//Retrieve JSON using cURL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$json = curl_exec($ch);
	curl_close($ch);
	
	$obj = json_decode($json);
	$results = $obj->result->docs;
	
	//For each result retrieved find source name and document sentiment. Store in associative array with source name as key
	$sources = array();
	foreach($results as $doc){
		$docRes = $doc->source->enriched->url;
		$sentiment = $docRes->docSentiment->score;
		$docUrl = $docRes->url;
		
		//Parse source name
		if(strpos($docUrl, "http://") !== false){
			$source = ltrim($docUrl, "http://");
			if (strpos($source, "/") !== false) {
				$source = substr($source, 0, strpos($source, "/"));
			}	
		}
		elseif(strpos($docUrl, "https://") !== false){
			$source = ltrim($docUrl, "https://");
			if (strpos($source, "/") !== false) {
				$source = substr($source, 0, strpos($source, "/"));
			}	
		}
		else{
			$source = "Unknown";
		}
		
		if(array_key_exists($source, $sources)){
			$sources[$source][0] += $sentiment;
			$sources[$source][1] += 1;
		}
		else{
			$sources[$source] = array($sentiment, 1);
		}
	}
	
	//Calculate average sentiment of each source and store in associative array. Also calculate 'global' average accross all retrieved articles 
	$global_total = 0;
	$global_num = 0;
	$sentiments = array();
	foreach($sources as $source => $value){
		$sentiments[$source] = $value[0] / $value[1];
		$global_total += $value[0];
		$global_num += $value[1];
	}
	$global_avg = $global_total / $global_num;
	$sentiments["Global"] = $global_avg;
	
	
	//Return JSON of associative array
	echo json_encode($sentiments);
	
?>