<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SowArsipResource\Pages;
use App\Models\SowArsip;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SowArsipResource extends Resource
{
    protected static ?string $model = SowArsip::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip SOW';
    protected static ?string $navigationGroup = 'Arsip SOW';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Arsip')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Arsip')
                    ->dateTime('d/m/Y H:i'),
                    
            ])
            // Default: data terbaru selalu di atas
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
                            // Data dari kemarin sampai hari ini
                            'yesterday' => $query->where('created_at', '>=', now()->subDay())
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Data dalam 7 hari terakhir
                            'last_week' => $query->where('created_at', '>=', now()->subDays(7))
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Data dalam 30 hari terakhir
                            'last_month' => $query->where('created_at', '>=', now()->subMonth())
                                                  ->orderBy('created_at', 'desc'),
                            
                            // Data dari awal tahun ini
                            'this_year' => $query->whereYear('created_at', now()->year)
                                                 ->orderBy('created_at', 'desc'),
                            
                            // Data paling lama (kebalikan dari default)
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
                        ->modalHeading('Hapus Arsip SOW')
                        ->modalDescription('Semua data di dalam arsip ini juga akan ikut terhapus.')
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
            \App\Filament\Resources\SowArsipResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSowArsips::route('/'),
            'view'  => Pages\ViewSowArsip::route('/{record}'),
        ];
    }
}