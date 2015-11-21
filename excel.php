<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

// if(!empty($data['firstScrape'])){
if(!empty($_GET['delete'])){
	$_SESSION = [];
}

if(!empty($data["setSession"])){
	$key = $data["key"];
	$_SESSION["key_".$key]["fultonPage"] = $data["fultonPage"];
	$_SESSION["key_".$key]["fultonTaxes"] = $data["fultonTaxes"];
	$_SESSION["key_".$key]["fultonWaste"] = $data["fultonWaste"];
	$_SESSION["key_".$key]["fultonPdf"] = $data["fultonPdf"];
	$_SESSION["key_".$key]["parselNumber"] = $data["parselNumber"];
	// echo "<pre>".print_r($_SESSION,1)."</pre>";
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

$tr = "";
$th = "";
if(empty($_SESSION)){
	$tr = "
	<tr>
		<td>Data is empty!</td>
	</tr>";
}else{
	$i = 1;
	$th .= "<tr><th>No</th>";
	foreach ($_SESSION as $k => $v) {
		$tr .= "<tr><td style='text-align:center;'>$i</td>";
		foreach ($v["fultonPage"] as $key => $value) {
			if($i==1){
				$th .= "<th>".str_replace("_", " ", $key)."</th>";
			}

			$align = "";
			if (strpos($value,'$') !== false){
				$align = "style='text-align:right;'";
			}

			$tr .= "<td $align>$value</td>";
		}
		if($i==1){
			$th .= "<th style='width:200px;'>Fulton Taxes</th>";
			$th .= "<th style='width:200px;'>Fulton Waste</th>";
			$th .= "<th style='width:600px;'>Fulton PDF</th>";
		}
		$tr .= "<td>";
		foreach ($v["fultonTaxes"] as $key => $value) {
			$tr .= $key." => ".$value."<br>";
		}
		$tr .= "</td>";
		$tr .= "<td>";
		foreach ($v["fultonWaste"] as $key => $value) {
			$tr .= $key." => ".$value."<br>";
		}
		$tr .= "</td>";
		$tr .= "<td>".$v["fultonPdf"]."</td>";
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
	<table id='fulton' style='width:7000px;border: 1px solid;' cellspacing='0'>
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