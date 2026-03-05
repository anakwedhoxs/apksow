<?php


namespace App\Filament\Resources;


use App\Filament\Resources\RekapResource\Pages;
use App\Models\Rekap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class RekapResource extends Resource
{
    protected static ?string $model = Rekap::class;


    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'SOW';
    protected static ?string $navigationLabel = 'Rekap SOW';
    protected static ?int $navigationSort = 1;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->placeholder('Masukkan kategori')
                            ->required(),


                        Forms\Components\TextInput::make('merk')
                            ->label('Merk')
                            ->placeholder('Masukkan merk')
                            ->required(),


                        Forms\Components\TextInput::make('seri')
                            ->label('Seri')
                            ->placeholder('Masukkan seri')
                            ->required(),


                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('merk')
                    ->label('Merk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seri')
                    ->label('Seri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric(),
            ])
            ->actions([
                
            ])
            ->headerActions([

                Action::make('export')
                ->label('Export Rekap')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {

                    $tanggal = now()->format('d-m-Y'); 
                    $namaFile = "rekapsow-{$tanggal}.xlsx";

                    return Excel::download(
                        new \App\Exports\RekapExport(),
                        $namaFile
                    );
                }),


                Action::make('arsipkan')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('nama_arsip')
                            ->label('Nama Arsip')
                            ->placeholder('ex:SOWx-blnthn')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        if (Rekap::count() === 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Data Rekap kosong')
                                ->danger()
                                ->send();
                            return;
                        }


                        $arsip = \App\Models\RekapArsip::create([
                            'nama_arsip' => $data['nama_arsip'],
                            'keterangan' => 'Diarsipkan dari menu utama',
                        ]);


                        Rekap::chunk(50, function ($rekaps) use ($arsip) {
                            foreach ($rekaps as $rekap) {
                                \App\Models\RekapArsipItem::create([
                                    'rekap_arsip_id' => $arsip->id,
                                    'kategori' => $rekap->kategori,
                                    'merk' => $rekap->merk,
                                    'seri' => $rekap->seri,
                                    'jumlah' => $rekap->jumlah,
                                ]);
                            }
                        });


                        Rekap::truncate();


                        \Filament\Notifications\Notification::make()
                            ->title('Data berhasil diarsipkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }


    public static function getRelations(): array
    {
        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekaps::route('/'),
            'create' => Pages\CreateRekap::route('/create'),
            
        ];
    }
}





