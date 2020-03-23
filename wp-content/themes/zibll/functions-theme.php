<?php
/**
 * 子比主题
 * Zibll Theme
 * 官方网站：https://www.zibll.com/
 * 作者QQ：770349780
 * 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * 如您需要定制功能、或者其它任何交流欢迎加QQ
 */

$theme_data = wp_get_theme();
$_version = $theme_data['Version'];
define('THEME_VERSION', $_version);

//载入主题设置模块
require_once(get_theme_file_path('/options.php'));
require_once(get_theme_file_path('/functions-xzh.php'));
require_once(get_theme_file_path('/widgets/widget-index.php'));
require_once(get_theme_file_path('/functions/functions.php'));
require_once(get_theme_file_path('/vendor/autoload.php'));
require_once(get_theme_file_path('/oauth/oauth.php'));

if (is_admin()) {
	require_once(get_theme_file_path('/functions-admin.php'));
}
//  [zib_oauth_page_rewrite_rules OAuth登录处理页路由(/oauth)]
function zib_oauth_page_rewrite_rules($wp_rewrite)
{
	if ($ps = get_option('permalink_structure')) {
		$new_rules['oauth/([A-Za-z]+)$']          = 'index.php?oauth=$matches[1]';
		$new_rules['oauth/([A-Za-z]+)/callback$'] = 'index.php?oauth=$matches[1]&oauth_callback=1';
		$wp_rewrite->rules                        = $new_rules + $wp_rewrite->rules;
	}
}

add_action('generate_rewrite_rules', 'zib_oauth_page_rewrite_rules');

function zib_add_oauth_page_query_vars($public_query_vars)
{
	if (!is_admin()) {
		$public_query_vars[] = 'oauth'; // 添加参数白名单oauth，代表是各种OAuth登录处理页
		$public_query_vars[] = 'oauth_callback'; // OAuth登录最后一步，整合WP账户，自定义用户名
	}
	return $public_query_vars;
}
add_filter('query_vars', 'zib_add_oauth_page_query_vars');

function zib_oauth_page_template()
{
	$oauth          = strtolower(get_query_var('oauth')); //转换为小写
	$oauth_callback = get_query_var('oauth_callback');
	if ($oauth) {
		if (in_array($oauth, array('qq', 'qqagent', 'weixin', 'weixinagent', 'weibo', 'weiboagent', 'github', 'githubagent'))) :
			global $wp_query;
			$wp_query->is_home = false;
			$wp_query->is_page = true; //将该模板改为页面属性，而非首页
			$template          = $oauth_callback ? TEMPLATEPATH . '/oauth/' . $oauth . '/callback.php' : TEMPLATEPATH . '/oauth/' . $oauth . '/login.php';
			load_template($template);
			exit;
		else :
			// 非法路由处理
			unset($oauth);
			return;
		endif;
	}
}
add_action('template_redirect', 'zib_oauth_page_template', 5);

// 开启链接管理
//add_filter('pre_option_link_manager_enabled', '__return_true');

// 删除WordPress Emoji 表情
if (_pz('remove_emoji', true)) {
	remove_action('admin_print_scripts',	'print_emoji_detection_script');
	remove_action('admin_print_styles',	'print_emoji_styles');
	remove_action('wp_head',	'print_emoji_detection_script',	7);
	remove_action('wp_print_styles',	'print_emoji_styles');
	remove_filter('the_content_feed',	'wp_staticize_emoji');
	remove_filter('comment_text_rss',	'wp_staticize_emoji');
	remove_filter('wp_mail',	'wp_staticize_emoji_for_email');
}
//开启文章格式
add_theme_support('post-formats', array('image', 'gallery', 'video'));
	//开启特色图像
add_theme_support('post-thumbnails');
/**
 * 主题启动时执行函数
 *
 * @return void
 */

function zib_init_theme()
{
	$init_pages = array(
		'pages/newposts.php'      => array('发布文章', 'newposts', 'post_article_url'),
		'pages/resetpassword.php' => array('找回密码', 'resetpassword', 'user_rp'),
	);
	/**
	 * 刷新固定连接
	 */
	flush_rewrite_rules();
	foreach ($init_pages as $template => $item) {
		$one_page = array(
			'post_title'  => $item[0],
			'post_name'   => $item[1],
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_author' => 1,
		);
		$one_page_check = get_page_by_title($item[0]);
		if (!isset($one_page_check->ID)) {
			$one_page_id = wp_insert_post($one_page);
			update_post_meta($one_page_id, '_wp_page_template', $template);
			_spz($item[2], $one_page_id);
		}
	}
	global $pagenow;
	if ('themes.php' == $pagenow && isset($_GET['activated'])) {
		wp_redirect(of_get_menuurl());
		//exit;
	}
}
add_action('after_setup_theme', 'zib_init_theme');
add_action('after_switch_theme', 'zib_init_theme');

//删除google字体
if (_pz('remove_open_sans', true)) {
	function remove_open_sans()
	{
		wp_deregister_style('open-sans');
		wp_register_style('open-sans', false);
		wp_enqueue_style('open-sans', '');
	}
	add_action('init', 'remove_open_sans');
}

// 禁用更新
if (_pz('display_wp_update')) {
	remove_action('admin_init', '_maybe_update_core');    // 禁止 WordPress 检查更新
	remove_action('admin_init', '_maybe_update_plugins'); // 禁止 WordPress 更新插件
	remove_action('admin_init', '_maybe_update_themes');  // 禁止 WordPress 更新主题
}
//非管理员关闭顶部admin_bar
if (_pz('hide_admin_bar', true) || is_admin()) {
	add_filter('show_admin_bar', '__return_false');
}

if (_pz('disabled_pingback', true)) {
	// 阻止文章内相互 pingback
	add_action('pre_ping', '_noself_ping');
	function _noself_ping(&$links)
	{
		$home = get_option('home');
		foreach ($links as $l => $link) {
			if (0 === strpos($link, $home)) {
				unset($links[$l]);
			}
		}
	}
}

