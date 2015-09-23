<?php

namespace Koshatul\HAProxyWeb\HTML\v1_0_0;

use Respect\Rest\Routable;
use Icecave\SemVer\Version;

class BaseHTML implements Routable
{
	protected $_request = null;

	protected function __construct()
	{

	}

	public function getVersion()
	{
		$myClass = get_called_class();
		// echo "myClass[0]: $myClass".PHP_EOL;
		$myClass = str_replace("Koshatul\\HAProxyWeb\\HTML\\", "", $myClass);
		// echo "myClass[1]: $myClass".PHP_EOL;
		if (strpos($myClass, "\\") !== false) {
			$myClass = substr($myClass, 0, strpos($myClass, "\\"));
		}
		if (substr($myClass, 0, 1) == "v") {
			$myClass = substr($myClass, 1);
		}
		// echo "myClass[8]: $myClass".PHP_EOL;
		$myClass = str_replace("_", ".", $myClass);
		// echo "myClass[9]: $myClass".PHP_EOL;
		if (Version::isValid($myClass)) {
			return Version::parse($myClass);
		} else {
			return Version::parse("0.0.0");
		}
	}

	public function initRequest($path = null)
	{
		if (!is_null($this->_request)) {
			return false;
		}

		$this->_request = new Request($path);

		return false;
	}

	public function json_encode($object, $response_code = 200)
	{
		switch ($response_code) {
			case 470:
				header($_SERVER["SERVER_PROTOCOL"]." 470 Invalid DeviceID", true, 470);
				break;
			default:
				// Do Nothing
		}
		header('Content-Type: application/json');
		echo json_encode($object);
	}

	public function get($path = null)
	{
		echo get_called_class().":GET(".var_export($path, true).")".PHP_EOL;
	}

	public function post($path = null)
	{
		echo get_called_class().":POST(".var_export($path, true).")".PHP_EOL;
	}

	public function put($path = null)
	{
		echo get_called_class().":PUT(".var_export($path, true).")".PHP_EOL;
	}

	public function delete($path = null)
	{
		echo get_called_class().":DELETE(".var_export($path, true).")".PHP_EOL;
	}

	public function _formatTime($secs) {
		//21h37m
		// echo "TIME START: $secs".PHP_EOL;
		$output = array();
		$seconds = $secs;
		$days = floor($seconds / 86400);
		$seconds -= ($days * 86400);
		if ($days > 0) { $output[] = $days."d"; }
		$hours = floor($seconds / 3600);
		$seconds -= ($hours * 3600);
		if ($hours > 0) { $output[] = $hours."h"; }
		$minutes = floor($seconds / 60);
		$seconds -= ($minutes * 60);
		if ($minutes > 0) { $output[] = $minutes."m"; }
		if ($seconds > 0) { $output[] = $seconds."s"; }
		// echo "TIME END: $output".PHP_EOL;

		$output = join("", array_slice($output, 0, 2));
		return $output;
	}

	public function _formatBytes($bytes) {
		// 52<span class=\"rls\">4</span>612
		$bytestring = number_format($bytes, 0, '.', ',');
		$bytestring = explode(',', $bytestring);
		$output = array();
		foreach ($bytestring as $byteseg) {
			array_push($output, substr($byteseg, 0, -1));
			array_push($output, "<span class=\"rls\">".substr($byteseg, -1)."</span>");
		}
		return join('', $output);
	}

	public function _formatStatus($serviceData) {
		// 52<span class=\"rls\">4</span>612
		return $this->_formatTime($serviceData->getLastChange())."&nbsp;".$serviceData->getStatus();
	}

	public function _formatCheckFromService($serviceData) {
		// 52<span class=\"rls\">4</span>612
		return $serviceData->getCheckStatus()."/".$serviceData->getCheckCode()." in ".$serviceData->getCheckDuration()."ms";
	}

	public function _formatActiveFromService($serviceData) {
		// 52<span class=\"rls\">4</span>612
		return ($serviceData->getActive() == 1) ? "Y" : "N";
	}

