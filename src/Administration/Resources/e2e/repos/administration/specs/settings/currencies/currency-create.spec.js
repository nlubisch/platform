module.exports = {
    '@tags': ['setting','currency-create', 'currency','create'],
    'open currency module': (browser) => {
        browser
            .openMainMenuEntry('#/sw/settings/index', 'Settings', '#/sw/settings/currency/index', 'Currencies');
    },
    'create new currency (Yen)': (browser) => {
        browser
            .click('a[href="#/sw/settings/currency/create"]')
            .waitForElementVisible('.sw-settings-currency-detail .sw-card__content')
            .assert.urlContains('#/sw/settings/currency/create')
            .assert.containsText('.sw-card__title', 'Settings')
            .fillField('input[name=sw-field--currency-name]', 'Yen')
            .fillField('input[name=sw-field--currency-shortName]', 'JPY')
            .fillField('input[name=sw-field--currency-symbol]', '¥')
            .fillField('input[name=sw-field--currency-factor]', '1.0076')
            .waitForElementPresent('.sw-settings-currency-detail__save-action')
            .click('.sw-settings-currency-detail__save-action')
            .checkNotification('Currency "Yen" has been saved successfully.')
            .assert.urlContains('#/sw/settings/currency/detail');
    },
    'go back to listing and verify creation': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementVisible('.sw-settings-currency-list-grid')
            .waitForElementNotPresent('.sw-alert__message')
            .waitForElementVisible('.sw-grid-row:last-child .sw-currency-list__column-name')
            .assert.containsText('.sw-grid-row:last-child .sw-currency-list__column-name', 'Yen');
    },
    'delete currency': (browser) => {
        browser
            .waitForElementVisible('.sw-grid-row:last-child .sw-currency-list__column-name')
            .assert.containsText('.sw-grid-row:last-child .sw-currency-list__column-name', 'Yen')
            .clickContextMenuItem('.sw-context-menu-item--danger', '.sw-context-button__button','.sw-grid-row:last-child')
            .waitForElementVisible('.sw-modal')
            .assert.containsText('.sw-modal .sw-modal__body', 'Are you sure you want to delete the currency "Yen"?')
            .click('.sw-modal__footer button.sw-button--primary')
            .checkNotification('Currency "Yen" has been deleted successfully.');
    },
    after: (browser) => {
        browser.end();
    }
};
