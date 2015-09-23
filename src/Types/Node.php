<?php

namespace Koshatul\HAProxyWeb\Types;

class Node {
	protected $_server;
	protected $_serverName;
	protected $_group;
	protected $_data;

	public function __construct($servername, $server, $group, $data) {
		$this->_serverName = $servername;
		$this->_server     = $server;
		$this->_group      = $group;
		$this->_data       = $data;
	}

	public static function CreateFromObject($object) {
		
	}

	public function __clone() {
		// $this->_serverName = clone $this->_serverName;
		// $this->_server     = clone $this->_server;
		// $this->_group      = clone $this->_group;
		$this->_data       = clone $this->_data;
	}

	public function getData() {
		return $this->_data;
	}

	public function getDataValue($name) {
		return $this->_getDataValue($name);
	}

	protected function _getDataValue($name, $defaultValue = null) {
		if (is_array($this->_data)) {
			if (array_key_exists($name, $this->_data)) {
				return $this->_data[$name];
			} else {
				return $defaultValue;
			}
		} elseif (is_object($this->_data)) {
			if (property_exists($this->_data, $name)) {
				return $this->_data->{$name};
			} else {
				return $defaultValue;
			}
		} else {
			return $defaultValue;
		}
	}

	public function getServer() {
		return $this->_server;
	}

	public function getServerName() {
		return $this->_serverName;
	}
}