<?php
class Tt_Rss_Mobile extends Plugin {
	private $host;

	function about() {
		// version, name, description, author, is_system
		return array(0.1, "Change TT-RSS display for mobile", "Ti-Tom", true);
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		require_once "lib/Mobile_Detect.php";

		$mobile = new Mobile_Detect();
		if($mobile->isMobile()){
			if(!empty($_REQUEST)){
				$this->host = $host;
				$op = isset($_REQUEST["op"])?$_REQUEST["op"]:null;
				$host->add_handler("mobile", "index", $this);
				$host->add_handler("mobile", "login", $this);
				$host->add_handler("mobile", "load", $this);
				$host->add_handler("mobile", "logout", $this);
				$host->add_handler("mobile", "refresh", $this);
				if($op == 'mobile'){
					$method = isset($_REQUEST["method"])?$_REQUEST["method"]:null;
					$handler = new Handler_Public();
					switch($method){
						default:
						case 'index':
							$feeds = $articles = array();
							if($_SESSION['uid']){
								login_sequence();
								$feeds = $this->getFeeds();

								if(!empty($_REQUEST['fid'])){
									$articles = $this->loadArticle($_REQUEST['fid']);
								}
							}
							
							$this->index($feeds,$articles);
							break;
						case 'login':
							if(!$_SESSION['uid']){
								$handler->login();
							}
							header('Location: backend.php?op=mobile');
							exit;
							break;
						case 'logout':
							if($_SESSION['uid']){
								logout_user();
							}
							header('Location: backend.php?op=mobile');
							exit;
							break;
						/*case 'refresh':
							if($_SESSION['uid']){
								if(empty($_REQUEST['fid'])){
									ob_start();
									$handler->getFeeds();
									$feeds = ob_get_contents();
									ob_end_clean();
									$feeds = json_decode($feeds,true);
									$feeds = isset($feeds['content'])?$feeds['content']:array();
									foreach($feeds as $f){
										set_time_limit(0);
										$_REQUEST['feed_id'] = $f['id'];
										$handler->updateFeed();
									}
									header('Location: backend.php?op=mobile');
									exit;
								}else{
									$_REQUEST['feed_id'] = $_REQUEST['fid'];
									$handler->updateFeed();
									header('Location: backend.php?op=mobile&fid='.$_REQUEST['fid']);
									exit;
								}
							}
							break;*/
						case 'load':
							ob_clean();
							if($_SESSION['uid'] && !empty($_REQUEST['fid']) && !empty($_REQUEST['p'])){
								login_sequence();
								$articles = $this->loadArticle($_REQUEST['fid'],$_REQUEST['p']);
								foreach($articles as $a){
									include dirname(__FILE__)."/_display_article.php";
								}
							}
							die();
							break;
						case 'markfeedread':
							if($_SESSION['uid']&& !empty($_REQUEST['fid'])){
								login_sequence();
								$this->markFeedHasRead($_REQUEST['fid']);
							}
							header('Location: backend.php?op=mobile');
							exit;
							break;
						case 'markarticleread':
							ob_clean();
							if($_SESSION['uid'] && !empty($_REQUEST['fid']) && !empty($_REQUEST['aid'])){
								login_sequence();
								$this->markArticleHasRead($_REQUEST['fid'],$_REQUEST['aid']);
							}
							die();
							break;

					}
				}elseif(empty($op)){
					header('Location: backend.php?op=mobile');
					exit;
				}
			}else{
				header('Location: backend.php?op=mobile');
				exit;
			}
		}
	}

	function index($feeds = array(),$articles = array()) {
		if (file_exists("install") && !file_exists("config.php")) {
			print "<b>Fatal Error</b>: You forgot to install Tiny Tiny RSS.\n";
			exit;
		}

		if (!file_exists("config.php")) {
			print "<b>Fatal Error</b>: You forgot to copy
			<b>config.php-dist</b> to <b>config.php</b> and edit it.\n";
			exit;
		}

		// we need a separate check here because functions.php might get parsed
		// incorrectly before 5.3 because of :: syntax.
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			print "<b>Fatal Error</b>: PHP version 5.3.0 or newer required.\n";
			exit;
		}

		define('MOBILE_VERSION', true);

