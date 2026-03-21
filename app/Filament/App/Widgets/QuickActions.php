<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\Expenses\ExpenseResource;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected string $view = 'filament.app.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    /**
     * Helper pour obtenir l'URL de création
     */
    public function getCreateUrl(): string
    {
        return ExpenseResource::getUrl('create');
    }
}
