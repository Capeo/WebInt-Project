<?php
	$api_key = "b24b21443654829ad3a9598b26e64d7ed66b10b2";
	
	$keywords = $_POST['keywords'];
	$terms = explode(" ", $keywords);
	
	$query = "A[";
	foreach($terms as $t){                 
		$query = $query . $t . "^";
	}
	rtrim($query, "^");
	$query = $query . "]";
	
	$url = "https://gateway-a.watsonplatform.net/calls/data/GetNews?&apikey=" . $api_key . "&outputMode=json&rank=high&dedup=1&start=now-1d&end=now&count=10&q.enriched.url.text=" . $query . "&return=enriched.url.url,enriched.url.docSentiment.score";
	
	$json = file_get_contents($url);
	$arr = json_decode($json, true);
	
	$results = $arr['results']['docs'];
	
	$sourceVal = array();
	
	foreach($results as $doc => $values){
		$docRes = $values['source']['enriched']['url'];
		$sentiment = $docRes['docSentiment']['score'];
		$docUrl = $docRes['url'];
		
		ltrim($docUrl, "http://");
		if (strpos($docUrl, "/") !== false) {
			$source = substr($docUrl, 0, strpos($docUrl, "/"));
		}
		
		if(array_key_exists($source, $sourceVal)){
			$sourceVal[$source][0] += sentiment;
			$sourceVal[$source][1] += 1;
		}
		else{
			$sourceVal[$source] = array($sentiment, 1);
		}
	}
	
	$sentiments = array();
	
	foreach($sourceVal as $source){
		$sentiments[$source] = $sourceVal[$source][0] / $sourceVal[$source][1];
	}
	
	echo json_encode($sentiments);
	
?>