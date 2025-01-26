<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    public function getHeading(): string
    {
        return "";
    }

    public function getHeader(): ?View
    {
        return view('components.upgrade');
    }
}
