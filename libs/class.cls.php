<?php
define ('WP_CLS_URL', WP_PLUGIN_URL . '/' . basename(dirname(dirname(__FILE__))));
define ('WP_CLS_DB_PREFIX', 'cls_');
define ('WP_CLS_UPLOAD_PATH', dirname(dirname(__FILE__)) . '/attachments');
define ('WP_CLS_UPLOAD_URL', WP_PLUGIN_URL . '/' . basename(dirname(dirname(__FILE__))) . '/attachments');
define ('WP_CLS_NAME', 'wp-classifieds');

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
				likes int not null default 0 comment \'number of likes\',
				dislikes int not null default 0 comment \'number of dislikes\',
				priority int not null default 0 comment \'priority of this ad\',
				flags int not null default 0 comment \'flags field. usually &1 = off/on\');');
			}
		else {
			$wpdb->query ('drop table `' . $this->table .'`;');
			}
		}

	public function __destruct () {
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
			'type',
			'aid',
			'uid',
			'stamp'
			);
		foreach ($this->keys as $key) $this->data[$key] = '';
	
		if (is_array($data)) {
			$e = $this->_file_extension ($data['name']);
			if ($e !== FALSE) {
				$this->data['hash'] = md5_file($data['tmp_name']);
				$this->data['type'] = $e;
				$this->data['uid'] = get_current_user_id();
				$this->data['stamp'] = time();

				$this->path .= '/'.$this->data['hash'].'.'.$e;
				if (is_uploaded_file($data['tmp_name']))
					@move_uploaded_file($data['tmp_name'], $this->path);
				}
			}	
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from `' . $this->table .'` where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			$this->ID = (int) $this->data['id'];
			foreach ($this->keys as $key)
				$this->data[$key] = $data[$key];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'table') return $this->table;
		if ($key == 'resized file') {
			list ($x, $y) = is_array($value) ? $value : explode('x', $value);
			$r = $this->_resize_path ($x, $y);
			if (!file_exists($r)) $this->resize ($x, $y);
			return $r;
			}
		if ($key == 'resized url') return str_replace(WP_CLS_UPLOAD_PATH, WP_CLS_UPLOAD_URL, $this->get('resized file'));
		if ($key == 'url') return str_replace(WP_CLS_UPLOAD_PATH, WP_CLS_UPLOAD_URL, $this->path);
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

	private function _file_extension ($str = null) {
		$point = strrpos(is_null($str) ? $this->path : $str, '.');
		if ($point !== FALSE) return strtolower(substr(is_null($str) ? $this->path : $str, $point + 1));
		return FALSE;
		}

	private function _resize_path ($x, $y) {
		$piece = '-'.$x.'x'.$y;
		$point = strrpos($this->path, '.');
		if ($point !== FALSE) return substr($this->path, 0, $point - 1).$piece.substr($this->path, $point);
		return FALSE;
		}

	public function resize ($x = 200, $y = null) {
		$x = (int) $x;
		$y = (($y == null) || (!is_numeric($y))) ? $x : ((int) $y);
		if ($x < 1 || $y < 1) return FALSE;
		if (class_exists('Imagick')) {
			$i = new Imagick ($this->path);
			$g = $i->getImageGeometry();
			if (($g['width'] <= $x) && ($g['height'] <= $y)) {
				}
			else {
				$i->resizeImage($x, $y, imagick::FILTER_LANCZOS, 0.9, true);
				if (($r = $this->_resize_path($x, $y)) !== FALSE)
					$i->writeImage($r);
				$i->clear();
				$i->destroy();
				}
			}
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into `' . $this->table . '` ('.implode(',', $this->keys).') values ('.str_pad('', count($this->keys)*3 - 1, '%s,').');', array_values($this->data));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		return $this->ID ? $this->ID : FALSE;
		}

	public function init ($flag = TRUE) {
		global $wpdb;
		if ($flag) {
			#if (!@mkdir ($this->path, 0777, TRUE)) die ('WP_CLS::FatalError::error( Unable to create '.$this->path.'! )');
			$wpdb->query ('create table `' . $this->table . '` (
				id int not null primary key auto_increment comment \'the primary key\',
				hash varchar(32) not null default \'\' unique comment \'the md5sum of the file. preventing duplicates!\',
				type enum(\'jpg\',\'png\') not null default \'jpg\' comment \'the extension of the original file\',
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

	public function __destruct () {
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
			'name',
			'flags'
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

	public function __destruct () {
		}
	}

class WP_CLS_User {
	private $ID;
	private $current;

	public function __construct ($data = null) {
		$this->ID = 0;
		$this->current = null;
		if (is_numeric($data))
			$this->current = get_userdata((int) $data);
		else
			$this->current = wp_get_current_user();
		if (is_object($this->current)) $this->ID = $this->current->ID;
		}

	public function get ($key = '', $value = '') {
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		}

	public function can ($what = 'add_classifieds') {
		return user_can ($this->current, $what);
		}

	public function __destruct () {
		}
	}

class WP_CLS_List {
	private $list;

	public function __construct ($type, $filter = null) {
		global $wpdb;
		$this->list = array ();
		if ($type == 'ads') {
			$object = new WP_CLS_Ad ();
			if (is_null($filter))
				$sql = $wpdb->prepare ('select id from `'.$object->get('table').'` order by stamp desc;');
			$items = $wpdb->get_col ($sql);
			if (!empty($items))
				foreach ($items as $item)
					$this->list[] = new WP_CLS_Ad ((int) $item);
			}
		if ($type == 'attachments' || $type == 'files' || $type == 'images') {
			$object = new WP_CLS_Attachment ();
			if (is_null($filter)) {}
			else
			if (is_object($filter)) {
				if (get_class($filter) == 'WP_CLS_Ad')
					$sql = $wpdb->prepare ('select id from `'.$object->get('table').'` where aid=%d;', $filter->get());
				}
			$items = $wpdb->get_col ($sql);
			if (!empty($items))
				foreach ($items as $item)
					$this->list[] = new WP_CLS_Attachment ((int) $item);
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'size' || $key == 'count') return count($this->list);
		if ($key == 'last') return empty($this->list) ? null : $this->list[count($this->list)-1];
		if ($key == 'first') return empty($this->list) ? null : $this->list[0];
		if ($key == 'rand' || $key == 'random') return empty($this->list) ? null : $this->list[rand(0,count($this->list)-1];
		return $this->list;
		}

	public function is ($what == 'empty') {
		return empty($this->list) ? TRUE : FALSE;
		}
	
	public function __destruct () {
		}
	}

class WP_CLS_Ajax {
	private $out;
	private $user;
	private $page;

	public function __construct ($data = null) {
		$this->out = '';
		$this->page = null;
		if (is_object($data) && get_class($data) == 'WP_CLS_User') $this->user = $data;
		}

	public function fire ($action = null, $method = 'get') {
		$vars = $method == 'post' ? $_POST : $_GET;
		if (is_object($this->user) && $this->user->can()) {
			if ($action == 'newad') {
				}
			if ($action == 'logout') {
				}
			}
		else {
			if ($action == 'login') {
				}
			if ($action == 'register') {
				}
			}
		}

	public function page ($page = null) {
		$page = is_null($this->page) ? $page : $this->page;

		if (is_object($this->user) && $this->user->can()) {
			if ($page == 'add') {
				$this->out =  '<div class="wp-classifieds-newad">
					<form action="'.WP_CLS_URL.'/ajax/actions/add.php" method="post">
						<input type="text" name="f" value="" class="wp-classifieds-upload-files" />
						<div><label>'.__('Classified Ad Title', WP_CLS_NAME).'</label></div>
						<input type="text" name="n" value="" />
						<div><label>'.__('Classified Ad Content', WP_CLS_NAME).'</label></div>
						<textarea name="c" rows="7"></textarea>
						<div><label>'.__('Classified Ad Keywords', WP_CLS_NAME).'</label></div>
						<div><input type="text" name="k" value="" /></div>
						<div><label>'.__('Do you agree with the terms and conditions?', WP_CLS_NAME).'</label></div>
						<div>
							<input type="radio" name="y" value="1" id="y1" /><label for="y1">'.__('Yes', WP_CLS_NAME).'</label>
							<input type="radio" name="y" value="0" id="y0" /><label for="y0">'.__('No', WP_CLS_NAME).'</label>
						</div>
						<button>'.__('Post Ad', WP_CLS_NAME).'</button>
					</form>
				</div>
				<div class="wp-classifieds-upload">
					<div class="wp-classifieds-upload-placeholder"></div>
					<div class="wp-classifieds-upload-button">'.__('Upload', WP_CLS_NAME).'</div>
					<div class="wp-classifieds-upload-cancel">'.__('Cancel', WP_CLS_NAME).'</div>
					<div class="wp-classifieds-upload-status"></div>
					<div style="clear: both;"></div>
					<div class="wp-classifieds-upload-queue"></div>
					<div style="clear: both;"></div>
				</div>';
				}
			if ($page == 'logout') {
				}
			}
		else {
			if ($page == 'register') {
				$this->out = '<form action="" method="post">
					<input type="hidden" name="register" value="1" />
					<div><label>Nume utilizator:</label></div>
					<input type="text" name="username" value="" />
					<div><label>Adresa de email:</label></div>
					<input type="text" name="email" value="" />
					<button>Inscrie-te!</button>
				</form>
				<div></div>';
				}
			if ($page == 'login' || $page == 'add' || $page == 'logout') {
				$this->out = '<form name="loginform" id="loginform" action="" method="post">
					<input type="hidden" name="signon" value="1" />
					<div><label>Nume utilizator:</label></div>
					<input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10" />
					<div><label>Parola:</label></div>
					<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" />
					<div><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> <label for="rememberme">Pastreaza-ma autentificat pe acest calculator.</label></div>
					<input type="hidden" name="testcookie" value="1" />
					<button>Autentificare</button>
				</form>
				<div><a href="'.get_option('home').'/wp-login.php?action=lostpassword" title="Password Lost and Found">Ti-ai pierdut parola?</a></div>';
				}
			}
		if ($page == 'list') {
			}
		}

	public function view ($echo = TRUE) {
		if (!$echo) return $this->out;
		echo $this->out;
		}

	public function __destruct () {
		}
	}
?>
