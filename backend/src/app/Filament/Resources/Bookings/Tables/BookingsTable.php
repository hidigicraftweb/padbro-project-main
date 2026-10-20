<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('court.name')
                    ->label('Court')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_at')
                    ->label('Date')
                    ->date('D, d M Y')
                    ->sortable(),
                TextColumn::make('time_slot')
                    ->label('Time')
                    ->state(fn ($record) => $record->time_slot)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_hours')
                    ->label('Hours')
                    ->suffix(' hrs')
                    ->sortable(),
                TextColumn::make('grand_total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                ImageColumn::make('proof_of_payment')
                    ->label('Proof')
                    ->circular()
                    ->defaultImageUrl(fn () => null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Booked At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('court_id')
                    ->label('Court')
                    ->relationship('court', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Booking')
                    ->modalDescription('Are you sure you want to approve this booking? The status will be changed to paid.')
                    ->modalSubmitActionLabel('Yes, Approve')
                    ->action(function ($record) {
                        $record->update(['status' => 'paid']);

                        Notification::make()
                            ->title('Booking Approved')
                            ->success()
                            ->body('Booking #' . $record->id . ' has been approved successfully.')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Booking')
                    ->modalDescription('Are you sure you want to reject this booking? The status will be changed to failed.')
                    ->modalSubmitActionLabel('Yes, Reject')
                    ->action(function ($record) {
                        $record->update(['status' => 'failed']);

                        Notification::make()
                            ->title('Booking Rejected')
                            ->danger()
                            ->body('Booking #' . $record->id . ' has been rejected.')
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
