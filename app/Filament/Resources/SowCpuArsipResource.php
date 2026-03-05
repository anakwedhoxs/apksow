<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SowCpuArsipResource\Pages;
use App\Filament\Resources\SowCpuArsipResource\RelationManagers;
use App\Models\SowCpuArsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SowCpuArsipResource extends Resource
{
    protected static ?string $model = SowCpuArsip::class;
    protected static ?string $navigationLabel = 'Arsip SOW CPU';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?int $navigationSort = 2;
    

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
              Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSowCpuArsips::route('/'),
            'view' => Pages\ViewSowCpuArsip::route('/{record}'),
        ];
    }
}