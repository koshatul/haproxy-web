<?php

namespace Koshatul\HAProxyWeb\HTML\v1_0_0;

use \BigWhoop\HAProxyAPI;

class Stats extends BaseHTML {
	protected $_message = null;

	public function __construct() {

	}

	public function post($path = null) {
		// header("Refresh: 60");
		// echo "POST DATA".PHP_EOL;
		// print_r($_REQUEST);

		$servers = \Koshatul\Config\Config::Get('haproxyweb/server');
		$api = array();
		foreach ($servers as $server => $serverURI) {
			$api[$server] = new HAProxyAPI\API($serverURI, \Koshatul\Config\Config::Get('haproxyweb/username'), \Koshatul\Config\Config::Get('haproxyweb/password'));
		}

		if (array_key_exists('s', $_REQUEST) and is_array($_REQUEST['s'])) {
			$actionList = array();
			foreach ($_REQUEST['s'] as $service) {
				$action = $_REQUEST['action'];
				switch ($action) {
					case 'drain':
					case 'ready':
					case 'maint':
						$actionList[] = $this->executeAPICommand($action.'Server', $api, $service);
						break;
					default:
						throw new ServiceLineSummaryException("Unsupported Action: $action");
				}
			}
			$this->_message = $actionList;
		}

		$this->get($path);
	}

	public function get($path = null)
	{
		// header("Refresh: 60");
		$servers = \Koshatul\Config\Config::Get('haproxyweb/server');
		$stats = new \Koshatul\HAProxyWeb\Helper\GetStats($servers);
		$stats->process();

		$output = "";
		$output .= $this->_head();
		$output .= $this->body($stats->getData());
		$output .= $this->_foot();

		echo $output;
	}

	public function executeAPICommand($action, $api, $service) {
		$message = null;

		$serviceDetail = explode('/', $service, 3);
		if (count($serviceDetail) != 3) {
			throw new ServiceLineSummaryException("Invalid Service Detail: $service");
		}
		$serviceDetail = array_combine(array('backend', 'service', 'server'), $serviceDetail);
		// print_r($serviceDetail);

		try {
			$status = $api[$serviceDetail['server']]->execute($action, array('backend' => $serviceDetail['backend'], 'server' => $serviceDetail['service']));
			if ($status) {
				$message = "$action on ".$service." successful";
				// Action had an effect
			} else {
				// Action had no effect
				$message = "$action on ".$service." failed";
			}
		} catch (HAProxyAPI\Client\Exception $e) {
			// Server error
			$message = "$action on ".$service." server error";
		} catch (HAProxyAPI\Command\Exception $e) {
			// Data error
			$message = "$action on ".$service." data error";
		}
		return $message;
	}

	public function body($data) {
		$output = "";

		if (!is_null($this->_message)) {
			$output .= "<p/>";
			if (is_array($this->_message)) {
				foreach ($this->_message as $message) {
					$output .= "<div class=\"active4\"><a class=\"lfsb\" href=\"/\" title=\"Remove this message\">[X]</a> ".$message."</div>";
				}
			} else {
				$output .= "<div class=\"active4\"><a class=\"lfsb\" href=\"/\" title=\"Remove this message\">[X]</a> ".$this->_message."</div>";
			}
			$output .= "<p/>";
		}
		// print_r($data);
		// die();
		$groupOutput = array();
		foreach ($data as $group => $groupData) {
			$groupOutput[] = $this->_proxy($group, $groupData, $this->_getCheckboxNeededForGroup($groupData));
		}
		$output .= join("<p/>", $groupOutput);
		return $output;
	}

