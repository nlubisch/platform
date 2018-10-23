module.exports = {
    '@tags': ['language-edit', 'language', 'edit'],
    'open language module': (browser) => {
        browser
            .assert.containsText('.sw-settings .collapsible-text', 'Settings')
            .click('.sw-admin-menu__navigation-link[href="#/sw/settings/index"]')
            .waitForElementVisible('.sw-settings-item[href="#/sw/settings/language/index"]')
            .click('.sw-settings-item[href="#/sw/settings/language/index"]');
    },
    'create new language': (browser) => {
        browser
            .click('a[href="#/sw/settings/language/create"]')
            .waitForElementVisible('.sw-settings-language-detail .sw-card__content')
            .assert.urlContains('#/sw/settings/language/create')
            .assert.containsText('.sw-card__title', 'Settings')
            .setValue('input[name=sw-field--language-name]', 'Philippine English english')
            .waitForElementNotPresent('.sw-field--language-localeId .sw-field__select-load-placeholder')
            .setValue('select[name=sw-field--language-localeId]', 'English, Philippines (en_PH)')
            .waitForElementNotPresent('.sw-field--language-parentId .sw-field__select-load-placeholder')
            .setValue('select[name=sw-field--language-parentId]', 'English')
            .waitForElementPresent('.sw-settings-language-detail__save-action')
            .click('.sw-settings-language-detail__save-action')
            .waitForElementVisible('.sw-notifications .sw-alert')
            .assert.containsText('.sw-alert__message', 'The language Philippine English english has been saved successfully.')
            .assert.urlContains('#/sw/settings/language/detail');
    },
    'go back to listing and verify creation': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementVisible('.sw-settings-language-list-grid')
            .click('.sw-alert button.sw-alert__close')
            .waitForElementNotPresent('.sw-alert__message')
            .waitForElementVisible('.sw-grid-row:last-child .sw-language-list__column-name')
            .assert.containsText('.sw-grid-row:last-child .sw-language-list__column-name', 'Philippine English english');
    },
    'edit language': (browser) => {
        browser
            .assert.containsText('.sw-grid-row:last-child .sw-language-list__column-name', 'Philippine English english')
            .click('.sw-grid-row:last-child .sw-context-button__button')
            .waitForElementPresent('body > .sw-context-menu')
            .click('body > .sw-context-menu .sw-language-list__edit-action')
            .waitForElementVisible('.sw-settings-language-detail .sw-card__content')
            .clearValue('input[name=sw-field--language-name]')
            .setValue('input[name=sw-field--language-name]', 'Very Philippine English english')
            .waitForElementPresent('.sw-settings-language-detail__save-action')
            .click('.sw-settings-language-detail__save-action')
            .waitForElementVisible('.sw-notifications .sw-alert')
            .assert.containsText('.sw-alert__message', 'The language Very Philippine English english has been saved successfully.')
            .assert.urlContains('#/sw/settings/language/detail');
    },
    'verify edited language': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementVisible('.sw-settings-language-list-grid')
            .click('.sw-alert button.sw-alert__close')
            .waitForElementNotPresent('.sw-alert__message')
            .waitForElementVisible('.sw-grid-row:last-child .sw-language-list__column-name')
            .assert.containsText('.sw-grid-row:last-child .sw-language-list__column-name', 'Very Philippine English english');
    },
    'delete language': (browser) => {
        browser
            .assert.containsText('.sw-grid-row:last-child .sw-language-list__column-name', 'Very Philippine English english')
            .click('.sw-grid-row:last-child .sw-context-button__button')
            .waitForElementPresent('body > .sw-context-menu')
            .click('body > .sw-context-menu .sw-context-menu-item--danger')
            .waitForElementVisible('.sw-modal')
            .assert.containsText('.sw-modal .sw-modal__body', 'Are you sure, you want to delete the language Very Philippine English english?')
            .click('.sw-modal__footer button.sw-button--primary')
            .waitForElementVisible('.sw-notifications .sw-alert')
            .assert.containsText('.sw-notifications .sw-alert', 'The language Very Philippine English english has been deleted successfully.');
    },
    after: (browser) => {
        browser.end();
    }
};