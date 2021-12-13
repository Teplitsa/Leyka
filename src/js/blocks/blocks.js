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

@import './src/js/blocks/blocks-category.js'
@import './src/js/blocks/blocks-controls.js'
@import './src/js/blocks/block-form.js'
@import './src/js/blocks/block-card.js'
@import './src/js/blocks/block-cards.js'
@import './src/js/blocks/blocks-hooks.js'

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
