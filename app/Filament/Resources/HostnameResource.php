<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HostnameResource\Pages;
use App\Models\Hostname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action; // ✅ tambahkan ini
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HostnameImport;
use Filament\Notifications\Notification;

class HostnameResource extends Resource
{
    protected static ?string $model = Hostname::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationLabel = 'Data Hostname';
    protected static ?string $navigationGroup = 'Master Data';
        protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->label('Hostname')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Hostname')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])

            ->headerActions([
                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn () => auth()->user()->role === 'super_admin') 
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

                        Excel::import(new HostnameImport, $path);

                        Notification::make()
                            ->title('Import Hostname berhasil!')
                            ->success()
                            ->send();
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostnames::route('/'),
            'create' => Pages\CreateHostname::route('/create'),
            'edit' => Pages\EditHostname::route('/{record}/edit'),
        ];
    }
}