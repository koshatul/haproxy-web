<?php

namespace Koshatul\HAProxyWeb\Helper;

use \BigWhoop\HAProxyAPI;

class GetStats {
	private $_uri;
	private $_api;
	private $_data;

	public function __construct($v_uri) {
		$this->_uri = $v_uri;
	}

	public function process() {
		$this->_data = array();
		foreach ($this->_uri as $servername => $server) {
			$this->_api = new HAProxyAPI\API($server, \Koshatul\Config\Config::Get('haproxyweb/username'), \Koshatul\Config\Config::Get('haproxyweb/password'));
			try {
				$stats = $this->_api->execute('stats', array('grouping' => HAProxyAPI\Command\StatsCommand::GROUPING_BACKEND));
				foreach ($stats as $group => $services) {
					if (!array_key_exists($group, $this->_data)) {
						$this->_data[$group] = array();
					}
					foreach ($services as $service) {
						$backend = new \Koshatul\HAProxyWeb\Types\Backend($servername, $server, $group, $service);
						//echo "[".$group."] ".$backend->getProxyName()."/".$backend->getServiceName()." (".$backend->getServer().")".PHP_EOL;
						$this->_data[$group][] = $backend;
					}
				}
			} catch (HAProxyAPI\Client\Exception $e) {
				// Server error
			} catch (HAProxyAPI\Command\Exception $e) {
				// Data error
			}
		}
	}

	public function getData() {
		return $this->_data;
	}


}