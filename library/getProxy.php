<?php
class Proxy{
    private $proxyListUrl = 'http://www.ip-adress.com/proxy_list/';
    private $pattern = '(<td>[0-9.:]*</td>[\s]*?<td>Elite</td>)';
    private $proxy = '';
    private $port = '';
    public function setUrl($url){
        $this->proxyListUrl = $url;
    }
    public function getUrl(){
        return $this->proxyListUrl;
    }
    public function setPattern($pattern){
        $this->pattern = $pattern;
    }
    public function getPattern(){
        return $this->pattern;
    }
    public function getProxy(){
        return $this->proxy;
    }
    public function getPort(){
        return $this->port;
    }
    public function setRandomProxyAndPort(){
        $ch = curl_init($this->proxyListUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $proxyListUrlHtml = curl_exec($ch);
        curl_close($ch);
        preg_match($this->pattern, $proxyListUrlHtml, $matches);
        $this->setProxy($matches);
        $this->setPort($matches);
    }
    public function setProxy($matches){
        $start = strpos($matches[0], '<td>') + 4;
        $end = strpos($matches[0], ':');
        $proxy = substr($matches[0],$start,$end - $start);
        $this->proxy = $proxy;
    }
    public function setPort($matches){
        $start = strpos($matches[0], ':') + 1;
        $end = strpos($matches[0], '</td');
        $port = substr($matches[0],$start,$end - $start);
        $this->port = $port;
    }
}