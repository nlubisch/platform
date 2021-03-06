module.exports = {
    '@tags': ['product-create', 'product', 'create'],
    'create simple category to assign the product to it later ': (browser) => {
        browser
            .openMainMenuEntry('#/sw/catalog/index', 'Catalogues')
            .waitForElementPresent('.sw-catalog-list__intro')
            .waitForElementPresent('.sw-catalog-list__edit-action')
            .click('.sw-catalog-list__edit-action')
            .waitForElementPresent('input[name=sw-field--addCategoryName]')
            .getLocationInView('.sw-catalog-detail__categories')
            .fillField('input[name=sw-field--addCategoryName]', 'MainCategory')
            .click('.sw-catalog-detail__add-action')
            .waitForElementPresent('.sw-tree-item__label')
            .assert.containsText('.sw-tree-item__label', 'MainCategory')
            .click('.sw-button--primary')
            .waitForElementNotPresent('.sw-catalog-detail__properties .sw-card__content .sw-loader')
            .click('.sw-alert button.sw-alert__close')
            .waitForElementNotPresent('.sw-alert__message');
    },
    'open product listing': (browser) => {
        browser
            .assert.containsText('.sw-admin-menu__navigation-list-item.sw-product span.collapsible-text', 'Products')
            .click('a.sw-admin-menu__navigation-link[href="#/sw/product/index"]')
            .waitForElementVisible('.smart-bar__actions a')
            .waitForElementVisible('.sw-page__smart-bar-amount')
            .assert.containsText('.sw-page__smart-bar-amount', '(0)');
    },
    'go to create page, fill and save the new product': (browser) => {
        browser
            .click('a[href="#/sw/product/create"]')
            .waitForElementVisible('.sw-product-detail-base')
            .assert.urlContains('#/sw/product/create')
            .assert.containsText('.sw-card__title', 'Information')
            .fillField('input[name=sw-field--product-name]', 'Marci Darci')
            .fillField('.ql-editor', 'My very first description','editor')
            .waitForElementNotPresent('.sw-field--product-manufacturerId .sw-field__select-load-placeholder')
            .fillSelectField('select[name=sw-field--product-manufacturerId]', 'shopware AG')
            .waitForElementNotPresent('.sw-field--product-catalogId .sw-field__select-load-placeholder')
            .fillSelectField('select[name=sw-field--product-catalogId]', 'Default catalogue')
            .fillSwSelectComponent(
                '.sw-product-detail__select-category',
                {
                    value: 'MainCategory',
                    isMulti: true,
                    searchTerm: 'MainCategory'
                }
            )
            .waitForElementNotPresent('.sw-field--product-taxId .sw-field__select-load-placeholder')
            .fillSelectField('select[name=sw-field--product-taxId]', '19%')
            .fillField('input[name=sw-field--price-gross]', '99')
            .click('.sw-product-detail__save-action')
            .checkNotification('Product "Marci Darci" has been saved successfully')
            .assert.urlContains('#/sw/product/detail');
    },
    'go back to listing, search and verify creation': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementVisible('.sw-product-list__content')
            .fillGlobalSearchField('Marci Darci')
            .waitForElementVisible('.sw-page__smart-bar-amount')
            .assert.containsText('.sw-page__smart-bar-amount', '(1)');
    },
    'check if the data of the product is assigned correctly': (browser) => {
        browser
            .refresh()
            .waitForElementVisible('.sw-product-list__column-product-name')
            .assert.containsText('.sw-product-list__column-product-name', 'Marci Darci')
            .waitForElementPresent('.sw-product-list__column-manufacturer-name')
            .assert.containsText('.sw-product-list__column-manufacturer-name', 'shopware AG')
            .clickContextMenuItem('.sw_product_list__edit-action','.sw-context-button__button','.sw-grid-row:first-child')
            .waitForElementVisible('.sw-product-detail-base', 10000)
            .waitForElementPresent('.sw-product-category-form .sw-select__selection-text')
            .assert.containsText('.ql-editor', 'My very first description')
            .assert.containsText('.sw-product-category-form .sw-select__selection-text', 'MainCategory')
            .click('a.smart-bar__back-btn');
    },
    'delete created product and verify deletion': (browser) => {
        browser
            .clickContextMenuItem('.sw-context-menu-item--danger', '.sw-context-button__button','.sw-grid-row:first-child')
            .waitForElementVisible('.sw-modal')
            .assert.containsText('.sw-modal .sw-product-list__confirm-delete-text', 'Are you sure you really want to delete the product "Marci Darci"?')
            .click('.sw-modal__footer button.sw-button--primary')
            .waitForElementNotPresent('.sw-product-list__column-product-name')
            .waitForElementNotPresent('.sw-modal')
            .waitForElementPresent('.sw-empty-state__title')
            .assert.containsText('.sw-page__smart-bar-amount', '(0)');
    },
    'delete category': (browser) => {
        browser
            .click('a.sw-admin-menu__navigation-link[href="#/sw/catalog/index"]')
            .waitForElementPresent('.sw-catalog-list__intro')
            .waitForElementPresent('.sw-catalog-list__edit-action')
            .click('.sw-catalog-list__edit-action')
            .waitForElementPresent('input[name=sw-field--addCategoryName]')
            .getLocationInView('.sw-catalog-detail__categories')
            .clickContextMenuItem('.sw-context-menu-item--danger', '.sw-context-button__button')
            .waitForElementNotPresent('.sw-tree-item__label')
            .click('.sw-button--primary')
            .waitForElementNotPresent('.sw-catalog-detail__properties .sw-card__content .sw-loader');
    },
    after: (browser) => {
        browser.end();
    }
};