// 搜索内容排除页面
if (_pz('search_no_page')) {
	add_filter('pre_get_posts', 'ri_exclude_page_from_search');
	function ri_exclude_page_from_search($query)
	{
		if ($query->is_search) {
			$query->set('post_type', 'post');
		}
		return $query;
	}
}
// 注册菜单位置
if (function_exists('register_nav_menus')) {
	register_nav_menus(array(
		'mobilemenu' => __('移动端菜单', 'zib_language'),
		'topmenu' => __('PC端顶部菜单', 'zib_language'),
	));
}

// 获取及设置主题配置参数
function _pz($name, $default = false)
{
	return of_get_option($name, $default);
}
function _spz($name, $value)
{
	return of_set_option($name, $value);
}

function _name($name, $fenge = ' ')
{
	$n = get_option_framework_name();
	return $n . $fenge . $name;
}

if (_pz('posts_per_page')) {
	update_option('posts_per_page', _pz('posts_per_page'));
}

/*注册专题*/
function zib_register_topics()
{
	$labels = [
		'name'              => __('专题'),
		'singular_name'     => __('专题'),
		'search_items'      => __('搜索专题'),
		'all_items'         => __('所有专题'),
		'parent_item'       => __('父专题'),
		'parent_item_colon' => __('父专题:'),
		'edit_item'         => __('编辑专题'),
		'update_item'       => __('更新专题'),
		'add_new_item'      => __('添加新专题'),
		'new_item_name'     => __('新专题名称'),
		'menu_name'         => __('专题'),
	];
	$args = [
		'description'       => '添加文章专题',
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'query_var'         => true,
	];
	register_taxonomy('topics', ['post'], $args);
}
add_action('init', 'zib_register_topics');



function exclude_single_posts_orderby($query)
{
	if ($query->is_main_query()) {
		$query->set('orderby', _pz('list_orderby'), 'data');
	}
}
add_action('pre_get_posts', 'exclude_single_posts_orderby');

//首页文章排除
function exclude_single_posts_home($query)
{
	$d_cats = array();
	$exclude_cats = array();
	if ($query->is_home() && $query->is_main_query()) {
		if (_pz('home_exclude_posts')) {
			$query->set('post__not_in', preg_split("/,|，|\s|\n/", _pz('home_exclude_posts')));
		}

		if (_pz('home_exclude_cats')) {
			$exclude_cats = array_merge($exclude_cats, preg_split("/,|，|\s|\n/", _pz('home_exclude_cats')));
		}
		if (_pz('docs_mode_exclude')&&_pz('docs_mode_cats')) {

			foreach (_pz('docs_mode_cats') as $key => $value) {
				if ($value) $d_cats[] = $key;
			}
			foreach ($d_cats as $d_cat) {
				$children = get_term_children($d_cat, 'category');
				$d_cats = array_merge($d_cats, $children);
			}
			$exclude_cats = array_merge($exclude_cats, $d_cats);
		}
		$query->set('category__not_in', $exclude_cats);
	}
}
add_action('pre_get_posts', 'exclude_single_posts_home');

//获取用户id
function zib_get_user_id($id_or_email)
{
	$user_id = '';
	if (is_numeric($id_or_email))
		$user_id = (int) $id_or_email;
	elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email)))
		$user_id = $user->ID;
	elseif (is_object($id_or_email) && !empty($id_or_email->user_id))
		$user_id = (int) $id_or_email->user_id;
	return $user_id;
}

function zib_default_avatar()
{
	return _pz('avatar_default_img', get_stylesheet_directory_uri() . '/img/avatar-default.png');
}

function zib_default_thumb()
{
	return _pz('thumbnail') ? _pz('thumbnail') : get_stylesheet_directory_uri() . '/img/thumbnail.svg';
}

function zib_get_data_avatar($id_or_email = '', $size = '', $alt = '')
{
	$avatar = get_avatar($id_or_email, $size, $alt);
	if (_pz('lazy_avatar')) {
		$avatar =  str_replace(' src=', ' src="' . zib_default_avatar() . '" data-src=', $avatar);
	}
	return $avatar;
}

add_filter('get_avatar', 'zib_get_avatar', 1, 5);
function zib_get_avatar($avatar, $id_or_email, $size, $default, $alt)
{
	$user_id = zib_get_user_id($id_or_email);
	$custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
	$alt = $alt ? $alt : get_the_author_meta('nickname', $user_id);

	$avatar = $custom_avatar ? $custom_avatar : zib_default_avatar();
	$avatar = "<img alt='{$alt}' src='{$avatar}' class='lazyload avatar avatar-{$size}' height='{$size}' width='{$size}' />";
	return $avatar;
}
// 侧边栏显示判断
function zib_is_show_sidebar()
{
	$is = false;
	if (_pz('sidebar_home_s') && is_home()) {
		$is = true;
	}
	if (_pz('sidebar_single_s') && is_single()) {
		$is = true;
	}
	if (_pz('sidebar_cat_s') && is_category()) {
		$is = true;
	}
	if (_pz('sidebar_tag_s') && is_tag()) {
		$is = true;
	}
	if (_pz('sidebar_search_s') && is_search()) {
		$is = true;
	}
	if (wp_is_mobile()) {
		$is = false;
	}
	if (is_page_template('pages/newposts.php')) {
		$is = true;
	}
	return $is;
}

