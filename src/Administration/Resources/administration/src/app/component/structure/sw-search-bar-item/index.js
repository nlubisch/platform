import { Component } from 'src/core/shopware';
import template from './sw-search-bar-item.html.twig';
import './sw-search-bar-item.less';

/**
 * @public
 * @description
 * Renders the search result items based on the item type.
 * @status ready
 * @example-type code-only
 * @component-example
 * <sw-search-bar-item :item="{ type: 'customer', entity: [{ name: 'customer name', id: 'uuid' }]}">
 * </sw-search-bar-item>
 */
Component.register('sw-search-bar-item', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
            default() {
                return {};
            }
        }
    }
});
