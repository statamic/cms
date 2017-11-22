<?php

namespace Statamic\Http\Controllers;

use Statamic\Outpost;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Addon;
use Statamic\API\Config;
use Illuminate\Http\Request;
use Statamic\Extend\Management\AddonRepository;

class LicensingController extends CpController
{
    /**
     * @var Outpost
     */
    private $outpost;

    /**
     * @var bool
     */
    private $statamicValid = true;

    /**
     * @var AddonRepository
     */
    private $addonRepo;

    /**
     * @var array
     */
    private $items;

    public function index(Outpost $outpost, AddonRepository $addonRepo)
    {
        $this->outpost = $outpost;
        $this->addonRepo = $addonRepo;

        $messages = [];

        if ($message = $this->statamicLicenseMessage()) {
            $this->statamicValid = false;
            $messages[] = $message;
        }

        if ($message = $this->addonLicenseMessage()) {
            $messages[] = $message;
        }

        $this->items = [
            ['id' => 'statamic', 'name' => 'Statamic', 'valid' => $this->statamicValid, 'key' => $this->outpost->getLicenseKey()]
        ];

        $this->listAddons();

        return view('licensing.index', [
            'messages' => $messages,
            'items' => $this->items
        ]);
    }

    private function statamicLicenseMessage()
    {
        if (! $this->outpost->hasSuccessfulResponse()) {
            return t('couldnt_connect_to_outpost');
        }

        // Trial mode, and they've already put in their license key. They're ahead of the game. Awesome.
        if ($this->outpost->isTrialMode() && $this->outpost->hasLicenseKey() && $this->outpost->isLicenseValid()) {
            return;
        }

        if ($this->outpost->isTrialMode() && $this->outpost->hasLicenseKey() && !$this->outpost->isLicenseValid()) {
            return t('on_trial_with_invalid_license');
        }

        if ($this->outpost->isTrialMode() && !$this->outpost->hasLicenseKey()) {
            return t('on_trial_without_license');
        }

        if (! $this->outpost->isLicenseValid()) {
            return t('invalid_statamic_license');
        }

        if (! $this->outpost->isOnCorrectDomain()) {
            $url = 'https://account.statamic.com/licenses';
            if ($domain = $this->outpost->getLicenseDomain()) {
                return t('license_wrong_domain', compact('domain', 'url'));
            } else {
                return t('license_no_domain', compact('url'));
            }
        }
    }

    private function addonLicenseMessage()
    {
        if (! $this->outpost->areAddonLicensesValid()) {
            $this->valid = false;
            return 'You have unlicensed addons installed.';
        }
    }

    private function listAddons()
    {
        $items = $this->addonRepo->thirdParty()->addons()->map(function ($addon) {
             return [
                 'id' => $addon->id(),
                 'name' => $addon->name(),
                 'key' => $addon->licenseKey(),
                 'valid' => $this->outpost->isAddonLicenseValid($addon),
             ];
        });

        $this->items = array_merge($this->items, $items->all());
    }

    public function refresh(Outpost $outpost)
    {
        $outpost->clearCachedResponse();

        return redirect()->route('licensing');
    }

    public function update(Outpost $outpost, Request $request)
    {
        $this->setStatamicLicenseKey($request->statamic);

        $this->setAddonLicenseKeys($request->except(['_token', 'statamic']));

        $outpost->clearCachedResponse();

        return back()->with('success', t('settings_updated'));
    }

    private function setStatamicLicenseKey($key)
    {
        Config::set('system.license_key', $key);
        Config::save();
    }

    private function setAddonLicenseKeys($keys)
    {
        foreach ($keys as $addon => $licenseKey) {
            $addon = Addon::create($addon);

            $config = $addon->config();
            $config['license_key'] = $licenseKey;

            File::put(settings_path('addons/'.$addon->handle().'.yaml'), YAML::dump($config));
        }
    }
}
