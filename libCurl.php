<?php

class LibCurl
{

    private $cookieFile = 'cookies-prom-ua.txt';
    //private $url = 'https://prom.ua/ua/Tehnika-i-elektronika';
    //private $url = 'https://my.prom.ua/cms/order';
    //private $url = 'https://my.prom.ua/cms/order/context';
    private $url = 'https://my.prom.ua/cms/order/context?page=1&per_page=20';
    //private $url = 'https://my.prom.ua/cms/order/context?page=2&per_page=20';

    public function init()
    {
        $cookies = $this->readCookies();
        $cookies = $this->parseCookies($cookies);

        $curl = $this->getCurl();
        $this->setCookies($curl, $cookies);

        $result = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerStr = substr($result, 0, $headerSize);
        $bodyStr = substr($result, $headerSize );

        curl_close($curl);

        if ($httpCode != 200){
            die("http code != 200");
        }

        $context = json_decode($bodyStr);

        echo "<pre>";
        //var_dump($context->status);
        var_dump($context->context->orders[0]);
        //var_dump($context->orders);
        echo "</pre>";

        //echo "<pre>";
        //var_dump($headerSize);
        //var_dump($headerStr);
        //var_dump($bodyStr);
        //echo "</pre>";
    }

    private function getCurl()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HEADER  => true,
            //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //CURLOPT_POSTFIELDS => json_encode($data) , // отправка кода
        ));

        return $curl;
    }

    private function setCookies($curl, $cookies)
    {
        $data = [];
        foreach ($cookies as $line){
            array_push($data, "{$line['name']}={$line['value']}");
        }

        $data = implode(';', $data);

        curl_setopt($curl, CURLOPT_COOKIE , $data);
    }

    private function readCookies()
    {
        if (!is_file($this->cookieFile)) {
            die("Cookie file \"{$this->cookieFile}\" not found!");
        }

        return file_get_contents($this->cookieFile);

    }

    private function parseCookies($cookies)
    {
        $cookies = explode("\n", $cookies);

        $result = [];

        foreach ($cookies as $key => $line) {
            if ($line == '') {
                unset($cookies[$key]);
                continue;
            }

            $line = preg_split("/\t+/", $line);

            $result[$key] = [
                'domain' => $line[0],
                'httpOnly' => $line[1],
                'secure' => $line[3],
                'ttl' => $line[4],
                'name' => $line[5],
                'value' => $line[6],
            ];
        }

        return $result;

    }

}