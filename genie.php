<?php
/*
Plugin Name: Genie
Plugin URI: http://meiclamo.net/2007/11/genie/
Description: 지니는 [genie isbn] 태그로 미리 정의된 템플릿에 따라 책 정보를 출력해줍니다.
Author: 智熏
Version: 0.0.6
Author URI: http://meiclamo.net/
*/ 

/*  Copyright 2008    (email : PLUGIN AUTHOR EMAIL)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('genie')) {
    class genie	{
		var $adminOptionsName = "genie_settings";
		
		function genie(){$this->__construct();}

		function __construct(){

			add_filter('the_content', array(&$this, 'genie_link'));
			add_action('admin_menu', array(&$this, 'add_admin_pages'));

			$this->adminOptions = $this->getAdminOptions();
			$this->intercept_trigger();
			$this->saveAdminOptions();
		}
		
		function intercept_trigger() {
			if (isset($_POST['template'])) $this->adminOptions['template'] = stripcslashes($_POST['template']);
			if (isset($_POST['ttbkey'])) $this->adminOptions['ttbkey'] = $_POST['ttbkey'];

			return FALSE;
		}
		
		function getAdminOptions() {
			
			$default_template = '<a href="BOOK_TTB_URL"><img src="BOOK_COVER_BIG" alt="BOOK_TITLE" style="border: 1px solid black;" /></a>';

			$adminOptions = array("template" => $default_template,
			"ttbkey" => "");
			$savedOptions = get_option($this->adminOptionsName);
			if (!empty($savedOptions)) {
				foreach ($savedOptions as $key => $option) {
					$adminOptions[$key] = $option;
				}
			}
			update_option($this->adminOptionsName, $adminOptions);

			$genie_settings['template'] = stripslashes($genie_settings['template']);

			return $adminOptions;
		}
		
		function saveAdminOptions(){
			update_option($this->adminOptionsName, $this->adminOptions);
		}
		
		function add_admin_pages(){
			add_submenu_page('options-general.php', "Genie", "Genie", 10, __FILE__, array(&$this,"genie_admin_page"));
		}
		
		function genie_admin_page() {
			$savedOptions = $this->getAdminOptions();
?>
	<div class="wrap">
		<h2>Genie Option</h2>
		<form action="" method="post">
		<table class="form-table">
			<tr>
				<th scope="row" valign="top">Template</th>
				<td><textarea id="template" name="template" rows="5" style="width: 98%;"><?php echo $savedOptions['template'];?></textarea></td>
			</tr>
			<tr>
				<th scope="row" valign="top">TTB Key</th>
				<td><input id="ttbkey" name="ttbkey" value="<?php echo $savedOptions['ttbkey'];?>" type="text" /> <a href="http://www.aladdin.co.kr/ttb/wguide.aspx?pn=signup">TTB Key 만들기</a><br />
				TTB Key를 입력하지 않으면, BOOK_TTB_URL이 BOOK_NO_TTB_URL로 인식됩니다.</td>
			</tr>
		</table>
		
		<h3>Available Genie Template Tags</h3>
		<dl>
			<dt>BOOK_TITLE </dt>
			<dd>책 제목 <small>(Ex. 페미니스트라는 낙인)</small></dd>
			<dt>BOOK_AUTHOR</dt>
			<dd>책 저자 <small>(Ex. 조주은 지음조주은)</small></dd>
			<dt>BOOK_TTB_URL</dt>
			<dd>알라딘 TTB 정보 포함 책 주소 - <small>(Ex. http://www.aladdin.co.kr/shop/wproduct.aspx?ISBN=8992484100&copyPaper=1&ttbkey=YOUR-TTBKEY)</small></dd>
			<dt>BOOK_NO_TTB_URL</dt>
			<dd>알라딘 TTB 정보 미포함 책 주소 - <small>(Ex. http://www.aladdin.co.kr/shop/wproduct.aspx?ISBN=8992484100&copyPaper=1)</small></dd>
			<dt>BOOK_DAUM_URL</dt>
			<dd>다음 책 주소 <small>(Ex. <a href="http://book.daum.net/bookdetail/book.do?bookid=KOR9788932452203">http://book.daum.net/bookdetail/book.do?bookid=KOR9788932452203</a>)</small></dd>
			<dt>BOOK_COVER_BIG</dt>
			<dd>큰 크기 표지 이미지 주소 <small>(Ex. <a href="http://image.aladdin.co.kr/cover/cover/8992484100_1.jpg">http://image.aladdin.co.kr/cover/cover/8992484100_1.jpg</a>)</small></dd>
			<dt>BOOK_COVER_MIDDLE</dt>
			<dd>중간 크기 표지 이미지 주소 <small>(Ex. <a href="http://image.aladdin.co.kr/coveretc/book/coversum/8992484100_1.jpg">http://image.aladdin.co.kr/coveretc/book/coversum/8992484100_1.jpg</a>)</small></dd>
			<dt>BOOK_COVER_SMALL</dt>
			<dd>작은 크기 표지 이미지 주소 <small>(Ex. <a href="http://image.aladdin.co.kr/coveretc/book/coveroff/8992484100_1.jpg">http://image.aladdin.co.kr/coveretc/book/coveroff/8992484100_1.jpg</a>)</small></dd>
			<dt>BOOK_COVER_MINI</dt>
			<dd>초-작은 크기 이미지 주소 <small>(Ex. <a href="http://image.aladdin.co.kr/coveretc/book/covermini/8992484100_1.jpg">http://image.aladdin.co.kr/coveretc/book/covermini/8992484100_1.jpg</a>)</small></dd>
		</dl>
		<p class="submit"><input name="submit" value="Save Changes" type="submit" /></p>
		</form>
	</div>
			<?php
		} 
		
		function check_cover_file($url) { // provided that either jpg or gif file exists
			if(!$this->url_exists($url)) {
				if (preg_match("/gif/i", $url)) $url = str_replace("gif", "jpg", $url);
				else if (preg_match("/jpg/i", $url)) $url = str_replace("jpg", "gif", $url);
			}
			return $url;
		}
		function url_exists($url){ //referred from http://kr2.php.net/manual/en/function.file-exists.php#78656
			$url = str_replace("http://", "", $url);
			if (strstr($url, "/")) {
				$url = explode("/", $url, 2);
				$url[1] = "/".$url[1];
			} else {
				$url = array($url, "/");
			}

			$fh = @fsockopen($url[0], 80);
			if ($fh) {
				fputs($fh,"GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
				if (fread($fh, 22) == "HTTP/1.1 404 Not Found") { return FALSE; }
				else { return TRUE;    }

			} else { return FALSE;}
		}
		
		function isbn13_to_10($isbnold) {
			if (strlen($isbnold) == 10) return $isbnold;

			$isbn = substr($isbnold, 3, 9);
			
			for ($i = 0; $i < 9; $i++) {
				$weight = 10 - $i;
				$check_sum_total = $check_sum_total + ($isbn{$i} * $weight);  
			}
			$new_check_sum = 11 - ($check_sum_total % 11);
			if ($new_check_sum == 10) $new_check_sum =  'X';
			if ($new_check_sum == 11) $new_check_sum =  0;

			return ($isbn.$new_check_sum);
		}

		function isbn10_to_13($isbnold) {
			if (strlen($isbnold) == 13) return $isbnold;

			$isbn = '978'. substr($isbnold, 0, 9);
			
			for ($i = 0; $i < 12; $i++) {
				$weight = ($i % 2 == 0) ? 1 : 3;
				$check_sum_total = $check_sum_total + ($isbn[$i] * $weight);
			}
			$new_check_sum = 10 - ($check_sum_total % 10);	
			
			return ($isbn.$new_check_sum);
		}
		
		function genie_link($text) {
			$savedOptions = $this->getAdminOptions();
			
			preg_match_all("/\[genie (\d{10}|\d{9}x|\d{2}-\d{5}-\d{2}-[\dx]|97[89]-\d{1}-\d{2}-\d{6}-\d{1}|\d{13})\]/i", $text, $genie_tag);
			$isbn = str_replace('-', '', $genie_tag[1]);
			$book_cnt = count($genie_tag[0]);
			for ( $i = 0; $i < $book_cnt; $i++ ) {
				$curr_isbn = (strlen($isbn[$i]) == 13) ? $this->isbn13_to_10($isbn[$i]) : $isbn[$i];
				$book = $this->get_book_meta($curr_isbn);

				$book_cover_big = $this->check_cover_file(str_replace('/coveretc/book/coversum/', '/cover/cover/', $book['COVER']));
				$book_cover_small = $this->check_cover_file(str_replace('/coveretc/book/coversum/', '/coveretc/book/coveroff/', $book['COVER']));
				$book_cover_mini = $this->check_cover_file(str_replace('/coveretc/book/coversum/', '/coveretc/book/covermini/', $book['COVER']));
				if (!empty($savedOptions['ttbkey'])) {
					$result_tag = str_replace('BOOK_TTB_URL', str_replace('&', '&amp;', $book['LINK']), $savedOptions['template']);
				}
				else {
					$result_tag = str_replace('BOOK_TTB_URL', str_replace('&', '&amp;', preg_replace('/&ttbkey=.*$/i', '', $book['LINK'])), $savedOptions['template']);
				}
				$result_tag = str_replace('BOOK_NO_TTB_URL', str_replace('&', '&amp;', preg_replace('/&ttbkey=.*$/i', '', $book['LINK'])), $result_tag);

				if (substr($curr_isbn, 0, 2) == 89)
					$result_tag = str_replace('BOOK_DAUM_URL', 'http://book.daum.net/bookdetail/book.do?bookid=KOR' . $this->isbn10_to_13($curr_isbn), $result_tag);
				elseif (substr($curr_isbn, 0, 1) < 2 ) // 0 or 1
					$result_tag = str_replace('BOOK_DAUM_URL', 'http://book.daum.net/bookdetail/book.do?bookid=ENG' . $this->isbn10_to_13($curr_isbn), $result_tag);
				else
					$result_tag = str_replace('BOOK_DAUM_URL', str_replace('&', '&amp;', $book['LINK']), $result_tag);
				$result_tag = str_replace('BOOK_COVER_BIG', $book_cover_big, $result_tag);
				$result_tag = str_replace('BOOK_COVER_MIDDLE', $book['COVER'], $result_tag);
				$result_tag = str_replace('BOOK_COVER_SMALL', $book_cover_small, $result_tag);
				$result_tag = str_replace('BOOK_COVER_MINI', $book_cover_mini, $result_tag);
				$result_tag = str_replace('BOOK_PUBLISHER', $book['PUBLISHER'], $result_tag);
				$result_tag = str_replace('BOOK_TITLE', $book['TITLE'], $result_tag);
				$book['AUTHOR'] = preg_replace("/(.*?) 지음.*/",'$1', $book['AUTHOR']);
				$result_tag = str_replace('BOOK_AUTHOR', $book['AUTHOR'], $result_tag);
				$text = str_replace($genie_tag[0][$i], $result_tag, $text);
			}
			return $text;
		}		
	
		function get_book_meta($isbn) {
			$savedOptions = $this->getAdminOptions();
			
			$url = 'http://www.aladdin.co.kr/ttb/api/ItemLookUp.aspx';
			$querySet = array(
				'TTBKey' => (!empty($savedOptions['ttbkey'])) ? $savedOptions['ttbkey'] : 'ttbmeiclamo1539002',
				'ItemId' => $isbn,
				'ItemIdType' => 'ISBN',
				'Cover' => $savedOptions['bookcover_size'],
				'Output' => 'XML',
				'Version' => '20070901'
			); 

			$query = http_build_query($querySet);
			$url = $url.'?'.$query;
			$req = new GenieHTTPRequest($url);
			$body = $req->DownloadToString();

			$errMsg = '';
			$isError = strpos($body, "<error xml");

			if ($isError){
				ereg ("\<errorMessage\>(.*)\<\/errorMessage", $body, $regs);
				$errMsg = $regs[0];
			}  else {
				$xmlParser = new AladdinXMLParser();
				$parser = @xml_parser_create();
				if (!is_resource($parser)){
					die("PHP XML parser에러");
				} else {
					xml_set_object($parser, $xmlParser);
					xml_set_element_handler($parser, "startHandler", "endHandler");
					xml_set_character_data_handler($parser, "cdataHandler");
				}

				if (!xml_parse($parser, $body, true)) {
					printf("XML error: %s at line %d\n",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser));
				}

				if (is_resource($parser)) {
					xml_parser_free($parser);
					unset( $parser );
				}
			}

			return $xmlParser->itemList[0];
		}
    }
}

