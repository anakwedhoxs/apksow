<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SowPcArsipResource\Pages;
use App\Filament\Resources\SowPcArsipResource\RelationManagers;
use App\Models\SowPcArsip;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SowPcArsipResource extends Resource
{
    protected static ?string $model = SowPcArsip::class;
    protected static ?string $navigationLabel = 'Arsip SOW PC';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('nama_arsip')->label('Nama Arsip')->disabled(),
                Forms\Components\Textarea::make('keterangan')->label('Keterangan')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nama_arsip')->label('Nama Arsip')->searchable(),
            Tables\Columns\TextColumn::make('items_count')->label('Jumlah Item')->counts('items'),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
        ])->filters([])
          ->actions([
              Tables\Actions\ViewAction::make(),
              Tables\Actions\DeleteAction::make()
                  ->label('Hapus Arsip')
                  ->requiresConfirmation()
                  ->modalHeading('Hapus Arsip SOW PC')
                  ->modalDescription('Semua data di dalam arsip ini juga akan ikut terhapus.')
                  ->modalSubmitActionLabel('Ya, Hapus'),
          ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSowPcArsips::route('/'),
            'view' => Pages\ViewSowPcArsip::route('/{record}'),
        ];
    }
}