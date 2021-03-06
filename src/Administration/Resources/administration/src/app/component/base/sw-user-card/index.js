import { Component } from 'src/core/shopware';
import template from './sw-user-card.html.twig';
import './sw-user-card.less';

/**
 * @public
 * @description Renders a compact user information card using the provided user data.
 * @status ready
 * @example-type static
 * @component-example
 * <sw-user-card title="Account" :user="{
 *     email: 'test@example.com',
 *     active: true,
 *     addresses: [{
 *         salutation: 'Mister',
 *         title: 'Doctor',
 *         firstName: 'John',
 *         lastName: 'Doe',
 *         street: 'Main St 123',
 *         zipcode: '12456',
 *         city: 'Anytown',
 *         country: { name: 'Germany' }
 *     }],
 *     salutation: 'Mister',
 *     title: 'Doctor',
 *     firstName: 'John',
 *     lastName: 'Doe',
 *     street: 'Main St 123',
 *     zipcode: '12456',
 *     city: 'Anytown',
 *     country: { name: 'Germany' }
 * }">
 * <template slot="actions">
 *     <sw-button size="small" disabled>
 *         <sw-icon name="small-pencil" small></sw-icon>
 *         Edit user
 *     </sw-button>
 *
 *     <sw-button size="small" disabled>
 *         <sw-icon name="default-lock-key" small></sw-icon>
 *         Change password
 *      </sw-button>
 * </template>
 * </sw-user-card>
 */
Component.register('sw-user-card', {
    template,

    props: {
        user: {
            type: Object,
            required: true,
            default() {
                return {};
            }
        },
        title: {
            type: String,
            required: true,
            default: ''
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        hasActionSlot() {
            return !!this.$slots.actions;
        },

        moduleColor() {
            if (!this.$route.meta.$module) {
                return '';
            }
            return this.$route.meta.$module.color;
        },

        userName() {
            const user = this.user;

            if (!user.salutation && !user.firstName && !user.lastName) {
                return '';
            }

            const salutation = user.salutation ? user.salutation : '';
            const title = user.title ? user.title : '';
            const firstName = user.firstName ? user.firstName : '';
            const lastName = user.lastName ? user.lastName : '';

            return `${salutation} ${title} ${firstName} ${lastName}`;
        }
    }
});