class GenieHTTPRequest
{
	var $_fp;        // HTTP socket
	var $_url;        // full URL
	var $_host;        // HTTP host
	var $_protocol;    // protocol (HTTP/HTTPS)
	var $_uri;        // request URI
	var $_port;        // port
		
	// scan url
	function _scan_url() {
		$req = $this->_url;
		
		$pos = strpos($req, '://');
		$this->_protocol = strtolower(substr($req, 0, $pos));
		
		$req = substr($req, $pos+3);
		$pos = strpos($req, '/');
		if($pos === false)
			$pos = strlen($req);
		$host = substr($req, 0, $pos);
		
		if(strpos($host, ':') !== false)
		{
			list($this->_host, $this->_port) = explode(':', $host);
		}
		else 
		{
			$this->_host = $host;
			$this->_port = ($this->_protocol == 'https') ? 443 : 80;
		}
		
		$this->_uri = substr($req, $pos);
		if($this->_uri == '')
				$this->_uri = '/';
	}
	
	// constructor
	function GenieHTTPRequest($url)	{
		$this->_url = $url;
		$this->_scan_url();
	}
	
	// download URL to string
	function DownloadToString()	{
		$crlf = "\r\n";
		
		// generate request
		$req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf
				.    'Host: ' . $this->_host . $crlf
				.    $crlf;
		
		// fetch
		$this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port);
		fwrite($this->_fp, $req);
		while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp))
				$response .= fread($this->_fp, 1024);
		fclose($this->_fp);
		
		// split header and body
		$pos = strpos($response, $crlf . $crlf);
		if($pos === false)
				return($response);
		$header = substr($response, 0, $pos);
		$body = substr($response, $pos + 2 * strlen($crlf));
		
		// parse headers
		$headers = array();
		$lines = explode($crlf, $header);
		foreach($lines as $line)
				if(($pos = strpos($line, ':')) !== false)
						$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
		
		// redirection?
		if(isset($headers['location']))
		{
				$http = new GenieHTTPRequest($headers['location']);
				return($http->DownloadToString($http));
		}
		else 
		{
				return($body);
		}
	}
}

