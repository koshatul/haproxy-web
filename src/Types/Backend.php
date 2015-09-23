<?php

namespace Koshatul\HAProxyWeb\Types;

class Backend extends Node {

	public static function CreateFromObject($object) {

	}

	protected function avgToData($name, $value) {
		// TODO actually average the data (shifting average is fine)
		return $this->_data->{$name};
	}

	protected function setToData($name, $value) {
		$this->_data->{$name} = $value;
		return $this->_data->{$name};
	}

	protected function minToData($name, $value) {
		if (!property_exists($this->_data, $name)) {
			$this->_data->{$name} = $value;
		}
		if ($this->_data->{$name} > $value) {
			$this->_data->{$name} = $value;
		}
		return $this->_data->{$name};
	}

	protected function addToData($name, $value) {
		if (property_exists($this->_data, $name)) {
			$this->_data->{$name} += $value;
		} else {
			$this->_data->{$name} = $value;
		}
		return $this->_data->{$name};
	}

	public function addToSummary($object) {
		foreach ($object->getData() as $altKey => $altData) {
			switch ($altKey) {
				case 'qcur':
				case 'qmax':
				case 'rate':
				case 'scur':
				case 'smax':
				case 'stot':
				case 'lbtot':
				case 'bin':
				case 'bout':
				case 'dreq':
				case 'dresp':
				case 'ereq':
				case 'econ':
				case 'eresp':
				case 'wretr':
				case 'wredis':
				case 'chkfail':
				case 'chkdown':
					$this->addToData($altKey, $altData);
					break;
				case 'ratemax':
					$this->avgToData($altKey, $altData);
					break;
				case 'lastsess':
				case 'lastchg':
					$this->minToData($altKey, $altData);
					break;
				case 'status':
					if ($altData != "UP") {
						$this->setToData($altKey, $altData);
					}
					break;

				case 'slim':
				case 'checkstatus':
				case 'checkcode':
				case 'checkduration':
				case 'weight':
				case 'qlimit':
				case 'ratelim':
				case 'act':
				case 'bck':
				case 'downtime':
				case 'throttle':
					// TODO Ignore for now
					break;
				case 'pxname':
				case 'svname':
					// Never do anything
					break;
				case 'pid':
				case 'iid':
				case 'sid':
				case 'tracked':
				case 'type':
					// Not used yet (TODO getter)
					break;
				case 'hrspxx':
				case 'hrspother':
				case 'hanafail':
				case 'reqrate':
				case 'reqratemax':
				case 'reqtot':
				case 'cliabrt':
				case 'srvabrt':
				case 'compin':
				case 'compout':
				case 'compbyp':
				case 'comprsp':
				case 'lastchk':
				case 'lastagt':
				case 'qtime':
				case 'ctime':
				case 'rtime':
				case 'ttime':
					// Unknown
					break;
				default:
					echo "Unhandled data property: $altKey".PHP_EOL;
					// throw new \Exception("Unhandled data property: $altKey");
			}
		}
	}

	// Complex Types
	public function getProxyServiceName() {
		return $this->_getDataValue('pxname')."/".$this->_getDataValue('svname');
	}

	// Common
	public function getProxyName() {
		return $this->_getDataValue('pxname');
	}

	public function getServiceName() {
		return $this->_getDataValue('svname');
	}

	// Queue
	public function getQueueCurrent() { return $this->_getDataValue('qcur'); }
	public function getQueueMax()     { return $this->_getDataValue('qmax'); }
	public function getQueueLimit()   { return $this->_getDataValue('qlimit'); }

	// Session rate
	public function getSessionRateCurrent() { return $this->_getDataValue('rate'); }
	public function getSessionRateMax()     { return $this->_getDataValue('ratemax'); }
	public function getSessionRateLimit()   { return $this->_getDataValue('ratelim'); }

	// Sessions
	public function getSessionCurrent()  { return $this->_getDataValue('scur'); }
	public function getSessionMax()      { return $this->_getDataValue('smax'); }
	public function getSessionLimit()    { return $this->_getDataValue('slim'); }
	public function getSessionTotal()    { return $this->_getDataValue('stot'); }
	public function getSessionLbTotal()  { return $this->_getDataValue('lbtot'); }
	public function getSessionLastTime() { return $this->_getDataValue('lastsess'); }

	// Bytes
	public function getBytesIn()    { return $this->_getDataValue('bin'); }
	public function getBytesOut()   { return $this->_getDataValue('bout'); }

	// Denied
	public function getDeniedRequests()    { return $this->_getDataValue('dreq'); }
	public function getDeniedResponses()   { return $this->_getDataValue('dresp'); }

	// Errors
	public function getErrorRequests()    { return $this->_getDataValue('ereq'); }
	public function getErrorConnections() { return $this->_getDataValue('econ'); }
	public function getErrorResponses()   { return $this->_getDataValue('eresp'); }

	// Warnings
	public function getWarnRetries()       { return $this->_getDataValue('wretr'); }
	public function getWarnRedispatches()  { return $this->_getDataValue('wredis'); }

	// Check
	public function getCheckStatus()     { return $this->_getDataValue('checkstatus'); }
	public function getCheckCode()       { return $this->_getDataValue('checkcode'); }
	public function getCheckDuration()   { return $this->_getDataValue('checkduration'); }

	// Status
	public function getStatus()       { return $this->_getDataValue('status'); }
	public function getLastChange()   { return $this->_getDataValue('lastchg'); }

	// Server
	public function getWeight()       { return $this->_getDataValue('weight'); }
	public function getActive()       { return $this->_getDataValue('act'); }
	public function getBackup()       { return $this->_getDataValue('bck'); }
	public function getCheckFail()    { return $this->_getDataValue('chkfail'); }
	public function getCheckDown()    { return $this->_getDataValue('chkdown'); }
	public function getDowntime()     { return $this->_getDataValue('downtime'); }
	public function getThrottle()     { return $this->_getDataValue('throttle'); }


}