import { Component, Mixin, State, Filter } from 'src/core/shopware';
import { fileReader } from 'src/core/service/util.service';
import find from 'lodash/find';
import template from './sw-product-media-form.html.twig';
import './sw-product-media-form.less';

Component.register('sw-product-media-form', {
    template,

    inject: ['mediaService', 'mediaUploadService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        product: {
            type: Object,
            required: true,
            default: {}
        }
    },

    data() {
        return {
            columnCount: 7,
            previews: [],
            columnWidth: 90,
            unsavedEntities: []
        };
    },

    computed: {
        mediaItems() {
            const mediaItems = this.product.media.slice();
            const placeholderCount = this.getPlaceholderCount(this.columnCount);
            if (placeholderCount === 0) {
                return mediaItems;
            }

            for (let i = 0; i < placeholderCount; i += 1) {
                mediaItems.push(this.createPlaceholderMedia(mediaItems));
            }

            return mediaItems;
        },

        productMediaStore() {
            return this.product.getAssociation('media');
        },

        mediaStore() {
            return State.getStore('media');
        },

        gridAutoRows() {
            return `grid-auto-rows: ${this.columnWidth}`;
        },

        mediaNameFilter() {
            return Filter.getByName('mediaName');
        }
    },

    mounted() {
        this.mountedComponent();
    },

    beforeDestroy() {
        this.onBeforeDestroy();
    },

    methods: {
        mountedComponent() {
            const that = this;
            this.$device.onResize({
                listener() {
                    that.updateColumnCount();
                },
                component: this
            });
            this.updateColumnCount();
        },

        updateColumnCount() {
            const cssColumns = window.getComputedStyle(this.$refs.grid, null)
                .getPropertyValue('grid-template-columns')
                .split(' ');
            this.columnCount = cssColumns.length;
            this.columnWidth = cssColumns[0];
        },

        onBeforeDestroy() {
            this.unsavedEntities.forEach((entity) => {
                entity.delete();
            });
        },

        getPlaceholderCount(columnCount) {
            if (this.product.media.length + 3 < columnCount * 2) {
                columnCount *= 2;
            }
            const placeholderCount = columnCount - ((this.product.media.length + 3) % columnCount);

            if (placeholderCount === columnCount) {
                return 0;
            }

            return placeholderCount;
        },

        createPlaceholderMedia(mediaItems) {
            return {
                isPlaceholder: true,
                isCover: mediaItems.length === 0,
                media: {
                    isPlaceholder: true,
                    name: ''
                },
                mediaId: mediaItems.length
            };
        },

        onNewUpload({ mediaEntity, src }) {
            const productMedia = this.buildProductMedia(mediaEntity);

            this.unsavedEntities.push(productMedia);
            this.unsavedEntities.push(mediaEntity);

            this.product.media.push(productMedia);
            if (src instanceof File) {
                if (mediaEntity.isLocal) {
                    mediaEntity.fileName = src.name;
                }

                fileReader.readAsDataURL(src).then((dataURL) => {
                    this.addImageToPreview(dataURL, productMedia);
                });
            } else if (src instanceof URL) {
                if (mediaEntity.isLocal) {
                    mediaEntity.fileName = src.pathname.split('/').pop();
                }

                this.previews[productMedia.mediaId] = src.href;
                productMedia.isLoading = false;
            }
        },

        onMediaUploadButtonOpenSidebar() {
            this.$root.$emit('sw-product-media-form-open-sidebar');
        },

        buildProductMedia(mediaEntity) {
            const productMedia = this.productMediaStore.create();
            productMedia.isLoading = true;
            productMedia.catalogId = this.product.catalogId;

            if (this.product.media.length === 0) {
                productMedia.position = 0;
                this.product.coverId = productMedia.id;
            } else {
                productMedia.position = this.product.media.length + 1;
            }

            productMedia.media = mediaEntity;
            productMedia.mediaId = mediaEntity.id;

            delete mediaEntity.user;
            return productMedia;
        },

        addImageToPreview(sourceURL, productMedia) {
            const canvas = document.createElement('canvas');
            const columnWidth = this.columnWidth.split('px')[0];
            const size = this.isCover(productMedia) ? columnWidth * 2 : columnWidth;
            const img = new Image();
            img.onload = () => {
                // resize image with aspect ratio
                const dimensions = this.getImageDimensions(img, size);
                canvas.setAttribute('width', dimensions.width);
                canvas.setAttribute('height', dimensions.height);
                const ctx = canvas.getContext('2d');
                ctx.drawImage(
                    img, 0, 0, canvas.width, canvas.height
                );

                this.previews[productMedia.mediaId] = canvas.toDataURL();
                productMedia.isLoading = false;

                this.$forceUpdate();
            };
            img.src = sourceURL;
        },

        successfulUpload(mediaEntity) {
            const productMedia = find(this.mediaItems, (e) => e.mediaId === mediaEntity.id);
            this.productMediaStore.getByIdAsync(productMedia.id).then(() => {
                // just refresh
                this.unsavedEntities = [];
            });
        },

        getImageDimensions(img, size) {
            if (img.width > img.height) {
                return {
                    height: size,
                    width: size * (img.width / img.height)
                };
            }

            return {
                width: size,
                height: size * (img.height / img.width)
            };
        },

        getPreviewForMedia(mediaEntity) {
            if (mediaEntity.isPlaceholder) {
                return '';
            }

            if (mediaEntity.isLocal) {
                return mediaEntity.id in this.previews ? this.previews[mediaEntity.id] : '';
            }
            return mediaEntity.url;
        },

        getTooltipForMedia(mediaEntity) {
            if (mediaEntity.isPlaceholder) {
                return '';
            }

            if (mediaEntity.isLocal) {
                return mediaEntity.fileName;
            }

            return this.mediaNameFilter(mediaEntity);
        },

        removeFile(key) {
            const item = find(this.mediaItems, (e) => e.mediaId === key);

            this.product.media = this.product.media.filter((e) => e.mediaId !== key);
            if (this.isCover(item)) {
                if (this.product.media.length === 0) {
                    this.product.coverId = null;
                } else {
                    this.product.coverId = this.product.media[0].id;
                }
            }

            item.delete();
        },

        isCover(productMedia) {
            if (productMedia.isPlaceholder) {
                return productMedia.isCover;
            }

            if (this.product.coverId === null) {
                this.product.coverId = productMedia.id;
            }

            return this.product.coverId === productMedia.id;
        },

        markMediaAsCover(productMedia) {
            this.product.coverId = productMedia.id;
        }
    }
});
