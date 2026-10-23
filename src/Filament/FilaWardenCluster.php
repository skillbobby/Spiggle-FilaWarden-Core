<?php

namespace Spiggle\FilaWarden\Filament;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;

class FilaWardenCluster extends Cluster
{
    protected static ?string $navigationLabel = 'FilaWarden';

    protected static ?string $title = 'FilaWarden';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $slug = 'filawarden';

    protected static ?int $navigationSort = 2;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    /**
     * Child pages already use a filawarden/ slug, so the cluster must not prefix routes again.
     */
    public static function prependClusterSlug(Panel $panel, string $slug): string
    {
        return $slug;
    }

    public static function prependClusterRouteBaseName(Panel $panel, string $name): string
    {
        return $name;
    }
}
