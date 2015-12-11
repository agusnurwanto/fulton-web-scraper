<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);
define("RESULT_FILE", "https://fultonfile-agusnurwanto.rhcloud.com");

function appendFile($options){
	if(!empty($options["content"])){
		$data = readResultFile();
		foreach ($options["content"] as $k => $v) {
			$data->{$k} = $v;
		}
		request(array(
			"url"	=> RESULT_FILE."/createFolders.php", 
			"param"	=> array(
				"folder"	=> "json",
				"file"		=> "resultScrapping.json",
				"output"	=> json_encode($data)
			)
		));
	}
}

function request($option){
	$url = $option['url'];
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	if(!empty($option['param'])){
		$param = http_build_query($option['param']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	}
  	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36");
  	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
	return $server_output;
}

function readResultFile(){
	$oldData = @file_get_contents(RESULT_FILE."/tmp/json/resultScrapping.json");
	$data = json_decode("{}");
	if(!empty($oldData)){
		$data = json_decode($oldData);
	}
	return $data;
}

function clearFile(){
	request(array(
		"url"	=> RESULT_FILE."/createFolders.php", 
		"param"	=> array(
			"folder"	=> "json",
			"file"		=> "resultScrapping.json",
			"output"	=> "{}"
		)
	));
}

// if(!empty($data['firstScrape'])){
if(!empty($_GET['delete'])){
	clearFile();
}

if(!empty($data["setSession"])){
	$allNewData = array();
	if(!empty($data['action']) && $data['action']=="IdError"){
		$oldKey = $data["key"];
		foreach ($data["ids"] as $k => $v) {
			$id = str_split($v);
			$id[2] = ' '.$id[2];
			$v = implode('', $id);
			$fultonPage["Parcel Number"] = $v;
			$fultonPage["Location Address"] = "";
			$fultonPage["Land Value"] = "";
			$fultonPage["Building Value"] = "";
			$fultonPage["Total Value"] = "";
			$fultonPage["YearBuilt"] = "";
			$fultonPage["Square Feet"] = "";
			$fultonPage["Bedrooms"] = "";
			$fultonPage["Full Bath/Half Bath"] = "";
			$fultonPage["Owner Name"] = "";
			$fultonPage["Mailing Address"] = "";
			$fultonPage["Property Class"] = "";
			$fultonPage["Neighborhood"] = "";
			$fultonPage["Today’s Date"] = "";
			$fultonPage["Tax District"] = "";
			$fultonPage["Zoning"] = "";
			$fultonPage["Acres"] = "";
			$fultonPage["Homestead"] = "";
			$fultonPage["LUC"] = "";
			$fultonPage["Class"] = "";
			$fultonPage["Assessed Value"] = "";
			$fultonPage["Land Type"] = "";
			$fultonPage["Land Code"] = "";
			$fultonPage["Descripton"] = "";
			$fultonPage["Acreage"] = "";
			$fultonPage["Price"] = "";
			$fultonPage["Card"] = "";
			$fultonPage["Stories"] = "";
			$fultonPage["Exterior Wall"] = "";
			$fultonPage["Style"] = "";
			$fultonPage["Res Sq Ft"] = "";
			$fultonPage["Total Rooms"] = "";
			$fultonPage["Sale Date"] = "";
			$fultonPage["Sale Price"] = "";
			$fultonPage["Instrument"] = "";
			$fultonPage["Deed Book"] = "";
			$fultonPage["Deed Page"] = "";
			$fultonPage["Sale Qualification"] = "";
			$fultonPage["Validity"] = "";
			$fultonPage["Grantee"] = "";
			$fultonPage["Grantor"] = "";
			$key = $oldKey+$k;
			$allNewData["key_".$key]["fultonPage"] = $fultonPage;
			$allNewData["key_".$key]["fultonTaxes"] = array();
			$allNewData["key_".$key]["fultonWaste"] = array();
			$allNewData["key_".$key]["fultonPdf"] = "";
			$allNewData["key_".$key]["parselNumber"] = $v;
		}
	}else{
		$key = $data["key"];
		$allNewData["key_".$key]["fultonPage"] = $data["fultonPage"];
		$allNewData["key_".$key]["fultonTaxes"] = $data["fultonTaxes"];
		$allNewData["key_".$key]["fultonWaste"] = $data["fultonWaste"];
		$allNewData["key_".$key]["fultonPdf"] = $data["fultonPdf"];
		$allNewData["key_".$key]["parselNumber"] = $data["parselNumber"];
	}
	appendFile(array( "content"=>$allNewData ));
	die(json_encode(array("error"=>0)));
}

if(!empty($_GET["download"])){
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment;Filename=fulton_scraper.xls");
	$download = "";
	$script = "";
}else{
	$download = "<h3><a href='?download=true'>Download Excel!</a></h3>";
	$script = "
		<link rel='stylesheet' type='text/css' href='//cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css'>
		<script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js'></script>
		<script src='//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js'></script>
		<style>
			#fulton{
				width: 7000px;
				padding: 0px !important;
			}
			#fulton, th, td{
				border: 1px solid;
				padding: 5px 10px;
			}
			#fulton td{
				vertical-align: top;
			}
		</style>
		<script>
			$('#fulton thead th').each( function () {
		        var title = $('#fulton tfoot th').eq( $(this).index() ).text();
			    $(this).append( '<br><input type=\"text\" placeholder=\"Search '+title+'\"/>' );
			} );
			 
		    // DataTable
		    var table = $('#fulton').DataTable();
		 
		    // Apply the search
		    table.columns().eq( 0 ).each( function ( colIdx ) {
		        $( 'input', table.column( colIdx ).header() ).on( 'keyup change', function () {
		            table
		                .column( colIdx )
		                .search( this.value )
		                .draw();
		        } );
		    } );
		</script>";
}