// 分类链接删除 'category'
if (_pz('no_categoty') && !function_exists('no_category_base_refresh_rules')) {
	register_activation_hook(__FILE__, 'no_category_base_refresh_rules');
	add_action('created_category', 'no_category_base_refresh_rules');
	add_action('edited_category', 'no_category_base_refresh_rules');
	add_action('delete_category', 'no_category_base_refresh_rules');
	function no_category_base_refresh_rules()
	{
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	register_deactivation_hook(__FILE__, 'no_category_base_deactivate');
	function no_category_base_deactivate()
	{
		remove_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');
		// We don't want to insert our custom rules again
		no_category_base_refresh_rules();
	}

	// Remove category base
	add_action('init', 'no_category_base_permastruct');
	function no_category_base_permastruct()
	{
		global $wp_rewrite, $wp_version;
		if (version_compare($wp_version, '3.4', '<')) {
			// For pre-3.4 support
			$wp_rewrite->extra_permastructs['category'][0] = '%category%';
		} else {
			$wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
		}
	}

	// Add our custom category rewrite rules
	add_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');
	function no_category_base_rewrite_rules($category_rewrite)
	{
		//var_dump($category_rewrite); // For Debugging

		$category_rewrite = array();
		$categories = get_categories(array('hide_empty' => false));
		foreach ($categories as $category) {
			$category_nicename = $category->slug;
			if ($category->parent == $category->cat_ID) // recursive recursion
				$category->parent = 0;
			elseif ($category->parent != 0)
				$category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
			$category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
			$category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
			$category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
		}
		// Redirect support from Old Category Base
		global $wp_rewrite;
		$old_category_base = get_option('category_base') ? get_option('category_base') : 'category';
		$old_category_base = trim($old_category_base, '/');
		$category_rewrite[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';

		//var_dump($category_rewrite); // For Debugging
		return $category_rewrite;
	}
	// Add 'category_redirect' query variable
	add_filter('query_vars', 'no_category_base_query_vars');
	function no_category_base_query_vars($public_query_vars)
	{
		$public_query_vars[] = 'category_redirect';
		return $public_query_vars;
	}

	// Redirect if 'category_redirect' is set
	add_filter('request', 'no_category_base_request');
	function no_category_base_request($query_vars)
	{
		//print_r($query_vars); // For Debugging
		if (isset($query_vars['category_redirect'])) {
			$catlink = trailingslashit(get_option('home')) . user_trailingslashit($query_vars['category_redirect'], 'category');
			status_header(301);
			header("Location:$catlink");
			exit();
		}
		return $query_vars;
	}
}

//颜色转换
function hex_to_rgba($hex, $a)
{
	$hex = str_replace("#", "", $hex);
	if (strlen($hex) == 3) {
		$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
		$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
		$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
	} else {
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
	}
	$a = $a ? ',' . $a : '';
	$rgb = 'rgb(' . $r . ',' . $g . ',' . $b . $a . ')';
	return $rgb;
}
// 加载css和js文件
add_action('wp_enqueue_scripts', '_load_scripts');
function _load_scripts()
{
	if (!is_admin()) {
		wp_deregister_script('jquery');

		wp_deregister_script('l10n');

		$purl = get_stylesheet_directory_uri();

		$css = array(
			'no' => array(
				'fontawesome' => 'font-awesome.min',
				'bootstrap' => 'bootstrap.min'
			),
			'baidu' => array(
				'fontawesome' => '//cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css',
				'bootstrap' => '//apps.bdimg.com/libs/bootstrap/3.3.7/css/bootstrap.min.css'
			),
			'staticfile' => array(
				'fontawesome' => '//cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css',
				'bootstrap' => '//cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css'
			),
			'bootcdn' => array(
				'fontawesome' => '//cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css',
				'bootstrap' => '//cdn.bootcss.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css'
			),
			'he' => array(
				'fontawesome' => '//cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css',
				'bootstrap' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
			)
		);

		// common css
		_cssloader(array('bootstrap' => _pz('js_outlink') ? $css[_pz('js_outlink')]['bootstrap'] : 'bootstrap.min', 'fontawesome' => _pz('js_outlink') ? $css[_pz('js_outlink')]['fontawesome'] : 'fontawesome.min', 'main' => 'main'));

		// page css
		if (is_page_template('pages/newposts.php')) {
			_cssloader(array('new-posts' => 'new-posts'));
		}
		// page css
		if (is_page_template('pages/postsnavs.php')) {
			_cssloader(array('navs' => 'navs'));
		}

		$jss = array(
			'no' => array(
				'jquery' => $purl . '/js/libs/jquery.min.js',
				'bootstrap' => $purl . '/js/libs/bootstrap.min.js'
			),
			'baidu' => array(
				'jquery' => '//apps.bdimg.com/libs/jquery/1.9.1/jquery.min.js',
				'bootstrap' => '//apps.bdimg.com/libs/bootstrap/3.3.7/js/bootstrap.min.js'
			),
			'staticfile' => array(
				'jquery' => '//cdn.staticfile.org/jquery/1.9.1/jquery.min.js',
				'bootstrap' => '//cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js'
			),
			'bootcdn' => array(
				'jquery' => '//cdn.bootcss.com/jquery/1.9.1/jquery.min.js',
				'bootstrap' => '//cdn.bootcss.com/twitter-bootstrap/3.3.7/js/bootstrap.min.js'
			),
			'he' => array(
				'jquery' => '//code.jquery.com/jquery-1.9.1.min.js',
				'bootstrap' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'
			)
		);
		wp_register_script('jquery', _pz('js_outlink') ? $jss[_pz('js_outlink')]['jquery'] : $purl . '/js/libs/jquery.min.js', false, THEME_VERSION, false);
		wp_enqueue_script('bootstrap', _pz('js_outlink') ? $jss[_pz('js_outlink')]['bootstrap'] : $purl . '/js/libs/bootstrap.min.js', array('jquery'), THEME_VERSION, true);
		_jsloader(array('loader'));
	}
}

function _cssloader($arr)
{
	foreach ($arr as $key => $item) {
		$href = $item;
		if (strstr($href, '//') === false) {
			$href = get_stylesheet_directory_uri() . '/css/' . $item . '.css';
		}
		wp_enqueue_style('_' . $key, $href, array(), THEME_VERSION, 'all');
	}
}
function _jsloader($arr)
{
	foreach ($arr as $item) {
		wp_enqueue_script('_' . $item, get_stylesheet_directory_uri() . '/js/' . $item . '.js', array(), THEME_VERSION, true);
	}
}


function _get_delimiter()
{
	return _pz('connector') ? _pz('connector') : '-';
}

//文章列表新窗口打开
function _post_target_blank()
{
	return _pz('target_blank') ? ' target="_blank"' : '';
}


//中文用户名注册
function chinese_username($username, $raw_username, $strict)
{
	$username = wp_strip_all_tags($raw_username);
	$username = remove_accents($username);
	$username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
	$username = preg_replace('/&.+?;/', '', $username); // Kill entities
	if ($strict) {
		$username = preg_replace('|[^a-z\p{Han}0-9 _.\-@]|iu', '', $username);
	}
	$username = trim($username);
	$username = preg_replace('|\s+|', ' ', $username);
	return $username;
}

add_filter('sanitize_user', 'chinese_username', 10, 3);

function get_the_subtitle($span = true)
{
	global $post;
	$post_ID = $post->ID;
	$subtitle = get_post_meta($post_ID, 'subtitle', true);

	if (!empty($subtitle)) {
		if ($span) {
			return ' <span>' . $subtitle . '</span>';
		} else {
			return ' ' . $subtitle;
		}
	} else {
		return false;
	}
}

//小工具可视化编辑连接
function zib_get_customize_widgets_url()
{
	return esc_url(
		add_query_arg(
			array(
				array('autofocus' => array('panel' => 'widgets')),
				'return' => urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash($_SERVER['REQUEST_URI']))),
			),
			admin_url('customize.php')
		)
	);
}

