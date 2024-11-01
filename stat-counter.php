<?php
  /*
   Plugin Name: Stats Plugin
   Plugin URI: http://www.blog.ca/wordpress-stats-plugin/
   Donate link: http://www.blog.ca/wordpress-stats-plugin/
   Description: Improved real time stats for your website. Url Monitoring, Visitor/Bot Spy, Latest Search Terms, Referrers, New Hits, Feeds, Spiders, Bots and more!
   Version: 3.0.1
   Author: blogca
   Author URI: http://www.blog.ca/
*/
	
	global $wpdb, $STATCOUNTER_Option;
	$STATCOUNTER_Option = get_option('STATCOUNTER_Option');
	
	define("STATCOUNTER_DB_VERSION", "3.0.0");
	define("STATCOUNTER_PLUGIN_URL", plugin_dir_url( __FILE__ ));
	define("STATCOUNTER_PLUGIN_PATH", plugin_dir_path( __FILE__ ));
	define("STATCOUNTER_TABLE_NAME", $wpdb->prefix. "STATCOUNTER");
	$table_name = STATCOUNTER_TABLE_NAME;

	register_activation_hook( __FILE__,'STATCOUNTER_activate');
	register_deactivation_hook( __FILE__,'STATCOUNTER_deactivate');
	register_uninstall_hook( __FILE__,'STATCOUNTER_uninstall');

if ($STATCOUNTER_Option['STATCOUNTER_activate'] != 'installed_activation')
     add_action('admin_notices', 'STATCOUNTER_message');
	 
if ( !function_exists( 'add_action' ) ) 
		{
		echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
		exit;
		}
	
if (is_admin())
      {include STATCOUNTER_PLUGIN_PATH . 'admin/luc_admin.php';
       add_action('init', 'STATCOUNTER_load_textdomain');
       add_action('admin_menu', 'luc_add_pages');
	   add_action('admin_init', 'STATCOUNTER_admin_init');
      }
	  
