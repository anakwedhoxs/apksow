<?php


namespace App\Filament\Resources;


use App\Filament\Resources\InventarisResource\Pages;
use App\Models\Inventaris;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventarisImport;
use Filament\Notifications\Notification;


class InventarisResource extends Resource
{
    protected static ?string $model = Inventaris::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('Kategori')
                            ->label('Hardware')
                            ->placeholder('Masukkan Hardware')
                            ->required(),


                        Forms\Components\TextInput::make('Merk')
                            ->label('Merk')
                            ->placeholder('Masukkan Merk')
                            ->required(),


                        Forms\Components\TextInput::make('Seri')
                            ->label('Seri')
                            ->placeholder('Masukkan Seri')
                            ->required(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Kategori')->searchable(),
                Tables\Columns\TextColumn::make('Merk')->searchable(),
                Tables\Columns\TextColumn::make('Seri')->searchable(),
            ])


            ->headerActions([


                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('Upload File Excel')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->disk('public')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data) {


                        $path = storage_path('app/public/' . $data['file']);


                        Excel::import(new InventarisImport, $path);


                        Notification::make()
                            ->title('Import berhasil!')
                            ->success()
                            ->send();
                    }),


            ])


            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventaris::route('/'),
            'create' => Pages\CreateInventaris::route('/create'),
            'edit' => Pages\EditInventaris::route('/{record}/edit'),
        ];
    }
}





