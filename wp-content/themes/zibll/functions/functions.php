<?php
$functions = array(
    'zib-head',
    'zib-header',
    'zib-content',
    'zib-footer',
    'zib-index',
    'zib-category',
    'zib-author',
    'zib-posts-list',
    'zib-share',
    'zib-user',
    'zib-single',
    'zib-comments-list',
    'zib-svg-icon',
);

foreach ($functions as $function) {
    require_once $function . '.php';
}

function zib_get_img_slider($args)
{
    $defaults = array(
        'class' => '',
        'type' => '',
        'lazy' => false,
        'pagination' => true,
        'effect' => 'slide',
        'button' => true,
        'loop' => true,
        'auto_height' => false,
        'loop' => true,
        'interval' => 4000,
        'spaceBetween' => 15,
        'echo' => true,
    );
    $args = wp_parse_args((array) $args, $defaults);
    $class = $args['class'];
    $type = $args['type'];
    $lazy = $args['lazy'];
    $pagination = $args['pagination'];
    $effect = ' data-effect="' . $args['effect'] . '"';
    $button = $args['button'];
    $loop = $args['loop'] ? ' data-loop="true"' : '';
    $auto_h = $args['auto_height'] ? ' auto-height="true"' : '';
    $interval = $args['interval'] ? ' data-interval="' . $args['interval'] . '"' : '';
    $spaceBetween = $args['spaceBetween'] ? ' data-spaceBetween="' . $args['spaceBetween'] . '"' : '';

    $style = '';
    if (!$auto_h) {
        $_h = !empty($args['m_height']) ? '--m-height :' . (int) $args['m_height'] . 'px;' : '';
        $_h .= !empty($args['pc_height']) ? '--pc-height :' . (int) $args['pc_height'] . 'px;' : '';
        $style = ' style="' . $_h . '"';
    }

    if (_pz('lazy_sider')) {
        $lazy = true;
    }
    if (empty($args['slides'])) {
        return;
    }
    $slides = '';
    $pagination_rigth = '';
    foreach ($args['slides'] as $slide) {
        $lazy_src = get_stylesheet_directory_uri() . '/img/thumbnail-lg.svg';
        $s_class = isset($slide['class']) ? $slide['class'] : '';
        $s_href = isset($slide['href']) ? $slide['href'] : '';
        $s_image = isset($slide['image']) ? $slide['image'] : '';
        $s_blank = !empty($slide['blank']) ? ($s_href ? ' target="_blank"' : '') : '';
        $s_caption = isset($slide['caption']) ? $slide['caption'] : '';
        $s_desc = !empty($slide['desc']) ? '<div class="s-desc">' . $slide['desc'] . '</div>' : '';
        $pagination_rigth = !empty($slide['desc']) ? ' kaoyou' : ' kaoyou';
        $slides .= '<div class="swiper-slide' . ' ' . $s_class . '">' . $s_desc .
            '<a' . $s_blank . ($s_href ? ' href="' . $s_href . '"' : '') . '>
				<img class="lazyload swiper-lazy radius8" ' . ($lazy ? ' data-src="' . $s_image . '" src="' . $lazy_src . '"' : ' src="' . $s_image . '"') . '></a>'
            . ($s_caption ? '<div class="carousel-caption">' . $s_caption . '</div>' : '') . '</div>';
    }
    $pagination = $pagination ? '<div class="swiper-pagination' . $pagination_rigth . '"></div>' : '';
    $button = $button ? '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>' : '';

    $con = '<div class="new-swiper swiper-c ' . $class . '" ' . $effect . $loop . $auto_h . $interval . $spaceBetween . $style . '>
  <div class="swiper-wrapper">' . $slides . '</div>' .
        $button . $pagination . '</div>';
        if($args['echo']){
    echo '<div class="relative zib-slider theme-box">' . $con . '</div>';
}else{
    return '<div class="relative zib-slider">' . $con . '</div>';
}
}
//公告栏
function zib_get_notice()
{
}


