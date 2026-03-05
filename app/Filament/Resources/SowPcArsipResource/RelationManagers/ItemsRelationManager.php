<?php

namespace App\Filament\Resources\SowPcArsipResource\RelationManagers;

use App\Exports\SowPcArsipExport;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Maatwebsite\Excel\Facades\Excel;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Isi Arsip';

    public function table(Table $table): Table
    {
        return $table
            // ================= FILTER =================
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

            // ================= EXPORT BUTTON =================
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
                        $namaFile = "data-sow-pc-{$tanggal}.xlsx";

                        return Excel::download(
                            new SowPcArsipExport($arsipId, $divisi),
                            $namaFile
                        );
                    }),
            ])

            // ================= COLUMNS =================
            ->columns([
                Tables\Columns\TextColumn::make('case.Merk')->label('Case')->searchable(),
                Tables\Columns\TextColumn::make('psu.Merk')->label('PSU')->searchable(),
                Tables\Columns\TextColumn::make('prosesor.Merk')->label('Prosesor')->searchable(),
                Tables\Columns\TextColumn::make('ram.Merk')->label('RAM')->searchable(),
                Tables\Columns\TextColumn::make('motherboard.Merk')->label('Motherboard')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_penggunaan')->label('Tanggal Penggunaan')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('tanggal_perbaikan')->label('Tanggal Perbaikan')->date('d/m/Y'),
                Tables\Columns\IconColumn::make('helpdesk')->boolean(),
                Tables\Columns\IconColumn::make('form')->boolean(),
                Tables\Columns\TextColumn::make('nomor_perbaikan')->searchable(),
                Tables\Columns\TextColumn::make('hostname.nama')->label('Hostname')->searchable(),
                Tables\Columns\TextColumn::make('divisi')->searchable(),
                Tables\Columns\TextColumn::make('keterangan')->wrap(),
                Tables\Columns\TextColumn::make('pic.nama')->label('PIC')->searchable(),
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

            // ================= VIEW DETAIL =================
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Item SOW PC')
                    ->form([
                        // Spek Hardware PC (3 kolom)
                        Forms\Components\Section::make('Spesifikasi Hardware')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('case')
                                            ->label('Case')
                                            ->formatStateUsing(fn ($record) => $record->case?->Merk)
                                            ->disabled(),
                                        Forms\Components\TextInput::make('psu')
                                            ->label('PSU')
                                            ->formatStateUsing(fn ($record) => $record->psu?->Merk)
                                            ->disabled(),
                                        Forms\Components\TextInput::make('prosesor')
                                            ->label('Prosesor')
                                            ->formatStateUsing(fn ($record) => $record->prosesor?->Merk)
                                            ->disabled(),
                                        Forms\Components\TextInput::make('ram')
                                            ->label('RAM')
                                            ->formatStateUsing(fn ($record) => $record->ram?->Merk)
                                            ->disabled(),
                                        Forms\Components\TextInput::make('motherboard')
                                            ->label('Motherboard')
                                            ->formatStateUsing(fn ($record) => $record->motherboard?->Merk)
                                            ->disabled(),
                                    ]),
                            ]),

                        // Info Penggunaan (2 kolom)
                        Forms\Components\Section::make('Informasi Penggunaan')
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_penggunaan')->label('Tanggal Penggunaan')->disabled(),
                                Forms\Components\DatePicker::make('tanggal_perbaikan')->label('Tanggal Perbaikan')->disabled(),
                                Forms\Components\TextInput::make('hostname_label')
                                    ->label('Hostname')
                                    ->formatStateUsing(fn ($record) => $record->hostname?->nama)
                                    ->disabled(),
                                Forms\Components\TextInput::make('divisi')->label('Divisi')->disabled(),
                                Forms\Components\TextInput::make('pic_label')
                                    ->label('PIC')
                                    ->formatStateUsing(fn ($record) => $record->pic?->nama)
                                    ->disabled(),
                            ])->columns(2),

                        // Status & Foto
                        Forms\Components\Section::make('Status & Lampiran')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Checkbox::make('helpdesk')->label('Helpdesk')->disabled(),
                                        Forms\Components\Checkbox::make('form')->label('Form')->disabled(),
                                        Forms\Components\TextInput::make('nomor_perbaikan')->label('Nomor Perbaikan')->disabled(),
                                    ]),
                                Forms\Components\Textarea::make('keterangan')->label('Keterangan')->disabled()->columnSpanFull(),
                                Forms\Components\FileUpload::make('foto')
                                    ->label('Foto Lampiran')
                                    ->disk('public')
                                    ->image()
                                    ->downloadable()
                                    ->disabled()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}