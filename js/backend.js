wp.domReady(function() {
  var colors = [
		{
			name: 'Blue',
			slug: 'blue',
			color: '#034663',
    },
		{
			name: 'Yellow',
			slug: 'yellow',
			color: '#fdc716',
    },
    {
      name: 'Light-Grey',
      slug: 'light_grey',
      color: '#f6f7f8',
    },
    {
      name: 'Dark-Grey',
      slug: 'dark_grey',
      color: '#adb0b1',
    },
		{
			name: 'White',
			slug: 'white',
			color: '#ffffff',
    },
		{
			name: 'Black',
			slug: 'black',
			color: '#000000',
    },
  ];  
  wp.blocks.registerBlockType('fcc/box', {
    title: 'Box',
    icon: 'editor-contract',
    category: 'common',
    attributes: {
      bgColor: {
        type: 'string',
      },
      parallaxImage: {
        type: 'string',
      },
      borderColor: {
        type: 'string',
      },
      stack: {
        type: 'string',
      },
      textColor: {
        type: 'string',
      },
      textFontFamily: {
        type: 'string',
      },
      paddingTop: {
        type: 'string',
      },
      paddingRight: {
        type: 'string',
      },
      paddingBottom: {
        type: 'string',
      },
      paddingLeft: {
        type: 'string',
      },
      textAlign: {
        type: 'string'
      },
      fullHeight: {
        type: 'boolean'
      }
    },

    edit: function (props) {
      return (
        <wp.element.Fragment>
          <wp.blockEditor.InspectorControls>
            <wp.components.PanelBody title='Background' initialOpen={ false }>
              <wp.components.ColorPalette
                colors={colors}
                color={props.attributes.bgColor}
                disableCustomColors={false}
                onChange={(value) => props.setAttributes({ bgColor: value })}
              />
              <wp.blockEditor.MediaUploadCheck fallback={<p>To edit the parallax background image, you need permission to upload media.</p>}>
                {!props.attributes.parallaxImage && 
                <wp.blockEditor.MediaUpload
                  title='Parallax Image'
                  onSelect={( image ) => { 
                    props.setAttributes( {parallaxImage: image.url} ); 
                  }}
                  allowedTypes={['image']}
                  value={ props.attributes.parallaxImage }
                  render={ ( { open } ) => (
                    <wp.components.Button
                      className={ 'editor-post-featured-image__toggle' }
                      onClick={ open }>
                      {'Set Parallax background'}
                    </wp.components.Button>
                  ) }
                />}
                
                { props.attributes.parallaxImage &&
                props.attributes.parallaxImage }
                <br/>
                { props.attributes.parallaxImage &&
                <wp.blockEditor.MediaUploadCheck>
                    <wp.components.Button onClick={ () => props.setAttributes( {parallaxImage: undefined,} ) } isLink isDestructive>
                        { 'Remove parallax image' }
                    </wp.components.Button>
                </wp.blockEditor.MediaUploadCheck>}
              </wp.blockEditor.MediaUploadCheck>
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Border Color' initialOpen={ false }>
              <wp.components.ColorPalette
                colors={colors}
                color={props.attributes.borderColor}
                disableCustomColors={false}
                onChange={(value) => props.setAttributes({ borderColor: value })}
              />
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Stack Effect' initialOpen={ false }>
              <wp.components.SelectControl
                label="Stack Effect"
                value={ props.attributes.stack }
                options={ [
                  { label: 'None', value: '' },
                  { label: 'Left', value: 'left' },
                  { label: 'Right', value: 'right' },
                ] }
                onChange={ ( value ) => { props.setAttributes( { stack: value } ); } }
              />
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Text Color' initialOpen={ false }>
              <wp.components.ColorPalette
                colors={colors}
                color={props.attributes.textColor}
                disableCustomColors={false}
                onChange={(value) => props.setAttributes({ textColor: value })}
              />
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Font' initialOpen={ false }>
              <wp.components.TextControl
                label="Font"
                type="string"
                value={ props.attributes.textFontFamily }
                onChange={ ( value ) => props.setAttributes({ textFontFamily: value }) }
              />
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Spacings' initialOpen={ false }>
              <wp.components.TextControl
                label="Padding Top"
                type="number"
                value={ props.attributes.paddingTop }
                onChange={ ( value ) => props.setAttributes({ paddingTop: value }) }
              />
              <wp.components.TextControl
                label="Padding Right"
                type="number"
                value={ props.attributes.paddingRight }
                onChange={ ( value ) => props.setAttributes({ paddingRight: value }) }
              />
              <wp.components.TextControl
                label="Padding Bottom"
                type="number"
                value={ props.attributes.paddingBottom }
                onChange={ ( value ) => props.setAttributes({ paddingBottom: value }) }
              />
              <wp.components.TextControl
                label="Padding Left"
                type="number"
                value={ props.attributes.paddingLeft }
                onChange={ ( value ) => props.setAttributes({ paddingLeft: value }) }
              />
            </wp.components.PanelBody>
            <wp.components.PanelBody title='Content' initialOpen={ false }>
              <wp.components.SelectControl
                label="Alignment"
                value={ props.attributes.textAlign }
                options={ [
                  { label: 'Left', value: 'left' },
                  { label: 'Center', value: 'center' },
                  { label: 'Right', value: 'right' },
                ] }
                onChange={ ( value ) => { props.setAttributes( { textAlign: value } ); } }
              />
              <wp.components.CheckboxControl
                label="Full Height"
                checked={ props.attributes.fullHeight }
                onChange={ ( value ) => { props.setAttributes( { fullHeight: value } ); } }
              />
            </wp.components.PanelBody>
          </wp.blockEditor.InspectorControls>
          <div
            className={props.className}
            style={{ backgroundColor: props.attributes.bgColor }}
          >
            <wp.blockEditor.InnerBlocks />
          </div>
        </wp.element.Fragment>
      );
    },

    save: (props) => {
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
        height: props.attributes.fullHeight ? '100%' : 'auto',
      };

      var atts = {
        'data-parallax': props.attributes.parallaxImage ? "scroll" : undefined,
        'data-image-src':  props.attributes.parallaxImage || undefined
      };

      return (<div className={`fcc-box ${props.attributes.stack ? 'stack stack-' + props.attributes.stack : ''} bg-${(props.attributes.bgColor || '').substring(1)}`} style={styles} {...atts} >
        <wp.blockEditor.InnerBlocks.Content />
      </div>);
    }
  });
});