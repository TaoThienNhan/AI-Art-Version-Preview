<?php

namespace App\Filament\Resources;

use App\Core\Enums\CategoryType;
use App\Core\Enums\Status;
use App\Core\Utils\GalleryUtils;
use App\Filament\Resources\GalleryResource\Pages;
use App\Filament\Resources\GalleryResource\RelationManagers;
use App\Models\Category;
use App\Models\Gallery;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use stdClass;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('helper.tls.section.title.information'))
                            ->description(__('helper.tls.section.description.information'))
                            ->icon(__('helper.tls.section.icon.information'))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('resource.tls.form.field.gallery.title'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    })
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->label(__('resource.tls.form.field.gallery.category_id'))
                                    ->options(
                                        Category::where('type', CategoryType::Galleries)
                                        ->where('status', Status::Activated)
                                        ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label(__('resource.tls.form.field.gallery.status'))
                                    ->options(Status::class)
                                    ->default(Status::Pending)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Hidden::make('user_id')
                                    ->default(Auth::id())
                                    ->required(),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('resource.tls.form.field.gallery.description'))
                                    ->columnSpanFull()
                                    ->required()
                            ])
                            ->collapsible()
                            ->columns(2),
                        Forms\Components\Section::make(__('helper.tls.section.title.gallery'))
                            ->description(__('helper.tls.section.description.gallery'))
                            ->icon(__('helper.tls.section.icon.gallery'))
                            ->schema([
                                CuratorPicker::make('image')
                                    ->label(__('resource.tls.form.field.gallery.image'))
                                    ->directory(config('galleries.directory', 'galleries'))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->preserveFilenames()
                                    ->required()
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('resource.tls.form.field.gallery.slug'))
                                    ->maxLength(255)
                                    ->dehydrated()
                                    ->helperText(__('helper.tls.text.slug'))
                                    ->unique(Gallery::class, 'slug', ignoreRecord: true)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                SpatieTagsInput::make('tags')
                                    ->label(__('resource.tls.form.field.gallery.tags'))
                                    ->required()
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('resource.tls.form.field.gallery.created_at'))
                                    ->content(fn (
                                        ?Gallery $record
                                    ): string => $record ? $record->created_at->diffForHumans() : '-'),
                                Placeholder::make('updated_at')
                                    ->label(__('resource.tls.form.field.gallery.updated_at'))
                                    ->content(fn (
                                        ?Gallery $record
                                    ): string => $record ? $record->updated_at->diffForHumans() : '-'),
                            ]),
                    ])
                    ->columnSpan(1)
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(__('#'))->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                    $livewire->getTablePage() - 1
                                ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('resource.tls.table.column.gallery.title'))
                    ->copyable()
                    ->searchable(),
                CuratorColumn::make('image')
                    ->label(__('resource.tls.table.column.gallery.image'))
                    ->size(40),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('resource.tls.table.column.gallery.category_id')),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('resource.tls.table.column.gallery.user_id')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('resource.tls.table.column.gallery.status'))
                    ->searchable()
                    ->sortable(),
                SpatieTagsColumn::make('tags')
                    ->label(__('resource.tls.table.column.gallery.tags')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resource.tls.table.column.gallery.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('resource.tls.table.column.gallery.updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return GalleryUtils::isResourceNavigationGroupEnabled()
            ? __('navigation.tls.navigationGroup.gallery')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.tls.navigationLabel.gallery');
    }

    public static function getNavigationIcon(): string
    {
        return __('navigation.tls.navigationIcon.gallery');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.tls.modelLabel.gallery');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.tls.pluralModelLabel.gallery');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return GalleryUtils::isResourceNavigationRegistered();
    }

    public static function getNavigationSort(): ?int
    {
        return GalleryUtils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return GalleryUtils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return GalleryUtils::isResourceNavigationBadgeEnabled()
            ? static::getModel()::count()
            : null;
    }

    public static function canGloballySearch(): bool
    {
        return GalleryUtils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }
}
