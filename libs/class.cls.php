<?php
define ('WP_CLS_DB_PREFIX', 'cls_');
define ('WP_CLS_UPLOAD_PATH', dirname(dirname(__FILE__)) . '/attachments');

class WP_CLS_Ad {
	private $ID;
	private $table;
	private $keys;
	private $data;

	public function __construct ($data = null) {
		global $wpdb;
		$this->table = $wpdb->prefix . WP_CLS_DB_PREFIX . 'ads';
		$this->keys = array (
			'uid',
			'stamp',
			'content',
			'attachments',
			'likes',
			'dislikes',
			'priority',
			'flags'
			);
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from `' . $this->table .'` where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			}
		if (is_array($data)) {
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'table') return $this->table;
		if (in_array($key, $this->keys)) return $this->data[$key];
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		if (is_array ($key)) {
			$update = array ();
			foreach ($key as $_k => $_v) {
				if (!in_array($key, $this->keys)) continue;
				$update[] = $wpdb->prepare ($_k.'=%s', $_v);
				$this->data[$_k] = $_v;
				}
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.implode(',',$update).' where id=%d;', $this->id));
			}
		else {
			if (!in_array($key, $this->keys)) return FALSE;
			$this->data[$key] = $value;
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.$key.'=%s where id=%d;', $value, $this->id));
			}
		return TRUE;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into `' . $this->table . '` ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			$wpdb->query ('create table `' . $this->table . '` (
				id int not null primary key auto_increment comment \'the primary key\',
				uid int not null default 0 comment \'the user that created this ad\',
				stamp int not null default 0 comment \'unix time stamp of this ad\',
				content text not null comment \'the content of this ad\',
				attachments text not null comment \'the serialized array of attachments\',
				likes int not null default 0 \'number of likes\',
				dislikes int not null default 0 \'number of dislikes\',
				priority int not null default 0 \'priority of this ad\',
				flags int not null default 0 \'flags field. usually &1 = off/on\');');
			}
		else {
			$wpdb->query ('drop table `' . $this->table .'`;');
			}
		}
	}

class WP_CLS_Attachment {
	private $ID;
	private $table;
	private $path;
	private $keys;
	private $data;

	public function __construct ($data = null) {
		global $wpdb;
		$this->table = $wpdb->prefix . WP_CLS_DB_PREFIX . 'attachments';
		$this->path = WP_CLS_UPLOAD_PATH;
		$this->keys = array (
			'hash',
			'aid',
			'uid',
			'stamp'
			);
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * `' . $this->table .'` from where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			}
		if (is_array($data)) {
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'table') return $this->table;
		if (in_array($key, $this->keys)) return $this->data[$key];
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		if (is_array ($key)) {
			$update = array ();
			foreach ($key as $_k => $_v) {
				if (!in_array($key, $this->keys)) continue;
				$update[] = $wpdb->prepare ($_k.'=%s', $_v);
				$this->data[$_k] = $_v;
				}
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.implode(',',$update).' where id=%d;', $this->id));
			}
		else {
			if (!in_array($key, $this->keys)) return FALSE;
			$this->data[$key] = $value;
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.$key.'=%s where id=%d;', $value, $this->id));
			}
		return TRUE;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			if (!@mkdir ($this->path, 0777, TRUE)) die ('WP_CLS::FatalError::error( Unable to create '.$this->path.'! )');
			$wpdb->query ('create table `' . $this->table . '` (
				id int not null primary key auto_increment comment \'the primary key\',
				hash varchar(32) not null default \'\' unique comment \'the md5sum of the file. preventing duplicates!\',
				aid int not null default 0 comment \'the ad id\',
				uid int not null default 0 comment \'the uploader id\',
				stamp int not null default 0 comment \'the unix timestamp of the upload event\',
				flags int not null default 0 comment \'binary flags. usually &1 = off/on\');');
			}
		else {
			/* should do something to remove the directory.. neah.. not yet.. */
			$wpdb->query ('drop table `' . $this->table . '`;');
			}
		}
	}

