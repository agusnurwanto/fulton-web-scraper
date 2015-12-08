<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
use League\Csv\Reader;
define("RESULT_FILE", "database/resultScrapping.json");

// echo "<pre>". print_r($_FILES,1) ."</pre>";
// die();

$res = array( "error" => 0 );
if((!empty($_POST['action']) && $_POST['action']=="read_csv") 
	|| (!empty($_FILES) && $_FILES['async-upload']["type"]=="text/csv")){
	$file = handle_upload();
	if(empty($file["error"])){
		$csv = Reader::createFromPath($file["file"]);
		$csvData = $csv->setOffset(1)->fetchAll();
		$keys = [];
		foreach ($csvData as $k => $v) {
			if(!empty($v[1])){
				$key = getKey($v[1]);
				$keys[] = $key;
			}else if(!empty($v[0])){
				$key = getKey($v[0]);
				$keys[] = $key;
			}
		}
		$allData = readResultFile();
		foreach ($allData as $k => $v) {
			$key = getKey($v->parselNumber);
			$cek = array_search($key, $keys);
			if($cek>=0){
				unset($keys[$cek]);
			}
		}
		$res['msg'] = [];
		$res['msg_err'] = [];
		$res['key'] = count($allData);
		foreach ($keys as $key => $value) {
			// if(strlen($value)<13){
			// 	$res['msg_err'][] = $value;
			// }else{
				$res['msg'][] = $value;
			// }
		}
	}else{
		$res = $file;
	}
}else{
	$res["error"] = 1;
	$res["msg"] = $_FILES;
}

function readResultFile(){
	if(!file_exists(RESULT_FILE)){
		file_put_contents(RESULT_FILE, json_encode(array()));
	}
	$oldData = file_get_contents(RESULT_FILE);
	$data = array();
	if(!empty($oldData)){
		$data = json_decode($oldData);
	}
	return $data;
}

function getKey($string){
	// $number = preg_replace( '/[^0-9]/', '', $string );
	// return $number;
	return $string;
}


function handle_upload(){
	$res = array("error" => 0);
	
	// fix error upload;
	// $res["file"] = __DIR__.'/Fulton.csv';
	// return $res;

	try {
	    if (!isset($_FILES['async-upload']['error']) || is_array($_FILES['async-upload']['error'])) {
	        throw new RuntimeException('Invalid parameters.');
	    }
	    switch ($_FILES['async-upload']['error']) {
	        case UPLOAD_ERR_OK:
	            break;
	        case UPLOAD_ERR_NO_FILE:
	            throw new RuntimeException('No file sent.');
	        case UPLOAD_ERR_INI_SIZE:
	        case UPLOAD_ERR_FORM_SIZE:
	            throw new RuntimeException('Exceeded filesize limit.');
	        default:
	            throw new RuntimeException('Unknown errors.');
	    }
	    if ($_FILES['async-upload']['size'] > 100000000) {
	        throw new RuntimeException('Exceeded filesize limit.');
	    }
	    $finfo = new finfo(FILEINFO_MIME_TYPE);
	    if (false === $ext = array_search(
	        $finfo->file($_FILES['async-upload']['tmp_name']),
	        array( 'csv' => 'text/plain' ),
	        true
	    )) {
	        throw new RuntimeException('Invalid file format.'.$finfo->file($_FILES['async-upload']['tmp_name']));
	    }
	    if (!move_uploaded_file( $_FILES['async-upload']['tmp_name'], './uploads/fulton.csv')) {
	        throw new RuntimeException('Failed to move uploaded file.');
	    }
	    $res["file"] = __DIR__.'/uploads/fulton.csv';
	} catch (RuntimeException $e) {
		$res["error"] = 1;
	    $res["msg"] = $e->getMessage();
	}
	return $res;
}

die(json_encode($res));