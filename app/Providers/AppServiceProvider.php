<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\DiscountRequest;
use App\Models\Product;
use App\Observers\AddressObserver;
use App\Observers\CategoryObserver;
use App\Observers\ClientObserver;
use App\Observers\DealObserver;
use App\Observers\DiscountRequestObserver;
use App\Observers\NoteObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->hasHeader('x-forwarded-host')) {
            $host = request()->header('x-forwarded-host');
            $proto = request()->header('x-forwarded-proto', 'https');
            URL::forceRootUrl("{$proto}://{$host}");
            URL::forceScheme($proto);
        } else {
            URL::forceRootUrl(request()->schemeAndHttpHost());
            URL::forceScheme(request()->isSecure() ? 'https' : 'http');
        }

        Category::observe(CategoryObserver::class);
        Client::observe(ClientObserver::class);
        Product::observe(ProductObserver::class);
        Deal::observe(DealObserver::class);
        DealNote::observe(NoteObserver::class);
        Address::observe(AddressObserver::class);
        DiscountRequest::observe(DiscountRequestObserver::class);
    }
}