	public function _proxy($group, $groupData, $showCheckbox = false) {
		$output = "";
		$output .= "<form action=\"/\" method=\"post\">";
		$output .= "<table class=\"tbl\" width=\"100%\">";
		$output .= "<tr class=\"titre\"><th class=\"pxname\" width=\"10%\"><a name=\"".$group."\"></a><u><a class=px href=\"#".$group."\">".$group."</a><div class=tips>".$group."</div></u></th><th class=\"desc\" width=\"90%\">&nbsp;</th></tr>";
		$output .= "</table>";
		$output .= "<table class=\"tbl\" width=\"100%\">";
			$output .= "<tr class=\"titre\">";
				if ($showCheckbox) {
					$output .= "<th rowspan=2 width=1></th>";
				}
				$output .= "<th rowspan=2></th>";
				$output .= "<th colspan=3>Queue</th>";
				$output .= "<th colspan=3>Session rate</th>";
				$output .= "<th colspan=6>Sessions</th>";
				$output .= "<th colspan=2>Bytes</th>";
				$output .= "<th colspan=2>Denied</th>";
				$output .= "<th colspan=3>Errors</th>";
				$output .= "<th colspan=2>Warnings</th>";
				$output .= "<th colspan=9>Server</th>";
			$output .= "</tr>";
			$output .= "<tr class=\"titre\">";
				$output .= "<th>Cur</th>";
				$output .= "<th>Max</th>";
				$output .= "<th>Limit</th>";
				$output .= "<th>Cur</th>";
				$output .= "<th>Max</th>";
				$output .= "<th>Limit</th>";
				$output .= "<th>Cur</th>";
				$output .= "<th>Max</th>";
				$output .= "<th>Limit</th>";
				$output .= "<th>Total</th>";
				$output .= "<th>LbTot</th>";
				$output .= "<th>Last</th>";
				$output .= "<th>In</th>";
				$output .= "<th>Out</th>";
				$output .= "<th>Req</th>";
				$output .= "<th>Resp</th>";
				$output .= "<th>Req</th>";
				$output .= "<th>Conn</th>";
				$output .= "<th>Resp</th>";
				$output .= "<th>Retr</th>";
				$output .= "<th>Redis</th>";
				$output .= "<th>Status</th>";
				$output .= "<th>LastChk</th>";
				$output .= "<th>Wght</th>";
				$output .= "<th>Act</th>";
				$output .= "<th>Bck</th>";
				$output .= "<th>Chk</th>";
				$output .= "<th>Dwn</th>";
				$output .= "<th>Dwntme</th>";
				$output .= "<th>Thrtle</th>";
			$output .= "</tr>";


			$serviceNames = array();
			foreach ($groupData as $serviceData) {
				$serviceNames[$serviceData->getServiceName()] = 1;
			}
			$serviceNames = array_keys($serviceNames);
			if (array_key_exists('FRONTEND', $serviceNames)) {
				array_unshift($serviceNames, 'FRONTEND');
			}
			if (array_key_exists('BACKEND', $serviceNames)) {
				array_push($serviceNames, 'BACKEND');
			}
			natsort($serviceNames);
			try {
				$output .= $this->_getServiceLineSummary('FRONTEND', $groupData, $showCheckbox);
				$output .= $this->_getServiceLines('FRONTEND', $groupData, $showCheckbox);
			} catch (ServiceLineSummaryException $e) {
				// Do Nothing
			}
			foreach ($serviceNames as $serviceName) {
				if ($this->_serviceLineClassByServiceName($serviceName) == "socket") {
					$output .= $this->_getServiceLineSummary($serviceName, $groupData, $showCheckbox);
					$output .= $this->_getServiceLines($serviceName, $groupData, $showCheckbox);
				}
			}
			foreach ($serviceNames as $serviceName) {
				$serviceClass = $this->_serviceLineClassByServiceName($serviceName);
				switch ($serviceClass) {
					case 'frontend':
					case 'backend':
					case 'socket':
						break;
					default:
						$output .= $this->_getServiceLineSummary($serviceName, $groupData, $showCheckbox);
						$output .= $this->_getServiceLines($serviceName, $groupData, $showCheckbox);
						break;
				}
			}
			try {
				$output .= $this->_getServiceLineSummary('BACKEND', $groupData, $showCheckbox);
				$output .= $this->_getServiceLines('BACKEND', $groupData, $showCheckbox);
			} catch (ServiceLineSummaryException $e) {
				// Do Nothing
			}

		$output .= "</table>";

		if ($showCheckbox) {
			$output .= "Choose the action to perform on the checked servers : <select name=action><option value=\"\"></option><option value=\"ready\">Set state to READY</option><option value=\"drain\">Set state to DRAIN</option><option value=\"maint\">Set state to MAINT</option><option value=\"dhlth\">Health: disable checks</option><option value=\"ehlth\">Health: enable checks</option><option value=\"hrunn\">Health: force UP</option><option value=\"hnolb\">Health: force NOLB</option><option value=\"hdown\">Health: force DOWN</option><option value=\"dagent\">Agent: disable checks</option><option value=\"eagent\">Agent: enable checks</option><option value=\"arunn\">Agent: force UP</option><option value=\"adown\">Agent: force DOWN</option><option value=\"shutdown\">Kill Sessions</option></select>&nbsp;<input type=\"submit\" value=\"Apply\"></form>";
		}

		return $output;
	}

