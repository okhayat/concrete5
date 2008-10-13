<?

defined('C5_EXECUTE') or die(_("Access Denied."));

class LogEntry extends Object {
	
	public function getType() {return $this->logType;}
	public function getText() {return $this->logText;}
	public function getID() {return $this->logID;}
	
	public function getTimestamp() {return $this->timestamp;}

	/** 
	 * Returns a log entry by ID
	 */
	public static function getByID($logID) {
		$db = Loader::db();
		$r = $db->Execute("select * from Logs where logID = ?", array($logID));
		if ($r) {
			$row = $r->FetchRow();
			$obj = new LogEntry();
			$obj->setPropertiesFromArray($row);
			return $obj;
		}
	}
	
	
}

class DatabaseLogEntry extends LogEntry {
	
	public function getQuery() {return $this->query;}
	public function getParameters() {return $this->params;}
	public function getTrace() {return $this->tracer;}
	
	public static function getTotal() {
		$db = Loader::db();
		return $db->GetOne("select count(created) from adodb_logsql");
	}
	
	public static function clear() {
		$db = Loader::db();
		$db->Execute("delete from adodb_logsql");
	}
	
	public static function getList($limit) {
		$entries = array();
		$db = Loader::db();
		$r = $db->GetAll("select sql1 as query, created, params, tracer from adodb_logsql order by created desc limit " . $limit);
		foreach($r as $row) {
			$dle = new DatabaseLogEntry();
			$dle->setPropertiesFromArray($row);
			$entries[] = $dle;
		}
		
		return $entries;
	}
	
}


class Log {

	private $log;
	private $logfile;
	private $name;
	private $session = false;
	//private $isClosed = false;
	private $sessionText = null;
	private $isInternal = false;
	
	public function __construct($log = null, $session = false, $internal = false) {
		$th = Loader::helper('text');
		if ($log == null) {
			$log = '';
		}
		$this->log = $log;
		$this->name = $th->unhandle($log);
		$this->session = $session;
		$this->isInternal = $internal;
	}
	
	public function write($message) {
		$this->sessionText .= $message . "\n";
		if (!$this->session) {
			$this->close();
		}
	}
	
	/** 
	 * Removes all "custom" log entries - these are entries that an app owner has written and don't have a builtin C5 type
	 */
	public function clearCustom() {
		$db = Loader::db();
		$db->Execute("delete from Logs where logIsInternal = 0");
	}
	
	public function clearInternal() {
		$db = Loader::db();
		$db->Execute("delete from Logs where logIsInternal = 1");
	}
	
	public function close() {
		/*
		if ($this->isClosed) {
			throw new Exception("This logging session has already been closed.");
		}
		$this->isClosed = true;
		*/
		$v = array($this->log, $this->sessionText, $this->isInternal);
		$db = Loader::db();
		$db->Execute("insert into Logs (logType, logText, logIsInternal) values (?, ?, ?)", $v);
	}
	
	/** 
	 * Renames a log file and moves it to the log archive.
	 */
	public function archive() {

	}
	
	/** 
	 * Returns the total number of entries matching this type 
	 */
	public static function getTotal($keywords, $type, $isInternal) {
		$db = Loader::db();
		if ($keywords != '') {
			$kw = 'and logText like ' . $db->quote('%' . $keywords . '%');
		}
		if ($type != false) {
			$v = array($type, $isInternal);
			$r = $db->GetOne('select count(logID)  from Logs where logType = ? and logIsInternal = ? ' . $kw, $v);
		} else {
			$v = array($isInternal);
			$r = $db->GetOne('select count(logID)  from Logs where logIsInternal = ? ' . $kw, $v);
		}
		return $r;

	}
	
	/** 
	 * Returns a list of log entries
	 */
	public static function getList($keywords, $type, $isInternal, $limit) {
		$db = Loader::db();
		if ($keywords != '') {
			$kw = 'and logText like ' . $db->quote('%' . $keywords . '%');
		}
		if ($type != false) {
			$v = array($type, $isInternal);
			$r = $db->Execute('select logID from Logs where logType = ? and logIsInternal = ? ' . $kw . ' order by timestamp desc limit ' . $limit, $v);
		} else {
			$v = array($isInternal);
			$r = $db->Execute('select logID from Logs where logIsInternal = ? ' . $kw . ' order by timestamp desc limit ' . $limit, $v);
		}
		$entries = array();
		while ($row = $r->FetchRow()) {
			$entries[] = LogEntry::getByID($row['logID']);
		}
		return $entries;
	}
	
	public function getName() { return $this->name;}
	
	/** 
	 * Returns all the log files in the directory
	 */
	public static function getLogs() {
		$db = Loader::db();
		$r = $db->GetCol('select distinct logType from Logs order by logType asc');
		return $r;
	}

}