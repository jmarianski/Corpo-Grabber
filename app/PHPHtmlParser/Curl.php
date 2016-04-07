<?php
namespace PHPHtmlParser;

use PHPHtmlParser\Exceptions\CurlException;

/**
 * Class Curl
 *
 * @package PHPHtmlParser
 */
class Curl implements CurlInterface
{

    /**
     * A simple curl implementation to get the content of the url.
     *
     * @param string $url
     * @return string
     * @throws CurlException
     */
    public function get($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/x-www-form-urlencoded"));
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie-name');  //could be empty, but cause problems on some hosts
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'tmp/cookie');  //could be empty, but cause problems on some hosts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANYSAFE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $content = curl_exec($ch);
        if ($content === false) {
            // there was a problem
            $error = curl_error($ch);
            throw new CurlException('Error retrieving "'.$url.'" ('.$error.')');
        }

        return $content;
    }
}
