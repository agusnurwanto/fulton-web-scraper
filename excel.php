<?php
session_start();

if(!empty($_POST['firstScrape'])){
	session_destroy();
}

if(!empty($_POST["setSession"])){
	unset($_POST["setSession"]);
	unset($_POST["firstScrape"]);
	$key = $_POST["key"];
	unset($_POST["key"]);
	foreach ($_POST as $k => $v) {
		$_SESSION["key_".$key][$k] = $v;
	}
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
		foreach ($v as $key => $value) {
			if($i==1){
				$th .= "<th>".str_replace("_", " ", $key)."</th>";
			}

			$align = "";
			if (strpos($value,'$') !== false){
				$align = "style='text-align:right;'";
			}
			
			if (strpos($value,'http://') !== false){
				$value = "<a href='$value' target='blank'>$value</a>";
			}

			$tr .= "<td $align>$value</td>";
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