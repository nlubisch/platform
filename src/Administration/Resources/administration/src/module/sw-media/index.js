import { Module } from 'src/core/shopware';
import './mixin/media-grid-listener.mixin';
import './page/sw-media-index';
import './component/sw-media-grid';
import './component/sidebar/sw-media-sidebar';
import './component/sidebar/sw-media-quickinfo-metadata-item';
import './component/sidebar/sw-media-quickinfo-usage';
import './component/sw-media-collapse';
import './component/sidebar/sw-media-quickinfo';
import './component/sidebar/sw-media-quickinfo-multiple';

Module.register('sw-media', {
    type: 'core',
    name: 'Media',
    description: 'sw-media.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#FFD700',
    icon: 'default-object-image',

    routes: {
        index: {
            components: {
                default: 'sw-media-index'
            },
            path: 'index/:folderId?',
            props: {
                default: (route) => {
                    return {
                        routeFolderId: route.params.folderId
                    };
                }
            }
        }
    },

    navigation: [{
        id: 'sw-media',
        label: 'sw-media.general.mainMenuItemGeneral',
        color: '#FFD700',
        icon: 'default-object-image',
        path: 'sw.media.index',
        position: 40
    }]
});
