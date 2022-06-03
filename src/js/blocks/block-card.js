	// Register Block Leyka Card
	function registerBlockLyekaCard(){

		const thisBlock   = leykaBlock.blocks.card;
		const blockColors = thisBlock.colors;

		const icon = el( 'svg',
			{
				width: "24",
				height: "24",
				fill: "none",
			},
			el( "rect",
				{
					x: "2.2605",
					y: "3.60922",
					width: "19.479",
					height: "16.7815",
					rx: "1.25",
					fill: "none",
					stroke: "#39a94e",
					"stroke-width": "1.5"
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M19.5159 12.555L12 12.555V14.055H19.5159V12.555Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M19.5159 9.7455H12V11.2455L19.5159 11.2455V9.7455Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M19.5159 6.95077H12V8.45077H19.5159V6.95077Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M19.5159 16.3583L4.4841 16.3583V17.8583L19.5159 17.8583V16.3583Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M7.51302 9.65895C8.08912 9.65895 8.55614 9.19193 8.55614 8.61583C8.55614 8.03973 8.08912 7.5727 7.51302 7.5727C6.93692 7.5727 6.46989 8.03973 6.46989 8.61583C6.46989 9.19193 6.93692 9.65895 7.51302 9.65895ZM7.51302 10.2809C8.43259 10.2809 9.17805 9.5354 9.17805 8.61583C9.17805 7.69625 8.43259 6.95079 7.51302 6.95079C6.59345 6.95079 5.84799 7.69625 5.84799 8.61583C5.84799 9.5354 6.59345 10.2809 7.51302 10.2809Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					"fill-rule": "evenodd",
					"clip-rule": "evenodd",
					d: "M5.91178 11.5172C6.3113 11.2624 6.83507 11.1049 7.51301 11.1049C8.19121 11.1049 8.72071 11.2624 9.12968 11.5147C9.53782 11.7664 9.80834 12.1017 9.98635 12.4311C10.1634 12.7589 10.2505 13.0833 10.2938 13.3235C10.3155 13.4442 10.3266 13.5456 10.3322 13.6182C10.335 13.6545 10.3364 13.6837 10.3372 13.7047C10.3375 13.7152 10.3378 13.7237 10.3379 13.7299L10.338 13.7377L10.338 13.7403C10.338 13.7403 10.338 13.742 10.027 13.742H10.338V14.053H4.82834L4.81263 13.7586L5.12315 13.742C4.81263 13.7586 4.81263 13.7586 4.81263 13.7586L4.81254 13.7569L4.81242 13.7543L4.81211 13.7465C4.81189 13.7403 4.81164 13.7318 4.81146 13.7213C4.81109 13.7003 4.81099 13.671 4.81185 13.6347C4.81358 13.5621 4.8192 13.4605 4.83454 13.3395C4.86503 13.099 4.935 12.7729 5.09576 12.4428C5.25751 12.1107 5.51279 11.7717 5.91178 11.5172ZM5.44985 13.4311C5.45039 13.4267 5.45094 13.4222 5.45151 13.4177C5.47631 13.2221 5.53243 12.9666 5.65489 12.7151C5.77635 12.4657 5.96142 12.2232 6.24622 12.0415C6.53048 11.8602 6.93395 11.7269 7.51302 11.7268C8.09182 11.7268 8.50508 11.8601 8.80318 12.044C9.10211 12.2284 9.30296 12.4747 9.43921 12.7268C9.57576 12.9795 9.6458 13.2354 9.68125 13.4311H5.44985Z",
					fill: "#39a94e",
				}
			),
			el( "path",
				{
					d: "M4.81263 13.7586L4.82834 14.053H10.338V13.742H10.027C10.338 13.742 10.338 13.7403 10.338 13.7403L10.338 13.7377L10.3379 13.7299C10.3378 13.7237 10.3375 13.7152 10.3372 13.7047C10.3364 13.6837 10.335 13.6545 10.3322 13.6182C10.3266 13.5456 10.3155 13.4442 10.2938 13.3235C10.2505 13.0833 10.1634 12.7589 9.98635 12.4311C9.80834 12.1017 9.53782 11.7664 9.12968 11.5147C8.72071 11.2624 8.19121 11.1049 7.51301 11.1049C6.83507 11.1049 6.3113 11.2624 5.91178 11.5172C5.51279 11.7717 5.25751 12.1107 5.09576 12.4428C4.935 12.7729 4.86503 13.099 4.83454 13.3395C4.8192 13.4605 4.81358 13.5621 4.81185 13.6347C4.81099 13.671 4.81109 13.7003 4.81146 13.7213C4.81164 13.7318 4.81189 13.7403 4.81211 13.7465L4.81242 13.7543L4.81254 13.7569L4.81263 13.7586ZM4.81263 13.7586L5.12315 13.742C4.81263 13.7586 4.81263 13.7586 4.81263 13.7586ZM9.29671 11.8386L9.73864 12.2762M10.0968 13.5874V12.9655M8.55614 8.61583C8.55614 9.19193 8.08912 9.65895 7.51302 9.65895C6.93692 9.65895 6.46989 9.19193 6.46989 8.61583C6.46989 8.03973 6.93692 7.5727 7.51302 7.5727C8.08912 7.5727 8.55614 8.03973 8.55614 8.61583ZM9.17805 8.61583C9.17805 9.5354 8.43259 10.2809 7.51302 10.2809C6.59345 10.2809 5.84799 9.5354 5.84799 8.61583C5.84799 7.69625 6.59345 6.95079 7.51302 6.95079C8.43259 6.95079 9.17805 7.69625 9.17805 8.61583ZM5.44985 13.4311C5.45039 13.4267 5.45094 13.4222 5.45151 13.4177C5.47631 13.2221 5.53243 12.9666 5.65489 12.7151C5.77635 12.4657 5.96142 12.2232 6.24622 12.0415C6.53048 11.8602 6.93395 11.7269 7.51302 11.7268C8.09182 11.7268 8.50508 11.8601 8.80318 12.044C9.10211 12.2284 9.30296 12.4747 9.43921 12.7268C9.57576 12.9795 9.6458 13.2354 9.68125 13.4311H5.44985Z",
					fill: "none",
					stroke: "#39a94e",
					"stroke-width": "0.5",
				}
			)
		);

		let blockAttributes = {
			className: {
				type: 'string',
			},
			anchor: {
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
			buttonText: {
				type: 'string',
				default: blockI18n.donate,
			},
			showTitle: {
				type: 'boolean',
				default: true,
			},
			showExcerpt: {
				type: 'boolean',
				default: false,
			},
			showImage: {
				type: 'boolean',
				default: true,
			},
			showButton: {
				type: 'boolean',
				default: true,
			},
			showProgressbar: {
				type: 'boolean',
				default: true,
			},
			showTargetAmount: {
				type: 'boolean',
				default: true,
			},
			showCollectedAmount: {
				type: 'boolean',
				default: true,
			},
			titleFontSize: {
				type: 'string',
				default: '',
			},
			excerptFontSize: {
				type: 'string',
				default: '',
			},
		}

		// Add colors to attributes
		Object.entries( blockColors ).forEach( ( [ key ] ) => {
			blockAttributes[ key ] = { type: 'string' };
		});

		var colorControls = function( props, attributes ) {
			var colorControls =  el( 'div', {},
				el( ColorPaletteControl,
					{
						label: blockColors.colorBackground,
						value: props.attributes.colorBackground,
						onChange: ( val ) => {
							props.setAttributes({ colorBackground: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorTitle,
						value: props.attributes.colorTitle,
						onChange: ( val ) => {
							props.setAttributes({ colorTitle: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorExcerpt,
						value: props.attributes.colorExcerpt,
						onChange: ( val ) => {
							props.setAttributes({ colorExcerpt: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorButton,
						value: props.attributes.colorButton,
						onChange: ( val ) => {
							props.setAttributes({ colorButton: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorFulfilled,
						value: props.attributes.colorFulfilled,
						onChange: ( val ) => {
							props.setAttributes({ colorFulfilled: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorUnfulfilled,
						value: props.attributes.colorUnfulfilled,
						onChange: ( val ) => {
							props.setAttributes({ colorUnfulfilled: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorTargetAmount,
						value: props.attributes.colorTargetAmount,
						onChange: ( val ) => {
							props.setAttributes({ colorTargetAmount: val });
						}
					}
				),

				el( ColorPaletteControl,
					{
						label: blockColors.colorCollectedAmount,
						value: props.attributes.colorCollectedAmount,
						onChange: ( val ) => {
							props.setAttributes({ colorCollectedAmount: val });
						}
					}
				),

			);
			return colorControls;
		}

		var buttonTextControl = function( props, attributes ) {
			if ( ! props.attributes.showButton ) {
				return;
			}
			return el( TextControl, {
				label: blockI18n.buttonText,
				value: props.attributes.buttonText,
				onChange: ( val ) => {
					props.setAttributes( { buttonText: val } );
				},
			});

		}

		registerBlockType( 'leyka/card', {
			title: __('Campaign Card', 'leyka'),
			description: __('Campaign informer with configurable elements', 'leyka'),
			icon: icon,
			category: 'leyka',
			keywords: [ 'campaign', 'leyka', 'form', 'payment' ],
			attributes: blockAttributes,
			supports: {
				html: false,
				anchor: true,
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

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showTitle,
											onChange: ( value ) => {
												props.setAttributes( { showTitle: value } );
											},
											checked: props.attributes.showTitle,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showExcerpt,
											onChange: ( value ) => {
												props.setAttributes( { showExcerpt: value } );
											},
											checked: props.attributes.showExcerpt,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showImage,
											onChange: ( value ) => {
												props.setAttributes( { showImage: value } );
											},
											checked: props.attributes.showImage,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showProgressbar,
											onChange: ( value ) => {
												props.setAttributes( { showProgressbar: value } );
											},
											checked: props.attributes.showProgressbar,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showTargetAmount,
											onChange: ( value ) => {
												props.setAttributes( { showTargetAmount: value } );
											},
											checked: props.attributes.showTargetAmount,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showCollectedAmount,
											onChange: ( value ) => {
												props.setAttributes( { showCollectedAmount: value } );
											},
											checked: props.attributes.showCollectedAmount,
										}
									)
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.showButton,
											onChange: ( value ) => {
												props.setAttributes( { showButton: value } );
											},
											checked: props.attributes.showButton,
										}
									)
								),

								buttonTextControl( props, attributes ),

							),

							el( PanelBody,
								{
									title: blockI18n.color,
									initialOpen: false,
								},

								colorControls( props, attributes ),

							),

							el( PanelBody,
								{
									title: blockI18n.typography,
									initialOpen: false
								},

								leykaFontSizeControl( props, 'titleFontSize', blockAttributes, blockI18n.headingFontSize ),

								leykaFontSizeControl( props, 'excerptFontSize', blockAttributes, blockI18n.excerptFontSize ),

							),

						),

						el(	Disabled, null,
							el( ServerSideRender,
								{
									block: 'leyka/card',
									attributes: props.attributes,
								}
							),
						)
					)
				);
			},

		} );
	} // end registerBlockLyekaCard

	registerBlockLyekaCard();
