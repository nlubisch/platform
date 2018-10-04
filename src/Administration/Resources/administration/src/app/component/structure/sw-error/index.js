import { Component } from 'src/core/shopware';
import './sw-error.less';
import template from './sw-error.html.twig';

Component.register('sw-error', {
    template,

    props: {
        errorObject: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
        routerLink: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
        LinkText: {
            type: String,
            required: false,
            default: ''
        }
    },

    computed: {
        error() {
            if (Object.keys(this.errorObject).length > 0) {
                return this.errorObject;
            }
            return this.$root.initError;
        },

        message() {
            if (!this.error.message) {
                return this.$tc('sw-error.general.messagePlaceholder');
            }
            return this.error.message;
        },

        statusCode() {
            if (!this.error.response) {
                return this.$tc('sw-error.general.statusCodePlaceholder');
            }

            return this.error.response.status;
        },

        showStack() {
            return process.env.NODE_ENV === 'development' && this.error.stack;
        },

        showLink() {
            return Object.keys(this.routerLink).length > 0;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.linkText) {
                this.linkText = this.$tc('sw-error.general.textLink');
            }
        }
    }
});