		$basedir = dirname(dirname(dirname(__FILE__)));

		set_include_path(
			dirname(__FILE__) . PATH_SEPARATOR .
			$basedir . PATH_SEPARATOR .
			"$basedir/include" . PATH_SEPARATOR .
			get_include_path());

		require_once "autoload.php";
		require_once "sessions.php";
		require_once "functions.php";
		require_once "sanity_check.php";
		require_once "version.php";
		require_once "config.php";
		require_once "db-prefs.php";
		header("Content-type: text/html; charset=utf-8");
		include dirname(__FILE__)."/index.php";
		die();
	}

	function getFeeds(){
		$db = Db::get();
		// TODO: categories
		$query = "SELECT id, feed_url, cat_id, title, order_id, ".
				SUBSTRING_FOR_DATE."(last_updated,1,19) AS last_updated
				FROM ttrss_feeds
				WHERE owner_uid = " . $_SESSION["uid"] .
				" ORDER BY cat_id, title ";
		$result = $db->query($query);
		$feeds = array();
		if ($db->num_rows($result) != 0) {
			while ($line = $db->fetch_assoc($result)) {
				$unread = getFeedUnread($line["id"]);

				$has_icon = feed_has_icon($line['id']);

				if ($unread || !$unread_only) {

					$feeds[] = array(
						"feed_url" => $line["feed_url"],
						"title" => $line["title"],
						"id" => (int)$line["id"],
						"unread" => (int)$unread,
						"has_icon" => $has_icon,
						"cat_id" => (int)$line["cat_id"],
						"last_updated" => (int) strtotime($line["last_updated"]),
						"order_id" => (int) $line["order_id"],
					);
				}
			}
		}
		return $feeds;
	}

	function loadArticle($idFeed,$page = 0, $limit = 20){
		$db = Db::get();
		$idFeed = $db->escape_string($idFeed);
		$page = $db->escape_string($page);
		$limit = $db->escape_string($limit);

		$articles = array();
		if ($idFeed) {
			$query = "SELECT e.id,e.guid,e.title,e.link,e.content,ue.feed_id,e.comments,ue.int_id, ue.marked,ue.unread,ue.published,ue.score,ue.note,e.lang, SUBSTRING(e.updated,1,16) as updated, e.author,f.title AS feed_title, f.site_url AS site_url, f.hide_images AS hide_images 
				FROM ttrss_user_entries ue
				INNER JOIN ttrss_entries e
				ON e.id = ue.ref_id
				INNER JOIN ttrss_feeds f
				ON f.id = ue.feed_id
				WHERE ue.feed_id = ".$idFeed."
				AND ue.owner_uid = ".$_SESSION["uid"]."
				AND ue.unread = 1 
				GROUP BY e.guid 
				ORDER BY e.updated DESC";
			if($limit > 0){
				$page = $page>=0?$page:0;
				$query .= " LIMIT ".($page*$limit).",".$limit." ";
			}

			$result = $db->query($query);

			if ($db->num_rows($result) != 0) {

				while ($line = $db->fetch_assoc($result)) {
					$articles[] = $line;
				}
			}
		}
		return $articles;
	}

	function markFeedHasRead($idFeed){
		$db = Db::get();
		$feeds = $this->getFeeds();
		$query = "UPDATE ttrss_user_entries
				SET unread = 0
				WHERE owner_uid = ".$_SESSION["uid"]."
				AND feed_id = ".$db->escape_string($idFeed);
		$db->query($query);
		$f = current($feeds);
		while($f !== false){
			if($redirect){
				header('Location: backend.php?op=mobile&fid='.$f['id']);
				exit;
			}elseif($f['id'] = $idFeed){
				$redirect = true;
			}
			$f = next($feeds);
		}
	}

	function markArticleHasRead($idFeed, $idArticle){
		$db = Db::get();
		$query = "UPDATE ttrss_user_entries
				SET unread = 0
				WHERE owner_uid = ".$_SESSION["uid"]."
				AND feed_id = ".$db->escape_string($idFeed)."
				AND ref_id = ".$db->escape_string($idArticle);
		$db->query($query);
	}

	function get_js() {
		return "";
	}

	function get_prefs_js() {
		return "";
	}
}
