<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokumentasiResource\Pages;
use App\Models\Dokumentasi;
use App\Models\DokumentasiArsip;
use App\Exports\DokumentasiExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;


class DokumentasiResource extends Resource
{
    protected static ?string $model = Dokumentasi::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-s-photo';
    protected static ?string $navigationLabel = 'Dokumentasi';
    protected static ?string $navigationGroup = 'SOW';

    /* ================= FORM ================= */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required(),

            Forms\Components\FileUpload::make('foto')
                ->image()
                ->disk('public')
                ->directory('uploads')
                ->visibility('public')
                ->downloadable()
                ->nullable(),
        ]);
    }

    /* ================= TABLE ================= */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang'),
                    

                Tables\Columns\ImageColumn::make('foto')
                    ->disk('public')
                    ->height(40),
            ])
            ->headerActions([

                /* ===== ARSIPKAN ===== */
                Action::make('arsipkan')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Arsip')
                            ->placeholder('ex:SOWx-blnthn') 
                            ->required(),
                    ])
                    ->action(function (array $data) {

                        if (Dokumentasi::count() === 0) {
                            Notification::make()
                                ->title('Data dokumentasi kosong')
                                ->danger()
                                ->send();
                            return;
                        }

                        // buat arsip
                        $arsip = DokumentasiArsip::create([
                            'judul' => $data['judul'],
                        ]);

                        // pindahkan data ke arsip items
                        Dokumentasi::all()->each(function ($doc) use ($arsip) {
                            $arsip->items()->create([
                                'nama_barang' => $doc->nama_barang,
                                'foto' => $doc->foto,
                            ]);
                        });

                        // hapus data aktif
                        Dokumentasi::truncate();

                        Notification::make()
                            ->title('Dokumentasi berhasil diarsipkan')
                            ->success()
                            ->send();
                    }),

                /* ===== EXPORT ===== */
               Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    $tanggal = now()->format('d-m-Y');
                    $namaFile = "data-dokumentasi-{$tanggal}.xlsx";

                    return Excel::download(
                        new DokumentasiExport(),
                        $namaFile
                    );
                }),

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => route('dokumentasi.download', $record))
                    ->openUrlInNewTab(true)
                    ->visible(fn($record) => !empty($record->foto)),
                Tables\Actions\DeleteAction::make(),
                ])
                ->label('More') 
                ->icon('heroicon-m-ellipsis-vertical') 
                ->color('primary')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDokumentasis::route('/'),
            'create' => Pages\CreateDokumentasi::route('/create'),
            'edit'   => Pages\EditDokumentasi::route('/{record}/edit'),
        ];
    }
}
