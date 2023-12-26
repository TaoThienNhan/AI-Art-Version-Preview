<?php

namespace App\Filament\Resources;

use App\Core\Enums\CategoryType;
use App\Core\Enums\Status;
use App\Core\Utils\CategoryUtils;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use stdClass;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resource.tls.form.field.category.name'))
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
                        Forms\Components\Select::make('type')
                            ->label(__('resource.tls.form.field.category.type'))
                            ->options(CategoryType::class)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('parent')
                            ->label(__('resource.tls.form.field.category.parent'))
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\RichEditor::make('description')
                            ->label(__('resource.tls.form.field.category.description'))
                            ->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('resource.tls.form.field.category.slug'))
                                    ->maxLength(255)
                                    ->dehydrated()
                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                    ->helperText(__('helper.tls.text.slug'))
                                    ->required(),
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Hidden::make('owner')
                                    ->label(__('resource.tls.form.field.category.owner'))
                                    ->default(function () {
                                        return Auth::id();
                                    })
                                    ->required(),
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label(__('resource.tls.form.field.category.status'))
                                            ->options(Status::class)
                                            ->searchable()
                                            ->preload()
                                            ->default(Status::Pending)
                                            ->required(),
                                    ]),
                                Forms\Components\Section::make()
                                    ->schema([
                                        CuratorPicker::make('image')
                                            ->label(__('resource.tls.form.field.category.image'))
                                            ->directory(config('category.directory', 'categories'))
                                            ->pathGenerator(DatePathGenerator::class)
                                            ->preserveFilenames()
                                            ->helperText(__('helper.tls.text.image'))
                                    ]),
                            ])
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
                CuratorColumn::make('image')
                    ->label(__('resource.tls.table.column.category.image'))
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resource.tls.table.column.category.name'))
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('resource.tls.table.column.category.slug'))
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('parent')
                    ->label(__('resource.tls.table.column.category.parent'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('resource.tls.table.column.category.type'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('resource.tls.table.column.category.status'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resource.tls.table.column.category.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('resource.tls.table.column.category.updated_at'))
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return CategoryUtils::isResourceNavigationGroupEnabled()
            ? __('navigation.tls.navigationGroup.general')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.tls.navigationLabel.category');
    }

    public static function getNavigationIcon(): string
    {
        return __('navigation.tls.navigationIcon.category');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.tls.modelLabel.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.tls.pluralModelLabel.category');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return CategoryUtils::isResourceNavigationRegistered();
    }

    public static function getNavigationSort(): ?int
    {
        return CategoryUtils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return CategoryUtils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return CategoryUtils::isResourceNavigationBadgeEnabled()
            ? static::getModel()::count()
            : null;
    }

    public static function canGloballySearch(): bool
    {
        return CategoryUtils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }
}