if (($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked') && (!class_exists('geoiprecord')))
	include_once STATCOUNTER_PLUGIN_PATH . 'GeoIP/geoipcity.inc';
	
	
	add_action('plugins_loaded', 'STATCOUNTER_Widget_init');
	add_action('send_headers', 'luc_StatAppend');
				 
function STATCOUNTER_activate() 
	{
	global $wpdb, $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$old_Install = false; 
	if ( $STATCOUNTER_Option['STATCOUNTER_activate'] == 'installed_activation')
        $old_Install = true; 
	elseif ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)
		 $old_Install = true; 
		
    if ($STATCOUNTER_Option['STATCOUNTER_DB_Version'] <> STATCOUNTER_DB_VERSION)
				{	
				   $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%STATCOUNTER%'");
				   $wpdb->query('OPTIMIZE TABLE ' . $wpdb->options);
				   luc_STATCOUNTER_CreateTable();
				};
		
			
	$STATCOUNTER_Option['STATCOUNTER_activate'] =(($old_Install == true) ?  'update_activation': 'install_activation');
	update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
	}

function STATCOUNTER_deactivate()
    {	
		global $STATCOUNTER_Option;
		$STATCOUNTER_Option['STATCOUNTER_DB_Version'] = "3.3.0";
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
		STATCOUNTER_remove_action();
    }

function STATCOUNTER_uninstall()
    {	
		global $wpdb;
		$table_name = STATCOUNTER_TABLE_NAME;
		delete_option('STATCOUNTER_Option');
        $wpdb->query("DROP TABLE `$table_name`"); 
		STATCOUNTER_remove_action();
    }
  	
function STATCOUNTER_message() 
	{global $STATCOUNTER_Option;
     $opt = $STATCOUNTER_Option['STATCOUNTER_activate'];
	 if ( $opt == 'install_activation' || $opt == 'update_activation')
	 {
    $STATCOUNTER_Option['STATCOUNTER_activate'] = 'installed_activation'; 
	update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
    $msg = __('Settings activated: ', 'STATCOUNTER_domain' );
    $msg .= ($opt == 'install_activation') ? __('STATCOUNTER database created', 'STATCOUNTER_domain' ) : __('STATCOUNTER database updated', 'STATCOUNTER_domain' );
    echo "<div class='updated'><p><strong>$msg</strong></p></div>";
     }
	}

function STATCOUNTER_remove_action()
{		global $STATCOUNTER_Option;
		// Remove all the hook
		// general hooks
		remove_action('plugins_loaded','STATCOUNTER_Widget_init');
		remove_action('init', 'STATCOUNTER_load_textdomain');
        remove_action('admin_menu', 'luc_add_pages');
	    remove_action('admin_init', 'STATCOUNTER_admin_init');
		remove_action('send_headers', 'luc_StatAppend');
		
		// Ajax hook for the Main page
		remove_action('wp_ajax_table_latest_hits', 'luc_main_table_latest_hits');
		remove_action('wp_ajax_table_latest_search', 'luc_main_table_latest_search');
		remove_action('wp_ajax_table_latest_referrers', 'luc_main_table_latest_referrers');
		remove_action('wp_ajax_table_latest_feeds', 'luc_main_table_latest_feeds');
		remove_action('wp_ajax_table_latest_spiders', 'luc_main_table_latest_spiders');
		remove_action('wp_ajax_table_latest_spambots', 'luc_main_table_latest_spambots');
		remove_action('wp_ajax_table_latest_undefagents', 'luc_main_table_latest_undefagents');
		remove_action('wp_ajax_geoipdbupdate', 'luc_GeoIP_update_db');

}

	// a custom function for loading localization
function STATCOUNTER_load_textdomain() 
	{
		//check whether necessary core function exists
		if ( function_exists('load_plugin_textdomain') ) {
		//load the plugin textdomain
		load_plugin_textdomain('STATCOUNTER',false, STATCOUNTER_PLUGIN_PATH . 'locale');
		}
	}
	
function luc_STATCOUNTER_CreateTable()
      {
          global $wpdb, $STATCOUNTER_Option, $wp_db_version;
		  $table_name = STATCOUNTER_TABLE_NAME;
          $sql_createtable = "CREATE TABLE " . $table_name . " (
		id MEDIUMINT(9)UNSIGNED NOT NULL AUTO_INCREMENT,
		date INT(8) UNSIGNED NOT NULL,
		time CHAR(8),
		ip VARCHAR(39),
		urlrequested TEXT,
		agent TEXT,
		referrer TEXT,
		search TEXT,
		os TINYTEXT,
		browser TINYTEXT,
		searchengine TINYTEXT,
		spider TINYTEXT,
		feed TINYTEXT,
		user TINYTEXT,
		timestamp INT(10) UNSIGNED NOT NULL,
		language VARCHAR(3),
		country VARCHAR(3),
		realpost BOOLEAN,
		post_title TINYTEXT,
		UNIQUE KEY id (id),
		KEY `date` (`date`)
		);";
        if ($wp_db_version >= 5540)
			$page = 'wp-admin/includes/upgrade.php';
		else
			$page = 'wp-admin/upgrade-functions.php';
          require_once(ABSPATH . $page);
          dbDelta($sql_createtable);
		  $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_score");
		  $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_type");   
		  $wpdb->query("ALTER TABLE $table_name DROP COLUMN nation"); 
		 
		  $STATCOUNTER_Option['STATCOUNTER_DB_Version'] = STATCOUNTER_DB_VERSION;
		  update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
      }
      
