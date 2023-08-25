<?php
Class KN_file_get_contents {
    private $Path;
    private $ch;


    public function __construct($Path,$accessToken)
    {
        $x = curl_init();
        // Nastavení URL adresy API, ze které budeme stahovat data
        curl_setopt($x, CURLOPT_URL,$Path);

        curl_setopt($x, CURLOPT_HTTPHEADER, array(
            "Content-type: application/json",
            "Authorization: Bearer $accessToken",
            "Content-Length: 500 "));

        $x = curl_init();
        // Nastavení URL adresy API, ze které budeme stahovat data
        curl_setopt($x, CURLOPT_URL,$Path);
        
        $txt = file_get_contents('http://localhost/proxy.txt');
        $items = explode(';', $txt);
        // Nastavení použití proxy serveru
        curl_setopt($x, CURLOPT_PROXY, $items[0].":".$items[1]);
        // Nastavení typu proxy serveru (HTTP nebo SOCKS5)
        curl_setopt($x, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        $this->ch = $x;
        // Nastavení typu proxy serveru (HTTP nebo SOCKS5)
        curl_setopt($x, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($x, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($x, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
        $this->ch = $x;

    }

    public function json() {
        $chA = $this->ch;

        // Nastavení, že chceme získat odpověď od API jako řetězec
        curl_setopt($chA , CURLOPT_RETURNTRANSFER, true);

        // Odeslání požadavku a získání odpovědi od API
        $text = curl_exec($chA);

        // Uzavření spojení
        curl_close($chA);
        return $text;
    }
}
?>