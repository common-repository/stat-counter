<?php
	  
function luc_add_pages()
  {
      global $wpdb,$STATCOUNTER_Option;
      $table_name = STATCOUNTER_TABLE_NAME;
	  
	  // Create table if it doesn't exist
       if (($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) or ($STATCOUNTER_Option['STATCOUNTER_DB_Version'] <> STATCOUNTER_DB_VERSION))
                  {$wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_score");
				   $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_type");
				   $STATCOUNTER_Option['STATCOUNTER_DB_Version'] = STATCOUNTER_DB_VERSION;
				   update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
				   luc_STATCOUNTER_CreateTable();
				  };

      $mincap = $STATCOUNTER_Option['STATCOUNTER_MinPermit'];
      if ($mincap == '')
          $mincap = 'switch_themes';
		  
	
	if (isset($_GET['STATCOUNTER-action']))
	{	 
		if ($_GET['STATCOUNTER-action'] == 'updategeoipdat')
			luc_GeoIP_update_db('country');
		if ($_GET['STATCOUNTER-action'] == 'updategeoipcitydat')
			luc_GeoIP_update_db('city');
	}
	
	  // add submenu
      add_menu_page('Stats Plugin', 'Stats Plugin', $mincap, __FILE__, 'luc_main',STATCOUNTER_PLUGIN_URL.'/images/stat.png');
      add_submenu_page(__FILE__, __('Visitor Spy', 'STATCOUNTER'), __('Visitor Spy', 'STATCOUNTER'), $mincap,'STATCOUNTER/action=spyvisitors', 'luc_spy_visitors');
	  if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] == '')
	  add_submenu_page(__FILE__, __('Bot Spy', 'STATCOUNTER'), __('Bot Spy', 'STATCOUNTER'), $mincap,'STATCOUNTER/action=spybot', 'luc_spy_bot');
	  add_submenu_page(__FILE__, __('Yesterday ', 'STATCOUNTER'), __('Yesterday ', 'STATCOUNTER'), $mincap,'STATCOUNTER/action=yesterday', 'luc_yesterday');
	  add_submenu_page(__FILE__, __('Referrer', 'STATCOUNTER'), __('Referrer', 'STATCOUNTER'), $mincap, 'STATCOUNTER/action=referrer', 'luc_STATCOUNTER_referrer');
	  add_submenu_page(__FILE__, __('URL Monitoring', 'STATCOUNTER'), __('URL Monitoring', 'STATCOUNTER'), $mincap, 'STATCOUNTER/action=urlmonitoring', 'luc_STATCOUNTER_url_monitoring');
	  add_submenu_page(__FILE__, __('Statistics', 'STATCOUNTER'), __('Statistics', 'STATCOUNTER'), $mincap,'STATCOUNTER/action=details','luc_statistics');
      add_submenu_page(__FILE__, __('Options', 'STATCOUNTER'), __('Options', 'STATCOUNTER'), $mincap,'STATCOUNTER/action=options', 'luc_Options');
  }
  
function luc_STATCOUNTER_load_time()
{
	echo "<font size='1'><br>Stats Plugin page generated in " . timer_stop(0,2) . " seconds with ".get_num_queries()." SQL queries.</font>";
}
  
function STATCOUNTER_admin_init()
{
	global $wpdb,$STATCOUNTER_Option;
	
	// Add JQuery support
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
		
	// jQuery Tabs
	wp_enqueue_script('jquery-ui-tabs');
	
	// jQuery Pagination
	//wp_enqueue_script('jquery-pagination', STATCOUNTER_PLUGIN_URL.'js/jquery.pagination.js', array ('jquery', 'jquery-ui-core'));
	
	//  Add AJAX support
	wp_enqueue_script('luc-ajax-geoip', STATCOUNTER_PLUGIN_URL.'js/luc.ajax.geoip.js', array ('jquery'));
	//wp_enqueue_script('luc-ajax-pagination', STATCOUNTER_PLUGIN_URL.'js/luc.ajax.pagination.js', array ('jquery'));
	wp_enqueue_script('luc-ajax-tables', STATCOUNTER_PLUGIN_URL.'js/luc.ajax.tables.js', array ('jquery'));
	
	// jQuery CSS
	wp_enqueue_style('jquery.ui.theme', STATCOUNTER_PLUGIN_URL.'css/jquery-ui-1.8.16.custom.css');
	wp_enqueue_style('jquery.pagination.theme', STATCOUNTER_PLUGIN_URL.'css/pagination.css');
	wp_enqueue_style('jquery.overrides', STATCOUNTER_PLUGIN_URL.'css/jquery.override.css');
	
	//  Add AJAX support
	//wp_enqueue_script('my-ajax-request', STATCOUNTER_PLUGIN_URL.'js/ajax.js', array ('jquery'));

	// Handlers for AJAX tables in admin
	add_action('wp_ajax_table_latest_hits', 'luc_main_table_latest_hits');
	add_action('wp_ajax_table_latest_search', 'luc_main_table_latest_search');
	add_action('wp_ajax_table_latest_referrers', 'luc_main_table_latest_referrers');
	add_action('wp_ajax_table_latest_feeds', 'luc_main_table_latest_feeds');
	add_action('wp_ajax_table_latest_spiders', 'luc_main_table_latest_spiders');
	add_action('wp_ajax_table_latest_spambots', 'luc_main_table_latest_spambots');
	add_action('wp_ajax_table_latest_undefagents', 'luc_main_table_latest_undefagents');
	
	//AJAX handler for GeoIP database downloads
	add_action('wp_ajax_geoipdbupdate', 'luc_GeoIP_update_db');
	add_action('wp_ajax_page_views_table', 'luc_callback_page_views_table');
	
}

function luc_Options()
  {  	global $wpdb;
		global $STATCOUNTER_Option;
		if ($_POST['saveit'] == 'yes')
  
      {   // General Tab
	
		$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = (isset($_POST['STATCOUNTER_Use_GeoIP']) ? $_POST['STATCOUNTER_Use_GeoIP'] : '');
		$STATCOUNTER_Option['STATCOUNTER_MinPermit'] = (isset($_POST['STATCOUNTER_MinPermit']) ? $_POST['STATCOUNTER_MinPermit'] : $STATCOUNTER_Option['STATCOUNTER_MinPermit']);

		// Data Collection and Retention
		$STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] = (isset($_POST['STATCOUNTER_Dont_Collect_Logged_User']) ? $_POST['STATCOUNTER_Dont_Collect_Logged_User'] : '');
		$STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User_MinPermit'] = (isset($_POST['STATCOUNTER_Dont_Collect_Logged_User_MinPermit']) ? $_POST['STATCOUNTER_Dont_Collect_Logged_User_MinPermit'] : '');
		$STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] = (isset($_POST['STATCOUNTER_Dont_Collect_Spider']) ? $_POST['STATCOUNTER_Dont_Collect_Spider'] : '');

		// Pages Options
		$STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page'] = (isset($_POST['STATCOUNTER_SpyVisitor_IP_Per_Page']) ? $_POST['STATCOUNTER_SpyVisitor_IP_Per_Page'] : $STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page']);
		$STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP'] = (isset($_POST['STATCOUNTER_SpyVisitor_Visits_Per_IP']) ? $_POST['STATCOUNTER_SpyVisitor_Visits_Per_IP'] : $STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP']);
		$STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] = (isset($_POST['STATCOUNTER_Rows_Per_Latest']) ? $_POST['STATCOUNTER_Rows_Per_Latest'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
		$STATCOUNTER_Option['STATCOUNTER_Show_Browser_name']= (isset($_POST['STATCOUNTER_Show_Browser_name']) ? $_POST['STATCOUNTER_Show_Browser_name'] : '');
		$STATCOUNTER_Option['STATCOUNTER_Show_OS_name']= (isset($_POST['STATCOUNTER_Show_OS_name']) ? $_POST['STATCOUNTER_Show_OS_name'] : '');
		$STATCOUNTER_Option['STATCOUNTER_Show_domain_name']= (isset($_POST['STATCOUNTER_Show_domain_name']) ? $_POST['STATCOUNTER_Show_domain_name'] : '');
		$STATCOUNTER_Option['STATCOUNTER_locate_IP']= (isset($_POST['STATCOUNTER_locate_IP']) ? $_POST['STATCOUNTER_locate_IP'] : $STATCOUNTER_Option['STATCOUNTER_locate_IP']);
		
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
        echo "<br /><div class='wrap' align ='center'><h2>" . __('Recorded !', 'STATCOUNTER') . "<h2>
		  <IMG style='border:0px;width:20px;height:20px;' SRC='".STATCOUNTER_PLUGIN_URL."/images/ok.gif'></div>";
        
      }
	  ?>

	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.tabbed').tabs();
	});
	</script>
<div class='wrap'>
	<h2>Options</h2>
	<form method=post>
		<div class="tabbed">
		<!-- The tabs -->
			<ul>
				<li><a href="#tabs-1">General</a></li>
				<li><a href="#tabs-2">GeoIP</a></li>
			</ul>
