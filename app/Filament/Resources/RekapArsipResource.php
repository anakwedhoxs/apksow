<?php


namespace App\Filament\Resources;


use App\Filament\Resources\RekapArsipResource\Pages;
use App\Filament\Resources\RekapArsipResource\RelationManagers;
use App\Models\RekapArsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class RekapArsipResource extends Resource
{
    protected static ?string $model = RekapArsip::class;


    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?string $navigationLabel = 'Arsip Rekap';
    protected static ?int $navigationSort = 10;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nama_arsip')
                            ->label('Nama Arsip')
                            ->disabled(),


                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->disabled(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_arsip')
                    ->label('Judul Arsip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
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
            'index' => Pages\ListRekapArsips::route('/'),
            'view' => Pages\ViewRekapArsip::route('/{record}'),
        ];
    }
}

