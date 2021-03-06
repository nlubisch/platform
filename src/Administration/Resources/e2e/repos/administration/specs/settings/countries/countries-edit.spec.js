module.exports = {
    '@tags': ['setting', 'country-edit', 'country', 'edit'],
    'open country module': (browser) => {
        browser
            .openMainMenuEntry('#/sw/settings/index', 'Settings', '#/sw/settings/country/index', 'Countries');
    },
    'create new country': (browser) => {
        browser
            .click('a[href="#/sw/settings/country/create"]')
            .waitForElementVisible('.sw-settings-country-detail .sw-card__content')
            .assert.urlContains('#/sw/settings/country/create')
            .assert.containsText('.sw-card__title', 'Settings')
            .fillField('input[name=sw-field--country-name]', '1.Niemandsland')
            .waitForElementPresent('input[name=sw-field--country-active]')
            .tickCheckbox('input[name=sw-field--country-active]', 'on')
            .click('.sw-settings-country-detail__save-action')
            .checkNotification('Country "1.Niemandsland" has been saved successfully.')
            .assert.urlContains('#/sw/settings/country/detail');
    },
    'go back to listing and verify creation': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementNotPresent('.sw-loader')
            .waitForElementVisible('.sw-settings-country-list-grid')
            .waitForElementNotPresent('.sw-alert__message')
            .waitForElementVisible('.sw-country-list__column-name:first-child')
            .assert.containsText('.sw-country-list__column-name:first-child', '1.Niemandsland');
    },
    'edit country': (browser) => {
        browser
            .assert.containsText('.sw-country-list__column-name:first-child', '1.Niemandsland')
            .clickContextMenuItem('.sw-country-list__edit-action', '.sw-context-button__button','.sw-grid-row:first-child')
            .waitForElementVisible('.sw-settings-country-detail .sw-card__content')
            .assert.containsText('.sw-card__title', 'Settings')
            .fillField('input[name=sw-field--country-name]', '1.Niemandsland x2')
            .click('.sw-settings-country-detail__save-action')
            .checkNotification('Country "1.Niemandsland x2" has been saved successfully.')
            .assert.urlContains('#/sw/settings/country/detail');
    },
    'verify edited country': (browser) => {
        browser
            .click('a.smart-bar__back-btn')
            .waitForElementVisible('.sw-settings-country-list-grid')
            .waitForElementNotPresent('.sw-alert__message')
            .waitForElementVisible('.sw-country-list__column-name:first-child')
            .assert.containsText('.sw-country-list__column-name:first-child', '1.Niemandsland x2');
    },
    'delete country': (browser) => {
        browser
            .waitForElementVisible('.sw-country-list__column-name:first-child')
            .assert.containsText('.sw-country-list__column-name:first-child', '1.Niemandsland x2')
            .click('.sw-grid-row:first-child .sw-context-button__button')
            .waitForElementPresent('body > .sw-context-menu')
            .click('body > .sw-context-menu .sw-context-menu-item--danger')
            .waitForElementVisible('.sw-modal')
            .assert.containsText('.sw-modal .sw-modal__body', 'Are you sure you want to delete the country "1.Niemandsland x2"?')
            .click('.sw-modal__footer button.sw-button--primary')
            .checkNotification('1.Niemandsland x2');
    },
    after: (browser) => {
        browser.end();
    }
};
