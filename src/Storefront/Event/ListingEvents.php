<?php declare(strict_types=1);

namespace Shopware\Storefront\Event;

class ListingEvents
{
    /**
     * Dispatched as soon as the search has been executed and the data is assigned to the view
     *
     * @Event("Shopware\Storefront\Event\ListingPageLoadedEvent")
     */
    public const LOADED = ListingPageLoadedEvent::NAME;

    /**
     * Fired when a Criteria object is created for a product list in the storefront.
     *
     * @Event("Shopware\Storefront\Event\PageCriteriaCreatedEvent")
     */
    public const CRITERIA_CREATED = PageCriteriaCreatedEvent::NAME;

    /**
     * Fired when a listing page request comes in and transformed to the ListingRequest object
     *
     * @Event("Shopware\Storefront\Event\TransformListingPageRequestEvent")
     */
    public const REQUEST = ListingPageRequestEvent::NAME;
}
