(function(blocks, element, $) {
  var el = element.createElement,
    source = blocks.source;

  function Flagallery(atts) {
    var tagtext;
    var scid;
    var id = atts.id;
    var type = atts.type;
    var galleryname = atts.galleryname || '';
    var skin = atts.skin || '';
    var preset = atts.preset || '';
    var skinalign = atts.align || '';
    var gallerywidth = atts.width || '';

    var skinname = '';
    var skinpreset = '';
    var gallerysize = '';
    if('gallery' == type) {
      scid = ' gid=' + id;
    }
    else {
      scid = ' album=' + id;
    }
    if(galleryname && galleryname.indexOf(' ') >= 0) {
      galleryname = '\'' + galleryname + '\'';
    }
    if(galleryname) {
      galleryname = ' name=' + galleryname;
    }
    if(gallerywidth) {
      gallerysize = ' w=' + gallerywidth;
    }
    else {
      gallerysize = '';
    }

    if(skin) {
      skinname = ' skin=' + skin;
    }
    else {
      skinname = '';
      skin = flagallery_data.default_skin;
    }
    if(preset) {
      if(preset.indexOf(' ') >= 0) {
        skinpreset = ' preset=\'' + preset + '\'';
      } else {
        skinpreset = ' preset=' + preset;
      }
    }
    else {
      skinpreset = '';
      preset = '';
    }
    if(skinalign) {
      skinalign = ' align=' + skinalign;
    }
    else {
      skinalign = '';
    }

    tagtext = '[flagallery' + scid + galleryname + gallerysize + skinname + skinpreset + skinalign + ']';

    return el('div', {className: 'flagallery-shortcode'}, tagtext);
  }

  blocks.registerBlockType('flagallery/gallery', {
    title: 'Flagallery Gallery',
    icon: 'format-gallery',
    category: 'common',
    attributes: {
      galleryname: {
        type: 'string',
      },
      id: {
        type: 'string',
      },
      type: {
        type: 'string',
      },
      skin: {
        type: 'string',
      },
      preset: {
        type: 'string',
      },
      align: {
        type: 'string',
      },
      width: {
        type: 'string',
      },
    },

    edit: function(props) {
      var galleryname = props.attributes.galleryname;
      var type = props.attributes.type;
      var id = props.attributes.id;
      var skin = props.attributes.skin;
      var preset = props.attributes.preset;
      var align = props.attributes.align;
      var width = props.attributes.width;
      var elclass = '';
      var children = [];
      var options = [];
      var galleries = [];
      var albums = [];

      function setGallery(event) {
        var form = $(event.target).closest('form.flagallery-preview');
        var type_id = form.find('.flagallery-id').val().split('-')
            type = '',
            id = '';
        if(type_id.length === 2) {
          type = type_id[0];
          id = type_id[1];
        }
        var galleryname = form.find('.flagallery-name').val();
        if('album' == type && !galleryname) {
          var key = parseInt(id, 10);
          galleryname = flagallery_data.albums[key].name;
        }
        props.setAttributes({
          id: id,
          type: type,
          skin: form.find('.flagallery-skin').val(),
          preset: form.find('.flagallery-preset').val(),
          align: form.find('.flagallery-align').val(),
          galleryname: galleryname,
          width: form.find('.flagallery-width').val(),
        });
        event.preventDefault();
      }

      function setSkin(event) {
        setPreset($(event.target));
        setGallery(event);
      }

      function setPreset(skinSelect) {
        var form = skinSelect.closest('form.flagallery-preview');
        var skin = skinSelect.val();
        form.find('.flagallery-preset').val('').find('option').removeAttr('style');
        if(skin) {
          form.find('.flagallery-preset option').filter('.' + skin).show();
        }
      }

      // Choose galleries
      options.push(
        el('option', {value: ''}, '- Select your album / gallery -'),
      );
      Object.keys(flagallery_data.albums).forEach(function(key) {
        albums.push(
          el('option', {value: 'album-' + flagallery_data.albums[key].id}, flagallery_data.albums[key].name),
        );
      });
      galleries.push(
        el('option', {value: 'all'}, 'Show All Galleries'),
      );
      Object.keys(flagallery_data.galleries).forEach(function(key) {
        galleries.push(
          el('option', {value: 'gallery-' + flagallery_data.galleries[key].gid}, flagallery_data.galleries[key].title),
        );
      });
      options.push(
        el('optgroup', {label: 'Albums', type: 'album'}, null, albums),
      );
      options.push(
        el('optgroup', {label: 'Galleries', type: 'gallery'}, null, galleries),
      );
      if(id) {
        elclass = 'flagallery-id';
      }
      else {
        elclass = 'flagallery-id flagallery-required';
      }
      children.push(
        el('select', {className: elclass, value: type + '-' + id, onChange: setGallery}, options),
      );

      // skin
      options = [];
      options.push(
        el('option', {value: ''}, 'Skin active by default'),
      );
      Object.keys(flagallery_data.skins).forEach(function(key) {
        options.push(
          el('option', {value: flagallery_data.skins[key].id}, flagallery_data.skins[key].name),
        );
      });
      children.push(
        el('select', {className: 'flagallery-skin', value: skin, onChange: setSkin}, options),
      );

      // preset
      options = [];
      options.push(
        el('option', {className:'default-preset', value: ''}, 'Default preset'),
      );
      Object.keys(flagallery_data.presets).forEach(function(key) {
        options.push(
          el('option', {
            className: flagallery_data.presets[key].id,
            value: flagallery_data.presets[key].name,
            style: { display: flagallery_data.presets[key].id === skin? 'block' : false }
          }, flagallery_data.presets[key].name),
        );
      });
      children.push(
        el('select', {className: 'flagallery-preset', value: preset, onChange: setGallery}, options),
      );

      // skin align
      options = [];
      var skin_align = [
        {'value': '', 'name': 'align none'},
        {'value': 'left', 'name': 'align left'},
        {'value': 'center', 'name': 'align center'},
        {'value': 'right', 'name': 'align right'},
      ];
      skin_align.forEach(function(data) {
        options.push(
          el('option', {value: data.value}, data.name),
        );
      });
      children.push(
        el('select', {className: 'flagallery-align', value: align, onChange: setGallery}, options),
      );

      children.push(
        el('input', {className: 'flagallery-name', value: galleryname, placeholder: 'Gallery Title (optional)', type: 'text', onChange: setGallery}),
      );

      children.push(
        el('input', {className: 'flagallery-width', value: width, placeholder: 'Gallery Width (optional)', type: 'text', onChange: setGallery}),
      );

      if(!skin) {
        skin = flagallery_data.default_skin;
      }
      children.push(
        el('img', {className: 'flagallery-skin-screenshot', src: flagallery_data.skins[skin].screenshot}),
      );

      if(id) {
        children.push(Flagallery(props.attributes));
      }

      return el('form', {className: 'flagallery-preview', onSubmit: setGallery}, children);
    },

    save: function(props) {
      if(typeof props.attributes.id == 'undefined') {
        return;
      }
      return Flagallery(props.attributes);
    }
    ,
  });
})(
  window.wp.blocks,
  window.wp.element,
  jQuery
);