//主题切换
function zib_get_theme_mode()
{
	$theme_mode = '';
	$theme_mode = _pz('theme_mode');
	$time = current_time('G');
	if ($theme_mode == 'time-auto') {
		if ($time > 19 || $time < 9) {
			$theme_mode = 'dark-theme';
		} else {
			$theme_mode = 'white-theme';
		}
	}
	if (_pz('theme_mode_button', true) && isset($_COOKIE["theme_mode"])) {
		$theme_mode = $_COOKIE["theme_mode"];
	}
	return $theme_mode;
}

//根据主题筛选图片
function zib_get_adaptive_theme_img($white_src='' , $dark_src ='' , $atl='',$more='',$lazy=false)
{
	if(!$dark_src) $dark_src = $white_src;
	if(!$white_src) $white_src = $dark_src;
	if(!$dark_src&&!!$white_src) return;
	$lazy_src = get_stylesheet_directory_uri() . '/img/thumbnail-sm.svg';
	if(zib_get_theme_mode() == 'dark-theme'){
		$img = '<img '.($lazy?'src="' . $lazy_src . '" data-':'').'src="' . $dark_src . '" switch-src="' . $white_src . '" alt="' . $atl . '" '.$more.'>';
	}else{
		$img = '<img '.($lazy?'src="' . $lazy_src . '" data-':'').'src="' . $white_src . '" switch-src="' . $dark_src . '" alt="' . $atl . '" '.$more.'>';
	}
	return $img ;
}


function _bodyclass()
{
	$class = '';

	$class .= zib_get_theme_mode();

	if (is_super_admin()) {
		$class .= ' logged-admin';
	}
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	if (_pz('nav_fixed') || (is_home() && $paged == 1 && _pz('index_slide_s') && _pz('index_slide_position', 'top') == 'header' && _pz('index_slide_src_1'))) {
		$class .= ' nav-fixed';
	}

	if (zib_is_show_sidebar()) {
		$class .= _pz('sidebar_layout') == 'left' ? ' site-layout-3' : ' site-layout-2';
	} else {
		$class .= ' site-layout-1';
	}

	if ((is_single() || is_page()) && get_post_format()) {
		$class .= ' postformat-' . get_post_format();
	}
	return trim($class);
}

function _cut_count($number)
{
	$number = (int) $number;
	if ($number > 9999) {
		$number =  round($number / 10000, 1) . 'W+';
	}
	return $number;
}

function get_post_view_count($before = '阅读(', $after = ')')
{
	global $post;
	$post_ID = $post->ID;
	$views = _cut_count(get_post_meta($post_ID, 'views', true));
	return $before . $views . $after;
}

function zib_str_cut($str, $start, $width, $trimmarker)
{
	$output = preg_replace('/^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $width . '}).*/s', '\1', $str);
	return $output . $trimmarker;
}

function zib_get_excerpt($limit = 90, $after = '...')
{
	global $post;
	$excerpt = '';
	if (!empty($post->post_excerpt)) {
		$excerpt = $post->post_excerpt;
	} else {
		$excerpt = $post->post_content;
	}
	$excerpt = trim(str_replace(array("\r\n", "\r", "\n", "　", " "), " ", str_replace("\"", "'", strip_tags($excerpt))));
	$the = trim(get_post_meta($post->ID, 'description', true));

	if ($the) {
		$excerpt = $the;
	}
	if (_new_strlen($excerpt) > $limit) {
		$excerpt = zib_str_cut(strip_tags($excerpt), 0, $limit, $after);
	}
	return $excerpt;
}

function zib_get_post_comments($before = '评论(', $after = ')')
{
	return $before . get_comments_number('0', '1', '%') . $after;
}

function zib_is_url($C_url)
{
	if(preg_match("/^(http:\/\/|https:\/\/).*$/",$C_url)){
			return true;
		}else {
			return false;
		}
}
//中文文字计数
function _new_strlen($str, $charset = 'utf-8')
{
	$n = 0;
	$p = 0;
	$c = '';
	$len = strlen($str);
	if ($charset == 'utf-8') {
		for ($i = 0; $i < $len; $i++) {
			$c = ord($str{
				$i});
			if ($c > 252) {
				$p = 5;
			} elseif ($c > 248) {
				$p = 4;
			} elseif ($c > 240) {
				$p = 3;
			} elseif ($c > 224) {
				$p = 2;
			} elseif ($c > 192) {
				$p = 1;
			} else {
				$p = 0;
			}
			$i += $p;
			$n++;
		}
	} else {
		for ($i = 0; $i < $len; $i++) {
			$c = ord($str{
				$i});
			if ($c > 127) {
				$p = 1;
			} else {
				$p = 0;
			}
			$i += $p;
			$n++;
		}
	}
	return $n;
}