$allData = readResultFile();
//echo "<pre>".print_r($allData,1)."</pre>";
$tr = "";
$th = "";
if(empty($allData)){
	$tr = "
	<tr>
		<td>Data is empty!</td>
	</tr>";
}else{
	$headersFultonTaxes = [];
	$headers = array(
		"County Principal Amount",
		"County Interest",
		"County Penalties/Fees",
		"County Paid",
		"County Total",
		"City Principal Amount",
		"City Interest",
		"City Penalties/Fees",
		"City Paid",
		"City Total"
	);
	$year = date("Y");
	for($i=0; $i<=15; $i++){
		foreach ($headers as $key => $value) {
			$headersFultonTaxes[] = ($year-$i).' '.$value;
		}
	}

	$headersWasteTaxes = [];
	$headers = array(
		"Original Amount",
		"Exemptiont",
		"Interest",
		"Penalties/Fees",
		"Paid",
		"Amount Due",
		"Last Payment"
	);
	$year = date("Y");
	for($i=0; $i<=15; $i++){
		foreach ($headers as $key => $value) {
			$headersWasteTaxes[] = ($year-$i).' '.$value;
		}
	}

	$i = 1;
	$th .= "<tr><th>No</th>";
	$errorText = "Error scraping";
	foreach ($allData as $k => $v) {
		$tr .= "<tr><td style='text-align:center;'>$i</td>";
		foreach ($v->fultonPage as $key => $value) {
			if($i==1){
				$th .= "<th>".str_replace("_", " ", $key)."</th>";
			}

			$align = "";
			if (strpos($value,'$') !== false){
				$align = "style='text-align:right;'";
			}
			if(!empty($value)){
				$tr .= "<td $align>$value</td>";
			}else{
				$tr .= "<td>".$errorText."</td>";
			}
		}
		if($i==1){
			foreach ($headersFultonTaxes as $key => $value) {
				$th .= "<th>".$value."</th>";
			}
			foreach ($headersWasteTaxes as $key => $value) {
				$th .= "<th>".$value."</th>";
			}
			$th .= "<th style='width:600px;'>Fulton Page HTML</th>";
			$th .= "<th style='width:600px;'>Fulton Taxes PDF</th>";
			$th .= "<th style='width:600px;'>Fulton Waste PDF</th>";
		}
		foreach ($headersFultonTaxes as $val) {
			$cek = false;
			foreach ($v->fultonTaxes as $key => $value) {
				if($val==$key){
					$tr .= "<td>".$value."</td>";
					$cek = true;
					break;
				}
			}
			if(!$cek){
				$tr .= "<td>".$errorText."</td>";
			}
		}
		foreach ($headersWasteTaxes as $val) {
			$cek = false;
			foreach ($v->fultonWaste as $key => $value) {
				if($val==$key){
					$tr .= "<td>".$value."</td>";
					$cek = true;
					break;
				}
			}
			if(!$cek){
				$tr .= "<td>".$errorText."</td>";
			}
		}
		$urls = explode('<br>', $v->fultonPdf);
		for($j=0; $j<3; $j++){
			if(!empty($urls[$j])){
				$tr .= "<td>".$urls[$j]."</td>";
			}else{
				$tr .= "<td>".$errorText."</td>";
			}
		}
		$tr .= "</tr>";
		$i++;
	}
	$th .= "</tr>";
}

echo "
<html>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<style>
	td, th {
	    border: 1px solid;
	    padding: 10px;
	}
</style>
<body>
	$download
	<table id='fulton' style='width:10000px;border: 1px solid;' cellspacing='0'>
		<thead>
			$th
		</thead>
		<tbody>
			$tr
		</tbody>
	</table>
	$script
</body>
</html>";