<!-- tab 1 -->
			<div id="tabs-1">
				<table class=widefat>
				
					<thead><tr>
						<th scope='col' colspan='3'>Data Collection</th>
					</tr>
			</thead>
			<tbody>		
		<tr><td colspan='2'><input type=checkbox name='STATCOUNTER_Dont_Collect_Logged_User' id='STATCOUNTER_Dont_Collect_Logged_User' value='checked' 
		<?php echo $STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] ?> >
		<label for='STATCOUNTER_Dont_Collect_Logged_User'>Do not collect data about logged users who have at least these capability</label>
		<select name="STATCOUNTER_Dont_Collect_Logged_User_MinPermit"><?php luc_dropdown_caps($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User_MinPermit']); ?></select>
		<a href="http://codex.wordpress.org/Roles_and_Capabilities" target='_blank'>more info</a><br>
		<input type=checkbox name='STATCOUNTER_Dont_Collect_Spider' id='STATCOUNTER_Dont_Collect_Spider' value='checked' 
		<?php echo $STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] ?> >
		<label for='STATCOUNTER_Dont_Collect_Spider'>Do not collect spiders visits</label></td><td></td></tr>
		
		</tbody>
		<thead>
		<tr>
			<th scope='col' colspan='3'>Access Control</th>
					</tr>
		</thead>
		<tbody>
          <tr><td width=200px>Minimum capability to view statistics</td>
		  <td><select name="STATCOUNTER_MinPermit"><?php luc_dropdown_caps($STATCOUNTER_Option['STATCOUNTER_MinPermit']); ?></select>
		    <a href="http://codex.wordpress.org/Roles_and_Capabilities" target='_blank'>more info</a>
          </td><td></td></tr>
		  </tbody>
		  
		 
		 <thead><tr>
						<th scope='col' colspan='3'>Main page :</th>
					</tr>
			</thead>
			<tbody>		
		<tr><td colspan='2'> 
		<input type=checkbox name='STATCOUNTER_Show_OS_name' id='STATCOUNTER_Show_OS_name' value='checked' 
		<?php echo $STATCOUNTER_Option['STATCOUNTER_Show_OS_name'] ?> >
		<label for='STATCOUNTER_Show_OS_name'>Show the name of OS</label>
		<br>
		<input type=checkbox name='STATCOUNTER_Show_Browser_name' id='STATCOUNTER_Show_Browser_name' value='checked' 
		<?php echo $STATCOUNTER_Option['STATCOUNTER_Show_Browser_name'] ?> >
		<label for='STATCOUNTER_Show_Browser_name'>Show the name of browsers</label>
		</td><td></td></tr>
					<tr>
						 <td >'Latest' Reports : Default number of rows</td>
						 <td>
						 <select name="STATCOUNTER_Rows_Per_Latest">
								<option value="5"
								<?php if ($STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] == 5)
									echo "selected"; ?>>5</option>
								<option value="10"
								<?php if ($STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] == 10)
									echo "selected"; ?>>10</option>
								<option value="25"
								<?php if ($STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] == 25)
									echo "selected"; ?>>25</option>
								<option value="50"
								<?php if ($STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] == 50)
									echo "selected"; ?>>50</option>
								<option value="100"
								<?php if ($STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest'] == 100)
									echo "selected"; ?>>100</option>
						</select>
						</td><td></td>
					</tr>
					</tbody>
				 <thead><tr>
						<th scope='col' colspan='3'>Visitor Spy</th>
					</tr>
			</thead>
			 <tbody>		
          <tr><td>Visitors per page
		  <select name="STATCOUNTER_SpyVisitor_IP_Per_Page">
		      <option value="20" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page'] == 20) 
		                                   echo "selected"; ?>>20</option>
			  <option value="50" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page'] == 50) 
			                               echo "selected"; ?>>50</option>
              <option value="100" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page'] == 100)
                                           echo "selected"; ?>>100</option>
         </select>
		 <br>
		 Visits per visitor&nbsp;&nbsp;
         <select name="STATCOUNTER_SpyVisitor_Visits_Per_IP">
		      <option value="20" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP'] == 20)
                                           echo "selected"; ?>>20</option>
              <option value="50" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP'] == 50)
                                           echo "selected"; ?>>50</option>
              <option value="100" <?php if ($STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP'] == 100)
                                           echo "selected"; ?>>100</option>
         </select>
		 </td><td></td><td></td></tr>
		 </tbody>	
		 </table>
		 </div>
<!-- tab 2 -->
			<div id="tabs-2">
			<table class=widefat>
			

		 <?php // Use GeoIP? http://geolite.maxmind.com/download/geoip/api/php/
						if (($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked')
							AND (!function_exists('geoip_open')))
							include STATCOUNTER_PLUGIN_PATH .'/GeoIP/geoipcity.inc';
						luc_GeoIP(); 
					?>
			</div>
			</div><!-- tabbed -->
		 <br>
		 <input type=submit class="button-primary" value="Save options" >
		
         <input type=hidden name=saveit value=yes>
         <input type=hidden name=page value=STATCOUNTER>
		 <input type=hidden name=STATCOUNTER-action value=options>

         </form>
         </div>
<?php
		luc_STATCOUNTER_load_time();
      }

function luc_STATCOUNTER_SearchQ($surl)
{
	$url = parse_url($surl);
	if (strpos($url['host'], "google") > 0)
	{
		$p = strpos($surl, 'url=');
		if ($p === false)
			return $surl;
		$surl = substr($surl, 0, $p);
	}
	return $surl;
}
  
function luc_main_table_latest_hits()
{
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['hitsrows']) ? $_POST['hitsrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat'>
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Language</th>
			<th scope='col'>Country</th>
			<th scope='col' width="40%">Page</th>
			<th scope='col'>OS</th>
			<th scope='col'>Browser</th>
		<?php if (($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] != 'checked') or (($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] == 'checked')and (current_user_can($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User_MinPermit'])))) echo "<th scope='col'>User</th>"; ?>
			<th scope='col'>Feed</th>
		</tr>
		</thead>
		<tbody>
	<?php

	$rks = $wpdb->get_results("SELECT date, time, ip,urlrequested, os, browser,feed,user, language, country, post_title
			FROM $table_name
			WHERE (os<>'' OR browser <>'')
				AND spider NOT LIKE '%Spam Bot%'
			ORDER BY id DESC LIMIT $querylimit;");
	$text_OS = ($STATCOUNTER_Option['STATCOUNTER_Show_OS_name']=='checked') ? true : false;
	$text_browser =($STATCOUNTER_Option['STATCOUNTER_Show_Browser_name']=='checked' )? true : false;
	
	foreach ($rks as $rk)
	{
		echo "<tr>
					<td>" . luc_hdate($rk->date) . "</td>
					<td>" . $rk->time . "</td>
					<td>" . luc_create_href($rk->ip, 'ip') . "</td>";
				echo "
					<td>" . luc_language($rk) . "</td>
					<td>" . luc_HTML_IMG($rk->country, 'country', false) . "</td>";
				echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
				echo 
					"<td>" . luc_HTML_IMG($rk->os, 'os', $text_OS) . "</td>
					<td>" . luc_HTML_IMG($rk->browser, 'browser', $text_browser) . "</td>";
		if (($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] != 'checked') or (($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] == 'checked')and (current_user_can($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User_MinPermit']))))
			{echo (($rk->user != '') ? "<td>" .$rk->user. "</td>" : "<td>&nbsp;</td>");}
		echo "	<td>" . luc_HTML_IMG($rk->feed, 'feed', false) . "</td>
				</tr>";
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset($_POST['hitsrows']))
		die();
}

function luc_main_table_latest_search()
{
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['searchrows']) ? $_POST['searchrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Language</th>
			<th scope='col'>Country</th>
			<th scope='col'>Terms</th>
			<th scope='col' width="40%">Page</th>
			<th scope='col'>Engine</th>
		</tr>
		</thead>
		<tbody>
	<?php

	$qry = $wpdb->get_results("SELECT date, time, ip, urlrequested, referrer, search, searchengine, os, language, country, post_title
			FROM $table_name
			WHERE search<>''
			ORDER BY id DESC LIMIT $querylimit;");
				
	foreach ($qry as $rk)
	{
		echo "<tr>
					<td>" . luc_hdate($rk->date) . "</td>
					<td>" . $rk->time . "</td>
					<td>" . luc_create_href($rk->ip, 'ip') . "</td>";
				echo "
					<td>" . luc_language($rk) . "</td>
					<td>" . luc_HTML_IMG($rk->country, 'country', false) . "</td>
					<td><a target='_blank' href='" . $rk->referrer . "' title='Go to search page...'>" . urldecode($rk->search) . "</a></td>";
				echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
				echo "<td>" . luc_HTML_IMG($rk->searchengine, 'searchengine', false) . "</td>
				</tr>";
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['searchrows']))
		die();
}

function luc_main_table_latest_referrers()
{
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['referrersrows']) ? $_POST['referrersrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat' width="100%" >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
		<?php if ($STATCOUNTER_Option['STATCOUNTER_Show_domain_name']=='checked') echo "<th scope='col'>Domain</th>" ?>
			<th scope='col'>Language</th>
			<th scope='col'>Country</th>
			<th scope='col' width="30%">URL</th>
			<th scope='col' width="30%">Page</th>
		</tr>
		</thead>
		<tbody>

	<?php

	$qry = $wpdb->get_results("SELECT date, time, ip, referrer, urlrequested, os, language, country, post_title
			FROM $table_name
			WHERE (referrer NOT LIKE '" . get_option('home') . "%')
				AND spider=''
				AND referrer <>''
				AND searchengine=''
			ORDER BY id DESC LIMIT $querylimit;");
	foreach ($qry as $rk)
	{
		echo "<tr>
			<td>" . luc_hdate($rk->date) . "</td>
			<td>" . $rk->time . "</td>
			<td>" . luc_create_href($rk->ip, 'ip') . "</td>";
				echo "
			<td>" . luc_language($rk) . "</td>
			<td>" . luc_HTML_IMG($rk->country, 'country', false) . "</td>
			<td><a target='_blank' href='" . urldecode($rk->referrer) . "'>" . urldecode($rk->referrer) . "</a></td>";
				echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
				echo "</tr>\n";
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['referrersrows']))
		die();
}

function luc_main_table_latest_feeds()
{
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['feedsrows']) ? $_POST['feedsrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
		<?php if ($STATCOUNTER_Option['STATCOUNTER_Show_domain_name']=='checked') echo "<th scope='col'>Domain</th>" ?>
			<th scope='col'>Language</th>
			<th scope='col'>Country</th>
			<th scope='col' width="40%">Page</th>
			<th scope='col'>Feed</th>
	<?php

	if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] != 'checked')
		echo "<th scope='col'>User</th>";
	?>
		</tr>
		</thead>
		<tbody>
	<?php

	$qry = $wpdb->get_results("SELECT date, time, ip, urlrequested, feed, language, country, post_title
			FROM $table_name
			WHERE feed<>''
			AND spider=''
			ORDER BY id DESC LIMIT $querylimit;");
	foreach ($qry as $rk)
	{
		echo "<tr>
					<td>" . luc_hdate($rk->date) . "</td>
					<td>" . $rk->time . "</td>
					<td>" . luc_create_href($rk->ip, 'ip') . "</td>";
				echo "
					<td>" . luc_language($rk) . "</td>
					<td>" . luc_HTML_IMG($rk->country, 'country', false) . "</td>";
				echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
				echo "<td>" . luc_HTML_IMG($rk->feed, 'feed', true) . "</td>";
		if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Logged_User'] != 'checked')
		 echo ($rk->user != '') ? "<td>" . $rk->user . "</td>": "<td>&nbsp;</td>";
		echo "</tr>";
	}
		?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['feedsrows']))
		die();
}

function luc_main_table_latest_spiders()
{
	global $wpdb, $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['spidersrows']) ? $_POST['spidersrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'></th>
			<th scope='col' width="30%">Page</th>
			<th scope='col' width="30%">Agent</th>
		</tr>
		</thead>
		<tbody>
	<?php

	$qry = $wpdb->get_results("SELECT date, time, ip, urlrequested, spider, agent, post_title
			FROM $table_name
			WHERE spider<>''
				AND spider NOT LIKE '%spam bot'
			ORDER BY id DESC
			LIMIT $querylimit;");
	foreach ($qry as $rk)
	{
		echo "<tr>
					<td>" . luc_hdate($rk->date) . "</td>
					<td>" . $rk->time . "</td>
					<td>" . luc_create_href($rk->ip, 'ip') . "</td>
					<td>" . luc_HTML_IMG($rk->spider, 'spider', false) . "</td>";
		echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
		echo "<td> " . $rk->agent . "</td>
				</tr>";
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['spidersrows']))
		die();
}

function luc_main_table_latest_spambots()
{
	global $wpdb, $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['spambotsrows']) ? $_POST['spambotsrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);
	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col' width="40%">Agent</th>
			<th scope='col'>Count</th>
		</tr>
		</thead>
		<tbody>
	<?php
	$qry = $wpdb->get_results("SELECT date, time, ip, agent, COUNT(ip) AS ipcount
			FROM $table_name
			WHERE spider LIKE '%spam bot'
			GROUP BY ip,agent
			ORDER BY id DESC
			LIMIT $querylimit;");

	foreach ($qry as $rk)
	{
		echo "<tr>
				<td>" . luc_hdate($rk->date) . "</td>
				<td>" . $rk->time . "</td>
				<td>" . luc_create_href($rk->ip, 'ip') . "</td>
				<td><a target='_blank' href='http://www.google.com/search?q=%22User+Agent%22+" . urlencode($rk->agent) . "' target='_blank' title='Search for &quot;" . urldecode($rk->agent) . "&quot; on Google...'> " . $rk->agent . "</a> </td>
				<td>" . $rk->ipcount . "</td></tr>";
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['spambotsrows']))
		die();
}

function luc_create_href($str, $type)
{
	if ($type == 'ip')
	{
		$qrys = "admin.php?page=stat-counter/admin/luc_admin.php&ip=";
		$href = "<a target='_blank' href='" . admin_url() . $qrys . $str . "'target='_self' title='Generate report for " . $str . "'>" . $str . "</a>";
	}
	return $href;
}

function luc_main_table_latest_undefagents()
{
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$querylimit = (isset ($_POST['undefagentsrows']) ? $_POST['undefagentsrows'] : $STATCOUNTER_Option['STATCOUNTER_Rows_Per_Latest']);

	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col' width="30%">Agent</th>
			<th scope='col' width="30%">Page</th>
		</tr>
		</thead>
		<tbody>
	<?php

	$qry = $wpdb->get_results("SELECT date, time, ip, urlrequested, agent, post_title
			FROM $table_name
			WHERE (os='' OR browser='')
				AND searchengine=''
				AND spider=''
			GROUP BY ip,agent
			ORDER BY id DESC
			LIMIT $querylimit;");
	foreach ($qry as $rk)
	{
		echo "<tr>
			<td>" . luc_hdate($rk->date) . "</td>
			<td>" . $rk->time . "</td>
			<td>" . luc_create_href($rk->ip, 'ip') . "</td>
			<td><a target='_blank' href='http://www.google.com/search?q=%22User+Agent%22+" . urlencode($rk->agent) . "' title='Search for &quot;" . urldecode($rk->agent) . "&quot; on Google...'> " . $rk->agent . "</a> </td>";
		echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
	}
	?>
		</tbody>
	</table>
	<?php

	if (isset ($_POST['undefagentsrows']))
		die();
}


function luc_main()
{
	if (isset ($_GET['ip']))
			{ // This is a query for an IP address
				luc_Client_Lookup_IP($_GET['ip']);
				return true;
			}
    global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
		  
    $action = 'overview';
          // OVERVIEW table
    $visitors_color = "#114477";
	$rss_visitors_color = "#FFF168";
    $pageviews_color = "#3377B6";
    $rss_pageviews_color = "#f38f36";
    $spider_color = "#83b4d8";
    $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
    $today = gmdate('Ymd', current_time('timestamp'));
         
	// Overview
    echo "<div><div class='wrap'>
	<h2>" . __('Overview', 'STATCOUNTER') . "</h2>
    <table class='widefat'><thead><tr>
	<th scope='col'></th>
	<th scope='col'>Yesterday<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')-86400) ."</font></th>
	<th scope='col'>Today<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')) ."</font></th>
	</tr></thead>
	<tbody id='the-list'>";
          
          // VISITORS ROW
          luc_ROW ("DISTINCT ip","feed=''","spider=''",$visitors_color,"Visitors");
        
          // VISITORS FEEDS ROW
          luc_ROW ("DISTINCT ip","feed<>''","spider=''",$rss_visitors_color,"Visitors RSS Feeds");
     
          // PAGEVIEWS ROW
		  luc_ROW ("*","feed=''","spider=''",$pageviews_color,"Pageviews");
		
          // PAGEVIEWS FEEDS ROW
		  luc_ROW ("*","feed<>''","spider=''",$rss_pageviews_color,"Pageviews RSS Feeds");
		
		  // SPIDERS ROW
    if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] != 'checked')
			luc_ROW ("*","feed=''","spider<>''",$spider_color,"Spiders");
		   
		   ?>
	</tbody>
	</table>
	<!-- End of the Overview -->
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.tabbed').tabs();
	});
	</script>
	<div class="tabbed">
	<!-- The tabs -->
		<ul id="onglets">
			<li><strong><a href="#tabs-2">Latest Hits</a></strong></li>
			<li><strong><a href="#tabs-3">Latest Search Terms</a></strong></li>
			<li><strong><a href="#tabs-4">Latest Referrers</a></strong></li>
			<li><strong><a href="#tabs-5">Latest Feeds</a></strong></li>
			<?php
	if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] =='')
	{ ?>
			<li><strong><a href="#tabs-6">Latest Spiders</a></strong></li>
			<li><strong><a href="#tabs-7">Latest Spams Bots</a></strong></li>
			
		<?php
	} ?>
			<li><strong><a href="#tabs-8">Latest Undefined Agents</a></strong></li>
			</ul>	
	<!-- Latest Hits -->
	