function zib_post_thumbnail($size = '', $class = 'fit-cover', $url = false)
{
	if (!$size) {
		$size = _pz('thumb_postfirstimg_size');
	}
	global $post;
	$r_src = '';
	if (has_post_thumbnail()) {
		$domsxe = get_the_post_thumbnail('', $size);
		preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $domsxe, $strResult, PREG_PATTERN_ORDER);
		$images = $strResult[1];
	} elseif (_pz('thumb_postfirstimg_s', true)) {
		$images = zib_get_post_img($size, $class, 1, false, true);
	}
	if ($images) {
		foreach ($images as $src) {
			$r_src = $src;
			break;
		}
	} elseif (_pz('thumb_catimg_s', true)) {
		$category = get_the_category();
		foreach ($category as $cat) {
			$r_src = zib_get_taxonomy_img_url($cat->cat_ID, $size);
			if ($r_src) break;
		}
	}
	if ($r_src) {
		if ($url) {
			return $r_src;
		}
		if (_pz('lazy_posts_thumb')) {
			return sprintf('<img src="%s" data-src="%s" alt="%s" class="lazyload ' . $class . '">', zib_default_thumb(), $r_src, $post->post_title . _get_delimiter() . get_bloginfo('name'));
		} else {
			return sprintf('<img src="%s" alt="%s" class="' . $class . '">', $r_src, $post->post_title . _get_delimiter() . get_bloginfo('name'));
		}
	} else {
		if ($url) {
			return false;
		}
		return sprintf('<img data-thumb="default" src="%s" alt="%s" class="' . $class . '">', zib_default_thumb(), $post->post_title . _get_delimiter() . get_bloginfo('name'));
	}
}
//列表多图模式获取文章图片
function zib_posts_multi_thumbnail($size = '', $class = 'fit-cover')
{
	if (!$size) {
		$size = _pz('thumb_postfirstimg_size');
	}
	$html = zib_get_post_img($size, $class, 4);

	if (_pz('lazy_posts_thumb')) {
		$html = str_replace(' src=', ' src="' . zib_default_thumb() . '" data-src=', $html);
		$html = str_replace(' class="', ' class="lazyload ', $html);
	}

	return $html;
}

//获取文章分享图片
function zib_share_img()
{
	$r_src = zib_post_thumbnail('medium', '', true);
	if ($r_src) {
		return $r_src;
	} else {
		return  _pz('share_img_byimg') ? _pz('share_img_byimg') : get_stylesheet_directory_uri() . '/img/share_img.jpg';
	}
}

//获取文章图片
function zib_get_post_img($size = '', $class = '', $count = 0, $show_count = false, $show_array = false)
{
	if (!$size) {
		$size = _pz('thumb_postfirstimg_size');
	}
	global $post;
	$r_src = '';
	$html = '';
	$img_array = array();
	$content = $post->post_content;
	preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
	$images = $strResult[1];
	$i = 0;
	$counter = count($images);

	if ($show_array) {
		return $images;
	}
	if ($show_count) {
		return $counter;
	}
	$mun = $count ? $count : $counter;
	foreach ($images as $src) {
		$i++;
		$src2 = wp_get_attachment_image_src(_g_p_i_tm($src), $size);
		$src2 = $src2[0];
		if (!$src2 && true) {
			$src = $src;
		} else {
			$src = $src2;
		}
		if ($src) {
			$item = sprintf('<span><img src="%s" class="' . $class . '"></span>', $src);
		}
		$html .= $item;
		if (
			($i == $mun)
		) {
			break;
		}
	}
	return $html;
}

function _g_p_i_tm($link)
{
	global $wpdb;
	$link = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $link);
	return $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE guid='$link'");

}

//图片灯箱
if (_pz('imagelightbox')) {
	add_filter('the_content', 'imgbox_replace');
	function imgbox_replace($content)
	{
		global $post;
		$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
		$replacement = '<a$1href=$2$3.$4$5 data-imgbox="imgbox"$6>$7</a>';
		$content = preg_replace($pattern, $replacement, $content);
		return $content;
	}
}

//文章图片异步加载
if (_pz('lazy_posts_content')) {
	add_filter('the_content', 'lazy_img_replace');
	function lazy_img_replace($content)
	{
		global $post;
		$pattern = "/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
		$replacement = '<img$1src="' . get_stylesheet_directory_uri() . '/img/thumbnail-lg.svg' . '" data-src=$2$3.$4$5 $6>';
		$content = preg_replace($pattern, $replacement, $content);
		$pattern = "/<img(.*?)srcset=('|\")([^>]*)('|\")(.*?)>/i";
		$replacement = '<img$1data-srcset=$2$3$4 $5>';
		$content = preg_replace($pattern, $replacement, $content);

		$pattern = "/<img(.*?)class=('|\")([^>]*)('|\")(.*?)>/i";
		$replacement = '<img$1class=$2lazyload swiper-lazy $3$4 $5>';
		$content = preg_replace($pattern, $replacement, $content);
		return $content;
	}
}

//昵称是否有保留字符
function is_disable_username($name)
{
	$disable_reg_keywords = _pz('user_nickname_out');
	$disable_reg_keywords = preg_split("/,|，|\s|\n/", $disable_reg_keywords);

	if (!$disable_reg_keywords || !$name) {
		return false;
	}
	foreach ($disable_reg_keywords as $keyword) {
		if (stristr($name, $keyword) || $keyword == $name ) {
			return true;
		}
	}
	return false;
}

