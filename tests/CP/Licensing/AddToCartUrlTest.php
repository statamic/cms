<?php

namespace Tests\CP\Licensing;

use Facades\Statamic\Marketplace\Marketplace;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Controllers\CP\LicensingController;
use Tests\TestCase;

class AddToCartUrlTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_everything_is_licensed()
    {
        $statamic = new class
        {
            public function valid()
            {
                return true;
            }
        };

        $this->assertNull($this->controller()->addToCartUrl(null, $statamic, collect()));
    }

    #[Test]
    public function it_returns_a_url_when_statamic_is_unlicensed()
    {
        $site = new class
        {
            public function key()
            {
                return 'site_abcdefghijklmnopqrstuvwxyz';
            }
        };

        $statamic = new class
        {
            public function valid()
            {
                return false;
            }
        };

        $url = $this->controller()->addToCartUrl($site, $statamic, collect());

        $this->assertStringContainsString('cart/bulk-add', $url);
        $this->assertStringContainsString('statamic=1', $url);
        $this->assertStringContainsString('site_abcdefghijklmnopqrstuvwxyz', $url);
    }

    #[Test]
    public function it_returns_a_url_when_an_addon_is_unlicensed()
    {
        $site = new class
        {
            public function key()
            {
                return 'site_abcdefghijklmnopqrstuvwxyz';
            }
        };

        $statamic = new class
        {
            public function valid()
            {
                return true;
            }
        };

        $addon = new class
        {
            public function valid()
            {
                return false;
            }

            public function edition()
            {
                return 'pro';
            }

            public function addon()
            {
                return new class
                {
                    public function id()
                    {
                        return 'statamic/seo-pro';
                    }

                    public function marketplaceId()
                    {
                        return 'statamic/seo-pro';
                    }

                    public function edition()
                    {
                        return 'pro';
                    }
                };
            }
        };

        $url = $this->controller()->addToCartUrl($site, $statamic, collect([$addon]));

        $this->assertStringContainsString('statamic/seo-pro:pro', urldecode($url));
    }

    #[Test]
    public function purchase_is_null_when_everything_is_licensed()
    {
        $statamic = new class
        {
            public function valid()
            {
                return true;
            }
        };

        $this->assertNull($this->controller()->purchase(null, $statamic, collect()));
    }

    #[Test]
    public function purchase_labels_statamic_pro_when_only_core_is_unlicensed()
    {
        $purchase = $this->controller()->purchase($this->site(), $this->statamic(valid: false), collect());

        $this->assertEquals('Buy Statamic Pro', $purchase['label']);
        $this->assertEquals('Statamic Pro', $purchase['items'][0]['name']);
        $this->assertEquals('Per site. First year, then $99/year to renew.', $purchase['items'][0]['detail']);
        $this->assertNull($purchase['items'][0]['price']);
        $this->assertStringContainsString('cart/bulk-add', $purchase['checkoutUrl']);
    }

    #[Test]
    public function purchase_uses_site_name_from_statamic_com_when_present()
    {
        $purchase = $this->controller()->purchase(
            $this->site(name: 'Wayne Enterprises'),
            $this->statamic(valid: false),
            collect()
        );

        $this->assertEquals('Wayne Enterprises', $purchase['items'][0]['detail']);
    }

    #[Test]
    public function purchase_labels_addon_licenses_when_only_addons_are_unlicensed()
    {
        $purchase = $this->controller()->purchase($this->site(name: 'Wayne Enterprises'), $this->statamic(), collect([$this->unlicensedAddon()]));

        $this->assertEquals('Buy Addon Licenses', $purchase['label']);
        $this->assertEquals('SEO Pro', $purchase['items'][0]['name']);
        $this->assertNull($purchase['items'][0]['price']);
        $this->assertEquals('These commercial addons need a license for Wayne Enterprises.', $purchase['description']);
    }

    #[Test]
    public function purchase_includes_marketplace_price_and_thumbnail()
    {
        Marketplace::shouldReceive('packages')
            ->once()
            ->andReturn(collect([
                'statamic/seo-pro' => [
                    'price' => 55,
                    'thumbnail' => 'https://statamic.com/img/seo-pro.png',
                    'seller_name' => 'Statamic',
                ],
            ]));

        $purchase = $this->controller()->purchase($this->site(), $this->statamic(), collect([$this->unlicensedAddon()]));

        $this->assertEquals('$55', $purchase['items'][0]['price']);
        $this->assertEquals('https://statamic.com/img/seo-pro.png', $purchase['items'][0]['thumbnail']);
        $this->assertEquals('Statamic', $purchase['items'][0]['detail']);
    }

    #[Test]
    public function purchase_uses_seller_slug_when_seller_name_is_missing()
    {
        Marketplace::shouldReceive('packages')
            ->once()
            ->andReturn(collect([
                'statamic/seo-pro' => [
                    'seller' => 'statamic',
                ],
            ]));

        $purchase = $this->controller()->purchase($this->site(), $this->statamic(), collect([$this->unlicensedAddon(developer: null)]));

        $this->assertEquals('Statamic', $purchase['items'][0]['detail']);
    }

    #[Test]
    public function purchase_labels_licenses_when_core_and_addons_are_unlicensed()
    {
        $purchase = $this->controller()->purchase($this->site(), $this->statamic(valid: false), collect([$this->unlicensedAddon()]));

        $this->assertEquals('Buy Licenses', $purchase['label']);
        $this->assertCount(2, $purchase['items']);
    }

    #[Test]
    public function purchase_is_null_when_the_site_is_not_connected()
    {
        $this->assertNull($this->controller()->purchase(
            $this->site(connected: false),
            $this->statamic(valid: false),
            collect([$this->unlicensedAddon()])
        ));
    }

    #[Test]
    public function purchase_labels_renew_when_statamic_needs_renewal()
    {
        $purchase = $this->controller()->purchase(
            $this->site(),
            $this->statamic(valid: false, needsRenewal: true),
            collect()
        );

        $this->assertEquals('Renew License', $purchase['label']);
        $this->assertEquals('Your Statamic Pro license does not cover this version. Renew to keep using it, or downgrade to a version in range.', $purchase['description']);
    }

    private function site(?string $name = null, bool $connected = true): object
    {
        return new class($name, $connected)
        {
            public function __construct(private ?string $name, private bool $connected)
            {
            }

            public function key()
            {
                return 'site_abcdefghijklmnopqrstuvwxyz';
            }

            public function name()
            {
                return $this->name;
            }

            public function isConnected()
            {
                return $this->connected;
            }
        };
    }

    private function statamic(bool $valid = true, bool $needsRenewal = false): object
    {
        return new class($valid, $needsRenewal)
        {
            public function __construct(private bool $valid, private bool $needsRenewal)
            {
            }

            public function valid()
            {
                return $this->valid;
            }

            public function needsRenewal()
            {
                return $this->needsRenewal;
            }
        };
    }

    private function unlicensedAddon(?string $developer = 'Statamic'): object
    {
        return new class($developer)
        {
            public function __construct(private ?string $developer)
            {
            }

            public function valid()
            {
                return false;
            }

            public function name()
            {
                return 'SEO Pro';
            }

            public function version()
            {
                return '7.x-dev';
            }

            public function edition()
            {
                return 'pro';
            }

            public function addon()
            {
                $developer = $this->developer;

                return new class($developer)
                {
                    public function __construct(private ?string $developer)
                    {
                    }

                    public function id()
                    {
                        return 'statamic/seo-pro';
                    }

                    public function marketplaceId()
                    {
                        return 'statamic/seo-pro';
                    }

                    public function edition()
                    {
                        return 'pro';
                    }

                    public function marketplaceUrl()
                    {
                        return 'https://statamic.com/addons/statamic/seo-pro';
                    }

                    public function developer()
                    {
                        return $this->developer;
                    }
                };
            }
        };
    }

    private function controller(): LicensingController
    {
        return $this->app->make(LicensingController::class);
    }
}
