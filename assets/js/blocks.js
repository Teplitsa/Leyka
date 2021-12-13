/**
 * Leyka Blocks
 */

( function( blocks, editor, blockEditor, element, components, compose, data, hooks, i18n, serverSideRender ) {

	const ServerSideRender = serverSideRender;

	const el = element.createElement;

	const { TextControl, TextareaControl, SelectControl, CustomSelectControl, RangeControl, ColorPalette, PanelBody, PanelRow, ToggleControl, BaseControl, FormTokenField, Button, FontSizePicker, Disabled, UnitControl, __experimentalUnitControl } = components;

	const { registerBlockType, withColors, PanelColorSettings, getColorClassName, useBlockProps, updateCategory } = blocks;

	const { InspectorControls, ColorPaletteControl, MediaUpload, MediaUploadCheck, useSetting } = blockEditor;

	const { select, dispatch, withSelect, withDispatch, useSelect, useDispatch } = data;

	const { addFilter } = hooks;

	const { Fragment, useState } = element;

	const { withState } = compose;

	const { __ } = i18n;

	// Leyka Blocks Object.
	const blockI18n        = leykaBlock.blocks.i18n;
	const optionsCampaigns = leykaBlock.campaigns;

	// Update Category Icon
	function updateLeykaCategoryIcon(){

		const categoryIcon = el('svg',
			{
				width: 24,
				height: 24,
				fill: "#5ac147",
			},
			el( 'path',
				{
					d: "M11.8367 5.30166C11.8367 4.38929 12.5908 3.64944 13.5209 3.64944C14.4509 3.64944 15.2051 4.38929 15.2051 5.30166C15.2051 6.21403 14.4509 6.95373 13.5209 6.95373C12.5908 6.95373 11.8367 6.21403 11.8367 5.30166Z"
				}
			),
			el( 'path',
				{
					d: "M15.2039 9.16683H11.8355C11.5914 9.16683 11.3939 8.97277 11.3939 8.7334C11.3939 8.49422 11.5914 8.3005 11.8355 8.3005H15.2039C15.448 8.3005 15.646 8.49422 15.646 8.7334C15.646 8.97277 15.448 9.16683 15.2039 9.16683ZM7.93604 13.071C7.61086 13.071 7.3468 12.8128 7.3468 12.4934C7.3468 12.1743 7.61086 11.9154 7.93604 11.9154C8.26154 11.9154 8.52515 12.1743 8.52515 12.4934C8.52515 12.8128 8.26154 13.071 7.93604 13.071ZM3.32688 13.3887C3.30099 12.6374 3.64677 11.9478 4.11288 11.3947C4.28889 11.1861 4.08025 11.1459 3.956 10.9999C3.8436 10.8686 3.76368 10.6157 3.59565 10.7321C2.26232 11.6558 1.6152 13.3271 2.23926 14.9616C2.80711 16.4487 4.30144 17.0531 5.6516 16.829C6.64559 16.6632 7.38132 16.5622 7.95221 17.5311C8.33174 18.1767 8.97023 19.2619 9.25691 19.7496C9.37788 19.9541 9.44995 20 9.63416 20C10.1855 20 11.548 20 11.636 20C11.7883 20 11.8275 19.8883 11.8641 19.6955C12.0095 18.9286 12.6958 18.3478 13.5196 18.3478C14.3533 18.3478 15.0455 18.9418 15.1804 19.7215C15.2177 19.9395 15.2725 20 15.4298 20C15.494 20 16.8592 20 17.4086 20C17.5478 20 17.6349 20 17.7552 19.7948C17.8133 19.6962 20.8938 14.4625 20.8938 14.4625L20.9003 14.4504C21.1513 14.8168 21.5937 14.9722 21.9249 14.9655C22.0016 14.9637 22.0283 14.8794 21.9633 14.8168C21.6617 14.5262 21.447 14.1154 21.4151 13.5548C21.4017 13.3175 21.4346 13.0902 21.4736 12.8217C21.737 11.0216 20.8867 9.16292 19.1829 8.1977C18.4454 7.78034 17.6397 7.58186 16.8452 7.58257H10.1946C9.40014 7.58186 8.59455 7.78034 7.85706 8.1977C6.08607 9.20063 5.24083 11.1676 5.60626 13.0323C5.7484 13.755 5.74268 14.6094 5.06546 14.9207C4.31449 15.2657 3.37786 14.8474 3.32688 13.3887Z"
				}
			),
			el( 'path',
				{
					d: "M5.25968 8.63529C5.19795 8.19329 4.78407 7.89238 4.33312 7.94572C3.87269 8.00016 3.55467 8.41475 3.63009 8.8544C3.70095 9.26643 3.98705 9.33081 4.17286 9.78667C4.27438 10.0358 4.3023 10.2932 4.25394 10.511C4.23472 10.5978 4.27039 10.6233 4.32029 10.5891C5.09824 10.0618 5.32619 9.11582 5.25968 8.63529Z"
				}
			)
		);

		// Update category icon to svg.
		updateCategory( 'leyka', { icon: categoryIcon } );

	}

	updateLeykaCategoryIcon();

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
			anchor: {
				type: 'string',
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
			title: thisBlock.title,
			description: thisBlock.description,
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

	// Register Block Leyka Cards
	function registerBlockLyekaCards(){

		const thisBlock   = leykaBlock.blocks.cards;
		const blockColors = thisBlock.colors;

		const icon = el( 'svg',
			{
				width: "24",
				height: "24",
				fill: "none",
			},
			el( "path",
				{
					d: "M3.351,12.757l0.272-0.015C3.468,12.75,3.39,12.754,3.351,12.757z",
					fill: "#39a94e"
				}
			),
			el( "path",
				{
					d: "M20.989,18.141V3.859c0-1.103-0.897-2-2-2H2.011c-1.103,0-2,0.897-2,2v14.281c0,1.103,0.897,2,2,2h16.979 C20.092,20.141,20.989,19.243,20.989,18.141z M1.511,18.141V3.859c0-0.276,0.224-0.5,0.5-0.5h16.979c0.275,0,0.5,0.224,0.5,0.5 v14.281c0,0.275-0.225,0.5-0.5,0.5H2.011C1.735,18.641,1.511,18.416,1.511,18.141z",
					fill: "#39a94e"
				}
			),
			el( "circle",
				{
					cx: "6.013",
					cy: "7.616",
					r: "1.665",
					fill: "#39a94e"
				}
			),
			el( "rect",
				{
					x: "10.5",
					y: "11.555",
					width: "7.516",
					height: "1.5",
					fill: "#39a94e"
				}
			),
			el( "rect",
				{
					x: "10.5",
					y: "8.745",
					width: "7.516",
					height: "1.5",
					fill: "#39a94e"
				}
			),
			el( "rect",
				{
					x: "10.5",
					y: "5.951",
					width: "7.516",
					height: "1.5",
					fill: "#39a94e"
				}
			),
			el( "rect",
				{
					x: "2.984",
					y: "15.358",
					width: "15.032",
					height: "1.5",
					fill: "#39a94e"
				}
			),
			el( "path",
				{
					d: "M3.312,12.754l0,0.003l0,0.002l0.016,0.294h5.51v-0.311H8.527c0.311,0,0.311-0.002,0.311-0.002v-0.003l0-0.008 c0-0.006,0-0.015-0.001-0.025c-0.001-0.021-0.002-0.05-0.005-0.086c-0.006-0.073-0.017-0.174-0.038-0.295 c-0.043-0.24-0.13-0.565-0.307-0.892c-0.178-0.329-0.448-0.665-0.857-0.916c-0.409-0.252-0.938-0.41-1.617-0.41 c-0.678,0-1.202,0.157-1.601,0.412c-0.399,0.255-0.654,0.594-0.816,0.926c-0.161,0.33-0.231,0.656-0.261,0.897 c-0.015,0.121-0.021,0.223-0.023,0.295c-0.001,0.036-0.001,0.066,0,0.087c0,0.01,0,0.019,0.001,0.025L3.312,12.754z M3.623,12.742 l-0.272,0.015C3.39,12.754,3.468,12.75,3.623,12.742z",
					fill: "#39a94e"
				}
			),
			el( "path",
				{
					d: "M22.28,6.58c0.01,0.02,0.01,0.05,0.01,0.08v14.28c0,0.28-0.22,0.5-0.5,0.5H4.81c-0.05,0-0.1-0.01-0.14-0.02v1.51 c0.04,0.01,0.09,0.01,0.14,0.01h16.98c1.1,0,2-0.9,2-2V6.66c0-0.03,0-0.05,0-0.08H22.28z",
					fill: "#39a94e"
				}
			),
		);

		let blockAttributes = {
			align: {
				type: 'string',
				default: 'wide',
			},
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
			postsToShow: {
				type: 'number',
				default: 2
			},
			columns: {
				type: 'number',
				default: 2
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
			queryInclude: {
				type: 'array',
				default: [],
			},
			queryExclude: {
				type: 'array',
				default: [],
			},
			queryOffset: {
				type: 'string',
				default: '',
			},
			queryIsFinished: {
				type: 'boolean',
				default: true,
			},
			queryOrderBy: {
				type: 'string',
				default: 'date',
			},
			queryCampaignType: {
				type: 'string',
				default: 'all',
			}
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

		const queryPostsControl = ( props ) => {

			var campaigns = [];
			var getCampaigns = useSelect( function ( select ) {
				return wp.data.select('core').getEntityRecords('postType','leyka_campaign',{per_page: -1});
			}, [] );

			if ( getCampaigns ) {
				getCampaigns.map((campaign) => {
					var id = campaign.id;
					campaigns.push( campaign.title.raw );
				} )
			}

			const [ selectedCampaigns, setSelectedCampaigns ] = useState( props.attributes.queryInclude );

			return el ( FormTokenField,
				{
					label: blockI18n.includeCampaigns,
					value: selectedCampaigns,
					suggestions: campaigns,
					__experimentalExpandOnFocus: true,
					onChange: ( val ) => {
						setSelectedCampaigns( val ),
						props.setAttributes( { queryInclude: val } );
					},
				}
			);
		};

		const queryPostsExcludeControl = ( props ) => {

			var campaigns = [];
			var getCampaigns = useSelect( function ( select ) {
				return wp.data.select('core').getEntityRecords('postType','leyka_campaign',{per_page: -1});
			}, [] );

			if ( getCampaigns ) {
				getCampaigns.map((campaign) => {
					var id = campaign.id;
					campaigns.push( campaign.title.raw );
				} )
			}

			const [ selectedCampaigns, setSelectedCampaigns ] = useState( props.attributes.queryExclude );

			return el ( FormTokenField,
				{
					label: blockI18n.excludeCampaigns,
					value: selectedCampaigns,
					suggestions: campaigns,
					__experimentalExpandOnFocus: true,
					onChange: ( val ) => {
						setSelectedCampaigns( val ),
						props.setAttributes( { queryExclude: val } );
					},
				}
			);
		};

		registerBlockType( 'leyka/cards', {
			title: thisBlock.title,
			description: thisBlock.description,
			icon: icon,
			category: 'leyka',
			keywords: [ 'campaign', 'leyka', 'form', 'payment' ],
			attributes: blockAttributes,
			supports: {
				align: [ 'wide', 'full' ],
				html: false,
				anchor: true,
			},
			example: {
				attributes: {
					'preview' : true,
				},
				viewportWidth: 720
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

								el( RangeControl,
									{
										label: blockI18n.cardsToShow,
										value: props.attributes.postsToShow,
										initialPosition: 2,
										min: 1,
										max: 50,
										onChange: function( val ) {
											props.setAttributes({ postsToShow: val })
										}
									}
								),

								el( RangeControl,
									{
										label: blockI18n.columns,
										value: props.attributes.columns,
										initialPosition: 2,
										min: 1,
										max: 4,
										onChange: function( val ) {
											props.setAttributes({ columns: val });
										}
									}
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

							el( PanelBody,
								{
									title: blockI18n.query,
									initialOpen: false
								},

								el( SelectControl,
									{
										label: __( 'Order by' ),
										options : [
											{ value: 'date', label: __( 'Date' ) },
											{ value: 'post__in', label: blockI18n.includedCampaigns },
										],
										value: props.attributes.queryOrderBy,
										onChange: ( val ) => {
											props.setAttributes( { queryOrderBy: val } );
										},
									},
								),

								el( TextControl,
									{
										label: blockI18n.offset,
										type: 'number',
										min: 0,
										max: 100,
										value: props.attributes.queryOffset,
										help: blockI18n.offsetHelp,
										onChange: ( val ) => {
											props.setAttributes( { queryOffset: val } );
										},
									}
								),

								queryPostsControl(props),

								el( PanelRow, {},
									queryPostsExcludeControl(props),
								),

								el( SelectControl,
									{
										label: blockI18n.campaignType,
										options : [
											{ value: 'all', label: blockI18n.campaignAll },
											{ value: 'temporary', label: blockI18n.campaignTemporary },
											{ value: 'persistent', label: blockI18n.campaignPersistent },
										],
										value: props.attributes.queryCampaignType,
										onChange: ( val ) => {
											props.setAttributes( { queryCampaignType: val } );
										},
									},
								),

								el( PanelRow, {},
									el( ToggleControl,
										{
											label: blockI18n.includeFinished,
											onChange: ( value ) => {
												props.setAttributes( { queryIsFinished: value } );
											},
											checked: props.attributes.queryIsFinished,
										}
									)
								),

							),

						),

						el(	Disabled, null,
							el( ServerSideRender,
								{
									block: 'leyka/cards',
									attributes: props.attributes,
								}
							),
						)
					)
				);
			},

		} );
	} // end registerBlockLyekaCards

	registerBlockLyekaCards();

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

}(
	window.wp.blocks,
	window.wp.editor,
	window.wp.blockEditor,
	window.wp.element,
	window.wp.components,
	window.wp.compose,
	window.wp.data,
	window.wp.hooks,
	window.wp.i18n,
	window.wp.serverSideRender,
) );