// 记录用户登录时间
function user_last_login($user_login)
{
	global $user_ID;
	$user = get_user_by('login', $user_login);
	$time = current_time('mysql');
	update_user_meta($user->ID, 'last_login', $time);
}
add_action('wp_login', 'user_last_login');

//时间倒序格式化
function zib_get_time_ago($time)
{
	if (is_int($time)) {
		$time = intval($time);
	} else {
		$time = strtotime($time);
	}

	if(!_pz('time_ago_s',true) && _pz('time_format')){
		return date(_pz('time_format'),$time);
	}
	$ctime = intval(strtotime(current_time('mysql')));
	$t = $ctime - $time; //时间差 （秒）

	if ($t < 0) {
		return date('Y-m-d H:i', $time);
	}
	$y = intval(date('Y', $ctime) - date('Y', $time)); //是否跨年
	if ($t == 0) {
		$text = '刚刚';
	} elseif ($t < 60) { //一分钟内
		$text = $t . '秒前';
	} elseif ($t < 3600) { //一小时内
		$text = floor($t / 60) . '分钟前';
	} elseif ($t < 86400) { //一天内
		$text = floor($t / 3600) . '小时前'; // 一天内
	} elseif ($t < 2592000) { //30天内
		if ($time > strtotime(date('Ymd', strtotime("-1 day")))) {
			$text = '昨天';
		} elseif ($time > strtotime(date('Ymd', strtotime("-2 days")))) {
			$text = '前天';
		} else {
			$text = floor($t / 86400) . '天前';
		}
	} elseif ($t < 31536000 && $y == 0) { //一年内 不跨年
		$m = date('m', $ctime) - date('m', $time) - 1;

		if ($m == 0) {
			$text = floor($t / 86400) . '天前';
		} else {
			$text = $m . '月前';
		}
	} elseif ($t < 31536000 && $y > 0) { //一年内 跨年
		$text = (12 - date('m', $time) + date('m', $ctime)) . '月前';
	} else {
		$text = (date('Y', $ctime) - date('Y', $time)) . '年前';
	}

	return $text;
}

//评论者链接重定向
add_filter('get_comment_author_link', 'add_redirect_comment_link', 5);
add_filter('comment_text', 'add_redirect_comment_link', 99);
function add_redirect_comment_link($text = '')
{
	return go_link($text);
}

function go_link($text = '', $link = false)
{
	if (!$text || !_pz('go_link_s')) {
		return $text;
	}
	if ($link) {
		if (strpos($text, '://') !== false && strpos($text, home_url()) === false && !preg_match('/\.(jpg|jepg|png|ico|bmp|gif|tiff)/i', $text)) {
			$text = get_stylesheet_directory_uri() . "/go.php?url=" . base64_encode($text);
		}
		return $text;
	}
	preg_match_all("/<a(.*?)href='(.*?)'(.*?)>/", $text, $matches);
	if ($matches) {
		foreach ($matches[2] as $val) {
			if (strpos($val, '://') !== false && strpos($val, home_url()) === false && !preg_match('/\.(jpg|jepg|png|ico|bmp|gif|tiff)/i', $val)) {
				$text = str_replace("href=\"$val\"", "href=\"" . get_stylesheet_directory_uri() . "/go.php?url=".base64_encode($val)."\" ", $text);
			}
		}
		foreach ($matches[1] as $val) {
			$text = str_replace("<a" . $val, "<a" . $val . " target=\"_blank\" ", $text);
		}
	}
	return $text;
}

if (_pz('go_link_s') && _pz('go_link_post')) {
	add_filter('the_content', 'the_content_nofollow', 999);
	function the_content_nofollow($content)
	{
		preg_match_all('/<a(.*?)href="(.*?)"(.*?)>/', $content, $matches);
		if ($matches) {
			foreach ($matches[2] as $val) {
				if (strpos($val, '://') !== false && strpos($val, home_url()) === false && !preg_match('/\.(jpg|jepg|png|ico|bmp|gif|tiff)/i', $val)) {
					$content = str_replace("href=\"$val\"", "href=\"" . get_stylesheet_directory_uri() . "/go.php?url=".base64_encode($val)."\" ", $content);
				}
			}
		}
		return $content;
	}
}

// 给分类连接添加SEO
function _get_tax_meta($id = 0, $field = '')
{
	$ops = get_option("_taxonomy_meta_$id");

	if (empty($ops)) {
		return '';
	}

	if (empty($field)) {
		return $ops;
	}

	return isset($ops[$field]) ? $ops[$field] : '';
}

//内容删除空格
function trimall($str)
{
	$limit = array(" ", "　", "\t", "\n", "\r");
	$rep = array("", "", "", "", "");
	return str_replace($limit, $rep, $str);
}

// 打赏按钮
function zib_get_rewards_button($user_ID, $class = 'ml6 but c-blue', $before = '', $after = '')
{
	$text = _pz('post_rewards_text', '赞赏');
	$before = $before ? $before : zib_svg('money');
	$weixin = get_user_meta($user_ID, 'rewards_wechat_image_id', true);
	$alipay = get_user_meta($user_ID, 'rewards_alipay_image_id', true);
	if (!$user_ID || !_pz('post_rewards_s') || (!$weixin && !$alipay)) return;
	return '<a href="javascript:($(\'#rewards-popover\').modal(\'show\'));"  class="' . $class . '">' . $before . $text . $after . '</a>';
}

// 写文章、投稿按钮
function zib_get_write_posts_button($class = 'but b-theme', $text = '写文章', $before = '', $after = '')
{
	if (!_pz('post_article_s', true)) return;
	$class .= ' start-new-posts';
	$href = zib_get_permalink(_pz('post_article_url'));
	if ($href) {
		return '<a target="_blank" href="' . $href . '" class="' . $class . '">' . $before . $text . $after . '</a>';
	}
}