	public function _serviceLineClass($serviceData) {
		$serviceClass = $this->_serviceLineClassByServiceName($serviceData->getServiceName());
		switch ($serviceClass) {
			case 'service':
				switch ($serviceData->getStatus()) {
					case 'TODO':
						throw new ServiceLineSummaryException("Unknown Service Status: ".$serviceData->getStatus());
					case 'MAINT':
						return "maintain";
					case 'UP':
						return "active4";
					case 'DRAIN':
						return "active8";
					default:
						return "";
				}
				break;
			default:
				return $serviceClass;
		}
	}

	public function _serviceLineClassByServiceName($serviceName) {
		if ($serviceName == "FRONTEND") {
			return "frontend";
		} elseif (substr($serviceName, 0, 4) == "sock") {
			return "socket";
		} elseif ($serviceName == "BACKEND") {
			return "backend";
		} else {
			return "service";
		}
	}

	public function _serviceLineShowCheckbox($serviceData) {
		$serviceClass = $this->_serviceLineClass($serviceData);
		// echo "ServiceClass[".$serviceData->getProxyServiceName()."]:".$serviceClass.PHP_EOL;
		switch($serviceClass) {
			case 'frontend':
			case 'socket':
			case 'backend':
				return false;
			case 'service':
			default:
				return true;
		}
	}

	public function _getCheckboxNeededForGroup($groupData) {
		foreach ($groupData as $serviceData) {
			if ($this->_serviceLineShowCheckbox($serviceData)) {
				return true;
			}
		}
		return false;
	}

	public function _getServiceLines($serviceName, $groupData, $showCheckbox) {
		$output = "";
		foreach ($groupData as $serviceData) {
			if ($serviceData->getServiceName() == $serviceName) {
				$output .= $this->_serviceLine($serviceData, $showCheckbox);
			}
		}
		return $output;
	}


	public function _getServiceLineSummary($serviceName, $groupData, $showCheckbox) {
		$serviceDataOutput = null;
		foreach ($groupData as $serviceData) {
			if ($serviceData->getServiceName() == $serviceName) {
				if (is_null($serviceDataOutput)) {
					$serviceDataOutput = clone $serviceData;
				} else {
					$serviceDataOutput->addToSummary($serviceData);
				}
			}
		}
		if (is_null($serviceDataOutput)) {
			throw new ServiceLineSummaryException("Service Name not Found: ".$serviceName);
		}
		return $this->_serviceLineSummary($serviceDataOutput, $showCheckbox);
	}

