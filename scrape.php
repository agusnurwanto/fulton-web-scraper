<?php
require __DIR__ . '/vendor/autoload.php';

// https://github.com/dompdf/dompdf
define('DOMPDF_ENABLE_AUTOLOAD', false);
require_once __DIR__ . '/vendor/dompdf/dompdf/dompdf_config.inc.php';

$url["home"] = "http://qpublic9.qpublic.net/ga_search_dw.php?county=ga_fulton";
$url["search"] = "http://qpublic9.qpublic.net/ga_search_dw.php?county=ga_fulton&search=parcel";
$url["all_search"] = "http://qpublic9.qpublic.net/ga_alsearch_dw.php";
$url["detail"] = "http://qpublic9.qpublic.net/ga_display_dw.php?county=ga_fulton&KEY=";
$url["FultonTaxes"] = "https://www.fultoncountytaxes.org/property-taxes/TaxBill?ParcelID=";
$url["FultonWaste"] = "https://www.fultoncountytaxes.org/solid-waste/TaxBill?ParcelID=";

if(empty($_POST['getDetail'])){
	die(json_encode(array( "error"=>1, "msg"=>"undefained param!")));
}else{
	$key = $_POST['key'];
	$res["error"] = 0;
	$res["msg"]["fulton page"] = getDetail($key);
	$res["msg"]["fulton taxes"] = getDetailFultonTaxes($key);
	$res["msg"]["fulton waste"] = getDetailFultonWaste($key);
	$url = dirname("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"). "/pdf/".rawurlencode($key);
	$pdfFultonPage = $url."_pdfFultonPage.html";
	$pdfFultonTaxes = $url."_pdfFultonTaxes.pdf";
	$pdfFultonWaste = $url."_pdfFultonWaste.pdf";
	$res["msg"]["pdf"] = '<a href="'.$pdfFultonPage.'" target="blank">'.$pdfFultonPage.'</a><br><a href="'.$pdfFultonTaxes.'" target="blank">'.$pdfFultonTaxes.'</a><br><a href="'.$pdfFultonWaste.'" target="blank">'.$pdfFultonWaste.'</a>';
	
	// http://www.sitepoint.com/convert-html-to-pdf-with-dompdf/
	$dompdf = new DOMPDF();
	$dompdf->load_html($res["msg"]["fulton taxes"]);
	$dompdf->render();
	$output = $dompdf->output();
	file_put_contents(__DIR__ . "/pdf/".$key."_pdfFultonTaxes.pdf", $output);

	$dompdf = new DOMPDF();
	$dompdf->load_html($res["msg"]["fulton waste"]);
	$dompdf->render();
	$output = $dompdf->output();
	file_put_contents(__DIR__ . "/pdf/".$key."_pdfFultonWaste.pdf", $output);

	file_put_contents(__DIR__ . "/pdf/".$key."_pdfFultonPage.html", $res["msg"]["fulton page"]);

	echo json_encode($res);
}

function getDetailFultonWaste($id){
	global $url;
	$body = request(array('url'=>$url["FultonWaste"].rawurlencode($id)."&Year="));
	return $body;
}

function getDetailFultonTaxes($id){
	global $url;
	// http://stackoverflow.com/questions/5657382/curl-php-restful-service-always-returning-false
	$body = request(array('url'=>$url["FultonTaxes"].rawurlencode($id)."&Year="));
	return $body;
}

function getDetail($id){
	global $url;
	$body = request(array('url'=>$url["detail"].rawurlencode($id)));
	return $body;
}

function getPageResultSearch($id){
	global $url;
	$param = array(
			"BEGIN"			=>	"0",
			"INPUT"			=>	$id,
			"searchType"	=>	"parcel_id",
			"county"		=>	"ga_fulton",
			"Parcel_Search"	=>	"Search By Parcel ID"
		);
	$body = request(array(
			"url"	=> $url["all_search"], 
			"param"	=> $param
		));
	return $body;
}

function getPageSearch(){
	global $url;
	$body = request(array('url'=>$url["search"]));
	return $body;
}

function getHome(){
	global $url;
	$body = request(array('url'=>$url["home"]));
	return $body;
}

// http://www.jacobward.co.uk/using-proxies-for-scraping-with-php-curl/
function getProxy(){
	if(!empty($_GET['radomProxy'])){
		require __DIR__ . '/library/getProxy.php';
		$hoge = new Proxy();
		$hoge->setRandomProxyAndPort();
		$proxy = $hoge->getProxy().":".$hoge->getPort();
		return $proxy;
	}else{
		return false;
	}
}

function request($option){
	$url = $option['url'];
	$ch = curl_init();
	$proxy = getProxy();

	if (isset($proxy)) {
	    curl_setopt($ch, CURLOPT_PROXY, $proxy);
	}

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