<?php
/*
	Plugin Name: VideofyMe
	Description: Gives the opportunity to embed a VideofyMe video. And add a VideofyMe sidebar widget.
	Author: VideofyMe
	Version: 2.0.8
	Author URL: http://videofy.me/
	Plugin URL: http://videofy.me/wordpress/
	
	[videofyme id="2222" width="480" height ="360" class="aligncenter"]
 */

if ( class_exists('WP_Widget') )
{ 
class VideofyMeSidebarWidget extends WP_Widget
{
    /** constructor */
    function VideofyMeSidebarWidget() {
        parent::WP_Widget(false, $name = 'VideofyMe Sidebar Widget');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance)
    {
        extract( $args );
        $identifier = $instance['identifier'];
        $width = $instance['width'];
        $height = $instance['height'];
        $type = isset($instance['type']) ? $instance['type'] : 'sidebar';
        ?>
              <?php echo $before_widget; ?>
                <iframe style="display:block" width="<?php echo ($width ? $width : 180); ?>px" height="<?php echo ($height ? $height : 300); ?>px" frameborder="0" scrolling="no" src="http://widget.videofy.me/<?php if ( $identifier ) { echo $identifier; } ?>/<?php echo $type; ?>"> </iframe>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance)
    {
	    $instance = $old_instance;
	    preg_match('/(.+me\/)?(\w*)\/?/', $new_instance['identifier'], $matches);
	    $instance['identifier'] = $matches[2];
	    $instance['width'] = strip_tags($new_instance['width']);
	    $instance['height'] = strip_tags($new_instance['height']);
	    $instance['type'] = strip_tags($new_instance['type']);
	    
      return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance)
    {
      if ( ! $instance )
      {
        $instance = array(
          "identifier" => null,
          'width' => null,
          'height' => null,
          'type' => 'sidebar'
        );
      }
      
      $identifier = esc_attr($instance['identifier']);
      $width = esc_attr($instance['width']);
      $height = esc_attr($instance['height']);
      $type = esc_attr(isset($instance['type']) ? $instance['type'] : 'sidebar');
      
      $types = array(
        'sidebar' => 'Sidebar',
        'topbar' => 'Topbar',
        'latest' => 'Latest video'
      );
      
      $ds = array(
        'sidebar' => array(180, 300),
        'topbar' => array(700, 120),
        'latest' => array(500, 320)
      );
      
      ?><div id="widget-type" style="width:100%;padding-bottom:10px">
          <p>Widget type:</p>
          <?php foreach ( $types as $key => $name ): ?>
          <div style="width:30%;float:left;text-align:center">
            <label>
              <img src="<?php echo videofyme_plugin_url(); ?>/img/<?php _e($key) ;?>.png" alt="" />
              <br />
              <input onchange="document.getElementById('<?php _e($this->get_field_id('width')); ?>').value=<?php _e($ds[$key][0]); ?>;document.getElementById('<?php _e($this->get_field_id('height')); ?>').value=<?php _e($ds[$key][1]); ?>;" type="radio" <?php if($type == $key) echo ('checked="checked"'); ?> value="<?php _e($key); ?>" name="<?php _e($this->get_field_name('type')); ?>" /> <?php _e($name); ?>
            </label>
          </div>
          <?php endforeach; ?>  
          <div style="clear:both;"></div>
        </div>
        
         <p>
          <label for="<?php echo $this->get_field_id('identifier'); ?>"><?php _e('Your VideofyMe Address:'); ?></label> 
          <span style="display:block;margin:0;width:95%;border:1px solid #e1e1e1; background: #fff;padding: 3px;">
            http://videofy.me/<input style="width:100px;border:none;padding:0;margin:0;" id="<?php echo $this->get_field_id('identifier'); ?>" name="<?php echo $this->get_field_name('identifier'); ?>" type="text" value="<?php echo $identifier; ?>" />
          </span>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Size:'); ?></label> 
          <input size="4" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo ($width ? $width : 180); ?>" />
          <label for="<?php echo $this->get_field_id('height'); ?>"> x </label> 
          <input size="4" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo ($height ? $height : 300); ?>" />
        </p>
      <?php 
    }
}

# Add widgets
add_action('widgets_init', create_function('', 'return register_widget("VideofyMeSidebarWidget");'));

} # class_exists WP_Widget