//专题
function zib_get_topic()
{
    $inner = '';
    $title = _pz('topic_title', '精彩专题');
    $ms = _pz('topic_ms');
    $s_blank = _pz('topic_blank') ? ' target="_blank"' : '';
    $topic_i = _pz('topic_number', 4);

    echo '<div class="box-body notop">';
    echo '<div class="title-theme">' . $title . '<small class="ml10">' . $ms . '</small></div>';
    echo '</div>';
    echo '<div class="topic theme-box">';
    for ($i = 1; $i <= $topic_i; $i++) {
        $cat = get_term(_pz('topic_category_' . $i),'topics');
        if (_pz('topic_category_' . $i)) {
            echo '<a' . $s_blank . ' class="topic-cover but-ripple" href="' . get_category_link(_pz('topic_category_' . $i)) . '">';
            $count = $cat->count;
            $desc = $cat->description;
            if (is_super_admin() && !$desc) {
                $desc = '请在Wordress后台-文章-专题中添加专题描述！';
            }
            $tit = _pz('topic_name_' . $i) ? _pz('topic_name_' . $i) : $cat->name;
            $tit .= '<span class="icon-spot">共' . $count . '篇</span>';
            //$tit .= json_encode($cat);
            $img = zib_get_taxonomy_img_url(_pz('topic_category_' . $i));
            zib_page_cover($tit, $img, $desc);
            echo '</a>';
        }
    }
    echo '</div>';
}


function zib_avatar_metas($user_id)
{
    if (!$user_id) return;
    $avatar = zib_get_data_avatar($user_id);
    $like_n = get_user_posts_meta_count($user_id, 'like');
    $view_n = get_user_posts_meta_count($user_id, 'views');
    $com_n = get_user_comment_count($user_id);
    $post_n = (int) count_user_posts($user_id, 'post', true);

    if ($post_n) {
        echo '<a class="but c-blue tag-posts" data-toggle="tooltip" title="查看更多文章" href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . zib_svg('post') . $post_n . '</a>';
    }
    if ($com_n) {
        echo '<a class="but c-green tag-view" data-toggle="tooltip" title="共计' . $com_n . '条评论">' . zib_svg('comment') . $com_n . '</a>';
    }
    if ($view_n) {
        echo '<a class="but c-red tag-view" data-toggle="tooltip" title="人气值 ' . $view_n . '">' . zib_svg('huo') . $view_n . '</a>';
    }
    if ($like_n) {
        echo '<a class="but c-yellow tag-like" data-toggle="tooltip" title="获得' . $like_n . '个点赞">' . zib_svg('like') . $like_n . '</a>';
    }
}

function zib_yiyan($class = 'zib-yiyan', $before = '', $after = '')
{
    $yiyan = '<div class="' . $class . '">' . $before . '<div data-toggle="tooltip" data-original-title="点击切换一言" class="yiyan"></div>' . $after . '</div>';
    echo $yiyan;
}

function zib_posts_prevnext()
{
    $current_category = get_the_category();
    $prev_post = get_previous_post($current_category, '');
    $next_post = get_next_post($current_category, '');
    if (!empty($prev_post)) :
        $prev_title = $prev_post->post_title;
        $prev_link = 'href="' . get_permalink($prev_post->ID) . '"';
    else :
        $prev_title = '无更多文章';
        $prev_link = '';
    endif;
    if (!empty($next_post)) :
        $next_title = $next_post->post_title;
        $next_link = 'href="' . get_permalink($next_post->ID) . '"';
    else :
        $next_title = '无更多文章';
        $next_link = '';
    endif;
?>
<div class="theme-box" style="height:99px">
    <nav class="article-nav">
        <div class="main-bg box-body radius8 main-shadow">
            <a <?php echo $prev_link; ?>>
                <p class="muted-2-color">
                    << 上一篇</p>
                        <div class="text-ellipsis-2">
                            <?php echo $prev_title; ?>
                        </div>
            </a>
        </div>
        <div class="main-bg box-body radius8 main-shadow">
            <a <?php echo $next_link; ?>>
                <p class="muted-2-color">下一篇 >></p>
                <div class="text-ellipsis-2">
                    <?php echo $next_title; ?>
                </div>
            </a>
        </div>
    </nav>

</div>
<?php
}