	public function _formatBackupFromService($serviceData) {
		// 52<span class=\"rls\">4</span>612
		return ($serviceData->getBackup() == 1) ? "Y" : "N";
	}

	public function _head() {
		$output  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'.PHP_EOL;
		$output .= '<html><head><title>Statistics Report for HAProxy</title>'.PHP_EOL;
		$output .= '<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">'.PHP_EOL;
		$output .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>';
		$output .= '<script src="/js/haproxy.js"></script>';
		$output .= '<style type="text/css"><!--'.PHP_EOL;
		$output .= 'body { font-family: arial, helvetica, sans-serif; font-size: 12px; font-weight: normal; color: black; background: white;}'.PHP_EOL;
		$output .= 'th,td { font-size: 10px;}'.PHP_EOL;
		$output .= 'h1 { font-size: x-large; margin-bottom: 0.5em;}'.PHP_EOL;
		$output .= 'h2 { font-family: helvetica, arial; font-size: x-large; font-weight: bold; font-style: italic; color: #6020a0; margin-top: 0em; margin-bottom: 0em;}'.PHP_EOL;
		$output .= 'h3 { font-family: helvetica, arial; font-size: 16px; font-weight: bold; color: #b00040; background: #e8e8d0; margin-top: 0em; margin-bottom: 0em;}'.PHP_EOL;
		$output .= 'li { margin-top: 0.25em; margin-right: 2em;}'.PHP_EOL;
		$output .= '.hr {margin-top: 0.25em; border-color: black; border-bottom-style: solid;}'.PHP_EOL;
		$output .= '.titre	{background: #20D0D0;color: #000000; font-weight: bold; text-align: center;}'.PHP_EOL;
		$output .= '.total	{background: #20D0D0;color: #ffff80;}'.PHP_EOL;
		$output .= '.frontend	{background: #e8e8d0;}'.PHP_EOL;
		$output .= '.socket	{background: #d0d0d0;}'.PHP_EOL;
		$output .= '.backend	{background: #e8e8d0;}'.PHP_EOL;
		$output .= '.active0	{background: #ff9090;}'.PHP_EOL;
		$output .= '.active1	{background: #ff9090;}'.PHP_EOL;
		$output .= '.active2	{background: #ffd020;}'.PHP_EOL;
		$output .= '.active3	{background: #ffffa0;}'.PHP_EOL;
		$output .= '.active4	{background: #c0ffc0;}'.PHP_EOL;
		$output .= '.active5	{background: #ffffa0;}'.PHP_EOL;
		$output .= '.active6	{background: #20a0ff;}'.PHP_EOL;
		$output .= '.active7	{background: #ffffa0;}'.PHP_EOL;
		$output .= '.active8 {background: #20a0FF;}'.PHP_EOL;
		$output .= '.active9	{background: #e0e0e0;}'.PHP_EOL;
		$output .= '.backup0	{background: #ff9090;}'.PHP_EOL;
		$output .= '.backup1	{background: #ff9090;}'.PHP_EOL;
		$output .= '.backup2	{background: #ff80ff;}'.PHP_EOL;
		$output .= '.backup3	{background: #c060ff;}'.PHP_EOL;
		$output .= '.backup4	{background: #b0d0ff;}'.PHP_EOL;
		$output .= '.backup5	{background: #c060ff;}'.PHP_EOL;
		$output .= '.backup6	{background: #90b0e0;}'.PHP_EOL;
		$output .= '.backup7	{background: #c060ff;}'.PHP_EOL;
		$output .= '.backup8	{background: #cc9900;}'.PHP_EOL;
		$output .= '.backup9	{background: #e0e0e0;}'.PHP_EOL;
		$output .= '.maintain	{background: #c07820;}'.PHP_EOL;
		$output .= '.rls      {letter-spacing: 0.2em; margin-right: 1px;}'.PHP_EOL;
		$output .= ''.PHP_EOL;
		$output .= 'a.px:link {color: #ffff40; text-decoration: none;}a.px:visited {color: #ffff40; text-decoration: none;}a.px:hover {color: #ffffff; text-decoration: none;}a.lfsb:link {color: #000000; text-decoration: none;}a.lfsb:visited {color: #000000; text-decoration: none;}a.lfsb:hover {color: #505050; text-decoration: none;}'.PHP_EOL;
		$output .= 'table.tbl { border-collapse: collapse; border-style: none;}'.PHP_EOL;
		$output .= 'table.tbl td { text-align: right; border-width: 1px 1px 1px 1px; border-style: solid solid solid solid; padding: 2px 3px; border-color: gray; white-space: nowrap;}'.PHP_EOL;
		$output .= 'table.tbl td.ac { text-align: center;}'.PHP_EOL;
		$output .= 'table.tbl th { border-width: 1px; border-style: solid solid solid solid; border-color: gray;}'.PHP_EOL;
		$output .= 'table.tbl th.pxname { background: #b00040; color: #ffff40; font-weight: bold; border-style: solid solid none solid; padding: 2px 3px; white-space: nowrap;}'.PHP_EOL;
		$output .= 'table.tbl th.empty { border-style: none; empty-cells: hide; background: white;}'.PHP_EOL;
		$output .= 'table.tbl th.desc { background: white; border-style: solid solid none solid; text-align: left; padding: 2px 3px;}'.PHP_EOL;
		$output .= ''.PHP_EOL;
		$output .= 'table.lgd { border-collapse: collapse; border-width: 1px; border-style: none none none solid; border-color: black;}'.PHP_EOL;
		$output .= 'table.lgd td { border-width: 1px; border-style: solid solid solid solid; border-color: gray; padding: 2px;}'.PHP_EOL;
		$output .= 'table.lgd td.noborder { border-style: none; padding: 2px; white-space: nowrap;}'.PHP_EOL;
		$output .= 'table.det { border-collapse: collapse; border-style: none; }'.PHP_EOL;
		$output .= 'table.det th { text-align: left; border-width: 0px; padding: 0px 1px 0px 0px; font-style:normal;font-size:11px;font-weight:bold;font-family: sans-serif;}'.PHP_EOL;
		$output .= 'table.det td { text-align: right; border-width: 0px; padding: 0px 0px 0px 4px; white-space: nowrap; font-style:normal;font-size:11px;font-weight:normal;}'.PHP_EOL;
		$output .= 'u {text-decoration:none; border-bottom: 1px dotted black;}'.PHP_EOL;
		$output .= 'div.tips {'.PHP_EOL;
 		$output .= ' display:block;'.PHP_EOL;
 		$output .= ' visibility:hidden;'.PHP_EOL;
 		$output .= ' z-index:2147483647;'.PHP_EOL;
 		$output .= ' position:absolute;'.PHP_EOL;
 		$output .= ' padding:2px 4px 3px;'.PHP_EOL;
 		$output .= ' background:#f0f060; color:#000000;'.PHP_EOL;
 		$output .= ' border:1px solid #7040c0;'.PHP_EOL;
 		$output .= ' white-space:nowrap;'.PHP_EOL;
 		$output .= ' font-style:normal;font-size:11px;font-weight:normal;'.PHP_EOL;
 		$output .= ' -moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;'.PHP_EOL;
 		$output .= ' -moz-box-shadow:gray 2px 2px 3px;-webkit-box-shadow:gray 2px 2px 3px;box-shadow:gray 2px 2px 3px;'.PHP_EOL;
		$output .= '}'.PHP_EOL;
		$output .= 'u:hover div.tips {visibility:visible;}'.PHP_EOL;
		$output .= '-->'.PHP_EOL;
		$output .= '</style></head>'.PHP_EOL;
		$output .= '<body><h1><a href="http://www.haproxy.org/" style="text-decoration: none;">HAProxy version TODO_VERSION, released TODO_VERSION</a></h1>'.PHP_EOL;
		$output .= '<h2>Statistics Report for pid TODO_PID: "TODO Server Name"</h2>'.PHP_EOL;
		$output .= '<hr width="100%" class="hr">'.PHP_EOL;

		return $output;
	}
	
	public function _foot() {
		$output = "";
		$output .= "</body>".PHP_EOL;
		$output .= "</html>";
		return $output;
	}
}
