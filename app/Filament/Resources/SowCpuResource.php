<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SowCpuResource\Pages;
use App\Filament\Resources\SowCpuResource\RelationManagers;
use App\Models\SowCpu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SowCpuExport;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;

class SowCpuResource extends Resource
{
    protected static ?string $model = SowCpu::class;


    protected static ?string $navigationIcon = 'heroicon-s-queue-list';
    protected static ?string $navigationLabel = 'Data SOW CPU';
    protected static ?string $navigationGroup = 'SOW';
    protected static ?string $pluralLabel = 'SOW CPU';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('prosesor_id')
                        ->label('PROCESSOR')
                    ->options(
                        \App\Models\Inventaris::query()
                            ->where('kategori', 'PROCESSOR')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->Merk} {$item->Seri}"];
                            })
                            ->toArray()
                    )
                    ->searchable(),


                Forms\Components\Select::make('ram_id')
                    ->label('RAM')
                    ->options(
                        \App\Models\Inventaris::query()
                            ->where('kategori', 'RAM')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->Merk} {$item->Seri}"];
                            })
                            ->toArray()
                    )
                    ->searchable(),
                   


                Forms\Components\Select::make('motherboard_id')
                    ->label('MOTHERBOARD')
                    ->options(
                        \App\Models\Inventaris::query()
                            ->where('kategori', 'MOTHERBOARD')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->Merk} {$item->Seri}"];
                            })
                            ->toArray()
                    )
                    ->searchable()
                     ->required(),
                   


                    Forms\Components\Select::make('pic_id')
                    ->label('PIC')
                    ->relationship('pic', 'nama')
                    ->searchable()
                    ->preload(),


                Forms\Components\DatePicker::make('tanggal_penggunaan'),
                Forms\Components\DatePicker::make('tanggal_perbaikan'),
                Forms\Components\Grid::make(2)
                    ->columnSpan(2)
                    ->schema([
                Forms\Components\Checkbox::make('form'),
                Forms\Components\Checkbox::make('helpdesk'),
                    ]),
                Forms\Components\TextInput::make('nomor_perbaikan'),
                Forms\Components\Select::make('hostname_id')
                    ->label('Hostname')
                    ->relationship('hostname', 'nama')
                    ->searchable()
                    ->preload(),


                Forms\Components\Select::make('divisi')
                    ->options([
                        'MCP' => 'MCP',
                        'MKM' => 'MKM',
                        'PPG' => 'PPG',
                        'MKP' => 'MKP',
                        'PPM' => 'PPM',
                    ])
                    ->searchable()
                    ->preload(),
           
            Forms\Components\Textarea::make('keterangan')->columnSpanFull(),


            Forms\Components\FileUpload::make('foto')
            ->label('Foto')
            ->image() // preview gambar
            ->disk('public') // simpan di disk public
            ->directory('uploads') // folder penyimpanan
            ->visibility('public') // agar bisa diakses publik
            ->downloadable() // aktifkan tombol download
            ->columnSpanFull(),


             Forms\Components\Toggle::make('status')
                ->label('Rejected')
                ->helperText('ON = Rejected | OFF = Accept')
                ->onColor('danger')
                ->offColor('success')
                ->default(false)
                ->visible(fn ($record) => auth()->user()?->hasRole('super_admin') && $record !== null),


       


                   
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prosesor.Merk')
                    ->label('Prosesor'),


                Tables\Columns\TextColumn::make('ram.Merk')
                    ->label('RAM'),


                Tables\Columns\TextColumn::make('motherboard.Merk')
                    ->label('Motherboard'),
                    Tables\Columns\TextColumn::make('tanggal_penggunaan')->date(),
                Tables\Columns\TextColumn::make('tanggal_perbaikan')->date(),
                IconColumn::make('form')
                    ->label('Form')
                    ->boolean(),

                IconColumn::make('helpdesk')
                    ->label('Helpdesk')
                    ->boolean(),
                Tables\Columns\TextColumn::make('nomor_perbaikan'),
                Tables\Columns\TextColumn::make('hostname.nama')
                    ->label('Hostname')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('divisi'),
                Tables\Columns\TextColumn::make('pic.nama')->label('PIC')->default('-')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => false,
                        'danger' => true,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Rejected' : 'Accept'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisi')
                    ->options([
                        'MCP' => 'MCP',
                        'MKM' => 'MKM',
                        'PPG' => 'PPG',
                        'MKP' => 'MKP',
                        'PPM' => 'PPM',
                    ]),
       
            ])
            ->headerActions([
            Action::make('export')
    ->label('Export')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('primary')
    ->disabled(fn () => SowCpu::whereNull('status')->orWhere('status', true)->exists())
    ->action(function ($livewire) {

        // ambil filter divisi dari table
        $divisi = $livewire->tableFilters['divisi']['value'] ?? null;

        $tanggal = now()->format('d-m-Y');
        $namaFile = "data-sow-CPU-{$divisi}-{$tanggal}.xlsx";

        return Excel::download(
            new SowCpuExport($divisi),
            $namaFile
        );
    }),
                         Action::make('accept')
                ->label('Accept')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()?->hasRole('super_admin'))
                ->action(function () {
                    SowCpu::query()->update(['status' => false]);
                    Notification::make()
                        ->title('Semua data berhasil di Accept')
                        ->success()
                        ->send();
                }),

                Action::make('arsipkan')
    ->label('Arsipkan')
    ->disabled(fn () => SowCpu::whereNull('status')->orWhere('status', true)->exists())
    ->icon('heroicon-o-archive-box')
    ->color('warning')
    ->form([
        Forms\Components\TextInput::make('nama_arsip')
            ->label('Nama Arsip')
            ->placeholder('ex:SOWx-blnthn') 
            ->required(),
    ])
    ->requiresConfirmation()
    ->action(function(array $data) {
        if (\App\Models\SowCpu::count() === 0) {
            Notification::make()
                ->title('Data SOW CPU kosong')
                ->danger()
                ->send();
            return;
        }

        // Buat arsip
        $arsip = \App\Models\SowCpuArsip::create([
            'nama_arsip' => $data['nama_arsip'],
            'keterangan' => 'Diarsipkan dari menu SOW CPU',
        ]);

        // Masukkan semua SOW CPU ke arsip item
        \App\Models\SowCpu::chunk(50, function($cpus) use ($arsip) {
            foreach($cpus as $cpu) {
                \App\Models\SowCpuArsipItem::create([
                    'sow_cpu_arsip_id' => $arsip->id,
                    'prosesor_id' => $cpu->prosesor_id,
                    'ram_id' => $cpu->ram_id,
                    'motherboard_id' => $cpu->motherboard_id,
                    'tanggal_penggunaan' => $cpu->tanggal_penggunaan,
                    'tanggal_perbaikan' => $cpu->tanggal_perbaikan,
                    'helpdesk' => $cpu->helpdesk,
                    'form' => $cpu->form,
                    'nomor_perbaikan' => $cpu->nomor_perbaikan,
                    'hostname_id' => $cpu->hostname_id,
                    'divisi' => $cpu->divisi,
                    'pic_id' => $cpu->pic_id,
                    'keterangan' => $cpu->keterangan,
                    'foto' => $cpu->foto,
                    'status' => $cpu->status,
                ]);
            }
        });

        // Kosongkan tabel SOW CPU
        \App\Models\SowCpu::truncate();

        Notification::make()
            ->title('Data SOW CPU berhasil diarsipkan')
            ->success()
            ->send();
    }),
            ])
             ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSowCpus::route('/'),
            'create' => Pages\CreateSowCpu::route('/create'),
            'edit' => Pages\EditSowCpu::route('/{record}/edit'),
        ];
    }
}
