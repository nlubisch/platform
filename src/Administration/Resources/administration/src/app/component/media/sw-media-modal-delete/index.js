import { Component, Mixin, Filter } from 'src/core/shopware';
import template from './sw-media-modal-delete.html.twig';
import './sw-media-modal-delete.less';

/**
 * @status ready
 * @description The <u>sw-media-modal-delete</u> component is used to validate the delete action.
 * @example-type code-only
 * @component-example
 * <sw-media-modal-delete itemsToDelete="[items]">
 * </sw-media-modal-delete>
 */
Component.register('sw-media-modal-delete', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        itemsToDelete: {
            required: true,
            type: Array,
            validator(value) {
                return (value.length !== 0);
            }
        }
    },

    computed: {
        mediaNameFilter() {
            return Filter.getByName('mediaName');
        }
    },

    methods: {
        closeDeleteModal(originalDomEvent) {
            this.$emit('sw-media-modal-delete-close', { originalDomEvent });
        },

        deleteSelection() {
            const deletePromises = [];
            const NotificationMessageSuccess = this.$tc('global.sw-media-modal-delete.notificationSuccessOverall');
            const NotificationMessageError = this.$tc('global.sw-media-modal-delete.notificationErrorOverall');

            this.itemsToDelete.forEach((item) => {
                const messages = this._getNotificationMessages(item);
                item.isLoading = true;

                deletePromises.push(
                    item.delete(true).then(() => {
                        item.isLoading = false;
                        this.createNotificationSuccess({
                            message: messages.successMessage
                        });
                        return item.id;
                    }).catch(() => {
                        item.isLoading = false;
                        this.createNotificationError({
                            message: messages.errorMessage
                        });
                    })
                );
            });

            this.$emit(
                'sw-media-modal-delete-items-deleted',
                Promise.all(deletePromises).then((ids) => {
                    this.createNotificationSuccess({
                        message: NotificationMessageSuccess
                    });
                    return ids;
                }).catch(() => {
                    this.createNotificationError({
                        message: NotificationMessageError
                    });
                })
            );
        },

        _getNotificationMessages(item) {
            return {
                successMessage: this.$tc(
                    'global.sw-media-modal-delete.notificationSuccessSingle',
                    1,
                    { mediaName: this.mediaNameFilter(item) }
                ),
                errorMessage: this.$tc(
                    'global.sw-media-modal-delete.notificationErrorSingle',
                    1,
                    { mediaName: this.mediaNameFilter(item) }
                )
            };
        }
    }
});
