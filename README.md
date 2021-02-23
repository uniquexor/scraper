# Scraper
This is a helper component, to ease the creation of custom website scrapers.
It implements some basic logic of iterating the listing pages and downloading the items.
In order to use this, you must first implement your own ItemListDownloader (by extending AbstractItemListDownloader) and ItemDownloader,
(by extending AbstractItemDownloader or AbstractJsonItemDownloader) for your particular website.

## Installation
This package requires `php >= 7.4`. To install the component, use composer:
```
composer require unique/scraper
```

## Usage
In order to use this, you must first implement your own ItemListDownloader and ItemDownloader for your particular website.  
Since most of scraping uses (at least my uses) consist of iterating a list and scraping items from it.  
Maybe one day, as the need arises, I will expand it, but for now the scraper uses the same approch.

So, let's assume we have an ad website, that has a list of ads. The listing is divided in to however many pages and each page has 20 ads. We need to scrape all the ads.

We first create a class, that will represent our scraped Ad. It must implement `SiteItemInterface`.
```php
    class SiteItem implements \unique\scraper\interfaces\SiteItemInterface {
        
        protected $id;
        protected $url;
        protected $title;
        
        // @todo: implement setter and getters for $id, $url, $title
    }
```

We then implement ItemListDownloader:

```php
    class ItemListDownloader extends \unique\scraper\AbstractItemListDownloader {
        
        protected function getNumberOfItemsInPage( \Symfony\Component\DomCrawler\Crawler $doc ): ?int {

            // Or we could implement some logic of checking the website for the actual number.
            return 20;
        }

        protected function hasNextPage( \Symfony\Component\DomCrawler\Crawler $doc, int $current_page_num ): bool {

            // We could implement some logic of checking the page's paginator,
            // or we can just return true and let the scraper go through all of the listing
            // pages until it finds one, that has no items in it. It will then stop automatically.
            
            return true;
        }

        function getListUrl( ?int $page_num ): string {

            return 'https://some.website.here/?page_num=' . $page_num;
        }

        function getTotalItems( \Symfony\Component\DomCrawler\Crawler $doc ): ?int {

            // If possible, we could find the total number of items (that's in all of the listing pages)
            return null;
        }

        function getItems( \Symfony\Component\DomCrawler\Crawler $doc ): iterable {

            // We define a selector, where each item will be a unique ad.
            // The scraper will iterate these items and get all of them.
            // It doesn't need to be <a> tag, you define your own logic of how to get
            // to the actual item page.
            
            return $doc->filter( 'a.ad-item' );
        }

        function getItemUrl( \DOMElement $item ): ?string {

            // Here, $item is the item from the getItems() method,
            // we analyze it and return the url for scraping the item itself.
            return $item->getAttribute( 'href' );
        }

        function getItemId( string $url, \DOMElement $item ): string {

            // We return some string by which we can uniquely identify the ad.
            // This can later be used to skip the ads, that we already have in DB, for example.
            return $item->getAttribute( 'data-id' );
        }

        function getItemDownloader( string $url, string $id ): ?AbstractItemDownloader {

            return new ItemDownloader( 'https://some.website.here/' . $url, $id, $this, new SiteItem() );
        }
    }
```

Then we create a downloader for the ad itself:

```php
    class ItemDownloader extends \unique\scraper\AbstractItemDownloader {
        
        protected function assignItemData( \Symfony\Component\DomCrawler\Crawler $doc ) {

            // We set all the attributes we need for our custom SiteItem object,
            // which can be accessed by the $this->item attribute.
            $this->item->setTitle( $doc->filter( 'h1' )->text() );
        }
    }
```

Or you could extend AbstractJsonItemDownloader if ad data was fetched via json.

```php
    class ItemDownloader extends \unique\scraper\AbstractJsonItemDownloader {

        protected function assignItemData( array $json ) {

            // We set all the attributes we need for our custom SiteItem object,
            // which can be accessed by the $this->item attribute.
            $this->item->setTitle( $json['title'] );
        }
    }
```

So that takes care of scraping. All that's left now, is to create a for example command script,  
that initiates the scraping.

```php
    class ScraperController implements \unique\scraper\interfaces\ConsoleInterface {
        
        // @todo implement stdOut() and stdErr() methods for logging.
        
        public function actionRun() {
            
            $transport = new GuzzleHttp\Client();
            $log_container = new LogContainerConsole( $this );
            $downloader = new ItemListDownloader( SiteItem::class, $transport, $log_container );

            $downloader->on( \unique\scraper\AbstractItemListDownloader::EVENT_ON_ITEM_END, function ( \unique\scraper\events\ItemEndEvent $event ) {
                
                if ( $event->site_item ) {

                    $event->site_item->save();
                }
            } );

            $downloader->scrape();
        }
    }
```

You can use the optional `LogContainerConsole` for logging stuff to the console, using two methods:
stdOut() and stdErr(), which you need to implement yourself.

## Documentation

### Events
You can subscribe to various events triggered by the `AbstractItemListDownloader`, by using
`on( string $event_name, callable $handler )` method. Each handler will receive an `EventObject`,
which depends on the event type:
#### `on_list_begin`
The event object will be `ListBeginEvent`. This is a "breakable" event (read on to find out more).
Methods:
- `getPageNum(): int` returns the page number.

#### `on_list_end`
The event object will be `ListEndEvent`.
Methods:
- `getItemCount(): ItemCount` returns information about page number, size and total amount of items.
- `willContinue(): bool` returns true, if the scraper will continue to the next page.

#### `on_item_begin`
The event object will be `ItemBeginEvent`. This is a "breakable" event (read on to find out more).
Methods:
- `getId(): string` returns the id of the item.
- `getUrl(): string` returns url of the item.
- `getDomElement(): \DOMElement` returns the corresponding \DOMElement.

#### `on_item_end`
The event object will be `ItemEndEvent`.
Methods:
- `getItemCount(): ItemCount` returns information about page number, size and total amount of items.
- `getState(): int` One of the state constants found in `AbstractItemListDownloader::STATE_*`.
- `getSiteItem(): ?SiteItemInterface` If no errors where found, provides data for item, that was scraped.
- `getDomElement(): \DOMElement` returns the corresponding \DOMElement.

#### `on_item_missing_url`
The event object will be `ItemMissingUrlEvent`.
Methods:
- `getUrl(): ?string` returns url of the item.
- `setUrl( ?string $url )` Allows for a handler to set a new url.
- `getDomElement(): \DOMElement` returns the corresponding \DOMElement.

#### `on_break_list`
The event object will be `BreakListEvent`.
Methods:
- `getCausingEvent(): ?EventObjectInterface` returns the event object that instructed to break scraping of the list.

#### Breakable events
These are events that implement BreakableEventInterface and can instruct the scraper to either abort processing of the item  
or to abort scraping of the entire list. In php's terms, these are `continue` and `break` on `while` cycles.  
So a breakable event object implements these methods:
- `shouldSkip(): bool` - Returns true, if the list item should be skiped.
- `shouldBreak(): bool` - Returns true, if the scraping of the list should abort.
- `continue()` - Instructs the scraper to proceed with the item.
- `skip()` - Instructs the scraper to skip the current item, but proceed with the list.
- `break()` - Instructs the scraper to abort the list and stop scraping.

## More Documentation

For more details on what each and every method does, check out the source code, it should
be pretty clearly documented.