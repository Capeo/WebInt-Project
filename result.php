<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Result</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;

        }
    </style>
</head>
<body onresize="resize_canvas()">
    <canvas id="myCanvas" width="810" height="340">
    </canvas>
    <pre id="desc"></pre>
    <table id="myTable" style="width:100%">
        <tr>
            <td>Very negative</td>
            <td>A little negative</td>
            <td>A little positive</td>
            <td>Very positive</td>
        </tr>
        <tr id="newsTable">
            <td><pre id="vNeg"></pre></td>
            <td><pre id="lNeg"></pre></td>
            <td><pre id="lPos"></pre></td>
            <td><pre id="vPos"></pre></td>
        </tr>

    </table>




</body>

<script>

 var result = [ <?php
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

?> ];




    var i = result[0]["Global"];
    var search = "<?php echo $_POST['keywords']; ?>";
    var pElement = document.getElementById("desc");
    pElement.innerHTML="Search term: " + search + "\t\t\t" + "Average score: "+ i;




    var col1 = document.getElementById("vNeg");
    var col2 = document.getElementById("lNeg");
    var col3 = document.getElementById("lPos");
    var col4 = document.getElementById("vPos");
    //col1.innerHTML="New york times: -0.89" + "\n" + "BBC: -0.77";

for (var key in result[0]) {
            var attrName = key;
            var attrValue = result[0][key];
            if(key=="Global"){
            }
            else if(attrValue<-0.5){
                col1.innerHTML+="\n"+attrName+": "+attrValue;
            }
            else if(attrValue<0){
                col2.innerHTML+="\n"+attrName+": "+attrValue;
            }
            else if(attrValue<0.5){
                col3.innerHTML+="\n"+attrName+": "+attrValue;
            }
            else{
                col4.innerHTML+="\n"+attrName+": "+attrValue;
            }
    }


    function resize_canvas(){
        var canvas = document.getElementById("myCanvas");
        if (canvas.width  < window.innerWidth)
        {
            canvas.width  = window.innerWidth;
        }

        if (canvas.height < window.innerHeight)
        {
            canvas.height = window.innerHeight;
        }


        var ctx=canvas.getContext("2d");
        var x = window.innerWidth;
        var y = window.innerHeight/3;
        var m = x/2;
        ctx.canvas.width  = x;
        ctx.canvas.height = y;


        ctx.fillStyle="#FF0000";
        ctx.fillRect(0,0,x/4,y);

        var ctx2=canvas.getContext("2d");
        ctx2.fillStyle="#FF9933";
        ctx2.fillRect(x/4,0,x/2,y);

        var ctx3=canvas.getContext("2d");
        ctx3.fillStyle="#FFFF00";
        ctx3.fillRect(x/2,0,(3*x)/4,y);

        var ctx4=canvas.getContext("2d");
        ctx4.fillStyle="#00FF00";
        ctx4.fillRect((3*x)/4,0,x,y);

        var context = canvas.getContext('2d');

        context.beginPath();
        context.moveTo(m+m*i, y);
        context.lineTo(m+m*i, 0);
        context.lineWidth = 5;



        // set line color
        context.strokeStyle = '#000000';
        context.stroke();
    }


    window.onload = function() {
        resize_canvas();
        };
</script>
</html>