function luc_StatAppend()
      {
          global $wpdb, $STATCOUNTER_Option, $userdata, $_STATCOUNTER;
		  $table_name = STATCOUNTER_TABLE_NAME;
          get_currentuserinfo();
          $feed = '';
          
          // Time
          $timestamp = current_time('timestamp');
          $vdate = gmdate("Ymd", $timestamp);
          $vtime = gmdate("H:i:s", $timestamp);
          
          // IP
          if (strnatcmp(phpversion(),'5.2.0') >= 0) 
				$ipAddress = htmlentities(luc_get_ip());
		  else $ipAddress = htmlentities($_SERVER['REMOTE_ADDR']);
		  
          if (luc_CheckBanIP($ipAddress) === true)
              return '';
	
          // URL (requested)
          $urlRequested = luc_STATCOUNTER_URL();
		  $post_title =luc_post_title_Decode($urlRequested);
		  $real_post=(($post_title==$urlRequested )? 0 : 1);
		  
          $referrer = (isset($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER']) : '');
          $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
          $spider = luc_GetSpider($userAgent);
          
          if (($spider != '') and ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] == 'checked'))
              return '';
          
          if ($spider != '')
          {
              $os = '';
              $browser = '';
          }
          else
            {
              $prsurl = parse_url(get_bloginfo('url'));
              $feed = luc_STATCOUNTER_is_feed($prsurl['scheme'] . '://' . $prsurl['host'] . $_SERVER['REQUEST_URI']);
              $os = luc_GetOS($userAgent);
              $browser = luc_GetBrowser($userAgent);
              list($searchengine, $search_phrase) = explode("|", luc_GetSE($referrer));
            }

			 $code = explode(';',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			 $code = explode(',',$code[0]);
			 $lang = explode('-',$code[0]);
			 $language =$lang[0];
			 $country = $lang[1];
			 
			if (((!isset($lang[1]))  or ($STATCOUNTER_Option['STATCOUNTER_locate_IP'] == 'GeoIP')) & ($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked' & function_exists('geoip_open')))
				{	
					$gi = geoip_open(luc_GeoIP_dbname('country'), GEOIP_STANDARD);
					$cc = geoip_country_code_by_addr($gi, $ipAddress);
					$country = (($cc !== false) ? $cc: NULL);
				}
				
		    $today = gmdate('Ymd', current_time('timestamp'));
		   if ($today <> $STATCOUNTER_Option['STATCOUNTER_Delete_Today']) 
			{ 
			   $STATCOUNTER_Option['STATCOUNTER_Delete_Today'] = $today;
			   update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
			   $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
               $results = $wpdb->query("DELETE FROM " . $table_name . " WHERE date < '" . $yesterday . "'");
			   $results  = $wpdb->query('OPTIMIZE TABLE '. $table_name); 
		    }
              
          if ((!is_user_logged_in()) or (($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] != 'checked')) or($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] == 'checked')and (!current_user_can($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User_MinPermit'])))
         {  $result = $wpdb->query($wpdb->prepare(
			"INSERT INTO $table_name 
				(date,time,ip,urlrequested,agent,referrer,search,os,browser,searchengine,spider,feed,user,timestamp,language,country,realpost ,post_title)
			 VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%s,%s,%d,%s)", 
			   array($vdate,$vtime,$ipAddress,$urlRequested,$userAgent,$referrer,$search_phrase,$os,$browser,$searchengine,$spider,$feed,$userdata->user_login,$timestamp,$language,$country,$real_post,$post_title )
			   ) );  
			   
			   
          }
      }
	    
function luc_get_ip_lazy($key, $use_getenv = false)
{
	$ip = ""; 

	if(!$use_getenv)
	{
		if (isset($_SERVER[$key]) && luc_ip_not_private($_SERVER[$key]))
			$ip = $_SERVER[$key];
	}
	else
	{
		$val = "";
		$val = @getenv($key); 
		if ($val != "" && luc_ip_not_private($val))
			$ip = $val;
	}

	return $ip;
}

function luc_get_ip()
{
	$DoVars = Array('HTTP_X_REAL_IP', 'HTTP_X_CLIENT', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

	$use_getenv = false;
	if ($_SERVER)
		$use_getenv = false;
	else
		$use_getenv = true;

	$ip = "";
	foreach($DoVars as $aVar)
	{
		$ip = luc_get_ip_lazy($aVar, $use_getenv);
		if($ip != "") break;
	}

	return $ip;
}

function luc_ip_not_private($ip)
{if (strnatcmp(phpversion(),'5.2.0') >= 0) 
	{if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))
		return true;
	return false;
	}
}
		
    function luc_STATCOUNTER_is_feed($url) {
   if (stristr($url,get_bloginfo('comments_atom_url')) != FALSE) { return 'COMMENT ATOM'; }
   elseif (stristr($url,get_bloginfo('comments_rss2_url')) != FALSE) { return 'COMMENT RSS'; }
   elseif (stristr($url,get_bloginfo('rdf_url')) != FALSE) { return 'RDF'; }
   elseif (stristr($url,get_bloginfo('atom_url')) != FALSE) { return 'ATOM'; }
   elseif (stristr($url,get_bloginfo('rss_url')) != FALSE) { return 'RSS'; }
   elseif (stristr($url,get_bloginfo('rss2_url')) != FALSE) { return 'RSS2'; }
   elseif (stristr($url,'wp-feed.php') != FALSE) { return 'RSS2'; }
   elseif (stristr($url,'/feed') != FALSE) { return 'RSS2'; }
   return '';
}
    function luc_GetOS($arg)
      {
          $arg = str_replace(" ", "", $arg);
          $lines = file(STATCOUNTER_PLUGIN_PATH . 'def/os.dat');
          foreach ($lines as $line_num => $os)
          {
              list($nome_os, $id_os) = explode("|", $os);
              if (stripos($arg, $id_os) === false) 
				continue;
			  else 
				return $nome_os;
              // riconosciuto
          }
          return '';
      }
      
      function luc_GetBrowser($arg)
      {
          $arg = str_replace(" ", "", $arg);
          $lines = file(STATCOUNTER_PLUGIN_PATH . 'def/browser.dat');
          foreach ($lines as $line_num => $browser)
          {
              list($nome, $id) = explode("|", $browser);
              if (stripos($arg, $id) === false ) 
				continue;
			  else 
				return $nome;
              // riconosciuto
          }
          return '';
      }
      
	  function luc_CheckBanIP($arg)
      {
              $lines = file(STATCOUNTER_PLUGIN_PATH . 'def/banips.dat');
         
        if ($lines !== false)
        {
            foreach ($lines as $banip)
              {
               if (	preg_match('/^' . rtrim($banip, "\r\n") . '$/', $arg))
                   return true;
                  // riconosciuto, da scartare
              }
          }
          return false;
      }
	  
      function luc_GetSE($referrer = null)
      {
          $key = null;
          $lines = file(STATCOUNTER_PLUGIN_PATH . 'def/searchengine.dat');
          foreach ($lines as $line_num => $se)
          {
              list($name, $url, $key, $stop) = explode("|", $se);
              if (stripos($referrer, $url) === false)
			    continue;
			 if ($key===$url)
			 {$query_search = explode($key,$referrer);
			 return ($name . "|" . urlencode($query_search[1]));
			 }
			  if (stripos($key,$url) !== false)
		       { $query_search = explode($key,$referrer);
		         $query_search = explode($stop,$query_search[1]);
		         return ($name . "|" . urlencode($query_search[0]));
		       }
			  
              // trovato se
              $variables = luc_GetQueryPairs($referrer);
              $i = count($variables);
              while ($i--)
              {
                  $tab = explode("=", $variables[$i]);
                  if ($tab[0] == $key)
                      return($name . "|" . urlencode($tab[1]));
              }
          }
          return null;
      }
      
      function luc_GetSpider($agent = null)
      {
          $agent = str_replace(" ", "", $agent);
          $key = null;
          $lines = file(STATCOUNTER_PLUGIN_PATH . 'def/spider.dat');
           foreach ($lines as $line_num => $spider)
          {
              list($nome, $key) = explode("|", $spider);
              if (stripos($agent, $key) === false)
                  continue;
              // trovato
              return $nome;
          }
          return null;
      }
      
	  
      function STATCOUNTER_Print($body = '')
      {
          echo luc_STATCOUNTER_Vars($body);
      }
      
      function luc_STATCOUNTER_Vars($body)
      {
          global $wpdb;
          $table_name = STATCOUNTER_TABLE_NAME;
		  $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
          $today = gmdate('Ymd', current_time('timestamp'));
		  
          if (stripos(strtolower($body), "%today%") !== false)
              $body = str_replace("%today%", luc_hdate($today), $body);
          
          if (strpos(strtolower($body), "%visitorstoday%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE date = $today and spider='' and feed='';");
              $body = str_replace("%visitorstoday%", $qry[0]->visitors, $body);
          }
		   if (strpos(strtolower($body), "%visitorsyesterday%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE date = $yesterday and spider='' and feed='';");
              $body = str_replace("%visitorsyesterday%", $qry[0]->visitors, $body);
          }
		   if (strpos(strtolower($body), "%pageviewstoday%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE date = $today and spider='' and feed='';");
              $body = str_replace("%pageviewstoday%", $qry[0]->pageviews, $body);
          }
		   if (strpos(strtolower($body), "%pageviewsyesterday%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE date = $yesterday and spider='' and feed='';");
              $body = str_replace("%pageviewsyesterday%", $qry[0]->pageviews, $body);
          }
          if (strpos(strtolower($body), "%thistodaypageviews%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $today AND urlrequested='" . mysql_real_escape_string(luc_STATCOUNTER_URL()) . "';");
              $body = str_replace("%thistodaypageviews%", $qry[0]->pageviews, $body);
          }
		  if (strpos(strtolower($body), "%thisyesterdaypageviews%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $yesterday AND urlrequested='" . mysql_real_escape_string(luc_STATCOUNTER_URL()) . "';");
              $body = str_replace("%thisyesterdaypageviews%", $qry[0]->pageviews, $body);
          }
		  if (strpos(strtolower($body), "%thistodayvisitors%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(distinct ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $today AND urlrequested='" . mysql_real_escape_string(luc_STATCOUNTER_URL()) . "';");
              $body = str_replace("%thistodayvisitors%", $qry[0]->pageviews, $body);
          }
		  if (strpos(strtolower($body), "%thisyesterdayvisitors%") !== false)
          {
              $qry = $wpdb->get_results("SELECT count(distinct ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $yesterday AND urlrequested='" . mysql_real_escape_string(luc_STATCOUNTER_URL()) . "';");
              $body = str_replace("%thisyesterdayvisitors%", $qry[0]->pageviews, $body);
          }
		  
          if (strpos(strtolower($body), "%os%") !== false)
          {
              $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
              $os = luc_GetOS($userAgent);
              $body = str_replace("%os%", $os, $body);
          }
          if (strpos(strtolower($body), "%browser%") !== false)
          {
              $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
              $browser = luc_GetBrowser($userAgent);
              $body = str_replace("%browser%", $browser, $body);
          }
          if (strpos(strtolower($body), "%ip%") !== false)
          {
              $ipAddress = $_SERVER['REMOTE_ADDR'];
              $body = str_replace("%ip%", $ipAddress, $body);
          }
          if (strpos(strtolower($body), "%visitorsonline%") !== false)
          {
              $to_time = current_time('timestamp');
              $from_time = strtotime('-4 minutes', $to_time);
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE spider='' and feed='' AND timestamp BETWEEN $from_time AND $to_time;");
              $body = str_replace("%visitorsonline%", $qry[0]->visitors, $body);
          }
          if (strpos(strtolower($body), "%usersonline%") !== false)
          {
              $to_time = current_time('timestamp');
              $from_time = strtotime('-4 minutes', $to_time);
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as users FROM $table_name WHERE spider='' and feed='' AND user<>'' AND timestamp BETWEEN $from_time AND $to_time;");
              $body = str_replace("%usersonline%", $qry[0]->users, $body);
          }
          if (strpos(strtolower($body), "%toppost%") !== false)
          {
              $qry = $wpdb->get_results("SELECT urlrequested, count(ip) as totale FROM $table_name WHERE spider='' AND feed='' AND urlrequested <>'' GROUP BY urlrequested ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%toppost%", luc_post_title_Decode($qry[0]->urlrequested), $body);
          }
          if (strpos(strtolower($body), "%topbrowser%") !== false)
          {
              $qry = $wpdb->get_results("SELECT browser,count(*) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY browser ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%topbrowser%", luc_post_title_Decode($qry[0]->browser), $body);
          }
          if (strpos(strtolower($body), "%topos%") !== false)
          {
              $qry = $wpdb->get_results("SELECT os,count(id) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY os ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%topos%", luc_post_title_Decode($qry[0]->os), $body);
          }
         
      	  if (strpos(strtolower($body), "%latesthits%") !== false)
			{
				$qry = $wpdb->get_results("SELECT search FROM $table_name WHERE search <> '' ORDER BY id DESC LIMIT 10");
				$body = str_replace("%latesthits%", urldecode($qry[0]->search), $body);
				for ($counter = 0; $counter < 10; $counter += 1)
				{
					$body .= "<br>". urldecode($qry[$counter]->search);
				}
			}
          
          return $body;
      }
function STATCOUNTER_Widget_init($args)
{	  
	function STATCOUNTER_Widget_control()
{
	global $STATCOUNTER_Option;

	$options = $STATCOUNTER_Option['STATCOUNTER_Widget'];
	if (!is_array($options))
		$options = array (
			'title' => 'Visitor Stats',
			'body' => 'Today : %today%'
		);
	if ($_POST['STATCOUNTER-submit'])
	{
		$options['title'] = strip_tags(stripslashes($_POST['STATCOUNTER-title']));
		$options['body'] = stripslashes($_POST['STATCOUNTER-body']);

		$STATCOUNTER_Option['STATCOUNTER_Widget'] = $options;
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	$body = htmlspecialchars($options['body'], ENT_QUOTES);
	// the form
	?>
	<p style="text-align:left;">
		<label for="STATCOUNTER-title"><?php _e(__('Title:')) ?> <br>
		<input style="width:100%;" id="STATCOUNTER-title" name="STATCOUNTER-title" type="text" value="<?php _e($title) ?>" />
		</label>
	</p>
	<p style="text-align:right;">
		<label for="STATCOUNTER-body"><div><?php _e(__('Body:', 'widgets')) ?> </div> <br>
		<textarea style="width:100%;height:100px;" id="STATCOUNTER-body" name="STATCOUNTER-body" type="textarea"><?php _e($body) ?></textarea>
		</label>
	</p>
	<input type="hidden" id="STATCOUNTER-submit" name="STATCOUNTER-submit" value="1" />

	<strong>Available Macros:</strong>
	<div style="font-size:7pt;"> %today% %visitorstoday% %visitorsyesterday% %pageviewstoday% %pageviewsyesterday% %thistodaypageviews% %thisyesterdaypageviews% %thistodayvisitors% %thisyesterdayvisitors% %os% %browser% %ip% %visitorsonline% %usersonline% %toppost% %latesthits% %topbrowser% %topos% %pagestoday% %thistotalpages% %latesthits%</div>
	<?php
}

function STATCOUNTER_Widget($args)
{
	global $STATCOUNTER_Option;

	extract($args);
	$options = $STATCOUNTER_Option['STATCOUNTER_Widget'];
	$title = $options['title'];
	$body = $options['body'];
	echo $before_widget;
	echo ($before_title . $title . $after_title);
	echo luc_STATCOUNTER_Vars($body);
	echo $after_widget;
}


// Top posts
function STATCOUNTER_Widget_TopPosts_control()
{
	global $STATCOUNTER_Option;

	$options = $STATCOUNTER_Option['STATCOUNTER_Widget_TopPosts'];
	if (!is_array($options))
	{
		$options = array (
			'title' => 'STATCOUNTER Visitors Top Posts',
			'howmany' => '5',
			'howlong' => '0',
			'showcounts' => 'checked',
			'showpages' => 'checked'
		);
	}
	if ($_POST['STATCOUNTERtopposts-submit'])
	{
		$options['title'] = strip_tags(stripslashes($_POST['STATCOUNTERtopposts-title']));
		$options['howmany'] = stripslashes($_POST['STATCOUNTERtopposts-howmany']);
		$options['howlong'] = stripslashes($_POST['STATCOUNTERtopposts-howlong']);
		$options['showcounts'] = stripslashes($_POST['STATCOUNTERtopposts-showcounts']);
		if ($options['showcounts'] == "1")
		{
			$options['showcounts'] = 'checked';
		}
		$options['showpages'] = stripslashes($_POST['STATCOUNTERtopposts-showpages']);
		if ($options['showpages'] == "1")
		{
			$options['showpages'] = 'checked';
		}
		$STATCOUNTER_Option['STATCOUNTER_Widget_TopPosts'] = $options;
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	$howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
	$howlong = htmlspecialchars($options['howlong'], ENT_QUOTES);
	$showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
	$showpages = htmlspecialchars($options['showpages'], ENT_QUOTES);
	// the form
	?>
	<p style="text-align:left;">
		<label for="STATCOUNTERtopposts-title"><?php _e(__('Title:', 'STATCOUNTER')) ?>
		<input style="width:100%;" id="statpress-title" name="STATCOUNTERtopposts-title" type="text" value="<?php _e($title) ?>" />
		</label>
	</p>
	<p style="text-align:left;">
		<label for="STATCOUNTERtopposts-howmany"><?php _e(__('Limit results to:', 'STATCOUNTER')) ?>
		<input style="width:40px; align:right;" id="STATCOUNTERtopposts-howmany" name="STATCOUNTERtopposts-howmany" type="text" value="<?php _e($howmany) ?>" />
		</label>
	</p>


	<p style="text-align:left;">
		<label for="STATCOUNTERtopposts-howlong"><?php _e(__('Include # days (0 for all):', 'STATCOUNTER')) ?>
		<input style="width:40px; align:right" id="STATCOUNTERtopposts-howlong" name="STATCOUNTERtopposts-howlong" type="text" value="<?php _e($howlong ) ?>" />
		</label>
	</p>
	<p style="text-align:right;">
		<label for="STATCOUNTERtopposts-showcounts"><?php _e(__('Visits', 'STATCOUNTER')) ?>
		<input id="STATCOUNTERtopposts-showcounts" name="STATCOUNTERtopposts-showcounts" type=checkbox value="checked" <?php _e($showcounts) ?> />
		</label>
	</p>
	<p style="text-align:right;">
		<label for="STATCOUNTERtopposts-showpages"><?php _e(__('Include Pages', 'STATCOUNTER')) ?>
		<input id="STATCOUNTERtopposts-showpages" name="STATCOUNTERtopposts-showpages" type=checkbox value="checked" <?php _e($showpages) ?> />
		</label>
	</p>
	<input type="hidden" id="statpress-submitTopPosts" name="STATCOUNTERtopposts-submit" value="1" />
	<?php
}

function STATCOUNTER_Widget_TopPosts($args)
{
	global $STATCOUNTER_Option;

	extract($args);
	$options = $STATCOUNTER_Option['STATCOUNTER_Widget_TopPosts'];
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	$howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
	$howlong = htmlspecialchars($options['howlong'], ENT_QUOTES);
	$showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
	$showpages = htmlspecialchars($options['showpages'], ENT_QUOTES);
	echo $before_widget;
	echo ($before_title . $title . $after_title);
	echo luc_STATCOUNTER_TopPosts($howmany, $howlong, $showcounts, $showpages);
	echo $after_widget;
}

function luc_STATCOUNTER_TopPosts($limit = 5, $numdays = 0, $showcounts = 'checked', $showpages = 'checked')
{
	global $wpdb;
	$table_name = STATCOUNTER_TABLE_NAME;
	$res = "\n<ul>\n";
	
	if ($numdays == 0)
	{ // All dates chosen, default to epoch
		$stopdate = date('Ymd', strtotime('1970-01-01'));
	}
	else
		if ($numdays < 0)
		{ // Negative number of days, no change
			$stopdate = date('Ymd', strtotime($numdays . 'days'));
		}
		else
		{ // Invert sign
			$numdays = $numdays * -1;
			$stopdate = date('Ymd', strtotime($numdays . 'days'));
		}

	if (strtolower($showpages) == 'checked')
		$type = "(post_type = 'page' OR post_type = 'post')";
	else
		$type = "post_type = 'post'";

	$qry_s = "SELECT post_name, COUNT(*) as total, urlrequested
						FROM $wpdb->posts as p
						JOIN $table_name as t
						ON urlrequested LIKE CONCAT('%', p.post_name, '_' )
						WHERE post_status = 'publish'
							AND $type
							AND spider=''
							AND feed=''
							AND date >= $stopdate
						GROUP BY post_name
						ORDER BY total DESC LIMIT $limit;";

	$qry = $wpdb->get_results($qry_s);

	foreach ($qry as $rk)
	{
		$res .= "<li><a href='" .
		luc_GetBlogURL() .
		 ((strpos($rk->urlrequested, 'index.php') === FALSE) ? $rk->urlrequested : '') .
		"'>" . luc_post_title_Decode($rk->post_name) . "</a>";
		if (strtolower($showcounts) == 'checked')
		{
			$res .= " (" . $rk->total . ")</li>";
		}
	}
	return "$res</ul>\n";
}

	wp_register_sidebar_widget('STATCOUNTER', 'STATCOUNTER Stats Macros', 'STATCOUNTER_Widget', array('description' => 'Show a off your statistics in a widget'));
	wp_register_widget_control('STATCOUNTER', 'STATCOUNTER Stats Macros', 'STATCOUNTER_Widget_control');

	wp_register_sidebar_widget('STATCOUNTERTopPosts', 'STATCOUNTER V Top Posts', 'STATCOUNTER_Widget_TopPosts', array('description' => 'Show a configurable list of your most popular posts & pages'));
	wp_register_widget_control('STATCOUNTERTopPosts', 'STATCOUNTER V Top Posts', 'STATCOUNTER_Widget_TopPosts_control');
}
  
function permalinksEnabled()
{	global $wpdb;
      
	$result = $wpdb->get_row('SELECT option_value FROM $wpdb->options WHERE option_name = "permalink_structure"');
	if ($result->option_value != '' ) 
		return true; 
	else return false;
}
  
  function my_substr($str, $x, $y = 0)
  {
  	if($y == 0)
  		$y = strlen($str) - $x;
 	if (function_exists('mb_substr') )
	return  mb_substr($str, $x, $y);
	else return  substr($str, $x, $y);
  }
  
  function luc_permalink()
	      { global $wpdb;
            
	        $permalink = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'permalink_structure'");
	        $permalink = explode("%", $permalink);
	        return $permalink[0];
	       }
	  
     function luc_post_title_Decode($out_url)
{ 
	$permalink = luc_permalink();
    	
	$home_url = array ( '' , '/' . $perm[1] , $permalink , 'fb_xd_fragment') ;
	if (($permalink == '') and ( in_array($out_url,$home_url)))
	    $out_url = '[' . __('Page', 'STATCOUNTER') . "]: Home"; 
	else
	{
		$perm = explode('/', $permalink);
		if (($permalink != '') and ( in_array($out_url,$home_url) or 
				(strpos($out_url, $permalink . 'feed') === 0) or 
				(strpos($out_url, $permalink . 'comments') === 0)))
			$out_url = '[' . __('Page', 'STATCOUNTER') . "]: Home"; 
		else
		{
			$post_id = url_to_postid($out_url);

			if ($post_id == 0)
				return $out_url;
	
			$post_id = get_post($post_id, ARRAY_A);
	
			if ($post_id['post_type'] == 'page')
				$post_t = '[' . __('Page', 'STATCOUNTER') . ']: ' . $post_id['post_title'];
			elseif ($post_id['post_type'] == 'attachment')
					$post_t = '[' . __('File', 'STATCOUNTER') . ']: ' . $post_id['post_title'];
				elseif ($post_id['post_type'] == 'post')
						$post_t = $post_id['post_title'];
					else
						$post_t = '';
	
			$out_url = (($post_t == '') ? $out_url:$post_t);
		}
	}
	return $out_url;
} 
   
      function luc_STATCOUNTER_URL()
      {
          $urlRequested = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '');
          if (my_substr($urlRequested, 0, 2) == '/?')
              $urlRequested = my_substr($urlRequested, 2);
          if ($urlRequested == '/')
              $urlRequested = '';
          return $urlRequested;
      }
      
      function luc_getblogurl()
      {
      	$prsurl = parse_url(get_bloginfo('url'));
      	return $prsurl['scheme'] . '://' . $prsurl['host'] . ((!permalinksEnabled()) ? $prsurl['path'] . '/?' : '');
      }
      
      function luc_hdate($dt = "00000000")
      {
          return mysql2date(get_option('date_format'), my_substr($dt, 0, 4) . "-" . my_substr($dt, 4, 2) . "-" . my_substr($dt, 6, 2));
      }
      
      function luc_GetQueryPairs($url)
      {
          $parsed_url = parse_url($url);
          $tab = parse_url($url);
          $host = $tab['host'];
          if (key_exists("query", $tab))
          {
              $query = $tab["query"];
              $query = str_replace("&amp;", "&", $query);
              $query = urldecode($query);
              $query = str_replace("?", "&", $query);
              return explode("&", $query);
          }
          else
          {
              return null;
          }
      }
    
	function luc_GeoIP_dbname($edition)
{
	$geoip_db_name = (('city' == $edition )? ABSPATH . 'wp-content/GeoIP/GeoLiteCity.dat': ABSPATH . 'wp-content/GeoIP/GeoIP.dat');
	return $geoip_db_name;
}
	
?>