	public function _serviceLineSummary($serviceData, $showCheckbox = false) {
		$output = "";
		$output .= "<tr class=\"".$this->_serviceLineClass($serviceData)." servicemaster\" targetservice=\"".$serviceData->getProxyServiceName()."\">";
		if ($showCheckbox) {
			$output .= "<td>";
			if ($this->_serviceLineShowCheckbox($serviceData)) {
				$output .= "<input type=\"checkbox\" name=\"smaster[]\" class=\"smaster\" targetservice=\"".$serviceData->getProxyServiceName()."\" value=\"".$serviceData->getProxyServiceName()."\">";
			}
			$output .= "</td>";
		}
		$output .= "<td class=ac><a name=\"".$serviceData->getProxyServiceName()."\"></a><u><a class=lfsb href=\"#".$serviceData->getProxyServiceName()."\">".$serviceData->getServiceName()."</a>";
		$output .= "<div class=tips>".$serviceData->getServiceName()." on ".$serviceData->getServerName()."</div>";
		$output .= "</u></td>";
		// Queue
		$output .= "<td>".$serviceData->getQueueCurrent()."</td>";
		$output .= "<td>".$serviceData->getQueueMax()."</td>";
		$output .= "<td>".$serviceData->getQueueLimit()."</td>";
		// Session rate
		$output .= "<td>".$serviceData->getSessionRateCurrent()."</td>";
		$output .= "<td>".$serviceData->getSessionRateMax()."</td>";
		$output .= "<td>".$serviceData->getSessionRateLimit()."</td>";
		// Sessions
		$output .= "<td>".$serviceData->getSessionCurrent()."</td>";
		$output .= "<td>".$serviceData->getSessionMax()."</td>";
		$output .= "<td>".$serviceData->getSessionLimit()."</td>";
		$output .= "<td><u>".$serviceData->getSessionTotal()."";
			// $output .= "<div class=tips>";
			// $output .= "<table class=det>";
			// $output .= "<tr><th>Cum. sessions:</th><td>453</td></tr>";
			// $output .= "<tr><th>Cum. HTTP responses:</th><td>451</td></tr>";
			// $output .= "<tr><th>- HTTP 1xx responses:</th><td>402</td><td>(89%)</td></tr>";
			// $output .= "<tr><th>- HTTP 2xx responses:</th><td>49</td><td>(10%)</td></tr>";
			// $output .= "<tr><th>- HTTP 3xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- HTTP 4xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- HTTP 5xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- other responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th colspan=3>Avg over last 1024 success. conn.</th></tr>";
			// $output .= "<tr><th>- Queue time:</th><td>0</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Connect time:</th><td>4</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Response time:</th><td>0</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Total time:</th><td><span class=\"rls\">6</span>314</td><td>ms</td></tr>";
			// $output .= "</table>";
			// $output .= "</div>";
		$output .= "</u></td>";
		$output .= "<td>".$serviceData->getSessionLbTotal()."</td>";
		$output .= "<td>".$this->_formatTime($serviceData->getSessionLastTime())."</td>";
		// Bytes
		$output .= "<td>".$this->_formatBytes($serviceData->getBytesIn())."</td>";
		$output .= "<td>".$this->_formatBytes($serviceData->getBytesOut())."</td>";
		// Denied
		$output .= "<td>".$serviceData->getDeniedRequests()."</td>";
		$output .= "<td>".$serviceData->getDeniedResponses()."</td>";
		// Errors
		$output .= "<td>".$serviceData->getErrorRequests()."</td>";
		$output .= "<td>".$serviceData->getErrorConnections()."</td>";
		$output .= "<td><u>".$serviceData->getErrorResponses()."";
			// @TODO $output .= "<div class=tips>Connection resets during transfers: 36 client, 0 server</div>";
		$output .= "</u></td>";
		// Warnings
		$output .= "<td>".$serviceData->getWarnRetries()."</td>";
		$output .= "<td>".$serviceData->getWarnRedispatches()."</td>";
		// Server
		$output .= "<td class=ac>".$this->_formatStatus($serviceData)."</td>";
		$output .= "<td class=ac><u> ".$this->_formatCheckFromService($serviceData);
			// @TODO $output .= "<div class=tips>Layer7 check passed: OK</div>";
		$output .= "</u></td>";
		$output .= "<td class=ac>".$serviceData->getWeight()."</td>";
		$output .= "<td class=ac>".$this->_formatActiveFromService($serviceData)."</td>";
		$output .= "<td class=ac>".$this->_formatActiveFromService($serviceData)."</td>";
		$output .= "<td><u>".$serviceData->getCheckFail();
			// @TODO $output .= "<div class=tips>Failed Health Checks</div>";
		$output .= "</u></td>";
		$output .= "<td>".$serviceData->getCheckDown()."</td>";
		$output .= "<td>".$this->_formatTime($serviceData->getDowntime())."</td>";
		$output .= "<td class=ac>".$serviceData->getThrottle()."</td>";
		$output .= "</tr>";
		return $output;
	}