<!-- tab 2 -->
		<div id="tabs-2">
			<table>
				<form id='latesthitsForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='hitsrows' id='hitsrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latesthitsLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_hits" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latesthits"> <?php luc_main_table_latest_hits() ?> </div>
		</div>

<!-- tab 3 -->
		<div id="tabs-3">
			<table>
				<form id='latestsearchForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='searchrows' id='searchrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latestsearchLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_search" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latestsearch"> <?php luc_main_table_latest_search() ?> </div>
		</div>

<!-- tab 4 -->
		<div id="tabs-4">
			<table>
				<form id='latestreferrersForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='referrersrows' id='referrersrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latestreferrersLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_referrers" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latestreferrers"> <?php luc_main_table_latest_referrers() ?> </div>
		</div>

<!-- tab 5 -->
		<div id="tabs-5">
			<table>
				<form id='latestfeedsForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='feedsrows' id='feedsrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latestfeedsLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_feeds" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latestfeeds"> <?php luc_main_table_latest_feeds() ?> </div>
		</div>

<!-- tab 6 -->
	<?php
	if ($STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'] == '')
	{
	?>
		<div id="tabs-6">
			<table>
				<form id='latestspidersForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='spidersrows' id='spidersrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latestspidersLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_spiders" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latestspiders"> <?php luc_main_table_latest_spiders() ?> </div>
		</div>
<!-- tab 7 -->
		<div id="tabs-7">
			<table>
				<form id='latestspambotsForm'>
					<table class='main'>
						<tr>
							<td width=270px><h2>Latest Spambots</h2></td>
							<td align='right'>
								<select name='spambotsrows' id='spambotsrows'>
									<option value='0'>Rows:</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
								</select>
								<img src="<?php echo STATCOUNTER_PLUGIN_URL ?>/images/ajax-loader.gif" id="latestspambotsLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_spambots" />
							</td>
						</tr>
					</table>
				</form>
			</table>
	<div id="latestspambots"> <?php luc_main_table_latest_spambots() ?> </div>
	</div>
<?php
	}
	?>
<!-- tab 8 -->
		<div id="tabs-8">
			<table>
				<form id='latestundefagentsForm'>
					<table class='main'>
						<tr>
							<td>Rows:</td>
							<td align='right'>
								<select name='undefagentsrows' id='undefagentsrows'>
									<option value='0'>--Select--</option>
									<option value='5'>5</option>
									<option value='10'>10</option>
									<option value='25'>25</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
									<option value='250'>250</option>
									<option value='500'>500</option>
								</select>
								<img src="<?php _e(STATCOUNTER_PLUGIN_URL) ?>/images/ajax-loader.gif" id="latestundefagentsLoader" style="display: none;" />
								<input type="hidden" name="action" value="table_latest_undefagents" />
							</td>
						</tr>
					</table>
				</form>
			</table>
			<div id="latestundefagents"> <?php luc_main_table_latest_undefagents() ?> </div>
		</div>
	
	</div> <!-- End tabbed div -->
	</div>
	<!-- end of the tab -->
	
	<?php
          //echo "</table>";
          echo "<br />";
          echo "&nbsp;<i>". __('STATCOUNTER table size', 'STATCOUNTER').": <b>" . luc_tablesize($wpdb->prefix. 'STATCOUNTER') . "</b></i><br />";
          echo "&nbsp;<i>". __('STATCOUNTER current time', 'STATCOUNTER').": <b>".current_time('mysql')."</b></i><br />";
          echo "&nbsp;<i>". __('RSS2 url', 'STATCOUNTER').": <b>".get_bloginfo('rss2_url').'('.luc_STATCOUNTER_extractfeedreq(get_bloginfo('rss2_url')).")</b></i><br />";
          echo "&nbsp;<i>". __('ATOM url', 'STATCOUNTER').": <b>".get_bloginfo('atom_url').'('.luc_STATCOUNTER_extractfeedreq(get_bloginfo('atom_url')).")</b></i><br />";
          echo "&nbsp;<i>". __('RSS url', 'STATCOUNTER').": <b>".get_bloginfo('rss_url').'('.luc_STATCOUNTER_extractfeedreq(get_bloginfo('rss_url')).")</b></i><br />";
          echo "&nbsp;<i>". __('COMMENT RSS2 url', 'STATCOUNTER').": <b>".get_bloginfo('comments_rss2_url').' ('.luc_STATCOUNTER_extractfeedreq(get_bloginfo('comments_rss2_url')).")</b></i><br />";
          echo "&nbsp;<i>". __('COMMENT ATOM url', 'STATCOUNTER').": <b>".get_bloginfo('comments_atom_url').' ('.luc_STATCOUNTER_extractfeedreq(get_bloginfo('comments_atom_url')).")</b></i><br />";
     luc_STATCOUNTER_load_time();
	 ?>
	 </div>
	 <?php
}

function luc_ROW ($count,$feed,$spider,$color,$text)
{	$visitors_color = "#114477";
	$rss_visitors_color = "#FFF168";
    $pageviews_color = "#3377B6";
    $rss_pageviews_color = "#f38f36";
    $spider_color = "#83b4d8";
	$yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
    $today = gmdate('Ymd', current_time('timestamp'));
		  
		  //YESTERDAY TODAY
	$qry_yt=requete_main($count,$feed,$spider); 
	foreach ($qry_yt as $qry)
			{
				$total[$qry->date] = $qry->num;
			}
    echo "<tr><td><div style='background:$color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>$text</td>
		  <td>".(($total[$yesterday] != 0) ? $total[$yesterday] :'&nbsp;') ."</td>\n
          <td>".(($total[$today] != 0) ? $total[$today] :'&nbsp;') ."</td>\n</tr>";
}
		
