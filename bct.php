<?php

# create and load the HTML
include('simple_html_dom.php');
require_once('inc/db.php');

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
set_time_limit(0);
ignore_user_abort(true);
ini_set('display_errors', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();

function pretty_print($a) {
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}

function extract_unit($string, $start, $end)
{
    $pos = stripos($string, $start);
    $str = substr($string, $pos);
    $str_two = substr($str, strlen($start));
    $second_pos = stripos($str_two, $end);
    $str_three = substr($str_two, 0, $second_pos);
    $unit = trim($str_three); // remove whitespaces
    return $unit;
}

function bitly_url_shorten($aurl, $access_token, $domain)
{
  $url = 'https://api-ssl.bitly.com/v3/shorten?access_token='.$access_token.'&longUrl='.urlencode($aurl).'&domain='.$domain;
  try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $output = json_decode(curl_exec($ch));
  } catch (Exception $e) {
  }
  if(isset($output)){return $output->data->url;}
}

function get_web_page($url){
        // $headers = array( 'Accept : */*' );
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // to return web page
            CURLOPT_HEADER         => true, // to return headers in addition to content
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_ENCODING       => "",   // to handle all encodings
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3 GTB5",     // who am i
            CURLOPT_AUTOREFERER    => true, // to set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,  // set a timeout on connect
            CURLOPT_TIMEOUT        => 120,  // set a timeout on response
            CURLOPT_MAXREDIRS      => 10,   // to stop after 10 redirects
            CURLINFO_HEADER_OUT    => true, // no header out
            CURLOPT_SSL_VERIFYPEER => false,// to disable SSL Cert checks
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            // CURLOPT_HTTPHEADER     => $headers,
        );
        //http://scraping.pro/http-cookie-handler-curl/
        //http://us3.php.net/manual/en/function.curl-setopt-array.php#89850
        $handle = curl_init( $url );
        curl_setopt_array( $handle, $options );

        // unlink('cookie.txt');
    // additional for storing cookie
        $tmpfname = 'cookie.txt';
        curl_setopt($handle, CURLOPT_COOKIEJAR, $tmpfname);
        curl_setopt($handle, CURLOPT_COOKIEFILE, $tmpfname);

        $raw_content = curl_exec( $handle );
        $err = curl_errno( $handle );
        $errmsg = curl_error( $handle );
        $header = curl_getinfo( $handle );
        curl_close( $handle );
    return $raw_content;
}

$db = new Db();

$ii = 0;
$postids = array();

$list = 1;
pretty_print("<h2>The Lastest Coin ANN in BCT</h2>");

$rows = $db -> select("SELECT `post_id` FROM `bct_post`");
foreach ($rows as $r => $row) {
    $postid = $row['post_id'];
    array_push($postids, $postid);
}

for ($i=1; $i <4 ; $i++) {
    $aurl = "https://bitcointalk.org/index.php?board=159.".$ii.";sort=first_post;desc;wap2";
    $str = get_web_page($aurl);
    $html = new simple_html_dom();
    $html->load($str);

    $posts_url = $html->find('p[class="windowbg"]');
    foreach ($posts_url as $ee => $e) {
        $post_url = $e->find('a',0)->href;
        $post_title = trim($e->find('a',0)->plaintext);
        if (strpos($post_url, 'topic') !== false){
            if (strpos($post_title, 'ANN') !== false) {
                $posts_id = extract_unit($post_url, "topic=", ".0;wap2");
                $posts_url = "https://bitcointalk.org/index.php?action=printpage;topic=".$posts_id;
                $aurl = "https://bitcointalk.org/index.php?topic=".$posts_id;
                $posts_str = get_web_page($posts_url);
                $posts_html = new simple_html_dom();
                $posts_html->load($posts_str);

                $posts_date = $posts_html->find('b',2)->plaintext;
                $timestamp = strtotime($posts_date);
                $post_date = date("Y-m-d H:i:s", $timestamp);

                // pretty_print($list.' - '.$posts_id.' - <a href="'.$aurl.'" target="_blank" >'.$post_title."</a>".' - '.$posts_Date);
                $list ++;
                sleep(1);
                $posts_html->clear();
                unset($posts_html);
                if (in_array($posts_id, $postids) == false) {
                $access_token = "f83c92fd96bd4692489731355569204f2c2c2089";
                $domain = "bit.ly";
                $short_url = bitly_url_shorten($aurl, $access_token, $domain);
                $db -> query("INSERT INTO `bct_post` (`post_id`,`post_title`,`post_date`,`post_url`) VALUES('$posts_id','$post_title','$post_date','$short_url')");
                }

            }

        }


    }
    echo "</br>Doen For Page ".$i;
    flush();
    usleep(2);

    $html->clear();
    unset($html);
    $ii = $ii + 9;
}

?>
