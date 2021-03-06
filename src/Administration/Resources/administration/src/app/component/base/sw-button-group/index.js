import { Component } from 'src/core/shopware';
import './sw-button-group.less';
import template from './sw-button-group.html.twig';

/**
 * @status ready
 * @description The <u>sw-button-group</u> is a container element for sw-button and sw-context-button elements.
 * @example-type dynamic
 * @component-example
 * <sw-button-group>
 *     <sw-button>Button 1</sw-button>
 *     <sw-button>Button 2</sw-button>
 *     <sw-button>Button 3</sw-button>
 * </sw-button-group>
 */
Component.register('sw-button-group', {
    template,

    props: {
        block: {
            type: Boolean,
            required: false,
            default: false
        },

        splitButton: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        buttonGroupClasses() {
            return {
                'sw-button-group--block': this.block,
                'sw-button-group--split': this.splitButton
            };
        }
    }
});
