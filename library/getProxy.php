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
        preg_match_all($this->pattern, $proxyListUrlHtml, $matches);
        // echo "<pre>".print_r($matches,1)."</pre>";
        $proxy = $matches[0][array_rand($matches[0])];
        $this->setProxy($proxy);
        $this->setPort($proxy);
    }
    public function setProxy($match){
        $start = strpos($match, '<td>') + 4;
        $end = strpos($match, ':');
        $proxy = substr($match,$start,$end - $start);
        $this->proxy = $proxy;
    }
    public function setPort($match){
        $start = strpos($match, ':') + 1;
        $end = strpos($match, '</td');
        $port = substr($match,$start,$end - $start);
        $this->port = $port;
    }
}