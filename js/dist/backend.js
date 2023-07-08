var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

wp.domReady(function () {
  var colors = [{
    name: 'Blue',
    slug: 'blue',
    color: '#034663'
  }, {
    name: 'Yellow',
    slug: 'yellow',
    color: '#fdc716'
  }, {
    name: 'Light-Grey',
    slug: 'light_grey',
    color: '#f6f7f8'
  }, {
    name: 'Dark-Grey',
    slug: 'dark_grey',
    color: '#adb0b1'
  }, {
    name: 'White',
    slug: 'white',
    color: '#ffffff'
  }, {
    name: 'Black',
    slug: 'black',
    color: '#000000'
  }];
  wp.blocks.registerBlockType('fcc/box', {
    title: 'Box',
    icon: 'editor-contract',
    category: 'common',
    attributes: {
      bgColor: {
        type: 'string'
      },
      parallaxImage: {
        type: 'string'
      },
      borderColor: {
        type: 'string'
      },
      stack: {
        type: 'string'
      },
      textColor: {
        type: 'string'
      },
      textFontFamily: {
        type: 'string'
      },
      paddingTop: {
        type: 'string'
      },
      paddingRight: {
        type: 'string'
      },
      paddingBottom: {
        type: 'string'
      },
      paddingLeft: {
        type: 'string'
      },
      textAlign: {
        type: 'string'
      },
      fullHeight: {
        type: 'boolean'
      }
    },

    edit: function (props) {
      return React.createElement(
        wp.element.Fragment,
        null,
        React.createElement(
          wp.blockEditor.InspectorControls,
          null,
          React.createElement(
            wp.components.PanelBody,
            { title: 'Background', initialOpen: false },
            React.createElement(wp.components.ColorPalette, {
              colors: colors,
              color: props.attributes.bgColor,
              disableCustomColors: false,
              onChange: value => props.setAttributes({ bgColor: value })
            }),
            React.createElement(
              wp.blockEditor.MediaUploadCheck,
              { fallback: React.createElement(
                  'p',
                  null,
                  'To edit the parallax background image, you need permission to upload media.'
                ) },
              !props.attributes.parallaxImage && React.createElement(wp.blockEditor.MediaUpload, {
                title: 'Parallax Image',
                onSelect: image => {
                  props.setAttributes({ parallaxImage: image.url });
                },
                allowedTypes: ['image'],
                value: props.attributes.parallaxImage,
                render: ({ open }) => React.createElement(
                  wp.components.Button,
                  {
                    className: 'editor-post-featured-image__toggle',
                    onClick: open },
                  'Set Parallax background'
                )
              }),
              props.attributes.parallaxImage && props.attributes.parallaxImage,
              React.createElement('br', null),
              props.attributes.parallaxImage && React.createElement(
                wp.blockEditor.MediaUploadCheck,
                null,
                React.createElement(
                  wp.components.Button,
                  { onClick: () => props.setAttributes({ parallaxImage: undefined }), isLink: true, isDestructive: true },
                  'Remove parallax image'
                )
              )
            )
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Border Color', initialOpen: false },
            React.createElement(wp.components.ColorPalette, {
              colors: colors,
              color: props.attributes.borderColor,
              disableCustomColors: false,
              onChange: value => props.setAttributes({ borderColor: value })
            })
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Stack Effect', initialOpen: false },
            React.createElement(wp.components.SelectControl, {
              label: 'Stack Effect',
              value: props.attributes.stack,
              options: [{ label: 'None', value: '' }, { label: 'Left', value: 'left' }, { label: 'Right', value: 'right' }],
              onChange: value => {
                props.setAttributes({ stack: value });
              }
            })
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Text Color', initialOpen: false },
            React.createElement(wp.components.ColorPalette, {
              colors: colors,
              color: props.attributes.textColor,
              disableCustomColors: false,
              onChange: value => props.setAttributes({ textColor: value })
            })
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Font', initialOpen: false },
            React.createElement(wp.components.TextControl, {
              label: 'Font',
              type: 'string',
              value: props.attributes.textFontFamily,
              onChange: value => props.setAttributes({ textFontFamily: value })
            })
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Spacings', initialOpen: false },
            React.createElement(wp.components.TextControl, {
              label: 'Padding Top',
              type: 'number',
              value: props.attributes.paddingTop,
              onChange: value => props.setAttributes({ paddingTop: value })
            }),
            React.createElement(wp.components.TextControl, {
              label: 'Padding Right',
              type: 'number',
              value: props.attributes.paddingRight,
              onChange: value => props.setAttributes({ paddingRight: value })
            }),
            React.createElement(wp.components.TextControl, {
              label: 'Padding Bottom',
              type: 'number',
              value: props.attributes.paddingBottom,
              onChange: value => props.setAttributes({ paddingBottom: value })
            }),
            React.createElement(wp.components.TextControl, {
              label: 'Padding Left',
              type: 'number',
              value: props.attributes.paddingLeft,
              onChange: value => props.setAttributes({ paddingLeft: value })
            })
          ),
          React.createElement(
            wp.components.PanelBody,
            { title: 'Content', initialOpen: false },
            React.createElement(wp.components.SelectControl, {
              label: 'Alignment',
              value: props.attributes.textAlign,
              options: [{ label: 'Left', value: 'left' }, { label: 'Center', value: 'center' }, { label: 'Right', value: 'right' }],
              onChange: value => {
                props.setAttributes({ textAlign: value });
              }
            }),
            React.createElement(wp.components.CheckboxControl, {
              label: 'Full Height',
              checked: props.attributes.fullHeight,
              onChange: value => {
                props.setAttributes({ fullHeight: value });
              }
            })
          )
        ),
        React.createElement(
          'div',
          {
            className: props.className,
            style: { backgroundColor: props.attributes.bgColor }
          },
          React.createElement(wp.blockEditor.InnerBlocks, null)
        )
      );
    },

    save: props => {
      var styles = {
        backgroundColor: props.attributes.bgColor,
        borderColor: props.attributes.borderColor,
        borderWidth: props.attributes.borderColor ? '1px' : undefined,
        borderStyle: props.attributes.borderColor ? 'solid' : undefined,
        paddingTop: (props.attributes.paddingTop ? props.attributes.paddingTop : 1) + 'px',
        paddingRight: props.attributes.paddingRight ? props.attributes.paddingRight + 'px' : undefined,
        paddingBottom: (props.attributes.paddingBottom ? props.attributes.paddingBottom : 1) + 'px',
        paddingLeft: props.attributes.paddingLeft ? props.attributes.paddingLeft + 'px' : undefined,
        textAlign: props.attributes.textAlign,
        fontFamily: props.attributes.textFontFamily,
        color: props.attributes.textColor,
        height: props.attributes.fullHeight ? '100%' : 'auto'
      };

      var atts = {
        'data-parallax': props.attributes.parallaxImage ? "scroll" : undefined,
        'data-image-src': props.attributes.parallaxImage || undefined
      };

      return React.createElement(
        'div',
        _extends({ className: `fcc-box ${props.attributes.stack ? 'stack stack-' + props.attributes.stack : ''} bg-${(props.attributes.bgColor || '').substring(1)}`, style: styles }, atts),
        React.createElement(wp.blockEditor.InnerBlocks.Content, null)
      );
    }
  });
});