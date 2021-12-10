	// Register Block Leyka Form
	function registerBlockLyekaForm(){

		const thisBlock     = leykaBlock.blocks.form;
		const thisColors    = thisBlock.colors;
		const thisFontSizes = thisBlock['font-size'];

		const icon = el( 'svg',
			{
				width: 24,
				height: 24,
				fill: "none",
			},
			el( 'rect',
				{
					x: "2.7605",
					y: "3.60922",
					width: "19.479",
					height: "16.7815",
					rx: "1.25",
					fill: "none",
					stroke: "#39a94e",
					'stroke-width': "1.5"
				}
			),
			el( 'rect',
				{
					width: "7.20158",
					height: "2.16643",
					transform: "matrix(1 0 0 -1 5.73016 17.8385)",
					fill: "#39a94e",
				}
			),
			el( 'rect',
				{
					width: "13.5397",
					height: "1.43999",
					transform: "matrix(1 0 0 -1 5.73016 14.2201)",
					fill: "#39a94e",
				}
			),
			el( 'rect',
				{
					x: "2.98361",
					y: "6.47498",
					width: "19.0328",
					height: "1.43999",
					fill: "#39a94e",
				}
			),
			el( 'rect',
				{
					width: "13.5397",
					height: "1.43999",
					transform: "matrix(1 0 0 -1 5.73016 11.3276)",
					fill: "#39a94e",
				}
			),
		);

		let blockAttributes = {
			className: {
				type: 'string',
			},
			preview: {
				type: 'boolean',
				default: false,
			},
			campaign: {
				type: 'string',
				default: optionsCampaigns[1] ? optionsCampaigns[1].value : '',
			},
			template: {
				type: 'string',
				default: 'star',
			},
		}

		// Add colors to attributes.
		Object.entries( thisColors.star ).forEach( ( [ key ] ) => {
			blockAttributes[ key ] = { type: 'string' };
		});

		// Update colors for template need help.
		Object.entries( thisColors['need-help'] ).forEach( ( [ key ] ) => {
			blockAttributes[ key ] = { type: 'string' };
		});

		// Add font sizes to attributes.
		Object.entries( thisFontSizes ).forEach( ( [ key, value ] ) => {
			blockAttributes[ key ] = {
				type: 'string',
				default: value.default,
			};
		});

		// Color Controls.
		var colorControls = function( props, attributes ) {

			var colorControls = '';
			var blockColors   = '';
			var colorElements = [];
			var template      = props.attributes.template;

			blockColors = thisColors[ template ];

			Object.entries( blockColors ).forEach( ( [ attrName, label ] ) => {
				colorElements.push( leykaColorControl( props, attrName, blockAttributes, label ) );
			});

			colorControls = el( Fragment, null,
				colorElements
			);

			return colorControls;
		}

		// Font Size Panel.
		var fontSizePanel = function( props, attributes ) {

			var fontSizePanel    = '';
			var fontSizeControls = '';

			if ( props.attributes.template == 'need-help' ) {

				var fontSizeControls = function( props, attributes ) {

					var fontSizeElements = [];

					Object.entries( thisFontSizes ).forEach( ( [ attrName, value ] ) => {
						fontSizeElements.push( leykaFontSizeControl( props, attrName, blockAttributes, value.label ) );
					});

					return el( Fragment, null,
						fontSizeElements
					);
				}

				fontSizePanel = el( PanelBody,
					{
						title: blockI18n.typography,
						initialOpen: false,
					},

					fontSizeControls( props, attributes ),

				);

			}
			return fontSizePanel;
		}

		// Register Block Type leyka/form.
		registerBlockType( 'leyka/form', {
			title: thisBlock.title,
			description: thisBlock.description,
			icon: icon,
			category: 'leyka',
			keywords: [ 'campaign', 'leyka', 'form', 'payment' ],
			attributes: blockAttributes,
			supports: {
				html: false,
			},
			example: {
				attributes: {
					'preview' : true,
				},
			},

			edit: function( props ) {

				const { attributes, className, setAttributes } = props;

				return (
					el( Fragment, null,

						el( InspectorControls, null,

							el( PanelBody,
								{
									title: blockI18n.settings,
									initialOpen: true,
								},

								// Select Campaign
								el( SelectControl,
									{
										label: blockI18n.campaign,
										options : optionsCampaigns,
										value: props.attributes.campaign,
										onChange: ( val ) => {
											props.setAttributes( { campaign: val } );
										},
									},
								),

								// Select Tempalte
								el( SelectControl,
									{
										label: blockI18n.template,
										options: [
											{
												value: 'star',
												label: blockI18n.star
											},
											{
												value: 'need-help',
												label: blockI18n.needHelp
											}
										],
										value: props.attributes.template,
										onChange: ( val, event ) => {
											props.setAttributes( { template: val } );
										},
									},
								),

							),

							el( PanelBody,
								{
									title: blockI18n.color,
									initialOpen: false,
								},

								colorControls( props, attributes ),

							),

							fontSizePanel( props, attributes ),

						),

						el( Disabled, null,
							el( ServerSideRender,
								{
									block: 'leyka/form',
									attributes: props.attributes,
								}
							),
						)

					)
				);
			},

		} );

	} // end registerBlockLyekaForm

	registerBlockLyekaForm();
