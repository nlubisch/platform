[wikiUrl]: <>(../best-practices-and-conventions?category=shopware-platform-en/administration/getting-started)

# Best practices and conventions
This guide contains best practices and conventions for building an administration extension.

## Component conventions
 - Each component should be as slim as possible.
 - The component stands for its own and fulfills only one purpose. It is better to make multiple smaller components than putting too much complexity into a single component.
 - The component contains not much logic. Complex logic is handled by the modules.
   
### Properties order
The properties of a component should have the following order:

1. Template
2. Inject Services / Providers
3. Mixins
4. Pre-Hooks
5. Props
6. Data
7. Computed
8. Watch
9. [Lifecycle methods](https://vuejs.org/v2/guide/instance.html#Lifecycle-Diagram)
   - 1 beforeCreate
   - 2 created
   - 3 beforeMount
   - 4 mounted
   - 5 beforeUpdate
   - 6 updated
   - 7 before Destroy
   - 8 destroyed
10. Methods

The following is a full example of a component following the convention:

```
// sw-example/index.js

import { Component, Mixin } from 'src/core/shopware';
import template from './sw-example.html.twig';
import './sw-example.less';

Component.register('sw-example', {
    template,
    
    inject: ['exampleService'],

    mixins: [
        Mixin.getByName('example')
    ],
    
    beforeRouteEnter(param) {
        // beforeRouteEnter functionality
    }
    
    props: {
        exampleProp: {
            type: Array,
            required: false,
            default: []
        }
    },

    data() {
        return {
            isExample: true
        }
    },

    computed: {
        example() {
            return this.exampleProp;
        }
    },

    watch: {
        // Watchers
    }
    
    beforeCreate() {
        this.beforeCreateComponent();
    }
    
    // ... all other lifecycle hooks before methods
    
    methods: {
        beforeCreateComponent() {
            // beforeCreate functionality
        }
    }
});
```
*Example of a full component*

### Lifecycle hooks

Instead of using the [Vue.js' lifecycle hooks directly](https://vuejs.org/v2/guide/instance.html#Lifecycle-Diagram), the desired functionality should be placed inside a separate method.
This method is named like the lifecycle hook and has an additional `Component` at the end of the method name.

```
// sw-example/index.js

// ...

mounted() {
    this.mountedComponent();
}

beforeUpdate() {
    this.beforeUpdateComponent();
}

methods: {
    mountedComponent() {
        // mounted functionality
    }
    
    beforeUpdateComponent() {
        // beforeUpdate functionality
    }
}

// ...
```
*Lifecycle hooks example*

### Variants
Some components provide different variants or versions of itself. For example the `<sw-button>` component comes with different variants like `primary` or `ghost`. When there are more than two variants of a component this could be handled by a single property like `variant` or `size`. 

Using multiple boolean properties which do kind of the same thing should be avoided when possible. Whether you want to use booleans or something like `variant` depends on the use case.
When you want combine multiple variants with each other, booleans may be the better choice.

```
// template.html.twig

<!-- Default button (no variant) -->
<sw-button>Button text</sw-button>

<!-- Primary button -->
<sw-button variant="primary">Button text</sw-button>

<!-- Ghost button with size large -->
<sw-button variant="ghost" size="large">Button text</sw-button>
```
*Component variants example*

When a certain behavior should be active or inactive, a boolean property is of course the right way:

```
// template.html.twig

<!-- Button with isLoading flag -->
<sw-button isLoading>Button text</sw-button>
```
*Component boolean example*

### Class and style bindings

When creating a default component like `sw-card` or `sw-button` the class and style bindings should not be directly in the template. This pattern can often be found in the Vue.js documentation or different tutorials.

Instead there should be a computed property which contains all logic for toggling classes or handle inline styles. The computed prop should be named after the component in camel-case with the word "Classes" or "Styles" as a suffix - depending if there should be CSS classes or inline styles.

```
// example.js

cardClasses() {
    return {
        'sw-card--slim': this.slim,
        'sw-card--dark': this.dark,
        [`sw-card--${this.variant}`]: this.variant,
    };
}
```
*Computed classes property*

Now the prop can be bound in the template.
```
// template.html.twig

<div class="sw-card" :class="cardClasses" :style="cardStyles">
```
*Style classes in the template*

This makes it easier for other developers to override CSS classes or inline styles because
no root level Twig blocks have to be overridden.



## BEM

Because the administration is a component based application with reusable elements, the CSS structure is also component-driven. The Markup and CSS of the administration is using BEM as a naming convention. 

* BEM stands for "Block Element Modifier".
* In our case "Block" would be equal to the root element of a Vue component.
* "Element" describes the elements which are **inside** the component.
* "Modifier" is an additional class which can adjust the styling.
* Further reading: [getbem.com](http://getbem.com/)



```
// css file

/* Block component */
.sw-card {}

/* Element that depends upon the block */
.sw-card__body {}

/* Modifier that changes the style of the block (permanently, static) */
.sw-card--danger {}
.sw-card--large {}

/* State (temporarily, dynamic)  */
.is--selected {}
.is--active {}
```
*CSS example*



```
// template.html.twig

<div class="sw-card sw-card--large">
  <div class="sw-card__header">
    <h4 class="sw-card__title">
      Card Title
    </h4>
  </div>
  <div class="sw-card__body">
    Lorem ipsum dolor sit amet
    <div class="sw-card__divider sw-card__divider--primary"></div>
    Lorem ipsum dolor sit amet
  </div>
  <div class="sw-card__footer">
    Card Footer
  </div>
</div>
```
*Markup example for a component*

All CSS sub-classes rely on the root element of the component &ndash; even when they are nested further inside the markup. In the above example the root element is `sw-card`. The nested `<h4>` element `sw-card__title` relies on `sw-card` and not on `header`. This approach is recommended in the [BEM documentation](http://getbem.com/faq/#css-nested-elements).

## LESS variables
The naming scheme for less variables should also follow some conventions for easier understanding.

### General variable naming convention
- Each variable should be prefixed with its purpose in a meaningful way  
  Example: Variables which contain HEX or rgba values should begin with `@color- ...`.
- All variables should be kebab-case. Please avoid using camelCase or snake_case when possible.

```
// less file 

@color-primary:            #f00;
@z-index-dialog:           9000;
@border-radius-default:    6px;
@color-box-shadow-default: rgba(0, 0, 0, 0.2);
@width-content:            1200px;
@size-avatar-default:      50px;
@font-family-default:      'Source Sans Pro', Arial, sans-serif;

```
*Less variables naming example*

### Global variables
- In addition to the components own LESS files, the administration is also offering global variables to provide an easy way to develop styling.
- The global color variables match the color names in our design guidelines.

```
// less file 

// Primary
@color-shopware-blue:     #189EFF;
@color-biscay:            #16325C;
@color-deep-cove:         #303A4F;
@color-crimson:           #DE294C;
@color-pumpkin-spice:     #FFB75D;

// Neutrals
@color-kashmir:           #54698D;
@color-iron:              #FAFBFC;
@color-cadet-blue:        #E8F6FF;
...
```
*Global less variables*

- The global variables are used inside the components but will be assigned/re-mapped to component specific variable names. For example, the variable `@color-shopware-blue` could be used for a border color or a background color inside a component.
- As a result, the colors and other styling can be adjusted for each component individually.
- The component specific variables are declared at the top of the components LESS file.
- They should begin with the component name like `@sw-button-color-background`.

```
// less file

@sw-button-primary-color-background: @color-shopware-blue;
@sw-button-primary-color-text:       @color-iron;
@sw-button-border-radius:            @border-radius-default;

.sw-button {
  border-radius: @sw-button-border-radius;
  
  &.sw-button--primary {
    color: @sw-button-primary-color-text;
    background-color: @sw-button-primary-color-background;
  }  
}
```
*Button component less variables example*

## LESS structure for nested components

Sometimes a component needs different styling when it is nested inside a parent component. In this case the parent component is defining the LESS for the child component.

```
// less file

.sw-card {
    border: 1px solid @sw-card-color-border;
    padding: 40px;
    
    .sw-tabs {
        // Special stying when sw-tabs item is inside a card
    }
}
```
*Nested component less variables example*

## Twig blocks

- The core components contain twig blocks to provide the possibility to extend or override the components.
- The root block wraps the component and has the component name: `{% block sw_component %}`
- All block names of a component have the component name as a prefix.
- The `<slot>` element has an inner block: `{% block sw_component_slot_default %}`
- If there are multiple slots, they should be named after the slot name: `{% block sw_component_slot_message %}`


```
// template.html.twig

{% block sw_alert %}
    <div class="sw-alert" :class="alertClasses">
        {% block sw_alert_inner %}
            {% block sw_alert_icon %}
                <sw-icon :name="alertIcon" decorative />
            {% endblock %}

            {% block sw_alert_title %}
                <div v-if="title" class="sw-alert__title">{{ title }}</div>
            {% endblock %}

            {% block sw_alert_message %}
                <div class="sw-alert__message">
                    <slot>{% block sw_alert_slot_default %}{% endblock %}</slot>
                </div>
            {% endblock %}
        {% endblock %}
    </div>
{% endblock %}
```
*Component twig blocks example*