function zib_posts_related($title = '相关阅读', $limit = 6)
{
    global $post;

    $exclude_id = $post->ID;
    $posttags = get_the_tags();
    $i = 0;
    $thumb_s = _pz('post_related_type') == 'img';

    echo '<div class="theme-box relates' . ($thumb_s ? ' relates-thumb' : '') . '">
		<div class="box-body notop">
			<div class="title-theme">' . $title . '</div>
			<div class="re-an"></div>
        </div>';

    echo '<div ' . ($thumb_s ? 'data-scroll="x" ' : '') . 'class="box-body main-bg radius8 main-shadow relates-content">';
    echo '<ul class="' . ($thumb_s ? 'scroll-x mini-scrollbar list-inline' : 'no-thumb') . '">';
    if ($posttags) {
        $tags = '';
        foreach ($posttags as $tag) $tags .= $tag->slug . ',';
        $args = array(
            'post_status'         => 'publish',
            'tag_slug__in'        => explode(',', $tags),
            'post__not_in'        => explode(',', $exclude_id),
            'ignore_sticky_posts' => 1,
            'orderby'             => 'comment_date',
            'posts_per_page'      => $limit
        );

        query_posts($args);
        while (have_posts()) {
            the_post();
            if (_pz('post_related_type') == 'list') {

                $_thumb = zib_post_thumbnail('', 'fit-cover radius8');
                $author = get_the_author();
                $title = get_the_title() . '<span class="focus-color">' . get_the_subtitle(false) . '</span>';
                $author = '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . $author . '</a>';

                $lists_class = 'posts-mini';
                $title_l = '<div class="item-heading text-ellipsis-2">
                        <a' . _post_target_blank() . ' href="' . get_permalink() . '">' . $title . '</>
                        </div>
                        ';
                $time_ago = zib_get_time_ago(get_the_time('U'));
                $meta_l = '<item class="meta-author">' . $author . '<span class="icon-spot">' . $time_ago . '</span></item>';

                echo '<div class="' . $lists_class . '">';
                echo '<a' . _post_target_blank() . ' class="item-thumbnail" href="' . get_permalink() . '">' . $_thumb . '</a>';
                echo '<div class="posts-mini-con">';
                echo $title_l;
                echo '<div class="item-meta muted-3-color">';
                echo $meta_l;
                echo '</div>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<li>';
                if ($thumb_s) echo '<a class="relative radius8" href="' . get_permalink() . '">' . zib_post_thumbnail() . '
				<span class="post-info">' . get_the_title() . get_the_subtitle() . '</span>
			</a>';
                if (!$thumb_s) echo '<a class="icon-circle" href="' . get_permalink() . '">' . get_the_title() . get_the_subtitle() . '</a>';
                echo '</li>';
            }
            $i++;
            $exclude_id .= ',' . $post->ID;
        };
        wp_reset_query();
    }
    if ($i < $limit) {
        $cats = '';
        foreach (get_the_category() as $cat) $cats .= $cat->cat_ID . ',';
        $args = array(
            'category__in'        => explode(',', $cats),
            'post__not_in'        => explode(',', $exclude_id),
            'ignore_sticky_posts' => 1,
            'orderby'             => 'comment_date',
            'posts_per_page'      => $limit - $i
        );

        query_posts($args);
        while (have_posts()) {
            the_post();
            if (_pz('post_related_type') == 'list') {

                $_thumb = zib_post_thumbnail('', 'fit-cover radius8');
                $author = get_the_author();
                $title = get_the_title() . '<span class="focus-color">' . get_the_subtitle(false) . '</span>';
                $author = '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . $author . '</a>';

                $lists_class = 'posts-mini';
                $title_l = '<div class="item-heading text-ellipsis-2">
                        <a' . _post_target_blank() . ' href="' . get_permalink() . '">' . $title . '</a>
                        </div>
                        ';
                $time_ago = zib_get_time_ago(get_the_time('U'));
                $meta_l = '<item class="meta-author">' . $author . '<span class="icon-spot">' . $time_ago . '</span></item>';

                echo '<div class="' . $lists_class . '">';
                echo '<a' . _post_target_blank() . ' class="item-thumbnail" href="' . get_permalink() . '">' . $_thumb . '</a>';
                echo '<div class="posts-mini-con">';
                echo $title_l;
                echo '<div class="item-meta muted-3-color">';
                echo $meta_l;
                echo '</div>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<li>';
                if ($thumb_s) echo '<a class="relative radius8" href="' . get_permalink() . '">' . zib_post_thumbnail() . '
				<span class="post-info">' . get_the_title() . get_the_subtitle() . '</span>
			</a>';
                if (!$thumb_s) echo '<a class="icon-circle" href="' . get_permalink() . '">' . get_the_title() . get_the_subtitle() . '</a>';
                echo '</li>';
            }
            $i++;
        };
        wp_reset_query();
    }

    if ($i == 0) {
        echo '<li>暂无相关文章</li>';
    }
    echo '</ul></div></div>';
}

