var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    RichText = wp.editor.RichText;

registerBlockType( 'my-gutenberg/my-first-block', {
    title: 'My First Block',

    description: 'Description',

    icon: {
        background: '#7e70af',
        foreground: '#fff',
        src: 'format-aside',
    },

    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'div.yamap',
        }
    },

    category: 'common',

    edit: function( props ) {
        var content = props.attributes.content;

        function onChangeContent( newContent ) {
            props.setAttributes( { content: newContent } );
        }

        return el(
            RichText,
            {
                tagName: 'div',
                className: 'yamap', // props.className
                onChange: onChangeContent,
                value: content,
            }
        );
    },

    save: function( props ) {
        var content = props.attributes.content;

        console.log(props.className);

        return el( RichText.Content, {
            tagName: 'div',
            className: props.className,
            value: content
        } );
    },
} );

OpenYandexMapWindow(0, {});