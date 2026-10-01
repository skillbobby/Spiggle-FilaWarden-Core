<?php

namespace Spiggle\FilaWarden\Filament\Widgets;

use Filament\Widgets\Widget;

class ProUpgradeBannerWidget extends Widget
{
    protected string $view = 'filawarden::widgets.pro-upgrade-banner';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'upgradeUrl' => config('filawarden.pro_upgrade_url', 'https://spiggle.dev/filawarden-pro'),
        ];
    }
}
