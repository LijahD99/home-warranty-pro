<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('property.address')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('user.name')
                    ->label('Homeowner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area_of_issue')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->default('Unassigned'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'assigned' => 'info',
                        'in_progress' => 'warning',
                        'complete' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('image_path')
                    ->disk('public')
                    ->label('Image'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'assigned' => 'Assigned',
                        'in_progress' => 'In Progress',
                        'complete' => 'Complete',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->label('Assigned To'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
