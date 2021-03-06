import { Component, Mixin } from 'src/core/shopware';
import template from './sw-media-folder-item.html.twig';
import './sw-media-folder-item.less';

Component.register('sw-media-folder-item', {
    template,

    mixins: [
        Mixin.getByName('selectable-media-item')
    ],

    provide() {
        return {
            renameEntity: this.renameEntity,
            rejectRenaming: this.rejectRenaming
        };
    },

    props: {
        item: {
            type: Object,
            required: true,
            validator(value) {
                return value.entityName === 'media_folder';
            }
        }
    },

    computed: {
        defaultContextMenuClass() {
            return {
                'sw-context-menu__group': this.$slots['additional-context-menu-items']
            };
        },

        baseComponent() {
            return this.$refs.innerComponent;
        }
    },

    mounted() {
        this.mountedComponent();
    },

    methods: {
        mountedComponent() {
            if (this.item.name === '') {
                this.baseComponent.startInlineEdit();
            }
        },

        onStartRenaming() {
            this.baseComponent.startInlineEdit();
        },

        renameEntity(updatedName) {
            this.item.isLoading = true;
            this.item.name = updatedName;

            return this.item.save().then(() => {
                this.item.isLoading = false;
            });
        },

        rejectRenaming() {
            if (this.item.isLocal === true) {
                this.item.delete(true).then(() => {
                    this.$emit('sw-media-folder-item-delete', [this.item.id]);
                });
            }
        }
    }
});
