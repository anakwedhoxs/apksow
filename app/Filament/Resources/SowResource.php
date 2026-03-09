<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SOWResource\Pages;
use App\Models\Sow;
use App\Models\Inventaris;
use App\Exports\SowExport;
use App\Models\SowArsip;
use App\Models\SowArsipItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\IconColumn;


class SOWResource extends Resource
{
    protected static ?string $model = Sow::class;
    protected static ?string $navigationIcon = 'heroicon-s-queue-list';
    protected static ?string $navigationLabel = 'Data SOW';
    protected static ?string $navigationGroup = 'SOW';
    protected static ?string $pluralLabel = 'SOW';
    protected static ?int $navigationSort = 1;

    /* ================= FORM ================= */
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([

                /* ================= CREATE & EDIT ================= */

                Forms\Components\Select::make('kategori')
                    ->label('Hardware')
                    ->options(
                        Inventaris::query()
                            ->select('Kategori')
                            ->distinct()
                            ->pluck('Kategori', 'Kategori')
                    )
                    ->default(fn ($record) => $record?->inventaris?->Kategori) 
                    ->live()
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\CreateSOW ||
                        $livewire instanceof Pages\EditSOW
                    )
                    ->afterStateUpdated(function (callable $set) {
                        $set('merk', null);
                        $set('inventaris_id', null);
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('merk')
                    ->label('Merk')
                    ->options(fn (callable $get) =>
                        $get('kategori')
                            ? Inventaris::where('Kategori', $get('kategori'))
                                ->select('Merk')
                                ->distinct()
                                ->pluck('Merk', 'Merk')
                            : []
                    )
                    ->default(fn ($record) => $record?->inventaris?->Merk)
                    ->live()
                    ->dehydrated(false)
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\CreateSOW ||
                        $livewire instanceof Pages\EditSOW
                    )
                    ->searchable()
                    ->afterStateUpdated(fn (callable $set) =>
                        $set('inventaris_id', null)
                    )
                    ->required(),

                Forms\Components\Select::make('inventaris_id')
                    ->label('Seri')
                    ->options(fn (callable $get) =>
                        $get('kategori') && $get('merk')
                            ? Inventaris::where('Kategori', $get('kategori'))
                                ->where('Merk', $get('merk'))
                                ->pluck('Seri', 'id')
                            : []
                    )
                    ->searchable()
                    ->required()
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\CreateSOW ||
                        $livewire instanceof Pages\EditSOW
                    ),

                /* ================= VIEW MODE ================= */

                Forms\Components\TextInput::make('hardware_view')
                    ->label('Hardware')
                    ->default(fn ($record) => $record?->inventaris?->Kategori)
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\ViewSOW
                    ),

                Forms\Components\TextInput::make('merk_view')
                    ->label('Merk')
                    ->default(fn ($record) => $record?->inventaris?->Merk)
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\ViewSOW
                    ),

                Forms\Components\TextInput::make('seri_view')
                    ->label('Seri')
                    ->default(fn ($record) => $record?->inventaris?->Seri)
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($livewire) =>
                        $livewire instanceof Pages\ViewSOW
                    ),

                /* ================= FIELD LAIN ================= */

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
            ]),

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

    /* ================= TABLE ================= */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inventaris.Kategori')->label('Hardware'),
                Tables\Columns\TextColumn::make('inventaris.Merk')->label('Merk'),
                Tables\Columns\TextColumn::make('inventaris.Seri')->label('Seri'),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('divisi'),
                Tables\Columns\TextColumn::make('pic.nama')
                    ->label('PIC')
                    ->searchable(),
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
    ->disabled(fn () => Sow::whereNull('status')->orWhere('status', true)->exists())
    ->action(function ($livewire) {

        // Ambil filter divisi dari table
        $divisi = $livewire->tableFilters['divisi']['value'] ?? null;

        $tanggal = now()->format('d-m-Y');
        $namaFile = "data-sow-{$divisi}-{$tanggal}.xlsx";

        return Excel::download(
            new SowExport($divisi),
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
                    Sow::query()->update(['status' => false]);
                    Notification::make()
                        ->title('Semua data berhasil di Accept')
                        ->success()
                        ->send();
                }),

           Action::make('arsipkan')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->form([
                         Forms\Components\TextInput::make('judul')
                            ->label('Judul Arsip')
                            ->placeholder('ex:SOWx-blnthn') 
                            ->required(),
                    ])
                    ->disabled(fn () => Sow::whereNull('status')->orWhere('status', true)->exists())
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        if (Sow::count() === 0) {
                            Notification::make()->title('Data SOW kosong')->danger()->send();
                            return;
                        }

                        $arsip = SowArsip::create(['judul' => $data['judul']]);

                        Sow::chunk(50, function ($sows) use ($arsip) {
                            foreach ($sows as $sow) {
                                SowArsipItem::create([
                                    'sow_arsip_id' => $arsip->id,
                                    'inventaris_id' => $sow->inventaris_id,
                                    'tanggal_penggunaan' => $sow->tanggal_penggunaan,
                                    'tanggal_perbaikan' => $sow->tanggal_perbaikan,
                                    'helpdesk' => $sow->helpdesk,
                                    'form' => $sow->form,
                                    'nomor_perbaikan' => $sow->nomor_perbaikan,
                                    'hostname' => $sow->hostname?->nama,
                                    'divisi' => $sow->divisi,
                                    'keterangan' => $sow->keterangan,
                                    'pic' => $sow->pic->nama,
                                ]);
                            }
                        });

                        Sow::truncate();

                        Notification::make()->title('Data berhasil diarsipkan')->success()->send();
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
            'index' => Pages\ListSOWS::route('/'),
            'create' => Pages\CreateSOW::route('/create'),
            'view' => Pages\ViewSOW::route('/{record}'),
            'edit' => Pages\EditSOW::route('/{record}/edit'),
        ];
    }
}
