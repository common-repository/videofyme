<?php

  $width = isset($_GET['width']) ? intval($_GET['width']) : false;
  $height = isset($_GET['height']) ? intval($_GET['height']) : false;
  $vid = isset($_GET['video_id']) ? intval($_GET['video_id']) : false;
  $me = isset($_GET['me']) ? intval($_GET['me']) : false;
  
  $data = array();
  
  if ( $width )
    $data['width'] = sprintf('%d', $width);
  
  if ( $height )
    $data['height'] = sprintf('%d', $height);
  
  if ( $vid )
    $data['video_id'] = sprintf('%d', $vid);

  if ( $me )
    $data['me'] = sprintf('%d', $me);

?><div id="videofyme-embed-container" style="background: url(<?php echo videofyme_plugin_url(); ?>img/load.gif) center center no-repeat; height: 100%"></div>

<script type="text/javascript" src="http://sdk.cdn.videofy.me/javascript.js"></script>
<script type="text/javascript">
  var embed;
  
  function send_to_wordpress(code) {
      var w = window.top;
      
      try {
          var mce = w.tinymce;
          var editor = mce.EditorManager.activeEditor;
      
          var el = editor.selection.getNode();
          
          if ( el.nodeName == 'IMG' && editor.plugins.videofyme.updateNode(el, code) )
              return;
      
          if (mce.isIE && editor.windowManager.insertimagebookmark) {
                editor.selection.moveToBookmark(editor.windowManager.insertimagebookmark)
            }
            
          code = editor.plugins.videofyme._do_gallery(code);
          editor.execCommand('mceInsertContent', false, code);
      } catch (e) {
          w.send_to_editor(code);
          return;
      }
  }
  
  window.onload = function() {
    embed = new VideofyMe.Embed({
      mode: 'container',
      container: document.getElementById('videofyme-embed-container'),
      width: '100%',
      height: window.top.jQuery("#TB_ajaxContent").height(),
      callback: function(info) {
        var code;
          
        var attrs = ['id="' + info.id + '"'];
        
        if ( info.width )
          attrs.push('width="' + info.width + '"');
        
        if ( info.height )
          attrs.push('height="' + info.height + '"');
        
        if ( info.me > 1 )
          attrs.push('me="' + info.me + '"');
        
        code = '[videofyme ' + attrs.join(" ") + ']';
        
        send_to_wordpress(code);
        window.top.tb_remove();
      },
      params: {
        <?php if ( isset($data['width'])): ?>width: <?php echo $data['width']; ?>,<?php endif; ?>
        <?php if ( isset($data['height'])): ?>height: <?php echo $data['height']; ?>,<?php endif; ?>
        <?php if ( isset($data['video_id'])): ?>video_id: <?php echo $data['video_id']; ?>,<?php endif; ?>
        <?php if ( isset($data['me'])): ?>me: <?php echo $data['me']; ?>,<?php endif; ?>
        zA: 'b'
      }
    });
    
    function resizeEmbed() {
      if ( ! window.parent.jQuery ) {
        clearInterval(resizeInterval);
        return;
      }
      embed.iframe.style.height = window.parent.jQuery("#TB_iframeContent").height() + 'px';
    }
    
    var resizeInterval = setInterval(resizeEmbed, 2500);
    resizeEmbed();
  }
</script>