//前台也可上传图片
if (_pz('post_article_img_s')) {
	if (!current_user_can('upload_files'))
		add_action('admin_init', 'allow_contributor_uploads');
	function allow_contributor_uploads()
	{
		$contributor = get_role('contributor');
		$contributor->add_cap('upload_files');
		$subscriber = get_role('subscriber');
		$subscriber->add_cap('upload_files');
	}
}

function zib_get_comment_like($class = '', $pid = '', $text = '', $count = false, $before = '', $after = '')
{
	if (!_pz('comment_like_s') || !$pid) return;
	$like = _cut_count(get_comment_meta($pid, 'comment_like', true));
	$svg = zib_svg('like');
	$before = $before ? $before : $svg;
	if (zib_is_my_com_like($pid)) {
		$class .= ' actived';
	}
	if ($count) {
		return $like;
	}
	return '<a href="javascript:;" data-action="comment_like" class="' . $class . '" data-pid="' . $pid . '">' . $before . '<text>' . $text . '</text><count>' . ($like ? $like : 0) . '</count></a>';
}

function zib_get_admin_edit($title = '编辑', $type = '', $class = 'admin-edit', $before = '', $after = '')
{
	$bef = $before ? $before : '<span class="' . $class . '" data-toggle="tooltip" title="' . $title . '">';
	$aft = $after ? $after : '</span>';
	$name = '[编辑]';
	if (!is_super_admin()) return;
	$link = edit_term_link($name, $bef, $aft, null, false);
	if ($type == 'posts') {
		$link = $bef . '<a href="' . get_edit_post_link() . '">' . $name . '</a>' . $aft;
	}
	if ($type == 'comment') {
		$link = edit_comment_link($name, $bef, $aft);
	}
	return $link;
}

function zib_get_post_like($class = '', $pid = '', $text = '点赞', $count = false, $before = '', $after = '')
{
	if (!_pz('post_like_s')) return;
	$pid = $pid ? $pid : get_the_ID();
	$like = _cut_count(get_post_meta($pid, 'like', true));
	$svg = zib_svg('like');
	$before = $before ? $before : $svg;
	if (zib_is_my_like($pid)) {
		$class .= ' actived';
	}

	if ($count) {
		return $like;
	}

	return '<a href="javascript:;" data-action="like" class="' . $class . '" data-pid="' . $pid . '">' . $before . '<text>' . $text . '</text><count>' . ($like ? $like : 0) . '</count></a>';
}


function zib_get_user_follow($class = '', $follow_id = '', $text = '<i class="fa fa-heart-o mr6" aria-hidden="true"></i>关注', $ok_text = '<i class="fa fa-ban mr6" aria-hidden="true"></i>取消关注', $before = '', $after = '')
{

	if (!$follow_id || get_current_user_id() == $follow_id) return;
	if (zib_is_my_follow($follow_id)) {
		$class .= ' actived';
		$text = $ok_text;
	}

	$before = $before;
	$action = ' data-action="follow_user"';

	if (!is_user_logged_in()) {
		$action = '';
		$class .= ' signin-loader';
	}
	return '<a href="javascript:;"' . $action . ' class="' . $class . '" data-pid="' . $follow_id . '">' . $before . '<count>' . $text . '</count></a>';
}

function zib_is_docs_mode($pid = '', $cat_id = '')
{
	$d_cats = array();
	if (_pz('docs_mode_exclude')&&_pz('docs_mode_cats')) {
	foreach (_pz('docs_mode_cats') as $key => $value) {
		if ($value) $d_cats[] = $key;
	}
}
	if (is_single() && !$pid) {
		$pid = get_queried_object_id();
	}
	if (is_category() && !$cat_id) {
		$cat_id = get_queried_object_id();
	}
	if ($cat_id && in_array($cat_id, $d_cats)) return $cat_id;

	foreach ($d_cats as $c_id) {
		$posts = get_posts(array(
			'category' => $c_id,
			'numberposts' => -1,
		));
		foreach ($posts as $post) {
			if ($post->ID == $pid) return $c_id;
		}
	}
	return false;
}

function zib_get_post_favorite($class = '', $pid = '', $text = '收藏', $count = false, $before = '', $after = '')
{

	$pid = $pid ? $pid : get_the_ID();
	$favorite_count = get_post_meta($pid, 'favorite', true);
	$text = $text . '<count>' . ($favorite_count ? $favorite_count : 0) . '</count>';
	if (zib_is_my_favorite($pid)) {
		$class .= ' actived';
	}
	$svg = zib_svg('favorite');
	$before = $before ? $before : $svg;
	if ($count) {
		return $favorite_count;
	}
	$action = ' data-action="favorite"';
	if (!is_user_logged_in()) {
		$action = '';
		$class .= ' signin-loader';
	}
	return '<a href="javascript:;"' . $action . ' class="' . $class . '" data-pid="' . $pid . '">' . $before . '<text>' . $text . '</text></a>';
}

function zib_is_my_follow($pid = '')
{
	if (!is_user_logged_in() || !$pid) return false;
	$value = get_user_meta(get_current_user_id(), 'follow-user', true);
	$value = $value ? unserialize($value) : array();
	return in_array($pid, $value) ? true : false;
}

function zib_is_my_com_like($pid = '')
{
	if (!is_user_logged_in()) return false;
	$pid = $pid ? $pid : get_the_ID();
	$value = get_user_meta(get_current_user_id(), 'comment-posts', true);
	$value = $value ? unserialize($value) : array();
	return in_array($pid, $value) ? true : false;
}

function zib_is_my_like($pid = '')
{
	if (!is_user_logged_in()) return false;
	$pid = $pid ? $pid : get_the_ID();
	$value = get_user_meta(get_current_user_id(), 'like-posts', true);
	$value = $value ? unserialize($value) : array();
	return in_array($pid, $value) ? true : false;
}

function zib_is_my_favorite($pid = '')
{
	if (!is_user_logged_in()) return false;
	$pid = $pid ? $pid : get_the_ID();
	$value = get_user_meta(get_current_user_id(), 'favorite-posts', true);
	$value = $value ? unserialize($value) : array();
	return in_array($pid, $value) ? true : false;
}

