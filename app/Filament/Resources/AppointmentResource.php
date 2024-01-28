<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Gender;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Filament\Resources\Resource;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Count;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Forms\Components\Section::make([
                    Forms\Components\Select::make('patient_id')
                        ->relationship('patient', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->createOptionForm([
                            Forms\Components\Select::make('doctor_id')
                                ->relationship('doctor', 'first_name')
                                ->native(false)
                                ->required(),
                            Forms\Components\Section::make([
                                Forms\Components\TextInput::make('name')
                                    ->label('Patient Name')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email(),
                                Forms\Components\TextInput::make('address')
                                    ->required(),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->date('d/m/Y')
                                    ->required(),
                                Forms\Components\TextInput::make('age')
                                    ->required()
                                    ->numeric(),
                                // Forms\Components\Select::make('gender')
                                //     // ->native(false)
                                //     ->options(Gender::class),
                                Forms\Components\Select::make('gender')
                                    ->native(false)
                                    ->options(Gender::class)
                                    ->preload()
                                    ->required(),
                                Forms\Components\FileUpload::make('photo')
                                    ->image()
                                    ->imageEditor(),
                            ])->columns(4),

                        ])
                        ->required(),
                    Forms\Components\DatePicker::make('date')
                        ->required(),
                    Forms\Components\TimePicker::make('start')
                        ->required()
                        ->seconds(false)
                        // ->native(false)
                        // ->minutesStep(10)
                        ->displayFormat('h:i A'),
                    Forms\Components\TimePicker::make('end')
                        ->required()
                        ->seconds(false)
                        // ->native(false)
                        // ->minutesStep(10)
                        ->displayFormat('h:i A'),
                    Forms\Components\Select::make('status')
                        ->native(false)
                        ->options(AppointmentStatus::class)
                        ->visibleOn(Pages\EditAppointment::class),
                ])->columns(2),
                Forms\Components\Textarea::make('description')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.first_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start'),
                Tables\Columns\TextColumn::make('end'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('Doctor')
                    ->relationship('Doctor', 'first_name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Doctor')
                    ->indicator('Doctor'),
                SelectFilter::make('Patient')
                    ->relationship('Patient', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Patient')
                    ->indicator('Patient'),
                SelectFilter::make('status')
                    ->searchable()
                    ->preload()
                    ->options(AppointmentStatus::class)
                    ->label('Filter by Status')
                    ->indicator('status'),
            ] , layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Confirmed;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => $record->status == AppointmentStatus::Created)
                    ->color('success')
                    ->icon('heroicon-o-check'),
                Tables\Actions\Action::make('cancel')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Canceled;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => $record->status != AppointmentStatus::Canceled)
                    ->color('danger')
                    ->icon('heroicon-o-x-mark'),
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
