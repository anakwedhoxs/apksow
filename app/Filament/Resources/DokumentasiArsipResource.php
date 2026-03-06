<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokumentasiArsipResource\Pages;
use App\Filament\Resources\DokumentasiArsipResource\RelationManagers\ItemsRelationManager;
use App\Models\DokumentasiArsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DokumentasiArsipResource extends Resource
{
    protected static ?string $model = DokumentasiArsip::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip Dokumentasi';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->label('Judul Arsip')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Arsip')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Arsip')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(), // 👈 penting
                Tables\Actions\DeleteAction::make()
                ->label('Hapus Arsip')
                ->requiresConfirmation()
                ->modalHeading('Hapus Arsip SOW')
                ->modalDescription('Semua data di dalam arsip ini juga akan ikut terhapus.')
                ->modalSubmitActionLabel('Ya, Hapus'),
            ])
                ->label('More') 
                ->icon('heroicon-m-ellipsis-vertical') 
                ->color('primary')
                
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class, // 👈 WAJIB
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDokumentasiArsips::route('/'),
            'create' => Pages\CreateDokumentasiArsip::route('/create'),
            'view'   => Pages\ViewDokumentasiArsip::route('/{record}'),
            'edit'   => Pages\EditDokumentasiArsip::route('/{record}/edit'),
        ];
    }
}
