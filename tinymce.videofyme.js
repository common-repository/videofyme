(function() {
  
  tinymce.create('tinymce.plugins.videofyme', {

    init: function(ed, url) {
      var t = this;
      
      t.url = url;
      t._createButtons();

      // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
      ed.addCommand('VideofyMe', function() {
        var el = ed.selection.getNode(), post_id, vp = tinymce.DOM.getViewPort(),
          H = vp.h - 80, W = 750;

        if ( el.nodeName != 'IMG' ) return;
        if ( ed.dom.getAttrib(el, 'class').indexOf('videofymeVid') == -1 )  return;

        post_id = tinymce.DOM.get('post_ID').value;
        var vid = ed.dom.getAttrib(el, 'alt');
        var width = ed.dom.getAttrib(el, 'width');
        var height = ed.dom.getAttrib(el, 'height');
        var me = ed.dom.getAttrib(el, 'data-me') || 0;
        var url = tinymce.documentBaseURL + '/media-upload.php?post_id='+post_id+'&tab=videofyme&width='+width+'&height='+height+'&video_id=' + vid + '&me=' + me + '&TB_iframe=true';
        
        tb_show('', url);

        tinymce.DOM.setStyle( ['TB_overlay','TB_window','TB_load'], 'z-index', '999999' );
      });

      ed.onMouseDown.add(function(ed, e) {
        if ( e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'videofymeVid') )
          t._showButtons(e.target, 'wp_gallerybtns');
      });

      ed.onBeforeSetContent.add(function(ed, o) {
        o.content = t._do_gallery(o.content);
      });

      ed.onPostProcess.add(function(ed, o) {
        if (o.get)
          o.content = t._get_gallery(o.content);
      });
    },
    
    _get_embed_attributes: function(str) {
        var keys = str.replace('[', '').replace(']', '').replace(/ +/g, ' ').replace(/ *= */g, '=').split(' ');
        var ret = {};
        for ( var i = 0, j = keys.length; i < j; i++ ) {
          var pieces = keys[i].split('=');
          if ( ! pieces[1] ) continue;

          var value = pieces[1].replace(/"/g, '');
          ret[pieces[0]] = value;
        }
        return ret;
    },

    // Converting the shortcode to html for a visual editor 
    _do_gallery : function(co) {
      var t = this;
      
      return co.replace(/\[videofyme([^\]]*)\]/g, function(a,b){
        var attrs = t._get_embed_attributes(a);
        
        var html = '<img ';
        if ( attrs.id )
          html += ' alt="' + attrs.id + '" ';
        
        var styles = [];
        if ( attrs.width )
          styles[styles.length] = 'width: ' + attrs.width + 'px';
        
        if ( attrs.height )
          styles[styles.length] = 'height: ' + attrs.height + 'px';
        
        if ( attrs.width )
          html += ' width="' + attrs.width + '" ';
        if ( attrs.height )
          html += ' height="' + attrs.height + '" ';
        
        if ( attrs.me > 1 )
          html += ' data-me="' + attrs.me + '"';
        
        if ( ! attrs['class'] )
          attrs['class'] = '';
          
        
        html += ' style="' + styles.join(';') + '" ';
        html += ' src="'+t.url+'/img/t.gif" class="videofymeVid mceItem ' + attrs['class'] + '" title="VideofyMe" />';
        
        return html;
      });
    },

    // Converting the html to shortcode for non-visual editor
    _get_gallery: function(co) {
      function getAttr(s, n) {
        n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
        return n ? tinymce.DOM.decode(n[1]) : '';
      };

      return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a, im) {
        var cls = getAttr(im, 'class');
        var width = getAttr(im, 'width');
        var height = getAttr(im, 'height');
        var me = getAttr(im, 'data-me');
        var vid  = getAttr(im, 'alt');
        
        if ( cls.indexOf('videofymeVid') != -1 ) {
          var code = '[videofyme';
          if ( vid )
            code += ' id="' + vid + '"';
          if ( width )
            code += ' width="' + width + '"';
          if ( height )
            code += ' height="' + height + '"';
          cls = tinymce.trim(cls.replace('videofymeVid', '').replace('mceItem', ''));
          if ( cls )
            code += ' class="' + cls + '"';
          if ( me && me > 1 )
            code += ' me="' + me + '"';
          code += ']';
          
          return '<p>' + code + '</p>';
        }

        return a;
      });
    },

    _createButtons: function() {
      var t = this, ed = tinyMCE.activeEditor, DOM = tinymce.DOM, editButton, dellButton;

      DOM.remove('wp_gallerybtns');

      DOM.add(document.body, 'div', {
        id : 'wp_gallerybtns',
        style : 'display:none;'
      });

      editButton = DOM.add('wp_gallerybtns', 'img', {
        src : t.url+'/img/edit.png',
        id : 'wp_editgallery',
        width : '24',
        height : '24',
        title : ed.getLang('wordpress.editgallery')
      });

      tinymce.dom.Event.add(editButton, 'mousedown', function(e) {
        var ed = tinyMCE.activeEditor;
        ed.windowManager.bookmark = ed.selection.getBookmark('simple');
        ed.execCommand("VideofyMe");
      });

      dellButton = DOM.add('wp_gallerybtns', 'img', {
        src: t.url + '/img/delete.png',
        id: 'wp_delgallery',
        width: '24',
        height: '24',
        title: ''
      });

      tinymce.dom.Event.add(dellButton, 'mousedown', function(e) {
        var ed = tinyMCE.activeEditor, el = ed.selection.getNode();

        if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'videofymeVid') ) {
          ed.dom.remove(el);
          t._hideButtons('wp_gallerybtns');
          ed.execCommand('mceRepaint');
          return false;
        }
      });
    },

    _showButtons: function(n, id) {
      var ed = tinyMCE.activeEditor, p1, p2, vp, DOM = tinymce.DOM, X, Y;

      vp = ed.dom.getViewPort(ed.getWin());
      p1 = DOM.getPos(ed.getContentAreaContainer());
      p2 = ed.dom.getPos(n);

      X = Math.max(p2.x - vp.x, 0) + p1.x;
      Y = Math.max(p2.y - vp.y, 0) + p1.y;

      DOM.setStyles(id, {
        'position': 'absolute',
        'top' : Y+5+'px',
        'left' : X+5+'px',
        'display' : 'block'
      });

      if ( this.mceTout )
        clearTimeout(this.mceTout);

      var self = this;
      this.mceTout = setTimeout( function(){self._hideButtons(id);}, 5000 );
    },

    _hideButtons: function(id) {
      tinymce.DOM.setStyles(id, {display: 'none'});
    },
    
    updateNode: function(el, code) {
      if ( el.tagName != 'IMG' || ! tinymce.DOM.hasClass(el, 'videofymeVid') )
        return false;
      
      var attrs = this._get_embed_attributes(code);
      
      if ( attrs.id )
        el.setAttribute('alt', attrs.id);
      
      if ( attrs.width ) {
        el.setAttribute('width', attrs.width);
        el.style.width = attrs.width + 'px';
      }
      
      if ( attrs.height ) {
        el.setAttribute('height', attrs.height);
        el.style.height = attrs.height + 'px';
      }
      
      if ( attrs.me )
        el.setAttribute('data-me', attrs.me);
      else
        el.removeAttribute('data-me');
      
      return true;
    },

    getInfo: function() {
      return {
        longname : 'VideofyMe Settings',
        author : 'VideofyMe',
        authorurl : 'http://videofy.me/',
        infourl : '',
        version : "1.0"
      };
    }
  });

  tinymce.PluginManager.add('videofyme', tinymce.plugins.videofyme);
  
})();
