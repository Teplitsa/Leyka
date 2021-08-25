	// Register Block Leyka Form
	function registerBlockLyekaForm(){

		const thisBlock     = leykaBlock.blocks.form;
		const thisColors    = thisBlock.colors;
		const thisFontSizes = thisBlock['font-size'];

		const icon = el('svg',
			{
				width: 24,
				height: 24,
				fill: "#39a94e",
			},
			el( 'path',
				{ 
					d: "M11.8367 5.30166C11.8367 4.38929 12.5908 3.64944 13.5209 3.64944C14.4509 3.64944 15.2051 4.38929 15.2051 5.30166C15.2051 6.21403 14.4509 6.95373 13.5209 6.95373C12.5908 6.95373 11.8367 6.21403 11.8367 5.30166Z",
					fill: "#39a94e",
				}
			),
			el( 'path',
				{
					d: "M15.2039 9.16683H11.8355C11.5914 9.16683 11.3939 8.97277 11.3939 8.7334C11.3939 8.49422 11.5914 8.3005 11.8355 8.3005H15.2039C15.448 8.3005 15.646 8.49422 15.646 8.7334C15.646 8.97277 15.448 9.16683 15.2039 9.16683ZM7.93604 13.071C7.61086 13.071 7.3468 12.8128 7.3468 12.4934C7.3468 12.1743 7.61086 11.9154 7.93604 11.9154C8.26154 11.9154 8.52515 12.1743 8.52515 12.4934C8.52515 12.8128 8.26154 13.071 7.93604 13.071ZM3.32688 13.3887C3.30099 12.6374 3.64677 11.9478 4.11288 11.3947C4.28889 11.1861 4.08025 11.1459 3.956 10.9999C3.8436 10.8686 3.76368 10.6157 3.59565 10.7321C2.26232 11.6558 1.6152 13.3271 2.23926 14.9616C2.80711 16.4487 4.30144 17.0531 5.6516 16.829C6.64559 16.6632 7.38132 16.5622 7.95221 17.5311C8.33174 18.1767 8.97023 19.2619 9.25691 19.7496C9.37788 19.9541 9.44995 20 9.63416 20C10.1855 20 11.548 20 11.636 20C11.7883 20 11.8275 19.8883 11.8641 19.6955C12.0095 18.9286 12.6958 18.3478 13.5196 18.3478C14.3533 18.3478 15.0455 18.9418 15.1804 19.7215C15.2177 19.9395 15.2725 20 15.4298 20C15.494 20 16.8592 20 17.4086 20C17.5478 20 17.6349 20 17.7552 19.7948C17.8133 19.6962 20.8938 14.4625 20.8938 14.4625L20.9003 14.4504C21.1513 14.8168 21.5937 14.9722 21.9249 14.9655C22.0016 14.9637 22.0283 14.8794 21.9633 14.8168C21.6617 14.5262 21.447 14.1154 21.4151 13.5548C21.4017 13.3175 21.4346 13.0902 21.4736 12.8217C21.737 11.0216 20.8867 9.16292 19.1829 8.1977C18.4454 7.78034 17.6397 7.58186 16.8452 7.58257H10.1946C9.40014 7.58186 8.59455 7.78034 7.85706 8.1977C6.08607 9.20063 5.24083 11.1676 5.60626 13.0323C5.7484 13.755 5.74268 14.6094 5.06546 14.9207C4.31449 15.2657 3.37786 14.8474 3.32688 13.3887Z",
					fill: "#39a94e",
				}
			),
			el( 'path',
				{
					d: "M5.25968 8.63529C5.19795 8.19329 4.78407 7.89238 4.33312 7.94572C3.87269 8.00016 3.55467 8.41475 3.63009 8.8544C3.70095 9.26643 3.98705 9.33081 4.17286 9.78667C4.27438 10.0358 4.3023 10.2932 4.25394 10.511C4.23472 10.5978 4.27039 10.6233 4.32029 10.5891C5.09824 10.0618 5.32619 9.11582 5.25968 8.63529Z",
					fill: "#39a94e",
				}
			)
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
			keywords: [ 'campaing', 'leyka', 'form', 'payment' ],
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
