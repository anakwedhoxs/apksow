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
use Illuminate\Database\Eloquent\Builder;


class RekapArsipResource extends Resource
{
    protected static ?string $model = RekapArsip::class;


    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Arsip SOW';
    protected static ?string $navigationLabel = 'Arsip Rekap';
    protected static ?string $pluralLabel = 'Rekap Arsip';
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
                            'yesterday' => $query->where('created_at', '>=', now()->subDay())
                                                 ->orderBy('created_at', 'desc'),
                            
                            'last_week' => $query->where('created_at', '>=', now()->subDays(7))
                                                 ->orderBy('created_at', 'desc'),
                            
                            'last_month' => $query->where('created_at', '>=', now()->subMonth())
                                                  ->orderBy('created_at', 'desc'),
                            
                            'this_year' => $query->whereYear('created_at', now()->year)
                                                 ->orderBy('created_at', 'desc'),
                            
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

