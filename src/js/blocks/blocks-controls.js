	/**
	 * Custom Font Size Control
	 */
	var leykaFontSizeControl = function( props, attrName, blockAttributes, label ) {

		return el( 'div',
			{
				className: 'components-base-control leyka-components-base-control',
			},

			el( 'label',
				{
					className: 'components-base-control__label leyka-components-base-control__label',
				},
				label
			),

			el( 'div',
				{
					className: 'components-font-size-picker__controls',
				},

				el( __experimentalUnitControl,
					{
						units: [
							{
								value: 'px',
								label: 'px',
							}
						],
						__unstableInputWidth: '60px',
						value: props.attributes[attrName],
						onChange: ( val ) => {
							props.setAttributes( { [attrName]: val } );
						},
					},
				),

				el( Button,
					{
						isSmall: true,
						text: blockI18n.reset,
						variant: 'secondary',
						className: 'components-color-palette__clear is-secondary',
						onClick: () => {
							props.setAttributes( { [attrName]: blockAttributes[attrName].default } );
						},
					},
				),

			),
		);
	}

	/**
	 * Custom Color Control
	 */
	var leykaColorControl = function( props, attrName, blockAttributes, label ) {
		return el( ColorPaletteControl,
			{
				label: label,
				value: props.attributes[attrName],
				onChange: ( val ) => {
					props.setAttributes( { [attrName]: val } );
				}
			}
		);
	}