// 获取页面链接
function zib_get_permalink($pid)
{
    if (!$pid) {
        return false;
    }
    if (get_permalink($pid)) {
        return get_permalink($pid);
    }
    return false;
}

// 获取文章标签
function zib_get_posts_tags($class = 'but', $before = '', $after = '', $count = 0)
{
    global $post;
    $tags = get_the_tags($post->ID);
    return zib_get_tags($tags, $class, $before, $after, $count);
}

//数组按一个值从新排序
function arraySort($arrays, $sort_key, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
{
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            $key_arrays[] = $array->$sort_key;
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

// 获取标签
function zib_get_tags($tags, $class = 'but', $before = '', $after = '', $count = 0, $ajax_replace = false)
{
    $html = '';
    if (!empty($tags[0])) {
        $ii = 0;
        $t =  arraySort($tags, 'count');
        foreach ($t as $tag_id) {
            $ii++;
            $url = get_tag_link($tag_id);
            $tag = get_tag($tag_id);
            $html .= '<a href="' . $url . '"' . ($ajax_replace ? ' ajax-replace="true"' : '') . ' title="查看此标签更多文章" class="' . $class . '">' . $before . $tag->name . $after . '</a>';
            if ($count && $count == $ii) {
                break;
            }
        }
    }
    return $html;
}

// 获取专题标签
function zib_get_topics_tags($pid='',$class = 'but', $before = '', $after = '', $count = 0)
{
    if(!$pid){
        $pid = get_queried_object_id();
    }
    $category = get_the_terms($pid,'topics');
    $cat = '';
    if (!empty($category[0])) {
        $ii = 0;
        foreach ($category as $category1) {
            $ii++;
            $cls = array('c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red');
            $cat .=  '<a class="' . $class . ' ' . $cls[$ii - 1] . '" title="查看此专题更多文章" href="' . get_category_link($category1->term_id) . '">' . $before . $category1->name . $after . '</a>';
            if ($count && $ii == $count) break;
        }
    }
    return $cat;
}
// 获取分类标签
function zib_get_cat_tags($class = 'but', $before = '', $after = '', $count = 0)
{
    $category = get_the_category();
    $cat = '';
    if (!empty($category[0])) {
        $ii = 0;
        foreach ($category as $category1) {
            $ii++;
            $cls = array('c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red');
            if ($ii == 0) continue;
            $cat .=  '<a class="' . $class . ' ' . $cls[$ii - 1] . '" title="查看更多分类文章" href="' . get_category_link($category1->term_id) . '">' . $before . $category1->cat_name . $after . '</a>';
            if ($count && $ii == $count) break;
        }
    }
    return $cat;
}

// 获取文章meta标签
function zib_get_posts_meta()
{
    $meta = '';
    if (comments_open()) {
        $meta .= '<item class="meta-comm"><a data-toggle="tooltip" title="去评论" href="' . get_comments_link() . '">' . zib_svg('comment') . get_comments_number('0', '1', '%') . '</a></item>';
    }
    $meta .= '<item class="meta-view">' . zib_svg('view') . get_post_view_count($before = '', $after = '') . '</item>';
    $meta .= '<item class="meta-like">' . zib_svg('like') . (zib_get_post_like('', '', '', true) ? zib_get_post_like('', '', '', true) : '0') . '</item>';
    return $meta;
}


function zib_rewards_modal($user_ID = '')
{
    $weixin = get_user_meta($user_ID, 'rewards_wechat_image_id', true);
    $alipay = get_user_meta($user_ID, 'rewards_alipay_image_id', true);
    $rewards_title = get_user_meta($user_ID, 'rewards_title', true);
    $rewards_title = $rewards_title ? $rewards_title : '文章很赞！支持以下吧';
    $s_src = get_stylesheet_directory_uri() . '/img/thumbnail-sm.svg';
    $weixin_img = '';
    $alipay_img = '';
    if ($weixin) {
        $weixin = wp_get_attachment_image_src($weixin, 'medium');
        $weixin_img = '<img class="lazyload fit-cover" src="' . $s_src . '" data-src="' . $weixin[0] . '">';
    }
    if ($alipay) {
        $alipay = wp_get_attachment_image_src($alipay, 'medium');
        $alipay_img = '<img class="lazyload fit-cover" src="' . $s_src . '" data-src="' . $alipay[0] . '">';
    }
    if (!$user_ID || !_pz('post_rewards_s') || (!$weixin && !$alipay)) return;
?>
<div class="modal fade" id="rewards-popover" tabindex="-1">
    <div class="modal-dialog rewards-popover" style="max-width: 400px; margin: 241px auto auto;" role="document">
        <div class="modal-content">
            <div class="box-body">
                <i class="fa fa-heart c-red em12 ml10"></i>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i data-svg="close" data-class="ic-close" data-viewbox="0 0 1024 1024"></i>
                </button>
            </div>
            <div class="box-body notop">
                <div class="box-body box-body notop focus-color"><b><?php echo $rewards_title; ?></b></div>
                <ul class="avatar-upload text-center theme-box list-inline rewards-box">
                    <?php if ($weixin) { ?>
                    <li>
                        <p class="muted-2-color">微信扫一扫</p>
                        <div class="rewards-img">
                            <?php echo $weixin_img ?>
                        </div>
                    </li>
                    <?php } ?>
                    <?php if ($alipay) { ?>
                    <li>
                        <p class="muted-2-color">支付宝扫一扫</p>
                        <div class="rewards-img">
                            <?php echo $alipay_img ?>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<? }


function zib_single_cat_search($cat_id)
{
    $cat_obj = get_category($cat_id);
?>
<div class="theme-box zib-widget dosc-search">
    <div class="title-h-left"><b>搜索<?php echo $cat_obj->cat_name ?></b></div>

    <?php
        $more_cats = array();
        $more_cats = get_term_children($cat_id, 'category');
        array_push( $more_cats,$cat_id);
        $args = array(
            'class' => '',
            'show_keywords' => false,
            'show_input_cat' => true,
            'show_more_cat' => true,
            'placeholder' => '搜索'.$cat_obj->cat_name,
            'in_cat' => $cat_id,
            'more_cats' => $more_cats,
        );
        zib_get_search($args);
        ?>
</div>
<?
}
function zib_get_search($args = array())
{
    $defaults = array(
        'class' => '',
        'show_keywords' => true,
        'keywords_title' => '热门搜索',
        'placeholder' => '开启精彩搜索',
        'show_input_cat' => true,
        'show_more_cat' => true,
        'in_cat' => '',
        'more_cats' => array(),
    );

    $args = wp_parse_args((array) $args, $defaults);

    $all_cat = zib_get_search_cat($args['more_cats'], 'text-ellipsis');
    $keywords = get_option('search_keywords');
    $keyword_link = '';
    $k_i = 1;
    if (!empty($keywords)) {
        arsort($keywords);
        foreach ($keywords as $key => $keyword) {
            $keyword_link .= '<a class="search_keywords muted-2-color ml10" href="' . esc_url(home_url('/')) . '?s=' . $key . '">' . $key . '</a>';
        }
    }
?>
<div class="search-input">
    <form method="get" class="relative line-form" action="<?php echo esc_url(home_url('/')); ?>">
        <?php if ($args['show_input_cat']) { ?>
        <div
            class="search-input-cat option-dropdown splitters-this-r<?php echo $args['show_more_cat']?' show-more-cat':''  ?>">
            <span class="text-ellipsis"
                name="cat"><?php echo $args['in_cat'] ? get_category($args['in_cat'])->cat_name : '选择分类' ?></span>
            <input type="hidden" name="cat" tabindex="1" value="<?php echo $args['in_cat'] ? $args['in_cat'] : '' ?>">
            <?php if ($args['show_more_cat']) { ?>
            <i class="fa fa-fw fa-sort ml6" aria-hidden="true"></i>
            <div class="option-dropdown-items main-shadow radius8 main-bg scroll-y mini-scrollbar">
                <?php echo $all_cat; ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <input type="text" name="s" class="line-form-input" tabindex="2"
            placeholder="<?php echo $args['placeholder'] ?>">
        <div class="abs-right muted-color">
            <button type="submit" tabindex="3" class="null"><?php echo zib_svg('search'); ?></button>
        </div>
        <i class="line-form-line"></i>
    </form>
    <?php if ($args['show_keywords']) { ?>
    <div class="search-input box-body">
        <p class="muted-color"><?php echo $args['keywords_title'] ?>：</p>
        <div class="text-center">
            <?php echo $keyword_link; ?>
        </div>
    </div>
    <?php } ?>
</div>
<? }


function zib_get_search_cat($cat_ids = array(), $link_class = '', $before = '', $after = '', $shou_count = false)
{
    $cats = array();
    if (!empty($cat_ids[0])) {
        foreach ($cat_ids as $cat_id) {
            $cats[] = get_category($cat_id);
        }
    } else {
        $cats = get_categories();
    }

    $links = '';

    foreach ($cats as $cat) {
        if(!empty($cat->cat_ID)){
        $links .= $before . '<a class="' . $link_class . '"  data-for="cat" data-value="' . $cat->cat_ID . '">' . $cat->cat_name . '</a>' . $after;
    }}
    return $links;
}

// 链接列表盒子
function zib_links_box($links = array(), $type = 'card', $echo = true)
{
    $html = '';
    $card = '';
    $image = '';
    $simple = '';
    $i = 0;
    foreach ($links as $link) {
        if (!empty($link['href']) && !empty($link['title'])) {
            $href = empty($link['href']) ? '' : $link['href'];

            if ($link['go_link']) $href = go_link( $href, true);

            $title = empty($link['title']) ? '' : $link['title'];
            $src = empty($link['src']) ? '' : $link['src'];
            $blank = empty($link['blank']) ? '' : ' target="_blank"';
            $dec = empty($link['desc']) ? '' : $link['desc'];
            $img = '<img class="lazyload avatar" src="' . get_stylesheet_directory_uri() . '/img/thumbnail-sm.svg" data-src="' . $src . '">';
            $data_dec = $dec ? ' title="' . $title . '" data-content="' . $dec . '" ' : ' data-content="' . $title . '"';
            $card .= '<div class="author-minicard links-card radius8">
                <ul class="list-inline">
                    <li><a ' . $blank . ' class="avatar-img link-img" href="' . $href . '">' . $img . '</a>
                    </li>
                    <li>
                        <dl>
                            <dt><a' . $blank . ' href="' . $href . '">' . $title . '</a></dt>
                            <dd class="avatar-dest em09 muted-3-color text-ellipsis">' . $dec . '</dd>
                        </dl>
                    </li>
                </ul>
            </div>';
            $image .= '<a ' . $blank . ' class="avatar-img link-only-img"  data-trigger="hover" data-toggle="popover" data-placement="top"' . $data_dec . ' href="' . $href . '">' . $img . '</a>';
            $sc = $i == 0 ? '' : 'icon-spot';
            $simple .= '<a ' . $blank . ' class="' . $sc . '" data-trigger="hover" data-toggle="popover" data-placement="top"' . $data_dec . ' href="' . $href . '">' . $title . '</a>';
            $i++;
        }
    }
    if ($type == 'card') {
        $html = $card;
    }
    if ($type == 'image') {
        $html = $image;
    }
    if ($type == 'simple') {
        $html = $simple;
    }

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}


// 公告栏
function zib_notice($args = array(), $echo = true)
{
    $defaults = array(
        'class' => 'c-blue',
        'interval' => 5000,
        'notice' => array(),
    );

    $args = wp_parse_args((array) $args, $defaults);

    $interval = ' data-interval="' . $args['interval'] . '"';
    $i = 0;
    $slides = '';
    foreach ($args['notice'] as $notice) {
        if (!empty($notice['title'])) {
            $href = empty($notice['href']) ? '' : $notice['href'];
            $title = empty($notice['title']) ? '' : $notice['title'];
            $icon = empty($notice['icon']) ? '' : '<div class="relative bulletin-icon mr6"><i class="abs-center fa ' . $notice['icon'] . '"></i></div>';
            $blank = empty($notice['blank']) ? '' : ' target="_blank"';
            $s_class = ' notice-slide';
            $slides .= '<div class="swiper-slide' . ' ' . $s_class . '">
            <a class="text-ellipsis"' . $blank . ($href ? ' href="' . $href . '"' : '') . '>'
                . $icon . $title . '</a>
            </div>';
            $i++;
        }
    }

    $html = '<div class="new-swiper" ' . $interval . ' data-direction="vertical" data-loop="true">
            <div class="swiper-wrapper">' . $slides . '</div>
            </div>';

    if ($echo) {
        echo '<div class="swiper-bulletin ' . $args['class'] . '">' . $html . '</div>';
    } else {
        return $html;
    }
}

// 弹出通知
function zib_system_notice()
{
    if (!_pz('system_notice_s', true)) return;
    $args = array(
        'id' => 'modal-system-notice',
        'class' => _pz('system_notice_size', 'modal-sm'),
        'style' => '',
        'title' => _pz('system_notice_title'),
        'content' => _pz('system_notice_content'),
        'button1_title' => _pz('system_notice_b1_t'),
        'button1_class' => 'but ' . _pz('system_notice_b1_c', 'c-yellow') . (_pz('system_notice_radius') ? ' radius' : ''),
        'button1_href' => _pz('system_notice_b1_h'),
        'button2_title' => _pz('system_notice_b2_t'),
        'button2_class' => 'but ' . _pz('system_notice_b2_c', 'c-blue') . (_pz('system_notice_radius') ? ' radius' : ''),
        'button2_href' => _pz('system_notice_b2_h'),
    );
    if (!isset($_COOKIE["showed_system_notice"])) {
        zib_modal($args);
    }
}

//模态框构建
function zib_modal($args = array())
{
    $defaults = array(
        'id' => '',
        'class' => '',
        'style' => '',
        'title' => '',
        'content' => '',
        'button1_title' => '',
        'button1_class' => '',
        'button1_href' => '',
        'button2_title' => '',
        'button2_class' => '',
        'button2_href' => '',
    );

    $args = wp_parse_args((array) $args, $defaults);
    $button1 = '';
    $button2 = '';
    if (!$args['title'] && !$args['content']) return;
    if ($args['button1_title']) {
        $button1 = '<a type="button" class="ml10 ' . $args['button1_class'] . '" href="' . $args['button1_href'] . '">' . $args['button1_title'] . '</a>';
    }
    if ($args['button2_title']) {
        $button2 = '<a type="button" class="ml10 ' . $args['button2_class'] . '" href="' . $args['button2_href'] . '">' . $args['button2_title'] . '</a>';
    }
?>
<div class="modal fade" id="<?php echo $args['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog <?php echo $args['class'] ?>" <?php echo 'style="' . $args['style'] . '"' ?>
        role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button class="close" data-dismiss="modal">
                    <i data-svg="close" data-class="ic-close" data-viewbox="0 0 1024 1024"></i>
                </button>
                <h4><?php echo $args['title'] ?></h4>
                <div><?php echo $args['content'] ?></div>
            </div>
            <?php if ($button1 || $button2) {
                    echo '<div class="box-body notop text-right">' . $button1 . $button2 . '</div>';
                } ?>
        </div>
    </div>
</div>
<?
}

function zib_social_login($echo = true)
{
    $buttons = '';
    if (_pz('social') && function_exists('xh_social_loginbar')) {
        $buttons = xh_social_loginbar('', false);
    } else {
        $b_c = _pz('oauth_button_lg') ? ' button-lg' :'';
        if (_pz('oauth_qq_s')) {
            $buttons .= '<a title="QQ登录" href="' . esc_url(home_url('/oauth/qq/')) . '" class="social-login-item qq'.$b_c.'"><i class="fa fa-qq"></i>'.($b_c ? 'QQ登录':'').'</a>';
        }
        if (_pz('oauth_weixin_s')) {
            $buttons .= '<a title="微信登录" href="' . esc_url(home_url('/oauth/weixin/')) . '" class="social-login-item weixin'.$b_c.'"><i class="fa fa-weixin"></i>'.($b_c ? '微信登录':'').'</a>';
        }
        if (_pz('oauth_weibo_s')) {
            $buttons .= '<a title="微博登录" href="' . esc_url(home_url('/oauth/weibo/')) . '" class="social-login-item weibo'.$b_c.'"><i class="fa fa-weibo"></i>'.($b_c ? '微博登录':'').'</a>';
        }
        if (_pz('oauth_github_s')) {
            $buttons .= '<a title="GitHub登录" href="' . esc_url(home_url('/oauth/github/')) . '" class="social-login-item github'.$b_c.'"><i class="fa fa-github"></i>'.($b_c ? 'GitHub登录':'').'</a>';
        }
    }
    if ($buttons && $echo) {
        echo '<p class="social-separator separator muted-3-color em09">社交帐号登录</p>';
        echo '<div class="social_loginbar">';
        echo $buttons;
        echo '</div>';
    } else {
        return $buttons;
    }
}


/*
//添加自定义文章类型代码
new Ws_Post_Type('video','video_list','教程');
Class Ws_Post_Type
{
       private  $type,$type_category,$type_name;
       function __construct($type,$type_category,$type_name)
       {
              $this->type = $type;
              $this->type_category  = $type_category;
              $this->type_name  = $type_name;
              //添加自定义文章类型
              add_action('init', array($this,'ws_add_type'));
              //为商品类自定义类型增加分类功能
              add_action('init',array($this,'ws_add_categroy'), 0);
              //修改自定义类型文章的地址
              add_filter('post_type_link',array($this,'ws_add_link'), 1, 3);
              //让自定义的url支持伪静态
              add_action('init', array($this,'ws_rewrites_init'));
       }
       //新增自定义文章类型
       public function ws_add_type()
       {
              register_post_type($this->type,
                     array(
                            'labels' => array(
                                   'name' =>$this->type_name,
                                   'title' =>$this->type_name,
                                   'singular_name' => '所有'.$this->type_name,
                                   'add_new' => '添加'.$this->type_name,
                                   'add_new_item' => '添加新'.$this->type_name,
                                   'edit' => '编辑',
                                   'edit_item' => '编辑'.$this->type_name,
                                   'new_item' => '新'.$this->type_name,
                                   'view' => '查看'.$this->type_name,
                                   'view_item' => '查看'.$this->type_name,
                                   'search_items' => '搜索'.$this->type_name,
                                   'not_found' => '没有找到相关'.$this->type_name,
                                   'not_found_in_trash' => '没有'.$this->type_name.'评论',
                                   'parent' =>$this->type_name.'评论',
                            ),
                            'exclude_from_search' => false,
                            'public' => true,
                            'menu_position' => 6,
                            'supports' => array('title', 'editor', 'comments', 'custom-fields', 'thumbnail', 'excerpt', 'page-attributes'),
                            'taxonomies' => array(''),
                            'has_archive' => true,
                            'taxonomies' => array('post_tag'),
                     )
              );
       }
       function ws_add_categroy()
       {
              register_taxonomy(
                     $this->type_category,
                     $this->type,
                     array(
                            'labels' => array(
                                   'name' => $this->type_name.'分类',
                                   'add_new_item' => '添加分类',
                                   'new_item_name' => "新'.$this->type_name.'分类"
                            ),
                            'show_ui' => true,
                            'show_tagcloud' => true,
                            'hierarchical' => true,
                     )
              );
       }
       function ws_add_link($link, $post = 0)
       {
              global $post;
              if ($post->post_type == $this->type ) {
                     return home_url($this->type . '/' . $post->ID . '.html');
              }else{
                     return $link;
              }
       }
       function ws_rewrites_init()
       {
              add_rewrite_rule(
                     $this->type . '/([0-9]+)?.html$',
                     'index.php?post_type=' . $this->type . '&p=$matches[1]',
                     'top');
              add_rewrite_rule(
                     $this->type . '/([0-9]+)?.html/comment-page-([0-9]{1,})$',
                     'index.php?post_type=' . $this->type . '&p=$matches[1]&cpage=$matches[2]',
                     'top');
       }
}
*/