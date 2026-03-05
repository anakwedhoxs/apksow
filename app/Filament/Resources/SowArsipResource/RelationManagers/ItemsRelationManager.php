<?php

namespace App\Filament\Resources\SowArsipResource\RelationManagers;

use App\Exports\SowArsipExport;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms; 
use Carbon\Carbon;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Isi Arsip';

    public function table(Table $table): Table
    {
        return $table
            /* ================= FILTER ================= */
            ->filters([
                Tables\Filters\SelectFilter::make('divisi')
                    ->label('Divisi')
                    ->options([
                        'MKM' => 'MKM',
                        'PPG' => 'PPG',
                        'MKP' => 'MKP',
                        'MCP' => 'MCP',
                        'PPM' => 'PPM',
                    ]),
            ])

            /* ================= EXPORT BUTTON ================= */
            ->headerActions([
                Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $filters = $this->getTableFiltersForm()->getState();
                    $divisi = $filters['divisi'] ?? null;
                    
                    if (is_array($divisi)) {
                        $divisi = reset($divisi);
                    }

                    $arsipId = $this->getOwnerRecord()->id;
                    $tanggal = now()->format('d-m-Y');
                    $namaFile = "data-sow-{$tanggal}.xlsx";

                    return Excel::download(
                        new SowArsipExport($arsipId, $divisi),
                        $namaFile
                    );
                }),
            ])

            /* ================= COLUMNS (Tampilan Tabel) ================= */
            ->columns([
                Tables\Columns\TextColumn::make('inventaris.Kategori')
                    ->label('Hardware')
                    ->searchable(),

                Tables\Columns\TextColumn::make('inventaris.Merk')
                    ->label('Merk')
                    ->searchable(),

                Tables\Columns\TextColumn::make('inventaris.Seri')
                    ->label('Seri')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_penggunaan')
                    ->label('Tanggal Penggunaan')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('tanggal_perbaikan')
                    ->label('Tanggal Perbaikan')
                    ->date('d/m/Y'),

                Tables\Columns\IconColumn::make('helpdesk')->boolean(),
                Tables\Columns\IconColumn::make('form')->boolean(),

                Tables\Columns\TextColumn::make('nomor_perbaikan')->searchable(),
                Tables\Columns\TextColumn::make('hostname')->searchable(),
                Tables\Columns\TextColumn::make('divisi')->searchable(),
                Tables\Columns\TextColumn::make('keterangan')->wrap(),
                Tables\Columns\TextColumn::make('pic')->searchable(),

                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->height(80)
                    ->extraImgAttributes([
                        'style' => 'object-fit: cover;',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50])

            /* ================= ACTIONS (Lihat Detail) ================= */
            ->actions([ 
                Tables\Actions\ViewAction::make() 
                    ->label('Lihat Detail') 
                    ->modalHeading('Detail Item SOW')
                    ->form([
                        // Hardware, Merk, Seri seragam dengan TextInput
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('hardware')
                                    ->label('Hardware')
                                    ->formatStateUsing(fn ($record) => $record->inventaris?->Kategori)
                                    ->disabled(),

                                Forms\Components\TextInput::make('merk')
                                    ->label('Merk')
                                    ->formatStateUsing(fn ($record) => $record->inventaris?->Merk)
                                    ->disabled(),

                                Forms\Components\TextInput::make('seri')
                                    ->label('Seri')
                                    ->formatStateUsing(fn ($record) => $record->inventaris?->Seri)
                                    ->disabled(),
                            ]),

                        Forms\Components\Section::make('Informasi Penggunaan')
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_penggunaan')->label('Tanggal Penggunaan')->disabled(), 
                                Forms\Components\DatePicker::make('tanggal_perbaikan')->label('Tanggal Perbaikan')->disabled(),
                                Forms\Components\TextInput::make('hostname')->label('Hostname')->disabled(),
                                Forms\Components\TextInput::make('divisi')->label('Divisi')->disabled(),
                                Forms\Components\TextInput::make('pic')->label('PIC')->disabled(),
                            ])->columns(2),

                        Forms\Components\Section::make('Status & Dokumen')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Checkbox::make('helpdesk')->label('Helpdesk')->disabled(), 
                                        Forms\Components\Checkbox::make('form')->label('Form')->disabled(),
                                        Forms\Components\TextInput::make('nomor_perbaikan')->label('Nomor Perbaikan')->disabled(),
                                    ]),
                                
                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->disabled()
                                    ->columnSpanFull(), 

                                // Menampilkan Foto di View Modal
                                Forms\Components\FileUpload::make('foto')
                                    ->label('Foto Lampiran')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->image() // Memastikan tampilan sebagai gambar
                                    ->disabled()
                                    ->downloadable()
                                    ->columnSpanFull(), 
                            ]),
                    ]), 
            ]);
    }
}