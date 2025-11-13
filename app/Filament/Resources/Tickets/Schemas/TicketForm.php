<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'address')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Homeowner'),
                Select::make('assigned_to')
                    ->relationship('assignedTo', 'name', fn($query) => $query->whereIn('role', ['builder', 'admin']))
                    ->searchable()
                    ->preload()
                    ->label('Assigned To'),
                TextInput::make('area_of_issue')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->image()
                    ->disk('public')
                    ->directory('tickets')
                    ->maxSize(2048)
                    ->label('Image'),
                Select::make('status')
                    ->required()
                    ->options([
                        'submitted' => 'Submitted',
                        'assigned' => 'Assigned',
                        'in_progress' => 'In Progress',
                        'complete' => 'Complete',
                        'closed' => 'Closed',
                    ])
                    ->default('submitted'),
            ]);
    }
}