function requete_main($count,$feed,$spider)
{   global $wpdb;
	$table_name = STATCOUNTER_TABLE_NAME;
	$qry = $wpdb->get_results("SELECT count($count) AS num, date
                               FROM $table_name
                               WHERE $feed AND $spider
								GROUP BY date ASC");
	return $qry;							
	}
	
function luc_HTML_IMG($key, $type, $showname)
{
	if ($key != '')
	{
		$search = strtolower($key);
		$title = $key;
		
		// Look for fields in definition file
		$lines =($type === "country") ?  file(STATCOUNTER_PLUGIN_PATH . "/def/domain.dat"):file(STATCOUNTER_PLUGIN_PATH . "/def/" . $type . ".dat");
		
		foreach ($lines as $line_num => $line)
		{
			$entry = explode("|", strtolower($line));

			if (in_array($search, $entry, true))
			{
				// We have a match
					$title = explode('|',$line);
					$title = $title[0];

				$img = (($type === "country") ?
					STATCOUNTER_PLUGIN_URL . "images/domain/" . $entry[1] . ".png" :
					STATCOUNTER_PLUGIN_URL . "images/" . $type . "/" .  str_replace(" ", "_", str_replace(".", "-", $entry[0])) . ".png");					
				break;
			}
		}
		return ($showname === true )?
			"<IMG style='border:0px;height:16px;$width' alt='$title' title='$title' SRC='$img'>&nbsp;&nbsp;$title" :
			"<IMG style='border:0px;height:16px;$width' alt='" . $title . "' title='" . $title . "' SRC='" . $img . "'>";
	}
	else
		return "&nbsp;";
}
      
function luc_language($rk)
{
		if($rk->language != '') 
			         { $lines = file(STATCOUNTER_PLUGIN_PATH .'/def/languages.dat');
			           foreach($lines as $line_num => $ligne) 
			                  { list($langue,$id)=explode("|",$ligne);
							    if($id==$rk->language) 
									break; // break, the language is found
							   }
			           return $langue;
					  } 
			  else return "&nbsp;";
}
			
function luc_statistics()
{
          global $wpdb;
          
          $querylimit = "LIMIT 10";
		  # Top days
          luc_ValueTable2("date","Top days",5);
 
		   // Search terms
          luc_ValueTable2("search", __('Top search terms', 'STATCOUNTER'), 20, "", "", "AND search<>''");
          
          // Top referrer
          luc_ValueTable2("referrer", __('Top referrer', 'STATCOUNTER'), 10, "", "", "AND referrer<>'' AND referrer NOT LIKE '%" . get_bloginfo('url') . "%'");
		  
          // O.S.
          luc_ValueTable2("os", __('O.S.', 'STATCOUNTER'), 0, "", "", "AND feed='' AND spider='' AND os<>''");
          
          // Browser
          luc_ValueTable2("browser", __('Browser', 'STATCOUNTER'), 0, "", "", "AND feed='' AND spider='' AND browser<>''");
          
          // Feeds
          luc_ValueTable2("feed", __('Feeds', 'STATCOUNTER'), 5, "", "", "AND feed<>''");
          
          // SE
          luc_ValueTable2("searchengine", __('Search engines', 'STATCOUNTER'), 10, "", "", "AND searchengine<>''");
          
          // Countries
          luc_ValueTable2("country", __('Countries ', 'STATCOUNTER'), 10, "", "", "AND country<>'' AND spider=''");
          
          // Spider
          luc_ValueTable2("spider", __('Spiders', 'STATCOUNTER'), 10, "", "", "AND spider<>''");
		  luc_STATCOUNTER_load_time();
          
}
	  
function luc_page_periode()
{	global $wpdb;
	  // pp is the display page periode 
	if(isset($_GET['pp']))
          { // Get Current page periode from URL
          	$periode = $_GET['pp'];
          	if($periode <= 0)
          	// Periode is less than 0 then set it to 1
          	 $periode = 1;
          }
    else // URL does not show the page set it to 1
			$periode = 1;
			
	return $periode;	
}
			
function luc_page_posts()
{		global $wpdb;
			// pa is the display pages Articles
		if(isset($_GET['pa']))
            { 
          	$pageA = $_GET['pa'];// Get Current page Articles from URL
          	if($pageA <= 0) // Article is less than 0 then set it to 1
          	   $pageA = 1;
            }
        else  // URL does not show the Article set it to 1
          	$pageA = 1;
			
		return $pageA;
}

function luc_STATCOUNTER_url_monitoring()
{	global $wpdb, $STATCOUNTER_Option;
	$table_name =STATCOUNTER_TABLE_NAME;
	$querylimit = 100;
	$pa = luc_page_posts();
	$action = "urlmonitoring";
	// Number of distinct spiders after $day_ago
	$Num = $wpdb->get_var("SELECT COUNT(*)
				FROM $table_name
				WHERE realpost=0 AND (spider ='' OR spider LIKE 'Unknown Spam Bot')
				");	
				
	$NumPage = ceil($Num / $querylimit);

	echo "<div class='wrap'><h2>" . __('URL Monitoring', 'statpress') . "</h2></br> This page is designed to help you secure your website: <DIV title='Indeed this page shows all URLs that have access to your website or your blog and who are not posts or articles written by an author of your website.Some are legitimate as /category or the robots like Google. Nevertheless, they are all shown so you can secure your blog or your site by selecting the ones you want to block access to your site.'>Learn more</DIV>";
	luc_print_pa_link ($NumPage,$pa,$action);
	$LimitValue = ($pa * $querylimit) - $querylimit;
	?>
	<table class='widefat' >
		<thead>
		<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Country</th>
			<th scope='col' width="30%" >URL requested</th>
			<th scope='col' width="30%" >Agent</th>
			<th scope='col'>Spider</th>
			<th scope='col'>OS</th>
			<th scope='col'>Browser</th>
		</tr>
		</thead>
		<tbody>
	<?php

	$qry = $wpdb->get_results("SELECT date,time,ip,urlrequested,agent,os,browser,spider,country,realpost
			FROM $table_name
			WHERE realpost=0 AND (spider ='' OR spider LIKE 'Unknown Spam Bot')
			ORDER BY id DESC
			LIMIT $LimitValue, $querylimit;");
			
	
	foreach ($qry as $rk)
	{
		echo "<tr>
			<td>" .luc_hdate($rk->date). "</td>
			<td>" .$rk->time. "</td>
			<td>" .luc_create_href($rk->ip, 'ip'). "</td>
			<td>" .luc_HTML_IMG($rk->country, 'country', false). "</td>
			<td>" .$rk->urlrequested."</td>
			<td><a href='http://www.google.com/search?q=%22User+Agent%22+" . urlencode($rk->agent) . "' target='_blank' title='Search for User Agent string on Google...'> " . $rk->agent . "</a> </td>
			<td>" .luc_HTML_IMG($rk->spider, 'spider', false). "</td>
			<td>" .luc_HTML_IMG($rk->os, 'os', $text_OS). "</td>
			<td>" .luc_HTML_IMG($rk->browser, 'browser', $text_browser). "</td>";
	}
	?>
		</tbody>
	</table>
	
<?php

echo "</div>";
luc_print_pa_link ($NumPage,$pa,$action);
luc_STATCOUNTER_load_time();
}

function luc_spy_bot()
      {   global $wpdb;
		  $table_name = STATCOUNTER_TABLE_NAME;
	      $action = 'spybot';
          
		  // number of IP or bot by page
		  
		    $LIMIT = 5;
            $LIMIT_PROOF = 40;

			$pa = luc_page_posts();
			$LimitValue = ($pa * $LIMIT) - $LIMIT;
			
			// Number of distinct spiders 
			$Num = $wpdb->get_var("SELECT count(distinct spider) FROM $table_name WHERE spider<>''");
			$NA = ceil($Num/$LIMIT);
		
          	// Number of distinct spider between $currentdate and $limidate  
			$NumberSpiders = $wpdb->get_var("SELECT count(distinct spider) FROM $table_name WHERE spider<> '' AND date BETWEEN $limitdate AND $currentdate");
			$NumSpiders = ceil($NumberSpiders/ $LIMIT);

		    echo "<div class='wrap'><h2>" . __('Bot Spy', 'STATCOUNTER') . "</h2>";
			
            // selection of spider, group by spider, order by most recently visit (last id in the table)
			$sql = "SELECT *
			        FROM $table_name as T1
					JOIN
						(SELECT spider,max(id) as MaxId 
						FROM $table_name WHERE spider<>'' 
						GROUP BY spider 
						ORDER BY MaxId DESC LIMIT $LimitValue, $LIMIT 
						) as T2
				    ON T1.spider = T2.spider					 
			        ORDER BY MaxId DESC, timestamp DESC
			";
			
			$qry = $wpdb->get_results($sql);
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<?php
luc_print_pa_link ($NA,$pa,$action);
?>
<table class="widefat" id="mainspytab" name="mainspytab">
	<div align='left'>
<?php
          $spider="robot";
		  $num_row=0;
          foreach ($qry as $rk)
          {  // Bot Spy
			if ($robot <> $rk->spider)  
			    {echo "
				<tr>
				<td colspan='2' bgcolor='#dedede'>";
			     $img=str_replace(" ","_",strtolower($rk->spider));
			     $img=str_replace('.','',$img).".png";
				 $lines = file(STATCOUNTER_PLUGIN_PATH .'/def/spider.dat');
				 foreach($lines as $line_num => $spider) //seeks the tooltip corresponding to the photo
			                  { list($title,$id)=explode("|",$spider);
							    if($title==$rk->spider) break; // break, the tooltip ($title) is found
					            }
				 echo "<IMG style='border:0px;height:16px;align:left;' alt='".$title."' title='".$title."' SRC='" .STATCOUNTER_PLUGIN_URL.'/images/spider/'.$img."'>    		
				 <span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . $img . "');>http more info</span>
                 <div id='" . $img . "' name='" . $img . "'><br /><small>" . $rk->ip . "</small><br><small>" . $rk->agent . "<br /></small></div>
                 <script>document.getElementById('" . $img . "').style.display='none';</script>
			     </tr>
				 <tr><td>" . luc_hdate($rk->date) . " " . $rk->time . "</td>";
                 echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
			     $robot=$rk->spider;
			     $num_row=1;
			    }

			 elseif ($num_row < $LIMIT_PROOF)
			    {echo "<tr>
			     <td>" . luc_hdate($rk->date) . " " . $rk->time . "</td>";
                 echo ((isset($rk->post_title)) ?"<td>" .$rk->post_title."</td>" :"<td>" . luc_post_title_Decode(urldecode($rk->urlrequested)). "</td>");
			     $num_row+=1;
			    }
		echo "</td></tr>\n";
         }
		echo "</table>"; 
		luc_print_pa_link ($NA,$pa,$action);
        echo "</div></table>";
		luc_STATCOUNTER_load_time();
      }
	  
function luc_spy_visitors()
      {   global $wpdb;
		  global $STATCOUNTER_Option;
		  $table_name = STATCOUNTER_TABLE_NAME;
	      $action = 'spyvisitors';
          
		  // number of IP or bot by page
		  $LIMIT = $STATCOUNTER_Option['STATCOUNTER_SpyVisitor_IP_Per_Page'];
		  $LIMIT_PROOF = $STATCOUNTER_Option['STATCOUNTER_SpyVisitor_Visits_Per_IP'];
            if ($LIMIT == 0)
              $LIMIT = 20;
			if ($LIMIT_PROOF == 0)
              $LIMIT_PROOF = 20;
			   
            $pp = luc_page_periode();	
			
          	// Number of distinct ip (unique visitors)
			$NumIP = $wpdb->get_var("SELECT count(distinct ip) FROM $table_name WHERE spider=''");
			$NP = ceil($NumIP/$LIMIT);
            $LimitValue = ($pp * $LIMIT) - $LIMIT;
        
			$sql = "SELECT *
			FROM $table_name as T1
			JOIN
				(SELECT ip,max(id) as MaxId 
				FROM $table_name WHERE spider='' 
				GROUP BY ip 
				ORDER BY MaxId DESC LIMIT $LimitValue, $LIMIT 
				) as T2
			ON T1.ip = T2.ip
			ORDER BY MaxId DESC, id DESC
			";
			$qry = $wpdb->get_results($sql);	
			
			if ($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked' & function_exists('geoip_open'))
			{ // Open the database to read and save info
				if (file_exists(luc_GeoIP_dbname('city')))
				{
					$gic = geoip_open(luc_GeoIP_dbname('city'), GEOIP_STANDARD);
					$geoip_isok = true;
				}
			}
			
			echo "<div class='wrap'><h2>" . __('Visitor Spy', 'STATCOUNTER') . "</h2>";
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<?php
          $ip = 0;
		  $num_row=0;
		  luc_print_pp_link($NP,$pp,$action);
		 echo '<table class="widefat" id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">';
	 foreach ($qry as $rk)
	{ // Visitor Spy
		if ($ip <> $rk->ip) //this is the first time these ip appear, print informations
		{
			if ($geoip_isok === true)
				$gir = GeoIP_record_by_addr($gic, $rk->ip);

			echo "<thead><tr><th scope='colgroup' colspan='2'>";

			if ($rk->country <> '')
				echo "HTTP country " . luc_HTML_IMG($rk->country, 'country', false);
			else
				echo "Hostip country <IMG SRC='http://api.hostip.info/flag.php?ip=" . $rk->ip . "' border=0 width=18 height=12>  ";

			($geoip_isok === true?$lookupsvc = "GeoIP details":$lookupsvc = "Hostip details");

			echo "	<strong><span><font size='2' color='#7b7b7b'> " . $rk->ip . " </font></span></strong>
					<span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;'
						onClick=ttogle('" . $rk->ip . "');>" . $lookupsvc . "</span></div>
					<div id='" . $rk->ip . "' name='" . $rk->ip . "'>";

			if ($geoip_isok === true)
				echo "	<small><br>
							Country: " . utf8_encode($gir->country_name) . " (" . $gir->country_code . ")<br>
							City: " . utf8_encode($gir->city) . "<br>
							Latitude/Longitude: <a href='http://maps.google.com/maps?q=" . $gir->latitude . "+" . $gir->longitude . "' target='_blank' title='Lookup latitude/longitude location on Google Maps...'>" . $gir->latitude . " " . $gir->longitude . "</a>
						</small>";
			else
				echo "	<iframe style='overflow:hide;border:0px;width:100%;height:35px;font-family:helvetica;paddng:0;'
							scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=" . $rk->ip . ">
						</iframe>";

			echo "	<small>
						<br>" . $rk->os . ", " . $rk->browser . "
						<br>" . gethostbyaddr($rk->ip) . "
						<br>" . $rk->agent . "
					</small>
					</div></th></tr></thead>
					<tbody>
					<script> document.getElementById('" . $rk->ip . "').style.display='none';</script>
					<tr>
					<td>" . luc_hdate($rk->date) . " " . $rk->time . "</td>";
			echo ((isset($rk->post_title)) ? "<td><div>".$rk->post_title : "<td><div>".luc_post_title_Decode(urldecode($rk->urlrequested)));

			if ($rk->searchengine != '')
				echo "<br><small>arrived from <b>" . $rk->searchengine . "</b> searching <a href='" . $rk->referrer . "' target=_blank>" . urldecode($rk->search) . "</a></small>";
			else
				if ($rk->referrer != '' && strpos($rk->referrer, $home) === false)
					echo "<br><small>arrived from <a target='_blank' href='" . $rk->referrer . "'>" . $rk->referrer . "</a></small>";

			echo "</div></td></tr>\n";
			$ip = $rk->ip;
			$num_row = 1;
		}
		elseif ($num_row < $LIMIT_PROOF)
		{
			echo "<tr>
					<td>" . luc_hdate($rk->date) . " " . $rk->time . "</td>";
			echo ((isset($rk->post_title)) ? "<td><div>".$rk->post_title: "<td><div>".luc_post_title_Decode(urldecode($rk->urlrequested)));
			if ($rk->searchengine != '')
				echo "<br><small>arrived from <b>" . $rk->searchengine . "</b> searching <a target='_blank' href='" . $rk->referrer . "' " . urldecode($rk->search) . "</a>
							</small>";
			else
				if ($rk->referrer != '' && strpos($rk->referrer, $home) === false)
					echo "<br><small>arrived from <a target='_blank' href='" . $rk->referrer . "' >" . $rk->referrer . "</a>
								</small>";

			$num_row += 1;
			echo "</div></td></tr></tbody>";
		}
	}
		echo "</div></td></tr>\n</table>";   
        luc_print_pp_link($NP,$pp,$action);
        echo "</div>";
		luc_STATCOUNTER_load_time();
      }
	    
function luc_yesterday()
{	$start = microtime(true);
	global $wpdb;
	global $STATCOUNTER_Option;
	$table_name = STATCOUNTER_TABLE_NAME;
	$action = "yesterday";
	$visitors_color = "#114477";
	$rss_visitors_color = "#FFF168";
	$pageviews_color = "#3377B6";
	$rss_pageviews_color = "#f38f36";
	$spider_color = "#83b4d8";

	$yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
	
	$pa = luc_page_posts();
	$permalink = luc_permalink();

$sql_posts_pages = "SELECT post_date_gmt,post_title,post_name,post_type 
			FROM $wpdb->posts 
			WHERE post_status = 'publish' 
				AND (post_type = 'page' OR post_type = 'post')
				AND DATE_FORMAT(post_date_gmt, '%Y%m%d') <= $yesterday;";
	
	// Get all posts and pages
	$qry_posts_pages = $wpdb->get_results($sql_posts_pages);	
	$total_posts_pages = $wpdb->num_rows;
					
	$NumberDisplayPost = 100;
	$NA = ceil($total_posts_pages / $NumberDisplayPost);
	$LimitValueArticles = ($pa-1) * $NumberDisplayPost;

	
	foreach ($qry_posts_pages as $p)
	{	$posts[$p->post_name]['post_name'] = $p->post_name;
		$posts[$p->post_name]['post_title'] = $p->post_title;
		$posts[$p->post_name]['post_type'] = $p->post_type;
		$posts[$p->post_name]['visitors'] = NULL;
		$posts[$p->post_name]['visitors_feeds'] = NULL;
		$posts[$p->post_name]['pageviews'] = NULL;
		$posts[$p->post_name]['pageviews_feeds'] = NULL;
		$posts[$p->post_name]['spiders'] = NULL;
	}	
		$posts['page_accueil']['post_name'] = 'page_accueil';
		$posts['page_accueil']['post_title'] = 'Home';
		$posts['page_accueil']['post_type'] = 'page';
		$posts['page_accueil']['visitors'] = NULL;
		$posts['page_accueil']['visitors_feeds'] = NULL;
		$posts['page_accueil']['pageviews'] = NULL;
		$posts['page_accueil']['pageviews_feeds'] = NULL;
		$posts['page_accueil']['spiders'] = NULL;
		
	
	$qry_visitors = requete_day("DISTINCT ip", "urlrequested = ''", "spider = '' AND feed = ''", $yesterday);
	foreach ($qry_visitors as $p)
	{
		$posts[$p->post_name]['visitors'] = $p->total;
		$total_visitors += $p->total;
	}
	
	$qry_visitors_feeds = requete_day("DISTINCT ip", "(urlrequested LIKE '%" . $permalink . "feed%' OR urlrequested LIKE '%" . $permalink . "comment%') ", "spider='' AND feed<>''", $yesterday);
	foreach ($qry_visitors_feeds as $p)
	{
		$posts[$p->post_name]['visitors_feeds'] = $p->total;
		$total_visitors_feeds += $p->total;
	}
	$qry_pageviews = requete_day("ip", "urlrequested = ''", "spider = '' AND feed = ''", $yesterday);
	
	foreach ($qry_pageviews as $p)
	{
		$posts[$p->post_name]['pageviews'] = $p->total;
		$total_pageviews += $p->total;
	}

	$qry_pageviews_feeds = requete_day("ip", "(urlrequested LIKE '%" . $permalink . "feed%' OR urlrequested LIKE '%" . $permalink . "comment%')", " spider='' AND feed<>''", $yesterday);
	foreach ($qry_pageviews_feeds as $p)
	{	
		$posts[$p->post_name]['pageviews_feeds'] = $p->total;
		$total_pageviews_feeds += $p->total;
	}

	$spider = $STATCOUNTER_Option['STATCOUNTER_Dont_Collect_Spider'];
	if ($spider == '')
	{
		$qry_spiders = requete_day("ip", "urlrequested=''", "spider<>'' AND feed=''", $yesterday);
		foreach ($qry_spiders as $p)
		{	
			$posts[$p->post_name]['spiders'] = $p->total;
			$total_spiders += $p->total;
		}
	}

	$total_visitors = $wpdb->get_var("SELECT count(DISTINCT ip) AS total
			FROM $table_name
			WHERE feed=''
				AND spider=''
				AND date = $yesterday ;");
			
	$total_visitors_feeds = $wpdb->get_var("SELECT count(DISTINCT ip) as total
			FROM $table_name
			WHERE feed<>''
				AND spider=''
				AND date = $yesterday ;");
				
	echo "<strong>"; _e("Displaying report for " . gmdate('d M, Y', strtotime($yesterday)) . " (" . $total_posts_pages . " posts/pages)");echo" </strong>";

	luc_print_pa_link ($NA,$pa,$action);
	
    // Sort the results by total
	usort($posts, "luc_posts_pages_custom_sort");
	
	echo "<table class='widefat'; id='yesterday'>
	<thead><tr>
	<th scope='col'>" . __('URL', 'statpressV') . "</th>
	<th scope='col'><div style='background:$visitors_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>" . __('Visitors', 'statpressV') . "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$rss_visitors_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>" . __('Visitors Feeds', 'statpressV') . "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$pageviews_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>" . __('Views', 'statpressV') . "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$rss_pageviews_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>" . __('Views Feeds', 'statpressV') . "<br /><font size=1></font></th>";
	if ($spider == '')
		echo "<th scope='col'><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>" . __('Spider', 'statpressV') . "<br /><font size=1></font></th>";
	echo "</tr></thead>";

	echo "<tr>
	<th scope='col'>All URL</th>
	<th scope='col'>" . __($total_visitors, 'statpressV') . "</th>
	<th scope='col'>" . __($total_visitors_feeds, 'statpressV') . "</th>
	<th scope='col'>" . __($total_pageviews, 'statpressV') . "</th>
	<th scope='col'>" . __($total_pageviews_feeds, 'statpressV') . "</th>";
	if ($spider == '')
		echo "<th scope='col'>" . __($total_spiders, 'statpressV') . "</th>
			</tr>";
	$i = 0;

	foreach ($posts as $p)
	{ if (($i >= $LimitValueArticles) and ($i < $LimitValueArticles+$NumberDisplayPost))
		{
		echo "<td>" . ($p[post_type]=='page' ? "[page]&#58;&nbsp;".$p['post_title'] : $p['post_title'])."</td>
			<td>" . $p['visitors']. "</td>
			<td>" . $p['visitors_feeds'] . "</td>
			<td>" . $p['pageviews']. "</td>
			<td>" . $p['pageviews_feeds'] . "</td>";
		if ($spider == '')
			echo "<td>" . $p['spiders'] . "</td>";
		echo "</tr>";
		}
		$i++;
	};
	echo "</table>";
	luc_print_pa_link ($NA,$pa,$action);
	luc_STATCOUNTER_load_time();
}

function requete_day($count, $where_one, $where_two, $yesterday)
{
	global $wpdb;
	$table_name = STATCOUNTER_TABLE_NAME;
	$qry = $wpdb->get_results("SELECT post_name, total
			FROM (
			(SELECT 'page_accueil' AS post_name, count($count) AS total
				FROM $table_name
				WHERE date = $yesterday
					AND $where_one
					AND $where_two
				GROUP BY post_name)
			UNION ALL
			(SELECT post_name, count($count) AS total
				FROM $wpdb->posts AS p
				JOIN $table_name AS t
				ON t.urlrequested LIKE CONCAT('%',p.post_name,'%')
				WHERE t.date = $yesterday
					AND p.post_status = 'publish'
					AND (p.post_type = 'page' OR p.post_type = 'post')
					AND $where_two
				GROUP BY p.post_name)
			) req
			GROUP BY post_name");
	return $qry;
}

// Define the custom sort function
function luc_posts_pages_custom_sort($a, $b) 
{
	return $a['visitors'] < $b['visitors'];
}
	
function luc_STATCOUNTER_referrer()
	  {  global $wpdb;
         $table_name = STATCOUNTER_TABLE_NAME;
		 
		 $action = "referrer";
		 $visitors_color = "#114477";
		 $rss_visitors_color = "#FFF168";
         $pageviews_color = "#3377B6";
         $rss_pageviews_color = "#f38f36";
         $spider_color = "#83b4d8";
		 
		$pa = luc_page_posts();
			      
		$query = $wpdb->get_results("SELECT distinct referrer 
			                FROM $table_name 
                            WHERE referrer<>'' AND referrer NOT LIKE '%" . get_bloginfo('url') . "%' AND searchengine='' 
						    ");					   
		$NumberArticles = $wpdb->num_rows;

		 $LIMITArticles = 100;
		 $NA = ceil($NumberArticles / $LIMITArticles);
		 $LimitValueArticles = ($pa-1) * $LIMITArticles;
	 
		 echo "<div class='wrap'><h2>" . __('Referrer ', 'STATCOUNTER')."</div>";
		 luc_print_pp_link($NA,$pa,$action);

		 echo "<table class='widefat'><thead><tr>
	           <th scope='col' with = 10%>IP </th>
			   <th scope='col'>URL </th>
	           <th scope='col'>Total <br /><font size=1></font></th>
	           </tr></thead>";
			   
         $strqry = "SELECT count(referrer) as total,ip, referrer
			        FROM $table_name 
					WHERE referrer<>'' AND referrer NOT LIKE '%".get_bloginfo('url')."%'  AND searchengine='' 
					GROUP BY referrer 
					ORDER by total DESC LIMIT $LimitValueArticles, $LIMITArticles";
	      
		 $query = $wpdb->get_results($strqry);
		 foreach ($query as $url)
			       {echo "<tr><td>" . $url->ip . "</td>";
				    echo "<td><h4><a target='_blank' href='" . $url->referrer. "' >$url->referrer</a></h4></td>";
					echo "<td>" . $url->total . "</td></tr>\n";
					}
	               
         echo '</table>';
		
	     luc_print_pp_link ($NA,$pa,$action);
		luc_STATCOUNTER_load_time();
	  
	  }
	  
	
	function luc_print_pp_link($NA,$pp,$action)
       {  // For all pages ($NA) Display first 5 pages, 4 pages before current page($pp), 4 pages after current page , each 25 pages and the 5 last pages for($action)
          
           $GUIL1 = FALSE;
           $GUIL2 = FALSE;// suspension points  not writed
		   if ($NA >1)
           {for ($i = 1; $i <= $NA; $i++) 
             if ($i <= $NA)
               { // $page is not the last page
                if($i == $pp)  
	               echo " [{$i}] "; // $page is current page
	            else 
	            { // Not the current page Hyperlink them
	              if (($i <= 3) or (($i >= $pp-3) and ($i <= $pp+3)) or ($i >= $NA-2) or is_int($i/25)) 
				  
				  { if ($action == "overview")
				      echo '<a class="pagination" href="' .admin_url() .'admin.php?page=STATCOUNTER/STATCOUNTER.php/&pp=' . $i .'">' . $i . '</a> ';
					else  
	                  echo '<a class="pagination" href="' . admin_url() .'admin.php?page=STATCOUNTER/action='.$action.'/&pp=' . $i . '&pa=1'.'">' . $i . '</a> ';
					  }
	              else 
	                 { if ($GUIL1 == FALSE) 
	                     {echo "... "; $GUIL1 = TRUE;
		                  }
	              if (($i == $NA-3) and ($GUIL2 == FALSE)) 
	                 { echo " ... "; $GUIL2 = TRUE;
	                  } // suspension points writed
	                 }
	             }
                }
			}	
        }
		
	function luc_print_pa_link ($NA,$pa,$action)		
        {  // For all pages ($NA) Display first 5 pages, 5 pages before current page($pa), 5 pages after current page , 5 last pages 
            $GUIL1 = FALSE;// suspension points not writed
            $GUIL2 = FALSE;

		    if ($NA >1)
			{echo '<table width="100%" border="0"><tr></tr></table>';
			 echo "Pages and Posts : ";
			 
             for ($j = 1; $j <= $NA; $j++) 
             if ($j <= $NA)
              { // $i is not the last Articles page
               if($j == $pa)  // $i is current page
	              echo " [{$j}] ";
	           else { // Not the current page Hyperlink them
	                 if (($j <= 5) or (($j >= $pa-4) and ($j <= $pa+4)) or ($j >= $NA-5)or is_int($i/50)) 
					 echo '<a class="pagination" href="' .admin_url() .'admin.php?page=STATCOUNTER/action='.$action.'&pa='. $j . '">' . $j . '</a> ';
	                 else 
	                   { if ($GUIL1 == FALSE) 
					      {echo "... "; $GUIL1 = TRUE;}
	                   if (($j == $pa+6) and ($GUIL2 == FALSE)) 
					      { echo " ... "; $GUIL2 = TRUE;}
	                    // suspension points writed
	                   }
	                 }
               }
			}
		}
		
	function luc_gunzip($srcName, $dstName)
{
	$sfp = gzopen($srcName, "rb");
	if ($sfp !== false)
	{
		$fp = fopen($dstName, "w");
		if ($fp === false)
			return false;
		
		while ($string = gzread($sfp, 4096))
		{
			fwrite($fp, $string, strlen($string));
		}
		gzclose($sfp);
		fclose($fp);
		return true;
	}
	return false;
}	
		
	function luc_GeoIP()
{	global $STATCOUNTER_Option;
	$ipAddress = htmlentities($_SERVER['REMOTE_ADDR']);
	$geoip = luc_GeoIP_get_data($ipAddress);

	if (file_exists(luc_GeoIP_dbname('country')))
	{
		$stat = stat(luc_GeoIP_dbname('country'));
		$dbsize = number_format_i18n($stat['size']);
		$dbmtime = date_i18n('r', $stat['mtime']);
	}
	else {
			$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = '';
			update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
		}
		
	if (file_exists(luc_GeoIP_dbname('city')))
	{
		$statcity = stat(luc_GeoIP_dbname('city'));
		$dbcitysize = number_format_i18n($statcity['size']);
		$dbcitymtime = date_i18n('r', $statcity['mtime']);
	}
	else 
	{	
		$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = '';
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
	}
		
	// Draw page
	?>
	<table class='widefat'>
		<thead>
			<tr>
				<th scope='col' colspan='2'>GeoIP Lookup</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong>Warning:</strong> GeoIP consumes lot of CPU time, its use is discouraged, do not use it or have permission from your host before activating.
					</br><input type=checkbox name='STATCOUNTER_Use_GeoIP' id='STATCOUNTER_Use_GeoIP' value='checked'
					<?php echo $STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] ?> />
					<label for='STATCOUNTER_Use_GeoIP'>Enable (requires MaxMind GeoIP database files to be installed first)</label>
				</br>
				</td><td>
					<?php
					if ($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked')
					{
						$geoipdb = luc_GeoIP_dbname('country');
						if (file_exists($geoipdb))
							echo "<span style='color:green'>Database OK</span>";
						else
						{
							echo "<span style='color:red'>Database NOT found.  Please download and install databases first. Disabling Option! </span>";
							$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = '';
							update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
						}
					}
					?>
				
						<tr>
							<td>
							<input type='button' id='dogeoipdbupdate' value='Download/update database from MaxMind' class='button-secondary'>
							<img src="<?php echo STATCOUNTER_PLUGIN_URL ?>/images/ajax-loader.gif" id="geoipupdatedbLoader" style="display: none;" />
							<br /><br />
							<font size=2><b>WARNING!</b>  Downloading database updates from MaxMind <b>more than once per day</b> will get your <b>IP address banned!</b></font>
							</td><td></td>	
						</tr>
					
			
		</tbody>
		<thead><tr>
						<th scope='col' colspan='2'>Indicate the preferred method to locate the country of visitors :</th>
					</tr>
			</thead>
			<tbody>
			<form method="post" >
   <tr><td>
       <input type="radio" name="STATCOUNTER_locate_IP" value="browser" id="browser" <?php if ($STATCOUNTER_Option['STATCOUNTER_locate_IP']== 'browser')  echo'checked'; ?>
	   /> <label for="browser">Store the country provided by the browser first, otherwise use GeoIP (default, recommended)</label><br />
       <input type="radio" name="STATCOUNTER_locate_IP" value="GeoIP" id="GeoIP" <?php if ($STATCOUNTER_Option['STATCOUNTER_locate_IP']== 'GeoIP') echo'checked'; ?>
	   /> <label for="GeoIP">Always use GeoIP (accuracy 95%)</label><br />
   </td></tr>
</form>
</tbody>
		</table>
	<table class='widefat'>
		<thead><tr><th scope='col' colspan='2'>Status</th></tr></thead>
		<tbody>
		<tr>
			<td><strong>STATCOUNTER GeoIP status:</strong></td>
			<td>
	<?php
	if ($STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] == 'checked')
		echo "<span style='color:green'>Enabled</span>";
	else
		echo "<span style='color:red'>Disabled" . $geoipdb . "</span>";
	?>  </td>
		</tr>
		</tbody>
		<thead><tr><th scope='col' colspan='2'>Country database</th></tr></thead>
		<tbody>
		<tr>
			<td><strong>File status:</strong></td>
			
	<?php
	if (! file_exists(luc_GeoIP_dbname('country')))
		{echo "<td><span style='color:red'>Database NOT found" . $geoipdb . "</span></td>";
		$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = '';
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
		}
	else
	{ 
		echo "<td><span style='color:green'>Country database file exists</span></td>
		</tr>
		<tr><td><strong>File location:</strong></td>
			<td>" . luc_GeoIP_dbname('country') . "</td>
		</tr>
		<tr>
			<td><strong>File date (mtime):</strong></td>
			<td> $dbmtime </td>
		</tr>
		<tr>
			<td><strong>File size:</strong></td>
			<td> $dbsize bytes </td>
		</tr>";
	}
	?>  
		</tbody>

		<thead><tr><th scope='col' colspan='2'>City database</th></tr></thead>
		<tbody>
		<tr>
			<td><strong>File status:</strong></td>
			<td>
	<?php
	if (! file_exists(luc_GeoIP_dbname('city')))
		{echo "<span style='color:red'>City database NOT found" . $geoipcitydb . "</span></td>";
		$STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'] = '';
		update_option('STATCOUNTER_Option', $STATCOUNTER_Option);
		}
	else
	{
		echo "<span style='color:green'>City database file exists</span></td>
		<tr>
			<td><strong>File location:</strong></td>
			<td>" . luc_GeoIP_dbname('city') . "</td>
		</tr>
		<tr>
			<td><strong>File date (mtime):</strong></td>
			<td> $dbcitymtime </td>
		</tr>
		<tr>
			<td><strong>File size:</strong></td>
			<td> $dbcitysize bytes</td>
		</tr>";
	}
	?>
		</tbody>

	<?php
	if ($geoip !== false)
	{
		echo "
			<table class='widefat'>
				<thead><tr><th scope='col' colspan='4'>Your GeoIP Information ($ipAddress)</th></tr></thead>
				<tbody>
				<tr>
					<td><strong>Country:</strong></td>
					<td>" . $geoip['cn'] . " (" . $geoip['cc'] . ")
						<IMG style='border:0px;height:16px;' alt='$cn' title='$cn' SRC='" . STATCOUNTER_PLUGIN_URL.'images/domain/' . strtolower($geoip['cc']).'.png' . "'></td>
					<td><strong>Continent Code:</strong></td>
					<td>" . $geoip['continent_code'] . "</td>
				</tr>
				<tr>
					<td><strong>Region:</strong></td>
					<td>" . $geoip['region'] . "</td>
					<td><strong>Area Code (USA Only):</strong></td>
					<td>" . $geoip['area_code'] . "</td>
				</tr>
				<tr>
					<td><strong>City:</strong></td>
					<td>" . $geoip['city'] . "</td>
					<td><strong>Postal Code (USA Only):</strong></td>
					<td>" . $geoip['postal_code'] . "</td>
				</tr>
				<tr>
					<td><strong>Latitude/Longitude:</strong></td>
					<td>" . $geoip['latitude'] . " " . $geoip['longitude'] . "</td>
					<td><strong>Metro Code (USA Only):</strong></td>
					<td>" . $geoip['metro_code'] . "</td>
				</tr>
				</tbody>
			</table>
		";
	}

	?>
	</table>
	<div id='geoipupdatedbResultCountry'></div>
	<div id='geoipupdatedbResultCity'></div>
	<?php
//  End of GeoIP page

}

function luc_GeoIP_update_db($edition = null)
{
	$edition = (isset ($_POST['edition']) ? $_POST['edition'] : $edition);

	$db = luc_GeoIP_dbname($edition);
	$db_dir = dirname($db);
	$db_gz = $db . '.gz';
	if ('city' == $edition)
		$db_url = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz";
	else
		if ('country' == $edition)
			$db_url = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz";
		else
			return false;

	if (is_dir($db_dir) === false)
		mkdir($db_dir);

	$db_gz_f = fopen($db_gz, "w");
	$host = parse_url($db_url, PHP_URL_HOST);

	echo "
		<table class='widefat' >
			<thead><tr>
				<th scope='col'>Action</th>
				<th scope='col'>Information</th>
				<th scope='col'>Result</th>
				</tr></thead>
			<tbody>
	";

	echo "<tr><td width=\"20%\">Resolving hostname: </td><td>" . $host . "</td>";
	if (gethostbyname($host) === $host)
		echo "<td><span style='color:red'>[FAILED]</span></td></tr>";
	else
	{
		echo "<td><span style='color:green'>[OK]</span></td></tr>";
		
		if (function_exists('curl_init'))
		{
			echo "<tr><td>Requesting: </td><td>" . $db_url . "</td>";
	
			$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0';
			$ch = curl_init($db_url);
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FILE, $db_gz_f);
	
			$execute = curl_exec($ch);
	
			// Check if any error occured
			if (curl_errno($ch))
			{
				curl_close($ch);
				echo "<td><span style='color:red'>[FAILED]</span></td></tr>";
			}
			else
			{
				fclose($db_gz_f);
				$info = curl_getinfo($ch);
				curl_close($ch);
				clearstatcache();
				echo "<td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Server response: </td><td>" . $info['http_code'] . "</td><td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Content type: </td><td>" . $info['content_type'] . "</td><td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Remote file time: </td><td>" . $info['filetime'] . "</td><td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Bytes transfered: </td><td>" . number_format_i18n($info['size_download']) . " bytes</td><td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Avg download speed: </td><td>" . number_format_i18n($info['speed_download']) . " bytes/second</td><td><span style='color:green'>[OK]</span></td></tr>";
				echo "<tr><td>Time taken: </td><td>" . $info['total_time'] . "</td><td><span style='color:green'>[OK]</span></td></tr>";
	
				//  Check that the file is a plausable size
				if (filesize($db_gz) > 500000)
				{
					// Remove old backup
					if (file_exists($db . ".bak"))
						unlink($db . ".bak");
	
					// Move exisiting database to backup
					if (file_exists($db))
						rename($db, $db . ".bak");
					echo "<tr><td>Backing up old database:</td><td></td><td><span style='color:green'>[OK]</span></td></tr>";
	
					// Unpack new database
					if (luc_gunzip($db_gz, $db . ".new") !== true)
					{
						echo "<tr><td>Unpacking archive:</td><td></td><td><span style='color:red'>[FAILED]</span></td></tr>";
						
						// Restore backup file	
						if (file_exists($db))
							rename($db . ".bak", $db);
						echo "<tr><td>Restoring backup database:</td><td></td><td><span style='color:green'>[OK]</span></td></tr>";
					}
					else
					{
						echo "<tr><td>Unpacking archive:</td><td></td><td><span style='color:green'>[OK]</span></td></tr>";
		
						// Rename new database
						if (file_exists($db . ".new"))
							rename($db . ".new", $db);
		
						// Remove gzip file
						if (file_exists($db_gz))
							unlink($db_gz);
					}
				}
			}
		}
		else
		{
			echo "<tr><td><strong>PHP not built with cURL support: </strong></td><td>Manual install required</td><td><span style='color:red'>[FAILED]</span></td></tr>";
		}
		echo "</tbody></table>";
	}
	if (isset ($_POST['edition']))
		die();
}

function luc_GeoIP_get_data($ipAddress)
{	global $STATCOUNTER_Option;
	$STATCOUNTER_Use_GeoIP = $STATCOUNTER_Option['STATCOUNTER_Use_GeoIP'];

	if (file_exists(luc_GeoIP_dbname('country')))
	{
		if ($STATCOUNTER_Use_GeoIP == 'checked')
		{
			$gi = geoip_open(luc_GeoIP_dbname('country'), GEOIP_STANDARD);
			$array['cc'] = geoip_country_code_by_addr($gi, $ipAddress);
			$array['cn'] = utf8_encode(geoip_country_name_by_addr($gi, $ipAddress));
		}
	}

	if (file_exists(luc_GeoIP_dbname('city')))
	{
		if ($STATCOUNTER_Use_GeoIP == 'checked')
		{
			$gic = geoip_open(luc_GeoIP_dbname('city'), GEOIP_STANDARD);
			$gir = GeoIP_record_by_addr($gic, $ipAddress);
			$array['region'] = utf8_encode($gir->region);
			$array['city'] = utf8_encode($gir->city);
			$array['postal_code'] = $gir->postal_code;
			$array['latitude'] = $gir->latitude;
			$array['longitude'] = $gir->longitude;
			$array['area_code'] = $gir->area_code;
			$array['metro_code'] = $gir->metro_code;
			$array['continent_code'] = $gir->continent_code;
		}
	}
	if (count($array) > 0)
		return $array;
	else
		return false;
}	
		
	function luc_ValueTable2($fld,$fldtitle,$limit = 0,$param = "", $queryfld = "", $exclude= "") 
	{
	global $wpdb;
	$table_name = STATCOUNTER_TABLE_NAME;
	
	if ($queryfld == '') { $queryfld = $fld; }
	print "<div class='wrap'><table class='widefat'><thead><tr><th scope='col' style='width:400px;'><h2>$fldtitle</h2></th><th scope='col' style='width:100px;'>".__('Visits','STATCOUNTER')."</th><th></th></tr></thead>";
	$rks = $wpdb->get_var("SELECT count($param $queryfld) as rks FROM $table_name WHERE 1=1 $exclude;"); 
	if($rks > 0) {
		$sql="SELECT count($param $queryfld) as pageview, $fld FROM $table_name WHERE 1=1 $exclude GROUP BY $fld ORDER BY pageview DESC";
		if($limit > 0) { $sql=$sql." LIMIT $limit"; }
		$qry = $wpdb->get_results($sql);
	    $tdwidth=450;
		
		// Collects data
		$data=array();
		foreach ($qry as $rk) {
			$pc=round(($rk->pageview*100/$rks),1);
			if($fld == 'date') { $rk->$fld = luc_hdate($rk->$fld); }
			if($fld == 'urlrequested') { $rk->$fld = luc_post_title_Decode($rk->$fld); }
        	$data[substr($rk->$fld,0,50)]=$rk->pageview;
		}
	}

	// Draw table body
	print "<tbody id='the-list'>";
	if($rks > 0) {  // Chart!
		$chart=luc_GoogleChart("","500x200",$data);
		
		print "<tr><td></td><td></td><td rowspan='".($limit+2)."'>$chart</td></tr>";
		foreach ($data as $key => $value) {
    	   	print "<tr><td style='width:500px;overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>".$key;
        	print "</td><td style='width:100px;text-align:center;'>".$value."</td>";
			print "</tr>";
		}
	}
	print "</tbody></table></div><br>\n";
	
}

function luc_GoogleChart($title,$size,$data_array) {
	if(empty($data_array)) { return ''; }
	// get hash
	foreach($data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}
	$maxValue=max($values);
	$simpleEncoding='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$chartData="s:";
	for($i=0;$i<count($values);$i++) {
		$currentValue=$values[$i];
		if($currentValue>-1) {
			$chartData.=substr($simpleEncoding,61*($currentValue/$maxValue),1);
		} else {
			$chartData.='_';
		}
	}
	$data=$chartData."&chxt=y&chxl=0:|0|".$maxValue;
	return "<img src=http://chart.apis.google.com/chart?chtt=".urlencode($title)."&cht=p3&chs=$size&chd=".$data."&chl=".urlencode(implode("|",$labels)).">";
}

function luc_GoogleGeo($title,$size,$data_array) {
	if(empty($data_array)) { return ''; }
	// get hash
	foreach($data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}
	return "<img src=http://chart.apis.google.com/chart?chtt=".urlencode($title)."&cht=t&chtm=world&chs=440x220&chco=eeeeee,FFffcc,cc3300&chd=t:0,".(implode(",",$values))."&chld=XX".(implode("",$labels)).">";
}

     
	function luc_dropdown_caps($default = false)
      {
          global $wp_roles;
          $role = get_role('administrator');
          foreach ($role->capabilities as $cap => $grant)
          {
              echo "<option ";
              if ($default == $cap)
                  echo "selected ";
              
              echo ">$cap</option>";
          }
      }	
	  
	  	
      function luc_STATCOUNTER_Abbrevia($s, $c)
      {
          $res = "";
          if (strlen($s) > $c)
              $res = "...";
          return my_substr($s, 0, $c) . $res;
      }
	  
	   function luc_tablesize($table)
      {
          global $wpdb;
          $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
          foreach ($res as $fstatus)
          {
              $data_lenght = $fstatus->Data_length;
              $data_rows = $fstatus->Rows;
          }
          return number_format(($data_lenght / 1024 / 1024), 2, ",", " ") . " MB ($data_rows records)";
      }
      
	   function luc_rgbhex($red, $green, $blue)
      {
          $red = 0x10000 * max(0, min(255, $red + 0));
          $green = 0x100 * max(0, min(255, $green + 0));
          $blue = max(0, min(255, $blue + 0));
          // convert the combined value to hex and zero-fill to 6 digits
          return "#" . str_pad(strtoupper(dechex($red + $green + $blue)), 6, "0", STR_PAD_LEFT);
      }
	 
    function luc_STATCOUNTER_extractfeedreq($url)
    {
		if(!strpos($url, '?') === FALSE)
		{
        list($null, $q) = explode("?", $url);
    	list($res, $null) = explode("&", $q);
        }
    else
    {
    	$prsurl = parse_url($url);
    	$res = $prsurl['path'] . $$prsurl['query'];
    }
    
    return $res;
}


class GoogChart
{
	// Constants
	const BASE = 'http://chart.apis.google.com/chart?';

	// Variables
	protected $types = array(
							'pie' => 'p',
							'line' => 'lc',
							'sparkline' => 'ls',
							'bar-horizontal' => 'bhg',
							'bar-vertical' => 'bvg',
						);

	protected $type;
	protected $title;
	protected $data = array();
	protected $size = array();
	protected $color = array();
	protected $fill = array();
	protected $labelsXY = false;
	protected $legend;
	protected $useLegend = true;
	protected $background = 'a,s,ffffff';

	protected $query = array();

	// debug
	public $debug = array();

	// Return string
	public function __toString()
	{
		return $this->display();
	}


	/** Create chart
	*/
	protected function display()
	{
		// Create query
		$this->query = array(
							'cht'	 => $this->types[strtolower($this->type)],					// Type
							'chtt'	 => $this->title,											// Title
							'chd' => 't:'.$this->data['values'],								// Data
							'chl'   => $this->data['names'],									// Data labels
							'chdl' => ( ($this->useLegend) && (is_array($this->legend)) ) ? implode('|',$this->legend) : null, // Data legend
							'chs'   => $this->size[0].'x'.$this->size[1],						// Size
							'chco'   => preg_replace( '/[#]+/', '', implode(',',$this->color)), // Color ( Remove # from string )
							'chm'   => preg_replace( '/[#]+/', '', implode('|',$this->fill)),   // Fill ( Remove # from string )
							'chxt' => ( $this->labelsXY == true) ? 'x,y' : null,				// X & Y axis labels
							'chf' => preg_replace( '/[#]+/', '', $this->background),			// Background color ( Remove # from string )
						);

		// Return chart
		return $this->img(
					GoogChart::BASE.http_build_query($this->query),
					$this->title
				);
	}

	/** Set attributes
	*/
	public function setChartAttrs( $attrs )
	{
		// debug
		$this->debug[] = $attrs;

		foreach( $attrs as $key => $value )
		{
			$this->{"set$key"}($value);
		}
	}

	/** Set type
	*/
	protected function setType( $type )
	{
		$this->type = $type;
	}


	/** Set title
	*/
	protected function setTitle( $title )
	{
		$this->title = $title;
	}


	/** Set data
	*/
	protected function setData( $data )
	{
		// Clear any previous data
		unset( $this->data );

		// Check if multiple data
		if( is_array(reset($data)) )
		{
			/** Multiple sets of data
			*/
			foreach( $data as $key => $value )
			{
				// Add data values
				$this->data['values'][] = implode( ',', $value );

				// Add data names
				$this->data['names'] = implode( '|', array_keys( $value ) );
			}
			/** Implode data correctly
			*/
			$this->data['values'] = implode('|', $this->data['values']);
			/** Create legend
			*/
			$this->legend = array_keys( $data );
		}
		else
		{
			/** Single set of data
			*/
			// Add data values
			$this->data['values'] = implode( ',', $data );

			// Add data names
			$this->data['names'] = implode( '|', array_keys( $data ) );
		}

	}

	/** Set legend
	*/
	protected function setLegend( $legend )
	{
		$this->useLegend = $legend;
	}

	/** Set size
	*/
	protected function setSize( $width, $height = null )
	{
		// check if width contains multiple params
		if(is_array( $width ) )
		{
			$this->size = $width;
		}
		else
		{
			// set each individually
			$this->size[] = $width;
			$this->size[] = $height;
		}
	}

	/** Set color
	*/
	protected function setColor( $color )
	{
		$this->color = $color;
	}

	/** Set labels
	*/
	protected function setLabelsXY( $labels )
	{
		$this->labelsXY = $labels;
	}

	/** Set fill
	*/
	protected function setFill( $fill )
	{
		// Fill must have atleast 4 parameters
		if( count( $fill ) < 4 )
		{
			// Add remaining params
			$count = count( $fill );
			for( $i = 0; $i < $count; ++$i )
				$fill[$i] = 'b,'.$fill[$i].','.$i.','.($i+1).',0';
		}
		
		$this->fill = $fill;
	}


	/** Set background
	*/
	protected function setBackground( $background )
	{
		$this->background = 'bg,s,'.$background;
	}

	/** Create img html tag
	*/
	protected function img( $url, $alt = null )
	{
		return sprintf('<img src="%s" alt="%s" style="width:%spx;height:%spx;" />', $url, $alt, $this->size[0], $this->size[1]);
	}


}
function luc_Client_Lookup_IP($ip)
{
	function luc_BanIP_Check($ip)
{
	$lines = file(STATCOUNTER_PLUGIN_PATH . 'def/banips.dat');

	foreach ($lines as $line_num => $record)
	{
		if (strcmp(trim($record), $ip) == 0)
			return true;
	}
	return null;
}

function luc_BanIP_Add($ip)
{
	$fp = fopen(STATCOUNTER_PLUGIN_PATH . 'def/banips.dat', 'a');
	if ($fp)
		fwrite($fp, "\n" . $ip);
	fclose($fp);
}

function luc_BanIP($ip)
{
	if (luc_BanIP_Check($ip))
		echo "IP address " . $ip . " already in ban list<br>";
	else
	{
		luc_BanIP_Add($ip);
		echo "IP address " . $ip . " is now banned!<br>";
	}
}

function luc_print_uas($array)
{
	foreach ($array as $a)
		$ret = $ret . $a->agent . "<br>";

	return $ret;
}

function luc_display_by_IP($ip)
{	
	global $wpdb;
	$table_name = STATCOUNTER_TABLE_NAME;

	$qry_s = "SELECT *
				FROM $table_name
				WHERE ip = '$ip'
				ORDER BY id DESC
				";
	$qry = $wpdb->get_results($qry_s);
    $num = $wpdb->num_rows;

	$qry_sa = "SELECT DISTINCT agent
				FROM $table_name
				WHERE ip = '$ip'
				ORDER BY agent ASC ;
				";
	$qrya = $wpdb->get_results($qry_sa);

	if ($_POST['banip'] == 'Ban IP address')
		luc_BanIP($ip);

	$text = "Report for " . $ip . " ";
	?>
	<form method=post>
		<div class='wrap'><table style="width:50%"><tr><td><h2> <?php _e($text) ?> </h2></td>

		<td width=50px align='right'>
			<input type=submit
				name=banip value='Ban IP address' >
		</td>
		</tr>
		</table>
		<table class='widefat'>
			<thead>
				<tr>
				<th scope='col' colspan='2'></th>
			</thead>
			<tbody>
				<tr>
					<td>Records in database:</td>
					<td> <?php _e($num) ?> </td>
				</tr>
				<tr>
					<td>Latest hit:</td>
					<td> <?php _e(luc_hdate($qry[0]->date) . " " . $qry[0]->time) ?> </td>
				</tr>
				<tr>
					<td>First hit:</td>
					<td> <?php _e(luc_hdate($qry[$num - 1]->date) . " " . $qry[$num - 1]->time) ?> </td>
				</tr>
				<tr>
					<td>User agent(s):</td>
					<td> <?php _e(luc_print_uas($qrya)) ?> </td>
				</tr>
			</tbody>
		</table>
	<?php

	$geoip = luc_GeoIP_get_data($ip);
	if ($geoip !== false)
	{
		?>
		<table class='widefat'>
			<thead><tr><th scope='col' colspan='4'>GeoIP Information</th></tr></thead>
			<tbody>
			<tr>
				<td><strong>Country:</strong></td>
				<td> <?php _e($geoip['cn'] . " (" . $geoip['cc'] . ")") ?>
					<IMG style='border:0px;height:16px;' alt='$cn' title='$cn' SRC=' <?php _e(STATCOUNTER_PLUGIN_URL . "/images/domain/" . strtolower($geoip['cc']) . '.png') ?> '></td>
				<td><strong>Continent Code:</strong></td>
				<td> <?php _e($geoip['continent_code']) ?> </td>
			</tr>
			<tr>
				<td><strong>Region:</strong></td>
				<td> <?php _e($geoip['region']) ?> </td>
				<td><strong>Area Code: (USA Only)</strong></td>
				<td> <?php _e($geoip['area_code']) ?> </td>
			</tr>
			<tr>
				<td><strong>City:</strong></td>
				<td> <?php _e($geoip['city']) ?> </td>
				<td><strong>Postal Code: (USA Only)</strong></td>
				<td> <?php _e($geoip['postal_code']) ?> </td>
			</tr>
			<tr>
				<td><strong>Latitude/Longitude</strong></td>
				<td> <a href='http://maps.google.com/maps?q=<?php _e($geoip['latitude'] . "+" . $geoip['longitude']) ?>' target='_blank' title='Lookup latitude/longitude location on Google Maps...'><?php _e($geoip['latitude'] . " " . $geoip['longitude']) ?></a></td>
				<td><strong>Metro Code: (USA Only)</strong></td>
				<td> <?php _e($geoip['metro_code']) ?> </td>
			</tr>
			</tbody>
		</table>
		<?php

	}

	?>
		<table class='widefat'>
			<thead>
				<tr>
				<th scope='col' colspan='6'>URLs Requested</th>
				</tr>
			</thead>
			<thead>
				<tr>
				<th scope='col'>Date</th>
				<th scope='col'>Time</th>
				<th scope='col'>OS</th>
				<th scope='col'>Browser</th>
				<th scope='col'>Agent</th>
				<th scope='col'>Referrer</th>
				<th scope='col'>URL Requested</th>
				</tr>
			</thead>
			<tbody>
	<?php

	foreach ($qry as $rk)
	{
		?>
				<tr>
					<td> <?php _e(luc_hdate($rk->date)) ?> </td>
					<td> <?php _e($rk->time) ?> </td>
					<td> <?php _e(luc_HTML_IMG($rk->os, 'os', false)) ?> </td>
					<td> <?php _e(luc_HTML_IMG($rk->browser, 'browser', false)) ?></td>
					<td> <?php _e($rk->agent) ?> </td>
					<td> <?php _e($rk->referrer) ?> </td>
					<td> <?php _e(luc_post_title_Decode($rk->urlrequested)) ?> </td>
				</tr>
			</tbody>
		<?php
	}
	?>
		</table>
		</div>
	</form>
	<?php
	luc_STATCOUNTER_load_time();
}
	luc_display_by_IP($ip);
}
?>