	public function _serviceLine($serviceData, $showCheckbox = false) {
		$output = "";
		$output .= "<tr id=\"".$serviceData->getProxyServiceName()."/".$serviceData->getServerName()."\" class=\"".$this->_serviceLineClass($serviceData)."\">";

		// echo "ServiceClass[".$serviceData->getProxyServiceName()."]:".($showCheckbox ? "true" : "false").PHP_EOL;
		if ($showCheckbox) {
			$output .= "<td>";
			if ($this->_serviceLineShowCheckbox($serviceData)) {
				$output .= "<input id=\"".$serviceData->getProxyServiceName()."/".$serviceData->getServerName()."\" type=\"checkbox\" name=\"s[]\" value=\"".$serviceData->getProxyServiceName()."/".$serviceData->getServerName()."\">";
			}
			$output .= "</td>";
		}

		$output .= "<td class=ac><a name=\"".$serviceData->getProxyServiceName()."\"></a><u><a class=lfsb href=\"#".$serviceData->getProxyServiceName()."\">".$serviceData->getServiceName()."/".$serviceData->getServerName()."</a>";
		$output .= "<div class=tips>".$serviceData->getServiceName()." on ".$serviceData->getServerName()."</div>";
		$output .= "</u></td>";
		// Queue
		$output .= "<td>".$serviceData->getQueueCurrent()."</td>";
		$output .= "<td>".$serviceData->getQueueMax()."</td>";
		$output .= "<td>".$serviceData->getQueueLimit()."</td>";
		// Session rate
		$output .= "<td>".$serviceData->getSessionRateCurrent()."</td>";
		$output .= "<td>".$serviceData->getSessionRateMax()."</td>";
		$output .= "<td>".$serviceData->getSessionRateLimit()."</td>";
		// Sessions
		$output .= "<td>".$serviceData->getSessionCurrent()."</td>";
		$output .= "<td>".$serviceData->getSessionMax()."</td>";
		$output .= "<td>".$serviceData->getSessionLimit()."</td>";
		$output .= "<td><u>".$serviceData->getSessionTotal()."";
			// $output .= "<div class=tips>";
			// $output .= "<table class=det>";
			// $output .= "<tr><th>Cum. sessions:</th><td>453</td></tr>";
			// $output .= "<tr><th>Cum. HTTP responses:</th><td>451</td></tr>";
			// $output .= "<tr><th>- HTTP 1xx responses:</th><td>402</td><td>(89%)</td></tr>";
			// $output .= "<tr><th>- HTTP 2xx responses:</th><td>49</td><td>(10%)</td></tr>";
			// $output .= "<tr><th>- HTTP 3xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- HTTP 4xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- HTTP 5xx responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th>- other responses:</th><td>0</td><td>(0%)</td></tr>";
			// $output .= "<tr><th colspan=3>Avg over last 1024 success. conn.</th></tr>";
			// $output .= "<tr><th>- Queue time:</th><td>0</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Connect time:</th><td>4</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Response time:</th><td>0</td><td>ms</td></tr>";
			// $output .= "<tr><th>- Total time:</th><td><span class=\"rls\">6</span>314</td><td>ms</td></tr>";
			// $output .= "</table>";
			// $output .= "</div>";
		$output .= "</u></td>";
		$output .= "<td>".$serviceData->getSessionLbTotal()."</td>";
		$output .= "<td>".$this->_formatTime($serviceData->getSessionLastTime())."</td>";
		// Bytes
		$output .= "<td>".$this->_formatBytes($serviceData->getBytesIn())."</td>";
		$output .= "<td>".$this->_formatBytes($serviceData->getBytesOut())."</td>";
		// Denied
		$output .= "<td>".$serviceData->getDeniedRequests()."</td>";
		$output .= "<td>".$serviceData->getDeniedResponses()."</td>";
		// Errors
		$output .= "<td>".$serviceData->getErrorRequests()."</td>";
		$output .= "<td>".$serviceData->getErrorConnections()."</td>";
		$output .= "<td><u>".$serviceData->getErrorResponses()."";
			// @TODO $output .= "<div class=tips>Connection resets during transfers: 36 client, 0 server</div>";
		$output .= "</u></td>";
		// Warnings
		$output .= "<td>".$serviceData->getWarnRetries()."</td>";
		$output .= "<td>".$serviceData->getWarnRedispatches()."</td>";
		// Server
		$output .= "<td class=ac>".$this->_formatStatus($serviceData)."</td>";
		$output .= "<td class=ac><u> ".$this->_formatCheckFromService($serviceData);
			// @TODO $output .= "<div class=tips>Layer7 check passed: OK</div>";
		$output .= "</u></td>";
		$output .= "<td class=ac>".$serviceData->getWeight()."</td>";
		$output .= "<td class=ac>".$this->_formatActiveFromService($serviceData)."</td>";
		$output .= "<td class=ac>".$this->_formatActiveFromService($serviceData)."</td>";
		$output .= "<td><u>".$serviceData->getCheckFail();
			// @TODO $output .= "<div class=tips>Failed Health Checks</div>";
		$output .= "</u></td>";
		$output .= "<td>".$serviceData->getCheckDown()."</td>";
		$output .= "<td>".$this->_formatTime($serviceData->getDowntime())."</td>";
		$output .= "<td class=ac>".$serviceData->getThrottle()."</td>";
		$output .= "</tr>";
		return $output;
	}

}