class AladdinXMLParser {
	var $inItems = FALSE;
	var $currentElement = '';
	var $itemInfo = array();
	var $itemList = array();

	function startHandler($parser, $element, $attr){
			if($element=="ITEM") { $this->inItems = TRUE; }
			$this->currentElement = $element;
	}

	function endHandler($parser, $element){
		if($element=="ITEM") { 
			$this->itemList[] = $this->itemInfo;
			$this->itemInfo = array();
			$this->inItems = FALSE; 
		}
		$this->currentElement = '';
	}
	function cdataHandler($parser, $cdata){
		if($this->inItems==TRUE){
			if($this->currentElement=="TITLE"){
				$this->itemInfo["TITLE"] = $cdata;
			} else if($this->currentElement=="LINK"){
				$this->itemInfo["LINK"] = $this->itemInfo["LINK"].$cdata;
			}  else if($this->currentElement=="AUTHOR"){
				$this->itemInfo["AUTHOR"] = $this->itemInfo["AUTHOR"].$cdata;
			}  else if($this->currentElement=="COVER"){
				$this->itemInfo["COVER"] = $this->itemInfo["COVER"].$cdata;
			}  else if($this->currentElement=="PUBLISHER"){
				$this->itemInfo["PUBLISHER"] = $this->itemInfo["PUBLISHER"].$cdata;
			}
		}
	}
}

//instantiate the class
if (class_exists('genie')) {
	$genie = new genie();
}

?>
