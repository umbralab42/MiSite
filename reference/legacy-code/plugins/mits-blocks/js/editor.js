/* global wp */
/**
 * MITS Blocks — editor-side registration.
 * All blocks use server-side rendering so the editor shows real content.
 *
 * Dependencies (enqueued by PHP):
 *   wp-blocks, wp-element, wp-server-side-render,
 *   wp-block-editor, wp-components
 */
(function () {
    'use strict';

    var registerBlockType  = wp.blocks.registerBlockType;
    var el                 = wp.element.createElement;
    var ServerSideRender   = wp.serverSideRender;
    var InspectorControls  = wp.blockEditor.InspectorControls;
    var PanelBody          = wp.components.PanelBody;
    var TextControl        = wp.components.TextControl;

    /* ── Block category ──────────────────────────────────────────── */

    wp.blocks.updateCategory
        ? wp.blocks.updateCategory('mits', { title: 'МІТС' })
        : void 0;

    /* ── Simple blocks — no configurable attributes ──────────────── */

    var simpleBlocks = [
        { name: 'mits/address', title: 'Адреса',          icon: 'location'    },
        { name: 'mits/phones',  title: 'Телефони',         icon: 'phone'       },
        { name: 'mits/email',   title: 'Email',            icon: 'email'       },
        { name: 'mits/social',  title: 'Соціальні мережі', icon: 'share'       },
    ];

    simpleBlocks.forEach(function (b) {
        registerBlockType(b.name, {
            title:    b.title,
            icon:     b.icon,
            category: 'mits',
            edit: function () {
                return el(ServerSideRender, { block: b.name });
            },
            save: function () { return null; },
        });
    });

    /* ── FAQ block — category + count attributes ─────────────────── */

    registerBlockType('mits/faq', {
        title:    'FAQ',
        icon:     'editor-help',
        category: 'mits',
        attributes: {
            category: { type: 'string',  default: '' },
            count:    { type: 'string',  default: '' },
        },
        edit: function (props) {
            var attrs   = props.attributes;
            var setAttr = props.setAttributes;

            return [
                el(InspectorControls, { key: 'controls' },
                    el(PanelBody, { title: 'Налаштування FAQ', initialOpen: true },
                        el(TextControl, {
                            label:    'Категорія (slug)',
                            value:    attrs.category,
                            onChange: function (v) { setAttr({ category: v }); },
                            help:     'Порожньо — показати всі категорії.',
                        }),
                        el(TextControl, {
                            label:    'Кількість питань',
                            type:     'number',
                            value:    attrs.count,
                            onChange: function (v) { setAttr({ count: v }); },
                            help:     'Порожньо або -1 — показати всі.',
                        })
                    )
                ),
                el(ServerSideRender, {
                    key:        'preview',
                    block:      'mits/faq',
                    attributes: attrs,
                }),
            ];
        },
        save: function () { return null; },
    });

}());
