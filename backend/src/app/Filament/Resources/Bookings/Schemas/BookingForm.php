<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Court;
use App\Models\CourtBookingSlot;
use App\Models\CourtTimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Booking Details')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Select::make('court_id')
                                ->label('Court')
                                ->options(Court::where('status', 'active')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    // Reset time selections when court changes
                                    $set('start_time', null);
                                    $set('end_time', null);
                                    $set('total_hours', null);

                                    if ($state) {
                                        $court = Court::find($state);
                                        if ($court) {
                                            $set('price_per_hour_amount', (int) $court->price_per_hour);
                                        }
                                    } else {
                                        $set('price_per_hour_amount', null);
                                        $set('sub_total_amount', null);
                                        $set('total_tax_amount', null);
                                        $set('grand_total_amount', null);
                                    }
                                }),
                            Select::make('user_id')
                                ->label('Customer')
                                ->options(fn () => User::role('customer')->get()->mapWithKeys(fn ($user) => [$user->id => $user->name . ' (' . $user->email . ')']))
                                ->searchable()
                                ->required(),
                            DatePicker::make('booking_date')
                                ->label('Booking Date')
                                ->required()
                                ->minDate(now())
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function (callable $set) {
                                    // Reset time selections when date changes
                                    $set('start_time', null);
                                    $set('end_time', null);
                                    $set('total_hours', null);
                                    $set('sub_total_amount', null);
                                    $set('total_tax_amount', null);
                                    $set('grand_total_amount', null);
                                }),
                            Select::make('start_time')
                                ->label('Start Time')
                                ->options(function (callable $get) {
                                    return self::getAvailableTimeSlots($get('court_id'), $get('booking_date'));
                                })
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    $set('end_time', null); // Reset end time when start changes
                                    self::calculateTotalHoursAndPrice($set, $get);
                                })
                                ->helperText('Select court and date first'),
                            Select::make('end_time')
                                ->label('End Time')
                                ->options(function (callable $get) {
                                    return self::getAvailableTimeSlots($get('court_id'), $get('booking_date'), $get('start_time'));
                                })
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function (callable $set, callable $get) {
                                    self::calculateTotalHoursAndPrice($set, $get);
                                })
                                ->helperText('Select start time first'),
                            TextInput::make('total_hours')
                                ->label('Total Hours')
                                ->required()
                                ->numeric()
                                ->readOnly()
                                ->suffix('hours'),
                        ])
                        ->columns(2),

                    Step::make('Pricing')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('price_per_hour_amount')
                                ->label('Price per Hour')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->helperText('Auto-filled from selected court'),
                            TextInput::make('sub_total_amount')
                                ->label('Sub Total')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->helperText('Price per hour × Total hours'),
                            TextInput::make('total_tax_amount')
                                ->label('Tax Amount (11%)')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->helperText('11% of sub total'),
                            TextInput::make('grand_total_amount')
                                ->label('Grand Total')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->helperText('Sub total + Tax'),
                        ])
                        ->columns(4),

                    Step::make('Payment')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Select::make('status')
                                ->label('Booking Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                ])
                                ->default('pending')
                                ->required(),
                            FileUpload::make('proof_of_payment')
                                ->label('Proof of Payment')
                                ->image()
                                ->directory('bookings/proofs')
                                ->imageEditor()
                                ->maxSize(2048),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
            ]);
    }

    protected static function getAvailableTimeSlots(?int $courtId, ?string $bookingDate, ?string $startTime = null): array
    {
        if (!$courtId || !$bookingDate) {
            return [];
        }

        $date = Carbon::parse($bookingDate);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Get court time slots for this day
        $timeSlots = CourtTimeSlot::where('court_id', $courtId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($timeSlots->isEmpty()) {
            return [];
        }

        // Get already booked slots for this court and date
        $bookedSlots = CourtBookingSlot::where('court_id', $courtId)
            ->whereDate('slot_at', $date)
            ->where(function ($query) {
                $query->where('status', 'booked')
                    ->orWhere(function ($q) {
                        $q->where('status', 'locked')
                            ->where('expires_at', '>', now());
                    });
            })
            ->pluck('slot_at')
            ->map(fn ($slot) => Carbon::parse($slot)->format('H:i'))
            ->toArray();

        // Build available time options
        $options = [];

        foreach ($timeSlots as $slot) {
            $start = Carbon::parse($slot->start_time);
            $end = Carbon::parse($slot->end_time);

            // Generate hourly slots between start and end time
            while ($start->lt($end)) {
                $timeString = $start->format('H:i');

                // Skip if already booked
                if (!in_array($timeString, $bookedSlots)) {
                    // For end time, only show times after start time
                    if ($startTime === null) {
                        $options[$timeString] = $timeString;
                    } else {
                        $startTimeHour = (int) substr($startTime, 0, 2);
                        $currentHour = (int) $start->format('H');
                        if ($currentHour > $startTimeHour) {
                            $options[$timeString] = $timeString;
                        }
                    }
                }

                $start->addHour();
            }
        }

        ksort($options);
        return $options;
    }

    protected static function calculateTotalHoursAndPrice(callable $set, callable $get): void
    {
        $startTime = $get('start_time');
        $endTime = $get('end_time');

        if ($startTime && $endTime) {
            // Extract hours from time strings (format: "HH:MM")
            $startHour = (int) substr($startTime, 0, 2);
            $endHour = (int) substr($endTime, 0, 2);

            if ($endHour > $startHour) {
                $totalHours = $endHour - $startHour;
                $set('total_hours', $totalHours);

                // Get price per hour from court
                $pricePerHour = (int) $get('price_per_hour_amount');
                if ($pricePerHour > 0) {
                    self::calculatePricing($set, $pricePerHour, $totalHours);
                }
            } else {
                $set('total_hours', null);
                $set('sub_total_amount', null);
                $set('total_tax_amount', null);
                $set('grand_total_amount', null);
            }
        }
    }

    protected static function calculatePricing(callable $set, int $pricePerHour, int $totalHours): void
    {
        if ($totalHours > 0 && $pricePerHour > 0) {
            $subTotal = $pricePerHour * $totalHours;
            $taxAmount = (int) round($subTotal * 0.11);
            $grandTotal = $subTotal + $taxAmount;

            $set('sub_total_amount', $subTotal);
            $set('total_tax_amount', $taxAmount);
            $set('grand_total_amount', $grandTotal);
        }
    }
}
