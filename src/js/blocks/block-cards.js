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
			title: __('Campaigns Cards', 'leyka'),
			description: __('Campaigns informer with configurable elements', 'leyka'),
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
										label: __( 'Order by', 'leyka' ),
										options : [
											{ value: 'date', label: __( 'Date', 'leyka' ) },
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
