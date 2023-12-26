<?php

namespace App\Filament\Resources;

use App\Core\Enums\Status;
use App\Core\Enums\Verified;
use App\Core\Utils\UserUtils;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use stdClass;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

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
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resource.tls.form.field.user.name'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    })
                                    ->maxLength(255)
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('resource.tls.form.field.user.email'))
                                    ->maxLength(255)
                                    ->email()
                                    ->unique(User::class, 'email', fn ($record) => $record)
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('resource.tls.form.field.user.phone'))
                                    ->maxLength(255)
                                    ->unique(User::class, 'phone', fn ($record) => $record)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                        Forms\Components\Section::make(__('helper.tls.section.title.avatar'))
                            ->description(__('helper.tls.section.description.avatar'))
                            ->icon(__('helper.tls.section.icon.avatar'))
                            ->schema([
                                CuratorPicker::make('avatar')
                                    ->label(__('resource.tls.form.field.user.avatar'))
                                    ->directory(config('avatar.directory', 'avatars'))
                                    ->pathGenerator(DatePathGenerator::class)
                                    ->preserveFilenames()
                                    ->required()
                            ])
                            ->columns(2)
                            ->collapsible(),
                        Forms\Components\Section::make(__('helper.tls.section.title.properties'))
                            ->description(__('helper.tls.section.description.properties'))
                            ->icon(__('helper.tls.section.icon.properties'))
                            ->schema([
                                Forms\Components\Radio::make('verified')
                                    ->label(__('resource.tls.form.field.user.verified'))
                                    ->options(Verified::class)
                                    ->inline()
                                    ->default(Verified::NotVerified)
                                    ->required(),
                                Forms\Components\Radio::make('status')
                                    ->label(__('resource.tls.form.field.user.status'))
                                    ->options(Status::class)
                                    ->default(Status::Pending)
                                    ->inline()
                                    ->required(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('resource.tls.form.field.user.slug'))
                                    ->maxLength(255)
                                    ->dehydrated()
                                    ->unique(User::class, 'slug', ignoreRecord: true)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->label(__('resource.tls.form.field.user.role'))
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->required(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\DatePicker::make('email_verified_at')
                                    ->label(__('resource.tls.form.field.user.email_verified_at'))
                                    ->readOnly(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('resource.tls.form.field.user.created_at'))
                                    ->content(fn (
                                        ?User $record
                                    ): string => $record ? $record->created_at->diffForHumans() : '-'),
                                Placeholder::make('updated_at')
                                    ->label(__('resource.form.field.user.updated_at'))
                                    ->content(fn (
                                        ?User $record
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
                CuratorColumn::make('avatar')
                    ->label(__('resource.tls.table.column.user.avatar'))
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resource.tls.table.column.user.name'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('resource.tls.table.column.user.email'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('resource.tls.table.column.user.phone'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('resource.tls.table.column.user.status'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('verified')
                    ->label(__('resource.tls.table.column.user.verified'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('resource.tls.table.column.user.role'))
                    ->badge()
                    ->colors(['primary'])
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resource.tls.table.column.user.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('resource.tls.table.column.user.updated_at'))
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return UserUtils::isResourceNavigationGroupEnabled()
            ? __('navigation.tls.navigationGroup.user')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.tls.navigationLabel.user');
    }

    public static function getNavigationIcon(): string
    {
        return __('navigation.tls.navigationIcon.user');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.tls.modelLabel.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.tls.pluralModelLabel.user');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserUtils::isResourceNavigationRegistered();
    }

    public static function getNavigationSort(): ?int
    {
        return UserUtils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return UserUtils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return UserUtils::isResourceNavigationBadgeEnabled()
            ? static::getModel()::count()
            : null;
    }

    public static function canGloballySearch(): bool
    {
        return UserUtils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }
}
