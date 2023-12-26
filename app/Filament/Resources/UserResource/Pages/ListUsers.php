<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Core\Enums\Status;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'null' => ListRecords\Tab::make(__('enum.tls.status.label.all')),
            __('enum.tls.status.label.activated') => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', Status::Activated)),
            __('enum.tls.status.label.pending') => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', Status::Pending)),
            __('enum.tls.status.label.disabled') => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', Status::Disabled))
        ];
    }
}
