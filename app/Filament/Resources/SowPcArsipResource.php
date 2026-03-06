<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SowPcArsipResource\Pages;
use App\Filament\Resources\SowPcArsipResource\RelationManagers;
use App\Models\SowPcArsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SowPcArsipResource extends Resource
{
    protected static ?string $model = SowPcArsip::class;
    protected static ?string $navigationLabel = 'Arsip SOW PC';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?string $pluralLabel = 'Arsip SOW PC';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Arsip')
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
                    ->label('Nama Arsip')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i'),
            ])
            // Otomatis mengurutkan dari yang terbaru saat pertama dibuka
            ->defaultSort('created_at', 'desc')

            ->filters([
                Tables\Filters\SelectFilter::make('rentang_waktu')
                    ->label('Urutan Waktu')
                    ->options([
                        'yesterday' => 'Yesterday',
                        'last_week' => 'Last week',
                        'last_month' => 'Last month',
                        'this_year' => 'Earlier this year',
                        'long_ago' => 'A long time ago',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            // Data 24 jam terakhir
                            'yesterday' => $query->where('created_at', '>=', now()->subDay())
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Data 7 hari terakhir
                            'last_week' => $query->where('created_at', '>=', now()->subDays(7))
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Data 30 hari terakhir
                            'last_month' => $query->where('created_at', '>=', now()->subMonth())
                                                  ->orderBy('created_at', 'desc'),
                            
                            // Data dari awal tahun ini
                            'this_year' => $query->whereYear('created_at', now()->year)
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Balik urutan: Data paling lama muncul di atas
                            'long_ago' => $query->reorder('created_at', 'asc'),
                            
                            default => $query,
                        };
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus Arsip')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Arsip SOW PC')
                        ->modalDescription('Semua data di dalam arsip ini juga akan ikut terhapus secara permanen.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                ->label('Aksi') 
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
            'index' => Pages\ListSowPcArsips::route('/'),
            'view' => Pages\ViewSowPcArsip::route('/{record}'),
        ];
    }
}