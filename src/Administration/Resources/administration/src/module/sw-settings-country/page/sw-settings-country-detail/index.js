import { Component, State, Mixin } from 'src/core/shopware';
import template from './sw-settings-country-detail.html.twig';
import './sw-settings-country-detail.less';

Component.register('sw-settings-country-detail', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            country: {}
        };
    },

    computed: {
        countryStore() {
            return State.getStore('country');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.$route.params.id) {
                this.countryId = this.$route.params.id;
                this.country = this.countryStore.getById(this.countryId);
            }
        },

        onSave() {
            const countryName = this.country.name;
            const titleSaveSuccess = this.$tc('sw-settings-country.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('sw-settings-country.detail.messageSaveSuccess', 0, {
                name: countryName
            });

            return this.country.save().then(() => {
                this.createNotificationSuccess({
                    title: titleSaveSuccess,
                    message: messageSaveSuccess
                });
            });
        }
    }
});