class WP_CLS_Group {
	private $ID;
	private $table;
	private $tlink;
	private $keys;
	private $data;

	public function __construct ($data = null) {
		global $wpdb;
		$this->table = $wpdb->prefix . WP_CLS_DB_PREFIX . 'groups';
		$this->tlink = $wpdb->prefix . WP_CLS_DB_PREFIX . 'ad_group';
		$this->keys = array (
			);
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			}
		if (is_array($data)) {
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'table') return $this->table;
		if ($key == 'link table') return $this->tlink;
		if (in_array($key, $this->keys)) return $this->data[$key];
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		if (is_array ($key)) {
			$update = array ();
			foreach ($key as $_k => $_v) {
				if (!in_array($key, $this->keys)) continue;
				$update[] = $wpdb->prepare ($_k.'=%s', $_v);
				$this->data[$_k] = $_v;
				}
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.implode(',',$update).' where id=%d;', $this->id));
			}
		else {
			if (!in_array($key, $this->keys)) return FALSE;
			$this->data[$key] = $value;
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.$key.'=%s where id=%d;', $value, $this->id));
			}
		return TRUE;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			$wpdb->query ('create table `' . $this->table . '` (
				id int not null primary key auto_increment comment \'the primary key\',
				pid int not null default 0 comment \'the parent group id\',
				name text not null comment \'the group name\',
				flags int not null default 0 comment \'binary flags. usually &1 = off/on\');');
			$wpdb->query ('create table `' . $this->tlink . '` (
				id int not null primary key auto_increment comment \'the primary key\',
				gid int not null default 0 comment \'the group id\',
				aid int not null default 0 comment \'the ad id\',
				unique(gid,aid),
				index(gid),
				index(aid));');
			}
		else {
			$wpdb->query ('drop table `' . $this->table . '`;');
			$wpdb->query ('drop table `' . $this->tlink . '`;');
			}
		}
	}

class WP_CLS_User {
	private $ID;
	private $keys;
	private $data;

	public function __construct ($data = null) {
		global $wpdb;
		$this->keys = array (
			);
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			}
		if (is_array($data)) {
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if (in_array($key, $this->keys)) return $this->data[$key];
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		if (is_array ($key)) {
			$update = array ();
			foreach ($key as $_k => $_v) {
				if (!in_array($key, $this->keys)) continue;
				$update[] = $wpdb->prepare ($_k.'=%s', $_v);
				$this->data[$_k] = $_v;
				}
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.implode(',',$update).' where id=%d;', $this->id));
			}
		else {
			if (!in_array($key, $this->keys)) return FALSE;
			$this->data[$key] = $value;
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.$key.'=%s where id=%d;', $value, $this->id));
			}
		return TRUE;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			$wpdb->query ('create table (
				id int not null primary key auto_increment,
				);');
			}
		else {
			$wpdb->query ('drop table;');
			}
		}
	}

class WP_CLS_List {
	private $ID;
	private $keys;
	private $data;

	public function __construct ($data = null) {
		global $wpdb;
		$this->keys = array (
			);
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			}
		if (is_array($data)) {
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if (in_array($key, $this->keys)) return $this->data[$key];
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		if (is_array ($key)) {
			$update = array ();
			foreach ($key as $_k => $_v) {
				if (!in_array($key, $this->keys)) continue;
				$update[] = $wpdb->prepare ($_k.'=%s', $_v);
				$this->data[$_k] = $_v;
				}
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.implode(',',$update).' where id=%d;', $this->id));
			}
		else {
			if (!in_array($key, $this->keys)) return FALSE;
			$this->data[$key] = $value;
			if ($this->ID)
				$wpdb->query ($wpdb->prepare('update set '.$key.'=%s where id=%d;', $value, $this->id));
			}
		return TRUE;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			$wpdb->query ('create table (
				id int not null primary key auto_increment,
				);');
			}
		else {
			$wpdb->query ('drop table;');
			}
		}
	}
?>
