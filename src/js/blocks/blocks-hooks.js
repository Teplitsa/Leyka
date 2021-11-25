	var addCustomBlockClassName = compose.createHigherOrderComponent( function (
		BlockListBlock
	) {
		return function ( props ) {
			if ( props.name != 'leyka/form') {
				return el( BlockListBlock, props );
			}
			var newProps = lodash.assign( {}, props, {
				className: 'is-template-' + props.attributes.template
			} );
			return el( BlockListBlock, newProps );
		};
	},
	'addCustomBlockClassName' );

	addFilter( 'editor.BlockListBlock', 'leyka/blocks', addCustomBlockClassName );