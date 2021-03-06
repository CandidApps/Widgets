<?php
/**
 * Advance Post Listing
 * This all in one post listing widget has all the options you can think list your posts or custom post types in sidebar. Either you are planing to show popular or recent or random posts, this widget can fulfil your desire. You can toggle the display of post title, post content, post thumbnail, number of posts and post author line. You can even limit the post content by number of characters and selection of posts from all categories or some of the selected categories.
 *
 * Author: WPDevSnippets.com
 * @package WordPress
 *
 */
add_action('widgets_init', create_function('', "register_widget('AdvancedPostList');"));

class AdvancedPostList extends WP_Widget {

    var $defaults = null;

    function AdvancedPostList() {
        $this->defaults = array(
            'title' => '',
            'post_type' => 'post',
            'posts_cat' => '',
            'posts_cat_multi' => '',
            'posts_num' => 3,
            'orderby' => '',
            'order' => '',
            'show_image' => 0,
            'image_alignment' => '',
            'image_size' => '',
            'show_title' => 0,
            'show_byline' => 0,
            'post_meta' => '[post_date] ' . __('By', 'wpds') . ' [post_author] [post_comments] comments',
            'show_content' => 0,
            'content_type' => 'excerpt',
            'content_limit' => '',
            'read_more_text' => __('[Read More...]', 'wpds'));

        $widget_ops = array('classname' => 'advanced-post-listing', 'description' => __('Show posts with thumbnails', 'wpds'));
        $control_ops = array('width' => 350, 'id_base' => 'advanced-post-listing');
        $this->WP_Widget('advanced-post-listing', __('Advanced Post Listing', 'wpds'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);

        // defaults
        $instance = wp_parse_args((array) $instance, $this->defaults);

        echo $before_widget;

        if (!empty($instance['title']))
            echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
        $post_cat = !empty($instance['posts_cat_multi']) ? $instance['posts_cat_multi'] : $instance['posts_cat'];

        $posts = new WP_Query(array('post_type' => $instance['post_type'], 'cat' => $post_cat, 'showposts' => $instance['posts_num'], 'orderby' => $instance['orderby'], 'order' => $instance['order']));
        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();

                echo '<div ';
                post_class();
                echo '>';

                if (!empty($instance['show_image'])) {
                    if (has_post_thumbnail())
                        printf('<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), esc_attr($instance['image_alignment']), get_the_post_thumbnail(get_the_ID(), $instance['image_size'], array('class' => 'post_thumbnail')));
                    else
                        printf('<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), esc_attr($instance['image_alignment']), '<img src="' . CHILD_URL . '/images/default_post_icon.png" class="post_thumbnail" />');
                }

                if (!empty($instance['show_title']))
                    printf($post_type . '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute('echo=0'), the_title_attribute('echo=0'));

                if (!empty($instance['show_byline']) && !empty($instance['post_meta'])) {
                    $post_meta = str_replace('[post_date]', get_the_date(), esc_html($instance['post_meta']));
                    $post_meta = str_replace('[post_author]', '<a href="' . get_author_link(false, get_the_author_ID()) . '">' . get_the_author_nickname() . '</a>', $post_meta);
                    $post_meta = str_replace('[post_comments]', get_comments_number(), $post_meta);

                    printf('<p class="byline post-info">%s</p>', $post_meta);
                }
                if (!empty($instance['show_content']) && !empty($instance['content_type'])) {
                    $content = '';
                    if ($instance['content_type'] == 'excerpt')
                        $content = strip_shortcodes(strip_tags(get_the_excerpt()));
                    else {
                        $content = strip_shortcodes(strip_tags(get_the_content()));
                        the_content_limit((int) $instance['content_limit']);
                    }

                    if (!empty($instance['content_limit']) && is_numeric($instance['content_limit'])) {
                        if (strlen($content) > $instance['content_limit']) {
                            echo substr($content, 0, $instance['content_limit']) . '... ';
                            echo '<a href="' . get_permalink() . '" title="' . get_the_title() . '" >' . esc_html($instance['read_more_text']) . '</a>';
                        } else {
                            echo $content;
                        }
                    }
                }
                echo '</div>';
            }
        }

        echo $after_widget;
        wp_reset_query();
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function form($instance) {

        $instance = wp_parse_args((array) $instance, $this->defaults);
        ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" style="width:99%;" /></p>

        <p><label for="<?php echo $this->get_field_id('posts_cat'); ?>">Category: <small>(for multiple specify comma separated id or slug)</small></label><br/>
            <?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat'), 'selected' => $instance['posts_cat'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("Select Category", 'wpds'), 'hide_empty' => '0')); ?> Or multiple 
            <input type="text" id="<?php echo $this->get_field_id('posts_cat_multi'); ?>" name="<?php echo $this->get_field_name('posts_cat_multi'); ?>" value="<?php echo esc_attr($instance['posts_cat_multi']); ?>" size="20" /> </p>

        <p><label for="<?php echo $this->get_field_id('post_type'); ?>">Post Type:</label><br/>
            <?php $post_types = get_post_types(array('public' => true)); ?>
            <select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                <?php
                foreach ($post_types as $post_type)
                    echo '<option value="' . $post_type . '" ' . selected($post_type, $instance['post_type'], FALSE) . '>' . ucfirst($post_type) . '</option>';
                ?>
            </select> Limit to 
            <input type="text" id="<?php echo $this->get_field_id('posts_num'); ?>" name="<?php echo $this->get_field_name('posts_num'); ?>" value="<?php echo esc_attr($instance['posts_num']); ?>" size="2" /> posts</p>

        <p><label for="<?php echo $this->get_field_id('orderby'); ?>">Order By:</label><br/>
            <select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
                <option value="date" <?php selected('date', $instance['orderby']); ?>>Date</option>
                <option value="title" <?php selected('title', $instance['orderby']); ?>>Title</option>
                <option value="parent" <?php selected('parent', $instance['orderby']); ?>>Parent</option>
                <option value="ID" <?php selected('ID', $instance['orderby']); ?>>ID</option>
                <option value="comment_count" <?php selected('comment_count', $instance['orderby']); ?>>Comments</option>
                <option value="rand" <?php selected('rand', $instance['orderby']); ?>>Random</option>
            </select>
            <select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
                <option value="DESC" <?php selected('DESC', $instance['order']); ?>>Descending</option>
                <option value="ASC" <?php selected('ASC', $instance['order']); ?>>Ascending</option>
            </select></p>

        <p><input id="<?php echo $this->get_field_id('show_title'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php checked(1, $instance['show_title']); ?>/> <label for="<?php echo $this->get_field_id('show_title'); ?>">Show Post Title</label><br/>
            <input id="<?php echo $this->get_field_id('show_image'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_image'); ?>" value="1" <?php checked(1, $instance['show_image']); ?>/> <label for="<?php echo $this->get_field_id('show_image'); ?>">Show Image</label><br/>
            <?php $sizes = get_intermediate_image_sizes(); ?>
            <select id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>">
                <?php
                foreach ((array) $sizes as $name)
                    echo '<option value="' . esc_attr($name) . '" ' . selected($name, $instance['image_size'], FALSE) . '>' . esc_html($name) . '</option>';
                ?>
            </select>  
            <select id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('image_alignment'); ?>">
                <option value="">- None -</option>
                <option value="alignleft" <?php selected('alignleft', $instance['image_alignment']); ?>>Align Left</option>
                <option value="alignright" <?php selected('alignright', $instance['image_alignment']); ?>>Align Right</option>
            </select></p>

        <p><input id="<?php echo $this->get_field_id('show_byline'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_byline'); ?>" value="1" <?php checked(1, $instance['show_byline']); ?>/> <label for="<?php echo $this->get_field_id('show_byline'); ?>">Show Post Info</label>
            <input type="text" id="<?php echo $this->get_field_id('post_meta'); ?>" name="<?php echo $this->get_field_name('post_meta'); ?>" value="<?php echo esc_attr($instance['post_meta']); ?>" style="width: 99%;" /></p>

        <p><input id="<?php echo $this->get_field_id('show_content'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_content'); ?>" value="1" <?php checked(1, $instance['show_content']); ?>/><label for="<?php echo $this->get_field_id('show_content'); ?>">Show Content:</label><br/>
            <select id="<?php echo $this->get_field_id('content_type'); ?>" name="<?php echo $this->get_field_name('content_type'); ?>">
                <option value="content" <?php selected('content', $instance['content_type']); ?>>Show Content</option>
                <option value="excerpt" <?php selected('excerpt', $instance['content_type']); ?>>Show Excerpt</option>
            </select> Limit to <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr(intval($instance['content_limit'])); ?>" size="3" /> characters</p>

        <p><label for="<?php echo $this->get_field_id('read_more_text'); ?>">More Text (if applicable):</label><br/>
            <input type="text" id="<?php echo $this->get_field_id('read_more_text'); ?>" name="<?php echo $this->get_field_name('read_more_text'); ?>" value="<?php echo esc_attr($instance['read_more_text']); ?>" /></p>

        <?php
    }

}