function videofyme_shortcode($attributes, $content = null)
{
    $id = '';
    $width = '';
    $height = '';
    extract(shortcode_atts(array(
            'id' => '', 
            'width' => '480', 
            'height' => '360',
			'me' => '',
            'class' => ''), $attributes));
	
    if (empty($id) || !is_numeric($id))
        return $content;

    if ($height > 0)
    {
        if (! $width > 0)
        {
            $width = intval($height / 0.75);
        }
    }
		else
    {
        if ($width > 0)
        {
            $height = intval($width * 0.75);
        }
    }

	if ( $me )
		$id .= '?me=' . $me;

    $sPlayerTemplate = '<iframe src="http://p.videofy.me/v/%VIDEOID%" width="%WIDTH%" height="%HEIGHT%" frameborder="0"%CLASS%></iframe>';
    $sPlayerTemplate = str_replace('%WIDTH%', $width, $sPlayerTemplate);
    $sPlayerTemplate = str_replace('%HEIGHT%', $height, $sPlayerTemplate);
    $sPlayerTemplate = str_replace('%VIDEOID%', $id, $sPlayerTemplate);
    $sPlayerTemplate = str_replace('%CLASS%', ' class="' . htmlentities($class) . '"', $sPlayerTemplate);
    return $sPlayerTemplate;
}

# Support older than 3.0.0
$pieces = explode('.', get_bloginfo('version'));
if ((int)$pieces[0] < 3) {
  function get_upload_iframe_src($type) {
      global $post_ID, $temp_ID;
      $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
      $upload_iframe_src = add_query_arg('post_id', $uploading_iframe_ID, 'media-upload.php');

      if ( 'media' != $type )
          $upload_iframe_src = add_query_arg('type', $type, $upload_iframe_src);
      $upload_iframe_src = apply_filters($type . '_upload_iframe_src', $upload_iframe_src);

      return add_query_arg('TB_iframe', true, $upload_iframe_src);
  }
}

# Hooked on upload
function media_upload_videofyme() {
	$errors = array();
  $id = 0;

	if ( !empty($_POST) ) {
		preg_match("/[vid=|.*\/]([0-9]+)$/", $_POST['url'], $matches);
		
	  if ( !isset($matches[1]) || !is_numeric($_POST['width']) || !is_numeric($_POST['height']) ) {
      echo '<div class="error"><p>We could not parse the url you submitted.</p></div';
  		return wp_iframe( 'videofyme_popup', 'videofyme', $errors, $id );
		}
		return media_send_to_editor('[videofyme id="' . $matches[1] . '" width="' . $_POST['width'] . '" height="' . $_POST['height'] . '"]');
	}

	return wp_iframe( 'videofyme_popup', 'videofyme', $errors, $id );
}

function videofyme_plugin_url()
{
    return WP_PLUGIN_URL . '/videofyme/';
}

function videofyme_popup()
{
	include('popup.php');
}

if ( ! function_exists('esc_url') )
{
  function esc_url($url)
  {
    return clean_url($url);
  }
}

function videofyme_media_button()
{
    print "<a href='" . esc_url( get_upload_iframe_src('videofyme') ) . "' id='add_videofyme' class='thickbox' title='Add VideofyMe Video'><img src='" . esc_url( content_url('/plugins/videofyme/videofyme.gif') ) . "' alt='Add VideofyMe Video' /></a>";
}

function videofyme_init()
{
    if ( get_user_option('rich_editing') == 'true' )
    {
        add_filter('mce_external_plugins', 'videofyme_tinymce');
        add_filter('mce_css', 'videofyme_tinymce_css' );
    }
}

function videofyme_tinymce($plugin_array)
{
    $plugin_array['videofyme'] = videofyme_plugin_url() . 'tinymce.videofyme.js';
    return $plugin_array;
}

function videofyme_tinymce_css($wp)
{
    $wp .= ',' . videofyme_plugin_url() . 'videofyme.css';
    return $wp;
}

function videofyme_exists()
{
  return true;
}

function videofyme_xmlrpc_check( $methods ) {
    $methods['videofyme.exists'] = 'videofyme_exists';
    return $methods;   
}

# Add a button above the wysiwyg editor
add_action('media_buttons', 'videofyme_media_button', 22);

# Add [videofyme id="22"]
add_shortcode('videofyme', 'videofyme_shortcode');

# On post in videofyme form
add_action('media_upload_videofyme', 'media_upload_videofyme');

# Adds tinyMCE stuff
add_action('init', 'videofyme_init');

# Adds xmlrpc api for checking if plugin exists
add_filter('xmlrpc_methods', 'videofyme_xmlrpc_check');