//作者粉丝数量
function get_user_meta_count($user_id, $mata)
{
	if (!$user_id && $mata) return;
	$val = get_user_meta($user_id, $mata, true);
	if ($val) {
		$val = count(unserialize($val));
	}
	return _cut_count($val);
}

//作者总获赞
function get_user_posts_meta_count($user_id, $mata)
{
	global $wpdb;
	if (!$user_id || !$mata) return;
	$num = $wpdb->get_var("SELECT sum(meta_value) FROM $wpdb->posts,$wpdb->postmeta WHERE $wpdb->posts.post_author = $user_id AND $wpdb->postmeta.post_id=$wpdb->posts.ID AND $wpdb->postmeta.meta_key='$mata' AND $wpdb->posts.post_status='publish'");
	if($num){
		return _cut_count($num);
	}
	return 0;
}
//作者评论数
function get_user_comment_count($user_id)
{
	if (!$user_id) return;
	$args = array(
		'user_id' => $user_id,
		'count'   => true
	);
	$comments = get_comments($args);
	return _cut_count($comments);
}
//作者签名
function get_user_desc($user_id)
{
	if (!$user_id) return;
	$des = get_user_meta($user_id, 'description', true);
	if (!$des) {
		$des = _pz('user_desc_std', '这家伙很懒，什么都没有写...');
	}
	return $des;
}

// 获取分类封面图片
define('Z_IMAGE_PLACEHOLDER', get_stylesheet_directory_uri() . '/img/thumbnail-lg.svg');
function zib_get_taxonomy_img_url($term_id = null, $size = null, $return_placeholder = FALSE)
{
	if (!$term_id) {
		if (is_category() || is_tag())
			$term_id = get_queried_object_id();
		elseif (is_tax()) {
			$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
			$term_id = $current_term->term_id;
		}
	}
	$img = '';
	$img = get_option('_taxonomy_image_' . $term_id);
	if (!empty($img)) {
		$img_id = _g_p_i_tm($img);
		if (!empty($img_id)) {
			if (empty($size))
				$size = 'full';
			$img = wp_get_attachment_image_src($img_id, $size);
			$img = $img[0];
		}
	}

	if ($return_placeholder)
		return ($img != '') ? $img : Z_IMAGE_PLACEHOLDER;
	else
		return $img;
}

//作者封面图
function get_user_cover_img($user_id)
{
	$img = get_user_meta($user_id, 'cover_image', true);
	$default_img = _pz('user_cover_img', get_stylesheet_directory_uri() . '/img/user_t.jpg');
	return $img ? $img : $default_img;
}

add_action('init', 'custom_button');
function custom_button()
{
	add_filter('mce_external_plugins', 'add_plugin');
	add_filter('mce_buttons', 'register_button');
}
//前端编辑器
function register_button($buttons)
{
	array_push($buttons, "precode", "qedit");
	return $buttons;
}
//添加按钮动作
function add_plugin($plugin_array)
{
	$plugin_array['precode'] = get_bloginfo('template_url') . '/js/precode.js';
	return $plugin_array;
}
//禁用古腾堡
if (_pz('close_gutenberg')) {
	add_filter('use_block_editor_for_post', '__return_false');
}
// 编辑器按钮
function _add_editor_buttons($buttons)
{
	return $buttons;
}
add_filter("mce_buttons", "_add_editor_buttons");

//添加隐藏内容，回复可见
function reply_to_read($atts, $content = null)
{
	$a = '#comments';
	extract(shortcode_atts(array("notice" => '<a class="hidden-text" href="javascript:(scrollTo(\'' . $a . '\',-120));"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，请评论后刷新页面查看.</a>'), $atts));
	$_hide = '<div class="hidden-box">' . $notice . '</div>';
	$_show = '<div class="hidden-box"><div class="hidden-text">本文隐藏内容</div>' . do_shortcode($content) . '</div>';

	if (is_super_admin()) { //管理员登陆直接显示内容
		return $_show;
	} else {
		$email = null;
		$user_ID = (int) wp_get_current_user()->ID;
		if ($user_ID > 0) {
			$email = get_userdata($user_ID)->user_email;
		} else if (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
			$email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);
		} else {
			return $_hide;
		}
		if (empty($email)) {
			return  $_hide;
		}
		global $wpdb;
		$post_id = get_the_ID();
		$query = "SELECT `comment_ID` FROM {$wpdb->comments} WHERE `comment_post_ID`={$post_id} and `comment_approved`='1' and `comment_author_email`='{$email}' LIMIT 1";
		if ($wpdb->get_results($query)) {
			return $_show;
		} else {
			return  $_hide;
		}
	}
}

add_shortcode('reply', 'reply_to_read');
function zib_svg($name = '', $viewBox = '0 0 1024 1024', $class = "icon")
{
	if ($name) {
		return '<i data-class="' . $class . '" data-viewBox="' . $viewBox . '" data-svg="' . $name . '" aria-hidden="true"></i>';
	}
}

//邮件smtps
function zib_mail_smtp($phpmailer)
{
	if (_pz('mail_smtps')) {
		$phpmailer->IsSMTP();
		$phpmailer->FromName   = _pz('mail_name', 'idowns');
		$phpmailer->Host       = _pz('mail_host', 'smtp.qq.com');
		$phpmailer->Port       = _pz('mail_port', '465');
		$phpmailer->Username   = _pz('mail_name', '88888888@qq.com');
		$phpmailer->Password   = _pz('mail_passwd', '123456789');
		$phpmailer->From       = _pz('mail_name', '88888888@qq.com');
		$phpmailer->SMTPAuth   = _pz('mail_smtpauth', true);
		$phpmailer->SMTPSecure = _pz('mail_smtpsecure', 'ssl');
	}
}
add_action('phpmailer_init', 'zib_mail_smtp');
